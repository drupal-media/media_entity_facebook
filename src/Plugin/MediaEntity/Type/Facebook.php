<?php

namespace Drupal\media_entity_facebook\Plugin\MediaEntity\Type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;

/**
 * Provides media type plugin for Facebook.
 *
 * @MediaType(
 *   id = "facebook",
 *   label = @Translation("Facebook"),
 *   description = @Translation("Provides business logic and metadata for Facebook.")
 * )
 *
 * @todo On the long run we could switch to the facebook API which provides WAY
 *   more fields.
 * @todo Support embed codes
 */
class Facebook extends MediaTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'source_field' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $options = [];
    $bundle = $form_state->getFormObject()->getEntity();
    $allowed_field_types = ['string', 'string_long', 'link'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => t('Field with source information'),
      '#description' => t('Field on media entity that stores facebook embed code or URL. You can create a bundle without selecting a value for this dropdown initially. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    return [
      'author_name',
      'width',
      'height',
      'url',
      'html',
    ];
  }

  /**
   * Returns the oembed data.
   *
   * @param string $url
   *   The URl to the facebook post.
   *
   * @return array
   */
  protected function oEmbed($url) {
    $url = 'https://www.facebook.com/plugins/post/oembed.json/?url=' . $url;

    $response = $client = \Drupal::httpClient()->get($url);
    return json_decode((string) $response->getBody(), TRUE);
  }

  /**
   * Runs preg_match on embed code/URL.
   *
   * @param MediaInterface $media
   *   Media object.
   *
   * @return string|false
   *   The facebook url or FALSE if there is no field.
   */
  protected function getFacebookUrl(MediaInterface $media) {
    if (isset($this->configuration['source_field'])) {
      $source_field = $this->configuration['source_field'];
      if ($media->hasField($source_field)) {
        $property_name = $media->{$source_field}->first()->mainPropertyName();
        return $media->{$source_field}->{$property_name};
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media, $name) {
    $data = $this->oEmbed($this->getFacebookUrl($media));

    switch ($name) {
      case 'author_name':
        return $data['author_name'];
      case 'width':
        return $data['width'];
      case 'height':
        return $data['height'];
      case 'url':
        return $data['url'];
      case 'html':
        return $data['html'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    // @todo Add support for thumnails on the longrun.
    return $this->getDefaultThumbnail();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return drupal_get_path('module', 'media_entity_facebook') . '/images/FB-f-Logo__blue_100.png';
  }

}

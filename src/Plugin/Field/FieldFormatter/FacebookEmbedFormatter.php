<?php

namespace Drupal\media_entity_facebook\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\media_entity_facebook\FacebookMarkup;
use Drupal\media_entity_facebook\Plugin\MediaEntity\Type\Facebook;

/**
 * Plugin implementation of the 'facebook_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "facebook_embed",
 *   label = @Translation("Facebook embed"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class FacebookEmbedFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\media_entity\MediaInterface $media_entity */
    $media_entity = $items->getEntity();

    $element = [];
    if (($type = $media_entity->getType()) && $type instanceof Facebook) {
      foreach ($items as $delta => $item) {
        $element[$delta] = [
          '#markup' => FacebookMarkup::create($type->getField($media_entity, 'html')),
        ];
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getTargetEntityTypeId() === 'media';
  }

}

<?php

namespace Drupal\tweet_feed\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'user_mentions_formatter_type' formatter.
 *
 * @FieldFormatter(
 *   id = "user_mentions_formatter_type",
 *   label = @Translation("User mentions formatter type"),
 *   field_types = {
 *     "user_mentions_field_type"
 *   }
 * )
 */
class UserMentionsFormatterType extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $values = $item->toArray();
    $url = 'http://www.twitter.com/' . Html::escape($values['mention_screen_name']);
    $display = '<div class="tweet-feed-user-mention"><a class="tweet-feed-user-mention-anchor" href="' . $url . '">' . $values['mention_name'] . '</a></div>';
    return $display;
  }

}

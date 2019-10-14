<?php

namespace Drupal\tweet_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Form controller for Tweet entity edit forms.
 *
 * @ingroup tweet_feed
 */
class TwitterFeedsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tweet_feed.twitter_feeds',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tweet_feed_twitter_feeds';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $feed_machine_name = NULL) {
    $config = $this->config('tweet_feed.twitter_feeds');
    // Set up our settings form for this particular account (new or update)
    if (!empty($feed_machine_name)) {
      $feeds = $config->get('feeds');
      $feed = $accounts[$feed_machine_name];
      if (!empty($feed)) {
        $form['feed_machine_name'] = [
          '#type' => 'hidden',
          '#value' => $feed_machine_name,
        ];
        $form['feed_update'] = [
          '#type' => 'hidden',
          '#value' => 1,
        ];
      }

    }
    else {
      // Otherwise just initialize the form so we do not have a swath of errors
      $aid = $query_type = $search_term = $list_name = $feed_name = NULL;
      $twitter_user_id = $pull_count = $new_window = $clear_prior = $timeline_id = NULL;
      $hash_taxonomy = NULL;
    }

    // Get a list of the configured accounts so we can assign this feed to a particular
    // API account for pulling and allow user to select which one to use.
    $account_config = $this->config('tweet_feed.twitter_accounts');
    $accounts = $account_config->get('accounts');
    if (!empty($accounts)) {
      foreach ($accounts as $machine_name => $account) {
        $acc[$machine_name] = $account['account_name'];
      }
    }
    else {
      // You must have an account to add a feed. If you don't then everything falls apart.
      // Warn the user here if they are trying to add a feed without an account.
      drupal_set_message('You cannot create a feed until you have added an account. Please add an account here before proceeding to add a feed.', 'error');
      $response = new RedirectResponse(\Drupal::url('tweet_feed.twitter_accounts'));
      $response->send();
    }

    $form['tweet_feed_query_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Twitter Query Settings'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#weight' => 1,
    );
    $form['tweet_feed_query_settings']['feed_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Feed Name'),
      '#description' => t('The name of the feed as it will appear on administrative forms'),
      '#default_value' => $feed_name,
      '#required' => TRUE,
      '#weight' => 1,
    );
    $form['tweet_feed_query_settings']['aid'] = array(
      '#type' => 'select',
      '#title' => t('API Account To Use For Pulling This Feed'),
      '#options' => $acc,
      '#default_value' => $aid,
      '#required' => TRUE,
      '#weight' => 2
    );

    // I was going to remove this remnant of the old module since it really isn't all that
    // necessary. But because it makes the code below infinitely more readable, I am going
    // to leave it in.
    define('QUERY_SEARCH', 1);
    define('QUERY_TIMELINE', 2);
    define('QUERY_LIST', 3);

    $form['tweet_feed_query_settings']['query_type'] = array(
      '#type' => 'radios',
      '#title' => t('Type of Twitter Query'),
      '#options' => array(
        QUERY_SEARCH => t('Twitter Search'),
        QUERY_TIMELINE => t('User Timeline Display'),
        QUERY_LIST => t('User List Display'),
      ),
      '#required' => TRUE,
      '#default_value' => $query_type,
      '#weight' => 3,
    );
    $form['tweet_feed_query_settings']['search_term'] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter Search Term'),
      '#max_length' => 64,
      '#default_value' => $search_term,
      '#states' => array(
        'visible' => array(
          ':input[name="query_type"]' => array('value' => QUERY_SEARCH),
        ),
      ),
      '#weight' => 4,
    );
    $form['tweet_feed_query_settings']['timeline_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Exact Twitter User ID For Timline Query'),
      '#description' => t('You can get this by going to mytwitterid.com'),
      '#max_length' => 64,
      '#default_value' => $timeline_id,
      '#states' => array(
        'visible' => array(
          ':input[name="query_type"]' => array(
            array('value' => QUERY_TIMELINE),
            array('value' => QUERY_LIST),
          ),
        ),
      ),
      '#weight' => 5,
    );
    $form['tweet_feed_query_settings']['list_name'] = array(
      '#type' => 'textfield',
      '#title' => t('List name'),
      '#description' => t('Enter the list name exactly as it appears on twitter.com'),
      '#max_length' => 64,
      '#default_value' => $list_name,
      '#states' => array(
        'visible' => array(
          ':input[name="query_type"]' => array('value' => QUERY_LIST),
        ),
      ),
      '#weight' => 6,
    );
    $form['tweet_feed_query_settings']['pull_count'] = array(
      '#type' => 'textfield',
      '#title' => t('Number of Items to Pull x 100'),
      '#maxlength' => 3,
      '#size' => 3,
      '#description' => t('Twitter limits tweet pulling to 1500 every 15 minutes for Timeline and List queries and 18,000 for searches. Keep this in mind when setting the pull count in conjunction with the frequency of cron/drush runs. To say nothing of PHP memory :)'),
      '#required' => TRUE,
      '#default_value' => $pull_count,
      '#weight' => 7,
    );
    $form['tweet_feed_query_settings']['new_window'] = array(
      '#type' => 'checkbox',
      '#title' => t('Open tweeted links, hashtags and mentions in a new window.'),
      '#default_value' => $new_window,
      '#weight' => 8,
    );
    $form['tweet_feed_query_settings']['hash_taxonomy'] = array(
      '#type' => 'checkbox',
      '#title' => t('Link hashtags to taxonomy terms instead of Twitter.'),
      '#default_value' => $hash_taxonomy,
      '#weight' => 9,
    );
    $form['tweet_feed_query_settings']['clear_prior'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remove all tweets in this feed prior to import.'),
      '#default_value' => $clear_prior,
      '#weight' => 10,
    );
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    //$this->config('probo.probosettings')
    //  ->set('jira_enabled', $form_state->getValue('jira_enabled'))
    //  ->save();
  }
}

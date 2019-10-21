<?php

namespace Drupal\tweet_feed\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use Drush\Commands\DrushCommands;
use Drupal\tweet_feed\Controller\TweetFeed;


/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class TweetFeedCommands extends DrushCommands {

  /**
   * Import the latest batch of tweets.
   *
   * @param $feed
   *   The machine name of the feed to be imported
   * @usage tweet_feed:import feed1
   *   Import the feeds as configured in machine name feed1.
   *
   * @command tweet_feed:import
   * @aliases tfi
   */
  public function import($feed) {
    $feed_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_feeds');
    $feeds = $feed_config->get('feeds');
    if (!empty($feeds[$feed])) {
      /** Get the account of the feed we are processing */
      $accounts_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_accounts');
      $accounts = $accounts_config->get('accounts');
      if (!empty($accounts[$feeds[$feed]['aid']])) {
        $account = $accounts[$feeds[$feed]['aid']];
        $connection = new TwitterOAuth($account['consumer_key'], $account['consumer_secret'], $account['oauth_token'], $account['oauth_token_secret']);


        $content = $connection->get("search/tweets", ['count' => 100, 'q' => 'wmata', 'tweet_mode' => 'extended']);

        //$content = $connection->get("statuses/user_timeline", ['count' => 100, 'screen_name' => 'mbags17', 'tweet_mode' => 'extended']);
        print_r($content);

      }
    }
  }

}

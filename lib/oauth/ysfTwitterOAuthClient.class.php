<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfTwitterOAuthClient will use oauth for credential management.
 *
 * @package    ysfOpenPlugin
 * @subpackage oauth
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfTwitterOAuthClient.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfTwitterOAuthClient extends ysfOAuthClient
{
  /**
   * Initializes this ysfTwitterOAuthClient.
   *
   * Available options:
   *
   *  * oauth_request_token_api:         The oauth api endpoint for getting request tokens.
   *  * oauth_authorize_token_api:       The oauth api endpoint for authorizing tokens.
   *  * oauth_access_token_api:          The oauth api endpoint for getting access tokens.
   *  * oauth_consumer_key:              The oauth consumer key
   *  * oauth_consumer_secret:           The oauth consumer secret
   *  * oauth_consumer_signature_method: The oauth signature method (PLAINTEXT, HMAC-SHA1, RSA-SHA1)
   *  * oauth_consumer_auth_type:        The oauth authorization type (headers, parameters, post)
   *
   * @param sfEventDispatcher $dispatcher  An sfEventDispatcher instance.
   * @param sfStorage         $storage     An sfStorage instance.
   * @param array             $options     An associative array of options.
   *
   * @return Boolean          true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, OAuth $consumer = null, array $options = array())
  {
    $options = array_merge(array(
      'oauth_request_token_api'          => 'http://twitter.com/oauth/request_token',
      'oauth_authorize_token_api'        => 'http://twitter.com/oauth/access_token',
      'oauth_access_token_api'           => 'http://twitter.com/oauth/authorize',
      'oauth_consumer_signature_method'  => OAUTH_SIG_METHOD_HMACSHA1,
      'oauth_consumer_auth_type'         => OAUTH_AUTH_TYPE_AUTHORIZATION
    ), $options);

    parent::initialize($dispatcher, $consumer, $options);
  }

  public function getProfile()
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user presence for guid: "%s"', $guid))));
    }

    $this->getConsumer()->fetch('http://twitter.com/account/verify_credentials.json');

    return json_decode($this->getConsumer()->getLastResponse());
  }


/*

  https://twitter.com/account/verify_credentials.xml

  https://twitter.com/statuses/update.xml
  https://twitter.com/statuses/replies.xml

  Search API Methods

  search


  trends

  trends/current

  trends/daily

  trends/weekly


  REST API Methods


  Timeline Methods

  statuses/public_timeline

  statuses/home_timeline [COMING SOON]

  statuses/friends_timeline

  statuses/user_timeline

  statuses/mentions

  statuses/retweeted_by_me [COMING SOON]

  statuses/retweeted_to_me [COMING SOON]

  statuses/retweets_of_me [COMING SOON]

  Status Methods

  statuses/show

  statuses/update

  statuses/destroy

  statuses/retweet [COMING SOON]

  User Methods

  users/show

  statuses/friends

  statuses/followers

  Direct Message Methods

  direct_messages

  direct_messages/sent

  direct_messages/new

  direct_messages/destroy

  Friendship Methods

  friendships/create

  friendships/destroy

  friendships/exists

  friendships/show

  Social Graph Methods

  friends/ids

  followers/ids

  Account Methods

  account/verify_credentials

  account/rate_limit_status

  account/end_session

  account/update_delivery_device

  account/update_profile_colors

  account/update_profile_image

  account/update_profile_background_image

  account/update_profile


  Favorite Methods

  favorites

  favorites/create

  favorites/destroy

  Notification Methods

  notifications/follow

  notifications/leave

  Block Methods

  blocks/create

  blocks/destroy

  blocks/exists

  blocks/blocking

  blocks/blocking/ids



  Saved Searches Methods

  saved_searches

  saved_searches/show

  saved_searches/create

  saved_searches/destroy



  OAuth Methods

  oauth/request_token

  oauth/authorize

  oauth/authenticate

  oauth/access_token


  Help Methods

  help/test

  ->getProfile
  ->getContacts
*/

}

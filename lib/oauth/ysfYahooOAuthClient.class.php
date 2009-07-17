<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfYahooOAuthClient provides an oauth client + wrapper to use yahoo apis.
 *
 * @package    ysfOpenPlugin
 * @subpackage oauth
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfYahooOAuthClient.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfYahooOAuthClient extends ysfOAuthClient
{
  /**
   * Initializes this ysfOAuthYahooUser.
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
      'oauth_request_token_api'          => 'https://api.login.yahoo.com/oauth/v2/get_request_token',
      'oauth_authorize_token_api'        => 'https://api.login.yahoo.com/oauth/v2/request_auth',
      'oauth_access_token_api'           => 'https://api.login.yahoo.com/oauth/v2/get_token',
      'oauth_consumer_signature_method'  => OAUTH_SIG_METHOD_HMACSHA1,
      'oauth_consumer_auth_type'         => OAUTH_AUTH_TYPE_URI,

      // yahoo apis
      'yql_api'                         => 'http://query.yahooapis.com/v1/yql',
      'yql_public_api'                  => 'http://query.yahooapis.com/v1/public/yql',
      'social_api'                      => 'http://social.yahooapis.com/v1',
      'test_api'                        => 'http://json-service.appspot.com/echo',
    ), $options);

    parent::initialize($dispatcher, $consumer, $options);
  }
  
  /**
   * YQL API
   */

  public function yql($query, $public = false)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yql: "%s"', $query))));
    }
    
    $yql = ($public === false) ? $this->options['yql_api'] : $this->options['yql_public_api'];

    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch($yql.'?format=json&q='.oauth_urlencode($query));

    return json_decode($this->getConsumer()->getLastResponse());
  }
  
  
  /**
   * Social Directory API
   */
  
  public function getPresence($guid)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user presence for guid: "%s"', $guid))));
    }

    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch(sprintf('%s/user/%s/presence/presence?format=json', $this->options['social_api'], oauth_urlencode($guid)));

    return json_decode($this->getConsumer()->getLastResponse());
  }
  
  
  public function setPresence($guid, $presence)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Setting yahoo user presence "%s" for guid: "%s"', $presence, $guid))));
    }

    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch(sprintf('%s/user/%s/presence/presence?format=json', $this->options['social_api'], oauth_urlencode($guid)), json_encode(array('status' => $presence)));

    return json_decode($this->getConsumer()->getLastResponse());
  }
  
  public function getProfile($guid)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user profile for guid: %s', $guid))));
    }

    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch(sprintf('%s/user/%s/profile?format=json', $this->options['social_api'], oauth_urlencode($guid)));

    return json_decode($this->getConsumer()->getLastResponse());
  }
  
  public function getConnections($guid, $offset = 0, $limit = 100)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user connections for guid: %s', $guid))));
    }
    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    
    $this->getConsumer()->fetch(sprintf('%s/user/%s/connections?format=json&view=usercard&start=%s&count=%d', $this->options['social_api'], oauth_urlencode($guid), $offset, $limit));
    return json_decode($this->getConsumer()->getLastResponse());
  }
  
  public function getContacts($guid, $offset = 0, $limit = 100)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user contacts for guid: %s', $guid))));
    }

    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch(sprintf('%s/user/%s/contacts?format=json&view=tinyusercard&start=%s&count=%d', $this->options['social_api'], oauth_urlencode($guid), $offset, $limit));

    return json_decode($this->getConsumer()->getLastResponse());
  }

  public function getUpdates($guid, $offset = 0, $limit = 100)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user updates for guid: "%s"', $guid))));
    }

    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch(sprintf('%s/user/%s/updates?format=json&start=%s&count=%d', $this->options['social_api'], oauth_urlencode($guid), $offset, $limit));

    return json_decode($this->getConsumer()->getLastResponse());
  }
  
  public function getConnectionsUpdates($guid, $offset = 0, $limit = 100)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user connections updates for guid: %s', $guid))));
    }
    $this->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
    $this->getConsumer()->fetch(sprintf('%s/user/%s/updates/connections?format=json&start=%s&count=%d', $this->options['social_api'], oauth_urlencode($guid), $offset, $limit));

    return json_decode($this->getConsumer()->getLastResponse());
  }

}

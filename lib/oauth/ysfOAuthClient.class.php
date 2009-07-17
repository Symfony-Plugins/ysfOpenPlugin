<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfOAuthClient will create an oauth client for consuming oauth protected resources.
 *
 * @package    ysfOpenPlugin
 * @subpackage oauth
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfOAuthClient.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfOAuthClient
{

  protected
    $dispatcher  = null,
    $consumer    = null,
    $options     = array();

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, OAuth $consumer = null, array $options = array())
  {
    $this->initialize($dispatcher, $consumer, $options);
  }

  /**
   * Initializes this ysfOAuthClient.
   *
   * Available options:
   *
   *  * logging:                         Whether to enable logging (false by default)
   *  * oauth_request_token_api:         The oauth api endpoint for getting request tokens.
   *  * oauth_authorize_token_api:       The oauth api endpoint for authorizing tokens.
   *  * oauth_access_token_api:          The oauth api endpoint for getting access tokens.
   *  * oauth_consumer_key:              The oauth consumer key
   *  * oauth_consumer_secret:           The oauth consumer secret
   *  * oauth_consumer_signature_method: The oauth signature method (PLAINTEXT, HMAC-SHA1, RSA-SHA1)
   *  * oauth_consumer_auth_type:        The oauth authorization type (headers, parameters, post)
   *
   * @param sfEventDispatcher    $dispatcher       An sfEventDispatcher instance.
   * @param OAuth                $consumer         An OAuth consumer instance to use for the requests.
   * @param array                $options          An associative array of options.
   *
   * @return Boolean          true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, OAuth $consumer = null, array $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->consumer   = $consumer;

    $this->options = array_merge(array(
      'oauth_request_token_api'          => null,
      'oauth_authorize_token_api'        => null,
      'oauth_access_token_api'           => null,
      'oauth_consumer_key'               => null,
      'oauth_consumer_secret'            => null,
      'oauth_consumer_signature_method'  => OAUTH_SIG_METHOD_HMACSHA1,
      'oauth_consumer_auth_type'         => OAUTH_AUTH_TYPE_AUTHORIZATION,
      'logging'                          => false,
      'debug'                            => false,
    ), $options);

    if(is_null($this->consumer))
    {
      $this->consumer = new OAuth($this->options['oauth_consumer_key'], $this->options['oauth_consumer_secret'], $this->options['oauth_consumer_signature_method'], $this->options['oauth_consumer_auth_type']);
    }

    if($this->options['debug'])
    {
      $this->consumer->enableDebug();
    }
  }

  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  public function getConsumer()
  {
    return $this->consumer;
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
  }

  public function getRequestToken($callbackUrl)
  {
  	return $this->consumer->getRequestToken($this->options['oauth_request_token_api'], $callbackUrl);
  }

  public function setToken($token, $secret)
  {
	  $this->consumer->setToken($token, $secret);
  }

  public function getAccessToken($token, $secret, $session = null)
  {
	  $this->consumer->setToken($token, $secret);

	  return $this->consumer->getAccessToken($this->options['oauth_access_token_api'], $session);
  }
}

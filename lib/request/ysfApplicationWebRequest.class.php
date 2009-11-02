<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfApplicationWebRequest class.
 *
 * This class manages web requests. It parses input from the request and store them as parameters.
 *
 * @package    ysfOpenPlugin
 * @subpackage request
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfApplicationWebRequest.class.php 14634 2009-01-12 09:14:26Z dwhittle $
 */
class ysfApplicationWebRequest extends sfWebRequest
{

  /**
   * Initializes this sfRequest.
   *
   * Available options:
   *
   *  * formats:           The list of supported format and their associated mime-types
   *
   * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   * @param  array             $parameters  An associative array of initialization parameters
   * @param  array             $attributes  An associative array of initialization attributes
   * @param  array             $options     An associative array of options
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfRequest
   *
   * @see sfRequest
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array(), $options = array())
  {
    parent::initialize($dispatcher, $parameters, $attributes, $options);
  }

  public function isIphone()
  {
    return preg_match('#Mobile/.+Safari#i', $this->getHttpHeader('User-Agent'));
  }

  public function isOAuth()
  {
    return $this->hasParameter('oauth_signature');
  }

  public function getAccessToken(YahooOAuthApplication $yahoo)
  {
    $token = false;
    if($this->hasParameter('openid_oauth_request_token'))
    {
      // exchange openid+oauth authorized request token for yahoo access token
      $token = $yahoo->getAccessToken(new YahooOAuthRequestToken($this->getParameter('openid_oauth_request_token'), null));

      // update access token in yahoo oauth client
      $yahoo->token = $token;
    }
    elseif($this->isYahooAuthenticated())
    {
      // create new yahoo access token from yap request parameters
      $token = new YahooOAuthAccessToken($this->getParameter('yap_viewer_access_token'), $this->getParameter('yap_viewer_access_token_secret'), $this->getParameter('yap_time'), null, (time() + 60), $this->getParameter('yap_viewer_guid'));
      $yahoo->token = $token;
    }

    return $token;
  }

  public function getOAuthSignature()
  {
    return $this->getParameter('oauth_signature');
  }

  public function getOAuthSignatureMethod()
  {
    return $this->getParameter('oauth_signature_method', 'HMAC-SHA1');
  }

  public function isFacebook()
  {

  }

  public function isFacebookAuthenticated()
  {

  }

  public function isYahoo()
  {
    return $this->hasParameter('yap_appid');
  }

  public function isYahooAuthenticated()
  {
    $guid = $this->getParameter('yap_viewer_guid');

    return ($this->isOAuth() && !empty($guid));
  }

  public function isYahooFullView()
  {
    return ($this->getYahooView() == 'YahooFullView');
  }

  public function isYahooSmallView()
  {
    return ($this->getYahooView() == 'YahooSmallView');
  }

  public function getYahooView()
  {
    return $this->getParameter('yap_view');
  }

  public function getYahooUserId()
  {
    return $this->getParameter('yap_viewer_guid');
  }

  public function getYahooApplicationId()
  {
    return $this->getParameter('yap_appid');
  }

  public function getYahooDropzoneId()
  {
    return $this->getParameter('yap_dropzone_id');
  }

  public function getYahooConsumerKey()
  {
    return $this->getParameter('yap_consumer_key');
  }

  public function getYahooAccessToken()
  {
    return $this->getParameter('yap_viewer_access_token');
  }

  public function getYahooAccessTokenSecret()
  {
    return $this->getParameter('yap_viewer_access_token_secret');
  }

  public function getYahooTimezone()
  {
    return $this->getParameter('yap_tz', 'America/Los_Angeles');
  }

  public function getYahooCulture()
  {
    return $this->getParameter('yap_jurisdiction');
  }

  public function getYahooRequestedAt()
  {
    return $this->getParameter('yap_time');
  }

  public function getYahooDropzoneDoneUrl($default)
  {
    if($this->isYahoo())
    {
      /**
       * Move settings from app.yml to factories.yml options
       */
      $dropzones = sfConfig::get('app_yap_dropzones');

      $doneUrl = isset($dropzones[$this->getYahooDropzoneId()]) ? str_replace('%s', sfConfig::get('app_yap_application_id'), $dropzones[$this->getYahooDropzoneId()]) : $default;
    }
    else
    {
      $doneUrl = $default;
    }

    return $doneUrl;
  }

}

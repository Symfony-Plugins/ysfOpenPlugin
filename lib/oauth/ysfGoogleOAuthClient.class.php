<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfGoogleOAuthClient will use oauth for credential management.
 *
 * @package    ysfOpenPlugin
 * @subpackage oauth
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfGoogleOAuthClient.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfGoogleOAuthClient extends ysfOAuthClient
{
  /**
   * Initializes this ysfOAuthGoogleUser.
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
      'oauth_request_token_api'          => 'https://www.google.com/accounts/OAuthGetRequestToken',
      'oauth_authorize_token_api'        => 'https://www.google.com/accounts/OAuthAuthorizeToken',
      'oauth_access_token_api'           => 'https://www.google.com/accounts/OAuthGetAccessToken',
      'oauth_consumer_signature_method'  => OAUTH_SIG_METHOD_HMACSHA1,
      'oauth_consumer_auth_type'         => OAUTH_AUTH_TYPE_AUTHORIZATION,
    ), $options);
        
    parent::initialize($dispatcher, $consumer, $options);
  }

/*

	$scopes = urlencode("http://www.google.com/calendar/feeds/") . "%20" . urlencode("http://www.blogger.com/feeds/");
	$arrayResp = $o->getRequestToken("https://www.google.com/accounts/OAuthGetRequestToken?scope={$scopes}");
	$authorizeUrl = "https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token={$arrayResp["oauth_token"]}";
	
  ->getProfile
  ->getContacts
*/

}

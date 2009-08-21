<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 /**
  User
   -> Profile (profile -> import from openid or oauth (profile endpoints = google, yahoo, twitter)
   -> Identity (openid)
   -> Authorization (oauth)
 **/

/**
 * ysfOAuthUser will use oauth for authentication management and openid for identity management.
 *
 * @package    ysfOpenPlugin
 * @subpackage user
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfOAuthUser.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfOAuthUser extends ysfOpenIDUser
{

  protected
    $clients = array();

  /**
   * Initializes this ysfOAuthUser.
   *
   * Available options:
   *
   *  * auto_shutdown:                   Whether to automatically save the changes to the session (true by default)
   *  * culture:                         The user culture
   *  * default_culture:                 The default user culture (en by default)
   *  * use_flash:                       Whether to enable flash usage (false by default)
   *  * logging:                         Whether to enable logging (false by default)
   *
   * @param sfEventDispatcher $dispatcher  An sfEventDispatcher instance.
   * @param sfStorage         $storage     An sfStorage instance.
   * @param array             $options     An associative array of options.
   *
   * @return Boolean          true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);

    // addOAuthClient: google = oauth-google, twitter = oauth-twitter, yahoo = oauth-yahoo
    // add events for auth registration / check credentials

    // handle yap users
    if(isset($_REQUEST['yap_appid'], $_REQUEST['yap_viewer_guid']))
    {
      $username = $_REQUEST['yap_viewer_guid'].'@me.yahoo.com';
      $this->login($username, array());
      $this->addCredential('openid');
    }

    // user is logged in via open id
    if($this->isAuthenticated())
    {
      if($this->isYahoo()) // yahoo open id
      {
        if(isset($_REQUEST['yap_appid'], $_REQUEST['yap_viewer_access_token'], $_REQUEST['yap_viewer_access_token_secret'], $_REQUEST['oauth_signature']))
        {
          // yahoo yap application
          $this->setOAuthClient('yahoo', new ysfYahooOAuthClient($this->dispatcher, new OAuth($this->options['yahoo_yap_consumer_key'], $this->options['yahoo_yap_consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION), array('logging' => $this->options['logging'])));
          $this->getOAuthClient('yahoo')->setToken($_REQUEST['yap_viewer_access_token'], $_REQUEST['yap_viewer_access_token_secret']);
          $this->addCredential('oauth-yahoo');

          if ($this->options['logging'])
          {
            $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('User is for Yahoo! open application id: %s, validated oauth token, adding credential oauth-yahoo', var_export($_REQUEST['yap_appid'], true)))));
          }
        }
        else
        {
          // yahoo oauth application
          $this->setOAuthClient('yahoo', new ysfYahooOAuthClient($this->dispatcher, new OAuth($this->options['yahoo_oauth_consumer_key'], $this->options['yahoo_oauth_consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION), array('logging' => $this->options['logging'])));
          if(sfConfig::get('sf_debug'))
          {
            $this->getOAuthClient('yahoo')->getConsumer()->disableSSLChecks();
          }

          $oauthAccessToken = $this->getAttribute('yahoo_oauth_access_token');
          if(isset($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']))
          {
            // set user oauth token
            $this->getOAuthClient('yahoo')->setToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']);
            $this->addCredential('oauth-yahoo');

            if ($this->options['logging'])
            {
              $this->dispatcher->notify(new sfEvent($this, 'application.log', array('User is for Yahoo! OAuth standalone, validated oauth token, adding credential oauth-yahoo')));
            }

            // load profile if token is expired or expires in next minute than refresh access token
            $oauthAccessToken = $this->getAttribute('yahoo_oauth_access_token');
            try {
              $data = $this->getOAuthClient('yahoo')->getProfile($oauthAccessToken['xoauth_yahoo_guid']);
            }
            catch(OAuthException $e)
            {
              $this->getOAuthClient('yahoo')->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_URI);
              $oauthAccessToken = $this->getOAuthClient('yahoo')->getAccessToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret'], $oauthAccessToken['oauth_session_handle']);
              $this->setAttribute('yahoo_oauth_access_token', $oauthAccessToken);

              if ($this->options['logging'])
              {
                $this->dispatcher->notify(new sfEvent($this, 'application.log', array('Refreshing access token for scalable OAuthoken')));
              }

              $this->getOAuthClient('yahoo')->setToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']);
              $this->getOAuthClient('yahoo')->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);

              $data = $this->getOAuthClient('yahoo')->getProfile($oauthAccessToken['xoauth_yahoo_guid']);
            }

          }
        }
      }

      if($this->isGoogle()) // google open id
      {
        // google application
        $this->setOAuthClient('google', new ysfGoogleOAuthClient($this->dispatcher, new OAuth($this->options['google_oauth_consumer_key'], $this->options['google_oauth_consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION)));
        $this->addCredential('oauth-google');
      }
    }
  }

  public function hasOAuthClient($name = 'default')
  {
    return isset($this->clients[$name]);
  }

  public function getOAuthClient($name = 'default')
  {
    return isset($this->clients[$name]) ? $this->clients[$name] : false;
  }

  public function setOAuthClient($name = 'default', ysfOAuthClient $client)
  {
    $this->clients[$name] = $client;
  }

  public function doOAuthDanceForClient($name, sfAction $action)
  {

    if(!$this->hasCredential('oauth-'.$name))
    {
      $oauth = $this->getOAuthClient($name);
      if($oauth)
      {
        $oauth->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_URI);
        try {

          if(!$this->hasAttribute($name.'_oauth_access_token'))
          {
            if(!$this->hasAttribute($name.'_oauth_request_token'))
            {
              $oauthRequestToken = $oauth->getRequestToken($action->generateUrl('homepage', array(), true));
              if ($this->options['logging'])
              {
                $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching request token with data: %s', var_export($oauthRequestToken, true)))));
                /*
                  yahoo - request token responses
                  ["oauth_token"]=>  string(6) "k9nweg"
                  ["oauth_token_secret"]=>  string(40) "b7d22410f0432c7f17758afb4fb073a15529f3fd"
                  ["oauth_expires_in"]=>  string(4) "3600"
                  ["xoauth_request_auth_url"] => string(68) "https://api.login.yahoo.com/oauth/v2/request_auth?oauth_token=k9nweg"
                */
              }

              if(isset($oauthRequestToken['oauth_token'], $oauthRequestToken['oauth_token_secret']))
              {
                $this->setAttribute($name.'_oauth_request_token', $oauthRequestToken);

                $action->redirect($oauthRequestToken['xoauth_request_auth_url'].'&oauth_callback='.urlencode($action->getController()->genUrl('@homepage', true)));
              }
            }
            else
            {
              $oauthRequestToken = $this->getAttribute($name.'_oauth_request_token');
              $oauthAccessToken = $oauth->getAccessToken($oauthRequestToken['oauth_token'], $oauthRequestToken['oauth_token_secret']);
              if(isset($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']))
              {
                $this->setAttribute($name.'_oauth_access_token', $oauthAccessToken);
                $oauth->getConsumer()->setToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']);
                $this->addCredential('oauth-'.$name);
              }
            }
          }
          else
          {
            $oauthAccessToken = $this->getAttribute($name.'_oauth_access_token');
            if ($this->options['logging'])
            {
              $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Using access token: %s', var_export($oauthAccessToken, true)))));
              /*
                yahoo - access token response
                ["oauth_token"]=> string(782) "A=Rdjx59eas1_mctPVpGhBSLiq1vs4araufQAHYM_F_TiR9H74pXATykiozVYMkzHOX9DczzY8QIL62dUw3rO7Q3mnkJj5YO3.SDZxRzQ3mgqJRBHKWexSDxyi8c3W6UKcOhTOqVD836uxvXknk5M8Fz8oNIS56o8zGrZK3gKfRRJyj5QeUxkdFEov4tXt9PiALaggWaZ6KImAGMNt4GU9vA83K2vzFWOCmkJEnJvGfNLxGNwRFsNJJmL2XcEVxsAV5To85TW7OZZus1bHANdpA_DRF7uuD6KDrdlWKdbsDDzjM7ng3_3KE8EzzqrdxgTy6SBdMAEXzXFp2GOZa20tQmjF2V9xLFR3Y.ktQLapajPp_pCpzVn5NxtchbYKQl5qvHMQ8pKvbt.njzgorAXtbXMCLVnpo9SL6UQpvYq1P6Pl2QRlIDE85c8SyCfqe34OA8WQsBSNB7BMQD6VtFZRtyjY4gbNIlXwwTA3EKY42eVp5IQcfDeaVC.LaAbp0STYN3UeYiciOrUdqfUrsgKshDv6fXE2y2mfQpybK0.gk6zV5sKCAJg8sbQMYOgTveiRdF9dyHf7uRPni0hSmM13TM0vknnaCWxs5cJBzFCvD1e3CDA3a04RCDf8dxKtIJwBLBU7I6gLu__8htib.9ksHzhtrz_SDcUemeG8eo9vC.sibHgb3QL9TGeNld4p5v8LKYsmvzrwpPZ.iU6fKS6WY1ZwqBUcm0DtwRaGA.nuiMBK2JC5zAEYF3_ZQlqI2qVXlHI_1UTuzuA-"
                ["oauth_token_secret"]=> string(40) "87bc2503e16be0a815c42eb4c6d63211269713f6"
                ["oauth_expires_in"]=> string(4) "3600"
                ["oauth_session_handle"]=> string(56) "AOR3d0k0FJYs8zbAp_G2YlTBnxQ4w6SwxxLut2xL3PaFTdH8EncCoq0-"
                ["oauth_authorization_expires_in"]=> string(7) "2591892"
                ["xoauth_yahoo_guid"]=> string(26) "ECPZF7D765KTAXPDKWS7GE7CUU" }
              */
            }

            if(isset($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']))
            {
              // set user oauth token
              $oauth->getConsumer()->setToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']);
              $this->addCredential('oauth-'.$name);
            }
          }
        }
        catch(OAuthException $e)
        {
          if ($this->options['logging'])
          {
            /*
              http://oauth.pbwiki.com/ProblemReporting
            */
            $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('%s - %s', $e->getMessage(), var_export($e->lastResponse, true)), 'priority' => sfLogger::ERR)));
          }
        }
      }
    }
  }

}

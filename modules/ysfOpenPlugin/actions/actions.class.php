<?php

/**
 * ysfOpenPlugin actions.
 *
 * @package    ysfOpenPlugin
 * @subpackage actions
 * @author     Dustin Whittle <dustin@yahoo-inc.com>
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class ysfOpenPluginActions extends sfActions
{

  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $oauthAccessToken = $this->getUser()->getAttribute('yahoo_oauth_access_token');
    $yahoo = $this->getUser()->hasOAuthClient('yahoo') && $this->getUser()->getOAuthClient('yahoo')->hasAccessToken();

    if($yahoo)
    {
      try {
        $profile = $yahoo->getProfile($oauthAccessToken['xoauth_yahoo_guid']);
        $connections = $this->getUser()->getOAuthClient('yahoo')->getConnections($oauthAccessToken['xoauth_yahoo_guid'], 0, 1000);
      }
      catch(OAuthException $e)
      {
        $yahoo->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_URI);
        $oauthAccessToken = $yahoo->getAccessToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret'], $oauthAccessToken['oauth_session_handle']);
        $this->getUser()->setAttribute('yahoo_oauth_access_token', $oauthAccessToken);

        $yahoo->setToken($oauthAccessToken['oauth_token'], $oauthAccessToken['oauth_token_secret']);
        $yahoo->getConsumer()->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);

        $profile = $yahoo->getProfile($oauthAccessToken['xoauth_yahoo_guid']);
        $connections = $this->getUser()->getOAuthClient('yahoo')->getConnections($oauthAccessToken['xoauth_yahoo_guid'], 0, 1000);
      }
    }

    $this->profile = $profile->profile;
    $this->connections = $connections->connections;

  }

  /**
   * Executes login action
   *
   * @param sfRequest $request A request object
   */
  public function executeLogin(sfWebRequest $request)
  {
    $this->form = new OpenIdentityForm();

    $consumer = new Zend_OpenId_Consumer(new Zend_OpenId_Consumer_Storage_File(sfConfig::get('sf_cache_dir') . '/openid/'));
    $profile  = new Zend_OpenId_Extension_Sreg(array('nickname' => true, 'fullname' => false, 'email' => true, 'dob' => false, 'gender' => false, 'postcode' => false, 'language' => false, 'timezone' => false), null, '1.1');
    // $oauth    = new Zend_OpenID_Extension_Oauth();

    $openIdMode = $request->getParameter('openid_mode');
    $openIdRealm = sfConfig::get('app_openid_realm');

    if ($openIdMode == 'cancel')
    {
      $this->form->getErrorSchema()->addError(new sfValidatorError(new OpenIdentityValidator(), 'invalid'));
    }

    // handle form or csrf link
    if ($request->isMethod('post') || $request->hasParameter('_csrf_token'))
    {
      // validate form
      $this->form->bind(array('identity' => $request->getParameter('identity'), '_csrf_token' => $request->getParameter('_csrf_token')));
      if ($this->form->isValid())
      {
        $values = $this->form->getValues();

        // redirect to openid provider
        if (!$consumer->login($values['identity'], $this->generateUrl('login', array(), true), $openIdRealm, $profile))
        {
          // set error on form as open id login failed
          $this->getResponse()->setStatusCode(401);
          $this->form->getErrorSchema()->addError(new sfValidatorError(new OpenIdentityValidator(), 'invalid'));
        }
      }
    }
    else
    {
      // validate openid credentials
      if ($openIdMode == 'id_res' && $request->hasParameter('openid_identity'))
      {
        if($consumer->verify($request->getParameterHolder()->getAll(), $identity))
        {
          // openid is valid with or without profile (openid 1.1/2.0 discovery), log user in, and redirect
          $this->getUser()->login($identity, $consumer->verify($request->getParameterHolder()->getAll(), $identity, $profile) ? $profile->getProperties() : array());

          // $this->redirect($this->getUser()->getAttribute('referrer'));
          $this->redirect('@homepage');
        }
        else
        {
          // set error on form as open id verify failed
          $this->getResponse()->setStatusCode(401);
          $this->form->getErrorSchema()->addError(new sfValidatorError(new OpenIdentityValidator(), 'invalid'));
        }
      }
      else
      {
        // store referrer on first request
        if (!$this->getUser()->hasAttribute('referrer'))
        {
          $this->getUser()->setAttribute('referrer', ($this->getContext()->getActionStack()->getSize() === 1) ? $request->getReferer() : $request->getUri());
        }

        $this->getResponse()->setStatusCode(401);
      }
    }
  }

  /**
   * Executes logout action
   *
   * @param sfRequest $request A request object
   */
  public function executeLogout(sfWebRequest $request)
  {
    // oauth user
    $this->getUser()->logout();

    $this->redirect('@login');
  }

  /**
   * Executes secure action (fetches oauth credentials on demand)
   *
   * @param sfRequest $request A request object
   */
  public function executeSecure(sfWebRequest $request)
  {
    // oauth credentials oauth-clientName
    $this->getUser()->doOAuthDanceForClient('yahoo', $this);
  }

}

<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfOpenIDUser will use openid for authentication.
 *
 * @package    ysfOpenPlugin
 * @subpackage user
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfOpenIDUser.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfOpenIDUser extends sfBasicSecurityUser
{

  /**
   * Returns true if user is authenticated.
   *
   * @return boolean
   */
  public function isAuthenticated()
  {
    return ($this->authenticated && $this->hasCredential('openid'));
  }

  public function login($identity, $profile)
  {
    $this->setAuthenticated(true);
    $this->addCredential('openid');

    $this->setAttribute('identity', $identity);
    if(isset($profile['nickname']))
    {
      $this->setAttribute('nickname', $profile['nickname']);
    }
  }

  public function getProvider()
  {
    if($this->isGoogle())
    {
      return 'Google';
    }

    if($this->isYahoo())
    {
      return 'Yahoo';
    }

    return 'OpenID';
  }

  public function isGoogle()
  {
    return (stripos($this->getUsername(), 'www.google.com') !== false);
  }

  public function isYahoo()
  {
    return (stripos($this->getUsername(), 'me.yahoo.com') !== false);
  }

  public function getUsername()
  {
    return $this->getAttribute('nickname', $this->getAttribute('identity'));
  }

  public function logout()
  {
    $this->setAuthenticated(false);
  }

}

<?php

/**
 * OpenIdentityValidator for verifying openid identities.
 *
 * @package    ysfOpenPlugin
 * @subpackage validator
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: OpenIdentityValidator.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class OpenIdentityValidator extends sfValidatorBase
{
  public function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', 'The open identity specified is invalid.');
  }

  protected function doClean($value)
  {

    if(stristr($value, 'ymail.com') || stristr($value, 'yahoo.com')) // yahoo email = yahoo open id
    {
      $value = 'https://me.yahoo.com';
    }
    else if(stristr($value, 'gmail.com') || stristr($value, 'google.com')) // google email = google open id
    {
      $value = 'https://www.google.com/accounts/o8/id';
    }

    return $value;
  }
}

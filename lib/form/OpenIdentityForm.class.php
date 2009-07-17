<?php

/**
 * Open Identity login form.
 *
 * @package    ysfOpenPlugin
 * @subpackage form
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: OpenIdentityForm.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class OpenIdentityForm extends sfForm
{
  public function configure()
  {	
    $this->setWidgets(array(
      'identity' => new sfWidgetFormInput(array(), array('class' => 'openid required')),
    ));

    $this->setValidators(array(
      'identity' => new sfValidatorAnd(array(new sfValidatorString(),
                                             new sfValidatorOr(array(new sfValidatorEmail(), new sfValidatorUrl()), array(), array('invalid' => 'The open identity specified is invalid.')),
                                             new OpenIdentityValidator()
                                      ), array(), array('required' => 'An open identity url or email is required.')),
    ));
	}

}
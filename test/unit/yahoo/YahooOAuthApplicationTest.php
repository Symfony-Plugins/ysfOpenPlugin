<?php

require_once 'PHPUnit/Framework.php';

require dirname(__FILE__).'/../../../lib/vendor/oauth/OAuth.php';
require dirname(__FILE__).'/../../../lib/vendor/yahoo/YahooOAuthApplication.class.php';

class YahooOAuthApplicationTest extends PHPUnit_Framework_TestCase {

  public function setup()
  {
    // $this->oauthapp = new YahooOAuthApplication();
  }

	public function testgetRequestToken() {

	}

  public function tearDown()
  {
    unset($this->oauthapp);
  }
}

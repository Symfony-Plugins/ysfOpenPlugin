<?php
/**
 * Copyright 2009 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * This file is meant to be run through a php command line, not called
 * directly through the web browser. To run these tests from the command line:
 * # cd /path/to/client
 * # phpunit test/MySpaceTests.php
 */

require_once '__init__.php';
require_once 'online/OnlineTestSuite.php';

class MySpaceTests extends OnlineTestSuite {
  public $CONSUMER_KEY = 'http://opensocial-php-client.googlecode.com/svn/gadgets/opensocial-php-client.xml';
  public $CONSUMER_SECRET = 'f07208f6993c4db9bac9c23729c558dd';
  public $USER_A_ID = 'myspace.com:480224342';
  public $USER_A_DISPLAY_NAME = 'Barry';
  public $UNSUPPORTED_METHODS = array('appdata.get', 'appdata.create', 'activities.get', 'activities.create');
  public $USER_A_EXTENDED_PROFILE_FIELDS = array('aboutMe', 'birthday');
  
  protected function getOsapi() {
    $provider = new osapiMySpaceProvider();
    $auth = new osapiOAuth2Legged($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $this->USER_A_ID);
    return new osapi($provider, $auth);
  }

  public static function suite() {
    return new MySpaceTests();
  }
}

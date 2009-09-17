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
 * # phpunit test/FriendConnectRpcTests.php
 */

require_once '__init__.php';
require_once 'online/OnlineTestSuite.php';

class FriendConnectRpcTests extends OnlineTestSuite {
  public $CONSUMER_KEY = '*:17977379277592385609';
  public $CONSUMER_SECRET = '4oB93vo3EvQ=';
  public $USER_A_ID = '10314750576668418529';
  public $USER_A_DISPLAY_NAME = 'Alice Testington';
  public $USER_A_EXTENDED_PROFILE_FIELDS = array('aboutMe', 'profileUrl', 'thumbnailUrl');

  protected function getOsapi() {
    $provider = new osapiFriendConnectProvider();
    $auth = new osapiOAuth2Legged($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $this->USER_A_ID);
    return new osapi($provider, $auth);
  }

  public static function suite() {
    return new FriendConnectRpcTests();
  }
}

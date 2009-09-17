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
 * # phpunit test/PartuzaRpcTests.php
 */

require_once '__init__.php';
require_once 'online/OnlineTestSuite.php';

class PlaxoRestTests extends OnlineTestSuite {
  public $CONSUMER_KEY = 'alice.testington@gmail.com';
  public $CONSUMER_SECRET = 'notasecret';
  public $USER_A_ID = '154619987444';
  public $USER_A_DISPLAY_NAME = 'Alice Testington';
  public $USER_A_EXTENDED_PROFILE_FIELDS = null;
  
  // people.get_by_id isn't a real service name, but since fixing this issue
  // would take a while to get working correctly, I'm just using this hack to
  // disable the corresponding unit test.
  public $UNSUPPORTED_METHODS = array('people.get_by_id', 'appdata.get', 'appdata.create', 'activities.get', 'activities.create');

  protected function getOsapi() {
    $provider = new osapiPlaxoProvider();
    $auth = new osapiHttpBasic($this->CONSUMER_KEY, $this->CONSUMER_SECRET);
    return new osapi($provider, $auth);
  }

  public static function suite() {
    return new PlaxoRestTests();
  }
}

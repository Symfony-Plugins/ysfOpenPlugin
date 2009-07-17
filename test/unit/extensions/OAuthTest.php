<?php

include(dirname(__FILE__).'/../../../../../test/bootstrap/unit.php');

/**
 * OAuth Tests
 *
 * @package    ysfOpenPlugin
 * @subpackage test
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: OAuthTest.php 12479 2008-10-31 10:54:40Z dwhittle $
 */

$t = new lime_test(11, new lime_output_color());

$t->comment('OAuth');

$requestTokenUrl   = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
$accessTokenUrl   = 'https://api.login.yahoo.com/oauth/v2/get_token';

$consumerKey       = 'dj0yJmk9WUxPUkhFUWxISWpvJmQ9WVdrOWFYWmhTVzVDTXpBbWNHbzlNVGt4TmpJNU1EazROdy0tJnM9Y29uc3VtZXJzZWNyZXQmeD01Ng--';
$consumerKeySecret = 'f893cf549be5cb37f83b1414e2ff212df2ea4c18';
$callbackUrl       = 'http://imaginingtheweb.com/';

$t->info(sprintf('oauth consumer key: "%s"', $consumerKey));
$t->info(sprintf('oauth consumer key secret: "%s"', $consumerKeySecret));
$t->info(sprintf('oauth callback url: "%s"', $callbackUrl));

$t->info(sprintf('oauth request token url: "%s"', $requestTokenUrl));
$t->info(sprintf('oauth access token url: "%s"', $accessTokenUrl));

$oauth =  new OAuth($consumerKey, $consumerKeySecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
$oauth->enableDebug();
$oauth->enableSSLChecks();

$t->comment('OAuth->getRequestToken()');
try {
  $requestToken = $oauth->getRequestToken($requestTokenUrl, 'http://yahoo.com/');
  $t->fail('OAuth->getRequestToken() does not throw OAuthException when request token is requested with invalid callback url');
}
catch(OAuthException $e)
{
  $t->pass(sprintf('OAuth->getRequestToken() throws OAuthException with message "%s" when request token is requested with invalid callback url', $e->getMessage()));
}
$requestToken = $oauth->getRequestToken($requestTokenUrl, $callbackUrl);
$t->is(isset($requestToken['xoauth_request_auth_url']), true, 'OAuth->getRequestToken() returns valid redirect uri given request token endpoint and callback url');
$t->is(isset($requestToken['oauth_token']), true, 'OAuth->getRequestToken() returns valid oauth token given request token endpoint and callback url');

$t->comment('OAuth->getAccessToken()');
try {
  $accessToken = $oauth->getAccessToken($accessTokenUrl);
  $t->fail('OAuth->getAccessToken() does not throw OAuthException when access token is requested with invalid credentials');
}
catch(OAuthException $e)
{
  $t->pass(sprintf('OAuth->getAccessToken() throws OAuthException with message "%s" when access token is requested with invalid credentials', $e->getMessage()));
}
// $t->is(isset($accessToken['oauth_token']), true, 'OAuth->getAccessToken() returns valid oauth token');

$t->comment('OAuth->fetch()');
$result = $oauth->fetch('http://query.yahooapis.com/v1/public/yql?format=json&q=select+%2A+from+upcoming.events.bestinplace+where+woeid+in+%28select+woeid+from+geo.places+where+text%3D%22San+Francisco%2C+California%22+limit+10%29');
$t->is(is_string($oauth->getLastResponse()), true, 'OAuth->getLastResponse() returns response content as string');
$t->is(is_array($oauth->getLastResponseInfo()), true, 'OAuth->getLastResponseInfo() returns response meta information as array');

$response = array_merge(array('content' => $oauth->getLastResponse()), $oauth->getLastResponseInfo());
$t->is($response['http_code'], 200, 'OAuth->getLastResponseInfo() returns http status code "200"');
$t->is($response['content_type'], 'application/json;charset=utf-8', 'OAuth->getLastResponseInfo() returns content type "application/json;charset=utf-8"');

$data = json_decode($response['content']);

$t->comment('oauth_urlencode()');
$t->is(oauth_urlencode('http://imaginingtheweb.com/'), 'http%3A%2F%2Fimaginingtheweb.com%2F', 'oauth_urlencode() returns oauth url encoded given uri without parameters');
$t->is(oauth_urlencode('http://imaginingtheweb.com/my demo?example=true'), 'http%3A%2F%2Fimaginingtheweb.com%2Fmy%20demo%3Fexample%3Dtrue', 'oauth_urlencode() returns oauth url encoded given uri with parameters');

$t->comment('oauth_get_sbs()');
$t->is(oauth_get_sbs('post', 'http://imaginingtheweb.com/', array('example' => 'true')), 'post&http%3A%2F%2Fimaginingtheweb.com%2F&example%3Dtrue', 'oauth_get_sbs() returns oauth signature base string http method, uri, and parameters');

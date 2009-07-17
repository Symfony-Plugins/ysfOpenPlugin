<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfYahooApplication
 *
 * @package    ysfOpenPlugin
 * @subpackage oauth
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfYahooApplication.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfYahooApplication
{
  
  // guid
  // application id

  protected
    $client = null;

  public function __construct(ysfOAuthClient $client, array $options = array())
  {
    $this->initialize($client, $options = array());
  }

  public function initialize(ysfOAuthClient $client, array $options = array())
  {
    $this->client = $client;

    $this->options = array_merge(array(
      'application_id' => null,
      'social_api'     => 'http://social.yahooapis.com/v1',
      'yap_api'        => 'http://appstore.apps.yahooapis.com/v1',
      'yql_api'        => 'http://query.yahooapis.com/v1'
    ), $options);
  }

  public function getOAuthClient()
  {
    return $this->client;
  }

  /**
   * Sets the small view for the user given by the GUID.
   *
   * @param $guid The GUID of the user to set the small view for.
   * @param $content The content to set the small view to.
   * @return True on success, false otherwise.
   */
  public function setSmallView($guid, $content)
  {

    $response = $this->client->getConsumer()->fetch(sprintf('%s/cache/view/small/%s', $this->options['yap_api'], oauth_urlencode($guid)), $content);

    // check 200 response code
    return !is_null($response);
  }

  public function insertUpdate($guid, $title, $link, $description)
  {

    $time   = (string) time();
    $source = sprintf('APP.%s', $this->options['application_id']);
    $suid   = sha1($guid.':'.$source.':'.$title.':'.$link.':'.$description.':'.$time);

    $body = array('updates' => array(array('collectionID' => $guid, 'collectionType' => 'guid', 'class' => 'app', 'source' => $source, 'type' => 'appActivity', 'suid' => $suid, 'title' => $title, 'description' => $description, 'link' => $link, 'pubDate' => $time)));

    $response = $this->client->getConsumer()->fetch(sprintf('%s/user/%s/updates/%s/%s', $this->options['social_api'], oauth_urlencode($guid), oauth_urlencode($source), oauth_urlencode($suid)), json_encode($body));
  }
  
  public function deleteUpdate($guid, $suid)
  {
    $source = sprintf('APP.%s', $this->options['application_id']);
    
    $response = $this->client->getConsumer()->fetch(sprintf('%s/user/%s/updates/%s/%s', $this->options['social_api'], oauth_urlencode($guid), oauth_urlencode($source), oauth_urlencode($suid)));
  }

}

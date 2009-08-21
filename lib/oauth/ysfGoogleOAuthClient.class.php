<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ysfGoogleOAuthClient will use oauth for credential management.
 *
 * @package    ysfOpenPlugin
 * @subpackage oauth
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: ysfGoogleOAuthClient.class.php 11694 2008-09-21 08:26:37Z fabien $
 */
class ysfGoogleOAuthClient extends ysfOAuthClient
{
  /**
   * Initializes this ysfGoogleOAuthClient.
   *
   * Available options:
   *
   *  * oauth_request_token_api:         The oauth api endpoint for getting request tokens.
   *  * oauth_authorize_token_api:       The oauth api endpoint for authorizing tokens.
   *  * oauth_access_token_api:          The oauth api endpoint for getting access tokens.
   *  * oauth_consumer_key:              The oauth consumer key
   *  * oauth_consumer_secret:           The oauth consumer secret
   *  * oauth_consumer_signature_method: The oauth signature method (PLAINTEXT, HMAC-SHA1, RSA-SHA1)
   *  * oauth_consumer_auth_type:        The oauth authorization type (headers, parameters, post)
   *
   * @param sfEventDispatcher $dispatcher  An sfEventDispatcher instance.
   * @param sfStorage         $storage     An sfStorage instance.
   * @param array             $options     An associative array of options.
   *
   * @return Boolean          true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, OAuth $consumer = null, array $options = array())
  {
    $options = array_merge(array(
      'oauth_request_token_api'          => 'https://www.google.com/accounts/OAuthGetRequestToken',
      'oauth_authorize_token_api'        => 'https://www.google.com/accounts/OAuthAuthorizeToken',
      'oauth_access_token_api'           => 'https://www.google.com/accounts/OAuthGetAccessToken',
      'oauth_token_info_api'             => 'https://www.google.com/accounts/AuthSubTokenInfo',
      'oauth_consumer_signature_method'  => OAUTH_SIG_METHOD_HMACSHA1,
      'oauth_consumer_auth_type'         => OAUTH_AUTH_TYPE_AUTHORIZATION,

      // google scopes
      'contacts_api'       => 'http://www.google.com/m8/feeds/',
      'calendar_api'       => 'http://www.google.com/calendar/feeds/',
      'blogger_api'        => 'http://www.blogger.com/feeds/',
      'analytics_api'      => 'https://www.google.com/analytics/feeds/',
      'base_api'           => 'http://www.google.com/base/feeds/',
      'books_api'          => 'http://www.google.com/books/feeds/',
      'docs_api'           => 'http://docs.google.com/feeds/',
      'finance_api'        => 'http://finance.google.com/finance/feeds/',
      'gmail_api'          => 'https://mail.google.com/mail/feed/atom',
      'health_api'         => 'https://www.google.com/h9/feeds/',
      'maps_api'           => 'http://maps.google.com/maps/feeds/',
      'picasa_api'         => 'http://picasaweb.google.com/data/',
      'opensocial_api'     => 'http://www-opensocial.googleusercontent.com/api/',
      'spreadsheets_api'   => 'http://spreadsheets.google.com/feeds/',
      'webmaster_api'      => 'http://www.google.com/webmasters/tools/feeds/',
      'youtube_api'        => 'http://gdata.youtube.com',

    ), $options);

    parent::initialize($dispatcher, $consumer, $options);
  }

  public function getRequestToken(array $scopes = array())
  {
    $apis = array();
    foreach($scopes as $scope)
    {
      $scope_api = $scope.'_api';
      if (isset($this->options[$scope_api]))
      {
        $apis = $this->options[$scope_api];
      }
      else
      {
        throw new sfException();
      }
    }

  	return $this->getConsumer()->getRequestToken(sprintf('%s?scope=%s', $this->options['oauth_request_token_api'], oauth_urlencode(implode(' ', $apis))), $callbackUrl);
  }

  public function getProfile()
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Fetching yahoo user presence for guid: "%s"', $guid))));
    }

    $this->getConsumer()->fetch(sprintf('%s/user/%s/presence/presence?format=json', $this->options['social_api'], oauth_urlencode($guid)));

    return json_decode($this->getConsumer()->getLastResponse());
  }


/*

	$authorizeUrl = "https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token={$arrayResp["oauth_token"]}";


  # Google Base
  # http://www.google.com/base/feeds/snippets
  # http://www.google.com/base/feeds/items
  # http://www.google.com/base/feeds/attributes
  # http://www.google.com/base/feeds/itemtypes/<locale>
  #
  # Analytics
  # https://www.google.com/analytics/feeds/accounts/default
  #
  # Book Search
  # http://www.google.com/books/feeds/volumes/[<volume_ID>]
  # http://www.google.com/books/feeds/p/<PARTNER_COBRAND_ID>/volumes
  # http://www.google.com/books/feeds/users/me/collections/library/volumes
  # http://www.google.com/books/feeds/users/me/volumes
  #
  # Blogger
  # http://www.blogger.com/feeds/default/blogs
  # http://www.blogger.com/feeds/<blogID>/posts/default
  # http://www.blogger.com/feeds/<blogID>/[<postID>]/comments/default
  #
  # Calendar
  # http://www.google.com/calendar/feeds/default/allcalendars/full/[<calendarID>]
  # http://www.google.com/calendar/feeds/default/owncalendars/full
  # http://www.google.com/calendar/feeds/default/<visibility>/full/[<eventID>]
  #
  # Contacts
  # http://www.google.com/m8/feeds/contacts/default/full/[<contactID>]
  # http://www.google.com/m8/feeds/groups/default/full/[<contactID>]
  #
  # Documents List
  # http://docs.google.com/feeds/documents/private/full/
  # http://docs.google.com/feeds/acl/private/full/<resource_id>
  # http://docs.google.com/feeds/folder/private/full/<folder_id>
  #
  # Finance
  # http://finance.google.com/finance/feeds/default/portfolios/[<portfolioID>]
  # http://finance.google.com/finance/feeds/default/portfolios/<portfolioID>/positions/[<tickerID>]
  # http://finance.google.com/finance/feeds/default/portfolios/<portfolioID>/positions/<tickerID>/transactions/[<transactionID>]
  #
  # YouTube
  # http://gdata.youtube.com/feeds/api/users/default
  # http://gdata.youtube.com/feeds/api/users/default/contacts
  # http://gdata.youtube.com/feeds/api/users/default/favorites
  # http://gdata.youtube.com/feeds/api/users/default/playlists/[<playlistID>]
  # http://gdata.youtube.com/feeds/api/users/default/subscriptions
  # http://gdata.youtube.com/feeds/api/videos/<videoID>/related
  # http://gdata.youtube.com/feeds/api/videos/<videoID>/responses
  # http://gdata.youtube.com/feeds/api/videos/<videoID>/comments
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/top_rated
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/top_favorites
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/most_viewed
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/most_popular
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/most_recent
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/most_discussed
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/most_linked
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/most_responded
  # http://gdata.youtube.com/feeds/api/standardfeeds/[<regionID>]/recently_featured
  # http://gdata.youtube.com/feeds/api/standardfeeds/watch_on_mobile
  #
  # Spreadsheets
  # http://spreadsheets.google.com/feeds/spreadsheets/private/full/[<key>]
  # http://spreadsheets.google.com/feeds/worksheets/<key>/private/full/[<worksheetID>]
  # http://spreadsheets.google.com/feeds/list/<key>/<worksheetID>/private/full/[<rowID>]
  # http://spreadsheets.google.com/feeds/cells/<key>/<worksheetID>/private/full/[<cellID>]
  # http://spreadsheets.google.com/feeds/<key>/tables/[<tableID>]
  # http://spreadsheets.google.com/feeds/<key>/records/<tableID>/[<recordID>]
  #
  # Webmaster Tools
  # http://www.google.com/webmasters/tools/feeds/sites/[<siteID>]
  # http://www.google.com/webmasters/tools/feeds/<siteID>/sitemaps
  #
  # Picasa Web
  # http://picasaweb.google.com/data/feed/api/user/default/[albumid/<albumID>]
  # http://picasaweb.google.com/data/entry/api/user/default/albumid/<albumID>/<versionNumber>
  # http://picasaweb.google.com/data/entry/api/user/default/albumid/<albumID>/photoid/<photoID>/<versionNumber>
  # http://picasaweb.google.com/data/media/api/user/default/albumid/<albumID>/photoid/<photoID>/<versionNumber>
  #
  # Maps
  # http://maps.google.com/maps/feeds/maps/default/full
  # http://maps.google.com/maps/feeds/maps/userID/full/[<elementID>]
  # http://maps.google.com/maps/feeds/features/default/[<mapID>]/full/[<elementID>]
  #
  # GMail
  # https://mail.google.com/mail/feed/atom/[<label>]


  ->getProfile
  ->getContacts

}

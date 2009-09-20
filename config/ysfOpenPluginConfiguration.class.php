<?php

/**
 * ysfOpenPlugin configuration.
 *
 * @package     ysfOpenPlugin
 * @subpackage  config
 * @author      Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version     SVN: $Id: ysfOpenPluginConfiguration.class.php 12956 2008-11-12 17:35:45Z dwhittle $
 */
class ysfOpenPluginConfiguration extends sfPluginConfiguration
{

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    if (sfConfig::get('sf_debug'))
    {
      require_once(dirname(__FILE__).'/../lib/debug/ysfWebDebugPanelYahooYQL.class.php');

      $this->dispatcher->connect('debug.web.load_panels', array('ysfWebDebugPanelYahooYQL', 'listenToLoadDebugWebPanelEvent'));
    }
  }

}
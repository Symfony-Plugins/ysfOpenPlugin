<?php

/**
 * ysfWebDebugPanelYQL adds a panel to the web debug toolbar with a facebook console.
 *
 * @package     ysfOpenPlugin
 * @subpackage  debug
 * @author      Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version     SVN: $Id: ysfWebDebugPanelYUI.class.php 12982 2008-11-13 17:25:10Z dwhittle $
 */
class ysfWebDebugPanelYahooYQL extends sfWebDebugPanel
{
  public function listenToLoadDebugWebPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('YahooYQL', new self($event->getSubject()));
  }

  public function getTitle()
  {
    $yql_count = (isset($yql_count)) ? $yql_count : 0;

    return sprintf('<img src="/ysfOpenPlugin/images/yql-32.png" alt="Yahoo! YQL" height="16" width="24"  /> %s', $yql_count);
  }

  public function getPanelTitle()
  {
    return 'Yahoo! YQL';
  }

  public function getPanelContent()
  {
    return 'No YQL queries.';
  }
}

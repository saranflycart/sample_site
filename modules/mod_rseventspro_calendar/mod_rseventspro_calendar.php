<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!file_exists(JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php') && !file_exists(JPATH_SITE.'/components/com_rseventspro/helpers/query.php')) return;

require_once JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php';
require_once JPATH_SITE.'/components/com_rseventspro/helpers/route.php';
require_once JPATH_SITE.'/components/com_rseventspro/helpers/calendar.php';
require_once JPATH_SITE.'/modules/mod_rseventspro_calendar/helper.php';

rseventsproHelper::tooltipLoad();

// Load language
JFactory::getLanguage()->load('com_rseventspro');

// Add stylesheets
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root(true).'/modules/mod_rseventspro_calendar/style.css');
$doc->addScript(JURI::root(true).'/components/com_rseventspro/assets/js/scripts.js');

$cache = JFactory::getCache('mod_rseventspro_calendar');
$cache->setCaching($params->get('use_cache', 1));
$cache->setLifeTime($params->get('cache_time', 900));
$calendar = $cache->call(array('modRseventsProCalendar', 'getObject'), $params);

$itemid = $params->get('itemid');
$itemid = !empty($itemid) ? $itemid : RseventsproHelperRoute::getCalendarItemid();
$nofollow = $params->get('nofollow',0) ? 'rel="nofollow"' : '';

require JModuleHelper::getLayoutPath('mod_rseventspro_calendar', $params->get('layout', 'default'));
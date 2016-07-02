<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (file_exists(JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php')) {

	require_once JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php';
	require_once JPATH_SITE.'/components/com_rseventspro/helpers/route.php';
	require_once JPATH_SITE.'/modules/mod_rseventspro_popular/helper.php';

	// Get events
	$events = modRseventsProPopular::getEvents($params);

	// Load language
	JFactory::getLanguage()->load('mod_rseventspro_popular',JPATH_SITE);

	// Load scripts
	JFactory::getDocument()->addStyleSheet(JUri::root(true).'/modules/mod_rseventspro_popular/assets/style.css');

	$itemid = $params->get('itemid');
	$itemid = !empty($itemid) ? $itemid : RseventsproHelperRoute::getEventsItemid();

	require JModuleHelper::getLayoutPath('mod_rseventspro_popular');
}
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
	require_once JPATH_SITE.'/modules/mod_rseventspro_slider/helper.php';
	
	// Add stylesheets
	$doc = JFactory::getDocument();
	
	// Load jQuery
	rseventsproHelper::loadjQuery();
	
	// Get events
	$events = modRseventsProSlider::getEvents($params);

	// Get params
	$suffix		= $params->get('moduleclass_sfx');
	$links		= $params->get('links',0);
	$layout		= $params->get('layout','default');
	$pretext	= $params->get('text_above','');
	$posttext	= $params->get('text_below','');
	$height		= $params->get('height',250);
	$width		= $params->get('width',500);
	$length		= $params->get('desc_length',200);
	$title		= $params->get('showtitle',1);
	$date		= $params->get('showdate',1);
	$repeating	= $params->get('repeating',1);
	$buttons	= $params->get('responsive_buttons',1);
	$tduration	= ((double) $params->get('durationtimeline',1) * 1000);
	$nr_events	= (int) $params->get('eventsperpane',3);
	$itemid		= $params->get('itemid',0);
	$itemid		= !empty($itemid) ? $itemid : RseventsproHelperRoute::getEventsItemid();
	
	if ($layout == 'default') {
		modRseventsProSlider::carousel('mod_rseventspro_slider'.$module->id, $params);
	} else {
		$doc->addStyleSheet(JUri::root(true).'/modules/mod_rseventspro_slider/assets/css/timeline/style.css','text/css', 'screen');
		$doc->addScript(JUri::root(true).'/modules/mod_rseventspro_slider/assets/js/timeline.js'); 
	}
	
	require JModuleHelper::getLayoutPath('mod_rseventspro_slider',$layout);
}
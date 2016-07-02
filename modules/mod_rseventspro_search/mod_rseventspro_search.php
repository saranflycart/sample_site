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
	require_once JPATH_SITE.'/components/com_rseventspro/helpers/html.php';
	require_once JPATH_SITE.'/modules/mod_rseventspro_search/helper.php';

	// Load tooltips
	rseventsproHelper::tooltipLoad();
	// Load jQuery
	rseventsproHelper::loadjQuery();
	
	$suffix		= $params->get('moduleclass_sfx');
	$layout		= $params->get('layout','ajax');
	$links		= (int) $params->get('links',0);
	$categories	= (int) $params->get('categories',0);
	$locations	= (int) $params->get('locations',0);
	$start		= (int) $params->get('start',0);
	$end		= (int) $params->get('end',0);
	$archive	= (int) $params->get('archive',0);

	$locationslist	= JHTML::_('select.genericlist', modRseventsProSearch::getLocations(), 'rslocations[]', 'size="5" multiple="multiple" style="width:75%"', 'value', 'text' ,0);
	$archivelist	= JHTML::_('select.genericlist', modRseventsProSearch::getYesNo(), 'rsarchive', 'class="input-small"', 'value', 'text' ,0);
	$categorieslist = JHTML::_('select.genericlist', modRseventsProSearch::getCategories(), 'rscategories[]', 'size="5" multiple="multiple" style="width:75%"', 'value', 'text' ,0);

	// Load language
	JFactory::getLanguage()->load('com_rseventspro');

	// Add stylesheets
	JFactory::getDocument()->addStyleSheet(JURI::root(true).'/modules/mod_rseventspro_search/assets/css/style.css');
	JFactory::getDocument()->addScript(JURI::root(true).'/modules/mod_rseventspro_search/assets/js/scripts.js');
	if (rseventsproHelper::isJ3()) JFactory::getDocument()->addStyleSheet(JURI::root(true).'/modules/mod_rseventspro_search/assets/css/j3.css');

	// Get the Itemid
	$itemid = $params->get('itemid');
	$itemid = !empty($itemid) ? $itemid : RseventsproHelperRoute::getEventsItemid();

	require JModuleHelper::getLayoutPath('mod_rseventspro_search',$layout);
}
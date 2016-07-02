<?php
/**
* @package 		J2Store
* @copyright 	Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license 		GNU GPL v3 or later
*/
defined('_JEXEC') or die;
jimport( 'joomla.application.module.helper' );
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
require_once (JPATH_SITE.'/modules/mod_j2products/helper.php');

$document =JFactory::getDocument();
$document->addScript(JURI::root(true).'/media/j2store/js/j2store.js');
$subTemplate = $params->get('module_subtemplate', 'Default');
// add additional CSS and JS files associated with the layout
ModJ2ProductsHelper::includeAssets($params);

if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

$j2params 	= J2Store::config();
$j2currency	= J2Store::currency();

$list = ModJ2ProductsHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$module_id = $module->id;  // module id

require JModuleHelper::getLayoutPath('mod_j2products', $subTemplate.'/default');
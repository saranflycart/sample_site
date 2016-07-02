<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class modRseventsProSearch {
	
	public static function getLocations() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$default= array(JHTML::_('select.option', 0, JText::_('MOD_RSEVENTSPRO_SEARCH_ALL_LOCATIONS')));
		
		$query->clear()
			->select($db->qn('id','value'))->select($db->qn('name','text'))
			->from($db->qn('#__rseventspro_locations'))
			->where($db->qn('published').' = 1');
		
		$db->setQuery($query);
		$locations = $db->loadObjectList();
		
		if ($locations) {
			return array_merge($default, $locations);
		} else {
			return $default;
		}
	}
	
	public static function getCategories() {
		JHtml::addIncludePath(JPATH_SITE.'/libraries/joomla/html/html/');
		
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$default	= array(JHTML::_('select.option', 0, JText::_('MOD_RSEVENTSPRO_SEARCH_ALL_CATEGORIES')));
		$config		= array('filter.published' => array(1), 'filter.language' => array(JFactory::getLanguage()->getTag(),'*'));
		$categories = JHtml::_('category.options','com_rseventspro', $config);
		$user		= JFactory::getUser();
		$groups		= $user->getAuthorisedViewLevels();
		
		foreach ($categories as $i => $category) {
			$query->clear()
				->select($db->qn('access'))
				->from($db->qn('#__categories'))
				->where($db->qn('id').' = '.(int) $category->value);
			$db->setQuery($query);
			$access = $db->loadResult();
			
			if ($access && !in_array($access,$groups))
				unset($categories[$i]);
		}
		
		if ($categories) {
			return array_merge($default,$categories);
		} else {
			return $default;
		}
	}
	
	public static function getYesNo() {
		return array(JHTML::_('select.option', 0, JText::_('JNO')) ,JHTML::_('select.option', 1, JText::_('JYES')));
	}
}
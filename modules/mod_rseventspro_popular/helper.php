<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modRseventsProPopular {

	public static function getEvents($params) {
		$db			= JFactory::getDbo();
		$categories	= $params->get('categories','');
		$locations	= $params->get('locations','');
		$tags		= $params->get('tags','');
		$order		= $params->get('ordering','start');
		$direction	= $params->get('order','DESC');
		$limit		= (int) $params->get('limit',5);
		$where		= array();
		
		$query  = 'SELECT '.$db->qn('e.id').', '.$db->qn('e.name').', '.$db->qn('e.start').', '.$db->qn('e.end').', '.$db->qn('e.allday').', '.$db->qn('e.icon').', '.$db->qn('e.hits').' FROM '.$db->qn('#__rseventspro_events','e').' WHERE '.$db->qn('e.completed').' = 1 AND '.$db->qn('e.published').' = 1 AND '.$db->qn('e.hits').' > 0';
		
		if (!empty($categories)) {
			$categoryquery = '';
			if (JLanguageMultilang::isEnabled()) {
				$categoryquery .= ' AND c.language IN ('.$db->q(JFactory::getLanguage()->getTag()).','.$db->q('*').') ';
			}
			
			$user	= JFactory::getUser();
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$categoryquery .= ' AND c.access IN ('.$groups.') ';
			
			JArrayHelper::toInteger($categories);
			$where[] = ' AND '.$db->qn('e.id').' IN (SELECT '.$db->qn('tx.ide').' FROM '.$db->qn('#__rseventspro_taxonomy','tx').' LEFT JOIN '.$db->qn('#__categories','c').' ON '.$db->qn('c.id').' = '.$db->qn('tx.id').' WHERE '.$db->qn('c.id').' IN ('.implode(',',$categories).') AND '.$db->qn('tx.type').' = '.$db->q('category').' AND '.$db->qn('c.extension').' = '.$db->q('com_rseventspro').' '.$categoryquery.' )';
		}
		
		if (!empty($tags)) {
			JArrayHelper::toInteger($tags);
			$where[] = ' AND '.$db->qn('e.id').' IN (SELECT '.$db->qn('tx.ide').' FROM '.$db->qn('#__rseventspro_taxonomy','tx').' LEFT JOIN '.$db->qn('#__rseventspro_tags','t').' ON '.$db->qn('t.id').' = '.$db->qn('tx.id').' WHERE '.$db->qn('t.id').' IN ('.implode(',',$tags).') AND '.$db->qn('tx.type').' = '.$db->q('tag').') ';
		}
		
		if (!empty($locations)) {
			JArrayHelper::toInteger($locations);
			$where[] = ' AND '.$db->qn('e.location').' IN ('.implode(',',$locations).') ';
		}
		
		if (!empty($where))
			$query .= implode('',$where);
		
		$exclude = modRseventsProPopular::excludeEvents();
		
		if (!empty($exclude))
			$query .= ' AND '.$db->qn('e.id').' NOT IN ('.implode(',',$exclude).') ';
		
		$query .= ' ORDER BY '.$db->qn('e.hits').' DESC, '.$db->qn('e.'.$order).' '.$db->escape($direction).' ';
		
		$db->setQuery($query,0,$limit);
		return $db->loadObjectList();
	}
	
	protected static function excludeEvents() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		$ids	= array();
		
		$query->clear()
			->select($db->qn('ide'))
			->from($db->qn('#__rseventspro_taxonomy'))
			->where($db->qn('type').' = '.$db->q('groups'));
		
		$db->setQuery($query);
		$eventids = $db->loadColumn();
		
		if (!empty($eventids)) {
			foreach ($eventids as $id) {
				$query->clear()
					->select($db->qn('owner'))
					->from($db->qn('#__rseventspro_events'))
					->where($db->qn('id').' = '.(int) $id);
				
				$db->setQuery($query);
				$owner = (int) $db->loadResult();
				
				if (!rseventsproHelper::canview($id) && $owner != $user->get('id'))
					$ids[] = $id;
			}
			
			if (!empty($ids)) {
				JArrayHelper::toInteger($ids);
				$ids = array_unique($ids);
			}
		}
		
		return $ids;
	}
}
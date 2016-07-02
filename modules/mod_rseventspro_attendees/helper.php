<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modRseventsProAttendees {

	public static function getGuests() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$return = array();
		
		$query->clear()
			->select('DISTINCT('.$db->qn('email').')')->select($db->qn('idu'))->select($db->qn('name'))
			->from($db->qn('#__rseventspro_users'))
			->where($db->qn('ide').' = '.$id)
			->where($db->qn('state').' IN (0,1)');
		
		$db->setQuery($query);
		$guests = $db->loadObjectList();
		
		if (!empty($guests)) {
			foreach ($guests as $guest) {
				$object = new stdClass();
				$object->name = !empty($guest->idu) ? rseventsproHelper::getUser($guest->idu) : $guest->name;
				$object->url = !empty($guest->idu) ? rseventsproHelper::getProfile('guests',$guest->idu) : '';
				$object->avatar = rseventsproHelper::getAvatar($guest->idu,$guest->email);
				$return[] = $object;
			}
		}
		
		return $return;
	}
}
<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/shipping.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/toolbar.php');

class plgJ2StoreShipping_Free extends J2StoreShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element   = 'shipping_free';
	var $_order;

	/**
	 * Overriding
	 *
	 * @param $options
	 * @return unknown_type
	 */
	function onJ2StoreGetShippingView( $row )
	{
		if (!$this->_isMe($row))
		{
			return null;
		}
		$html = $this->viewList();
		return $html;
	}



	function onJ2StoreGetShippingOptions($element, $order)
	{
		// Check if this is the right plugin
		if (!$this->_isMe($element))
		{
			return null;
		}
		$found = true;
		$order->setAddress();
		// $this->_order = $order;
		$geozones = $this->params->get('geozones');
		//return true if we have empty geozones
        if(!empty($geozones))
        {
        	//incase All Geozone is selected
        	if(in_array('*',array_values($geozones))){
        		$found = true;
        	}else{
	        	$found = false;
				if(!is_array($geozones)){
	          		$geozones = explode(',', $geozones);
				}
	          	$orderGeoZones = $order->getShippingGeoZones();
	          	//loop to see if we have at least one geozone assigned
	          	foreach( $orderGeoZones as $orderGeoZone )
	          	{
	          		if(in_array($orderGeoZone->geozone_id, $geozones))
	          		{
	          			$found = true;
	          			break;
	          		}
	          	}
        	}
        }
        if($this->params->get('requires_coupon', 0)) {
        	if($order->has_free_shipping() == false) {
        		$found = false;        		 
        	} 
        }
		return $found;
	}

	/**
	 * Method to get shipping rates
	 * @param type string $element
	 * @param type obj $order
	 * @return $results
	 */
	function onJ2StoreGetShippingRates($element, $order)
	{
		// Check if this is the right plugin
		if (!$this->_isMe($element))
		{
			return null;
		}

		$vars = array();
		//set the address
		$order->setAddress();
		$subtotal = $order->order_subtotal;
		
		$min_subtotal = (float) $this->params->get('min_subtotal', 0);
		$max_subtotal = (float) $this->params->get('max_subtotal', -1);
		
		$status = true;
		if($min_subtotal > 0 && $min_subtotal > $subtotal) {
			$status = false;
		}
		if($max_subtotal > 0 && $subtotal > $max_subtotal ) {
			$status = false;
		}
		if(!$status) return $vars;
		
		$geozones_taxes = array();
		$params_geozones = $this->params->get('geozones');
		$i=0;
			$name = addslashes(JText::_($this->params->get('display_name', $this->_element)));	
			$vars[$i]['element'] = $this->_element;
			$vars[$i]['name'] = $name;
			$vars[$i]['code'] = '';
			$vars[$i]['price'] = 0;
			$vars[$i]['tax'] = 0;
			$vars[$i]['extra'] = 0;
			$vars[$i]['total'] = 0;
	
		return $vars;
	}
}
<?php
/*
* @package		plg_j2store_emailbasket
* @subpackage	J2Store
* @author    	Gokila Priya - Weblogicx India http://www.weblogicxindia.com
* @copyright	Copyright (c) 2014 Weblogicx India Ltd. All rights reserved.
* @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
* --------------------------------------------------------------------------------
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_Emailbasket extends J2StoreAppPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'app_emailbasket';

	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage('',JPATH_ADMINISTRATOR);
	}


	/**
	 * Overriding
	 *
	 * @param $options
	 * @return unknown_type
	 */
	function onJ2StoreGetAppView( $row )
	{

		if (!$this->_isMe($row))
		{
			return null;
		}

		$html = $this->viewList();


		return $html;
	}

	/**
	 * Validates the data submitted based on the suffix provided
	 * A controller for this plugin, you could say
	 *
	 * @param $task
	 * @return html
	 */
	function viewList()
	{
		$app = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.app.'.$this->_element;
		$html = "";
		JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
		JToolBarHelper::apply('apply');
		JToolBarHelper::save();
		JToolBarHelper::back('PLG_J2STORE_BACK_TO_APPS', 'index.php?option=com_j2store&view=apps');
		JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');

		$vars = new JObject();
		//model should always be a plural
		$this->includeCustomModel('AppEmailBaskets');
		$model = F0FModel::getTmpInstance('AppEmailBaskets', 'J2StoreModel');
		$data = $this->params->toArray();

		$newdata = array();
		$newdata['params'] = $data;
		$form = $model->getForm($newdata);
		$vars->form = $form;


		$this->includeCustomTables();

		$id = $app->input->getInt('id', '0');
		$vars->id = $id;
		$vars->action = "index.php?option=com_j2store&view=app&task=view&id={$id}";
		$html = $this->_getLayout('default', $vars);
		return $html;
	}

	/**
	 * Method to get the cart email layout
	 * @return layout
	 */
	function onJ2StoreAfterDisplayCart(){
		$user = JFactory::getUser();
		$vars = new JObject();
		//$cart = $this->getCartObject();
		$model =F0FModel::getTmpInstance('Carts','J2StoreModel');
		$items = $model->getItems();

		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->populateOrder($items)->getOrder();
		$order->validate_order_stock();
		//$vars->totals = $model->getTotal();

		$address_type =$this->params->get('address_field_type', 'register');

		$vars->storeProfile  =J2Store::storeProfile();


		$vars->items = $order->getItems();

		foreach($vars->items as $item) {
			if(isset($item->orderitemattributes) && count($item->orderitemattributes)) {
				foreach($item->orderitemattributes as &$attribute) {
					if($attribute->orderitemattribute_type == 'file') {
						unset($table);
						$table = F0FTable::getInstance('Upload', 'J2StoreTable');
						if($table->load(array('mangled_name'=>$attribute->orderitemattribute_value))) {
							$attribute->orderitemattribute_value = $table->original_name;
						}
					}
				}
			}
		}

		$selectableBase = J2Store::getSelectableBase();
		$vars->fieldsClass = $selectableBase;
		$address =F0FTable::getAnInstance('Address','J2StoreTable');
		$vars->fields = $selectableBase->getFields('billing',$address,'address');
		$vars->address = $address;
		$vars->currency = J2Store::currency();
		$vars->order = $order;
		$vars->params = J2Store::config();
		$vars->taxes = $order->getOrderTaxrates();
		$vars->shipping = $order->getOrderShippingRate();
		$vars->coupons = $order->getOrderCoupons();
		$vars->vouchers = $order->getOrderVouchers();
		$vars->body = $this->_getLayout('body', $vars);
		
		return $this->_getLayout('mail', $vars);
	}

	/**
	 * Method to get Cart Object
	 * to display as the body
	 * @return JObject
	 */
	function getCartObject(){

			$result = new JObject();
			$model = F0FModel::getTmpInstance('Carts','J2StoreModel');
			$params = JComponentHelper::getParams('com_j2store');
			$items = $model->getDataNew();
			$result->cartobject = $model->checkItems($items, $params->get('show_tax_total'));
			$result->total = $model->getTotals();
			return $result;
	}

	/**
	 * Method to send Email
	 *  @param array  $data
	 *  @return result
	 */
	function onJ2StoreCallback($row , $data){
		
		if (!$this->_isMe($row))
		{
			return null;
		}
		
		//get the application object
		$app = JFactory::getApplication();
		//create an empty array
		$json=array();
		//get the config class obj
		$config = JFactory::getConfig();
		//get the component params
		$params = J2Store::config();
		//get the mailer class object
		$mailer = JFactory::getMailer();
		$selectableBase =  J2Store::getSelectableBase();
		//get the error
		$json =  $selectableBase->validate($data, 'billing','address');



		//only enters if there is no error in the json
		if(!$json ){
			$json = $this->sendEmail($data);
		}
		echo json_encode($json);
		$app->close();
	}
	/**
	 * Method to send Email
	 * @param array $data
	 * @return array
	 */
	function sendEmail($data){
		$json = array();
		$vars = new JObject();
		//get the config class obj
		$config = JFactory::getConfig();
		//get the component params
		$params   = J2Store::config();

		$selectableBase = J2Store::getSelectableBase();
		$vars->fieldsClass = $selectableBase;
		$address = F0FTable::getAnInstance('Address','J2StoreTable');
		$address->bind($data);
		$vars->fields = $selectableBase->getFields('billing',$address,'address');
		$vars->data = $data;

		//get the mailer class object
		$mailer = JFactory::getMailer();
		//assign the item
		$model =F0FModel::getTmpInstance('Carts','J2StoreModel');
		$items = $model->getItems();

		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->populateOrder($items)->getOrder();
		//$order->validate_order_stock();
		$vars->items = $order->getItems();
		foreach($vars->items as $item) {
			if(isset($item->orderitemattributes) && count($item->orderitemattributes)) {
				foreach($item->orderitemattributes as &$attribute) {
					if($attribute->orderitemattribute_type == 'file') {
						unset($table);
						$table = F0FTable::getInstance('Upload', 'J2StoreTable');
						if($table->load(array('mangled_name'=>$attribute->orderitemattribute_value))) {
							$attribute->orderitemattribute_value = $table->original_name;
						}
					}
				}
			}
		}

		$vars->params = $params;
		$vars->currency = J2Store::currency();
		$vars->order = $order;
		$vars->taxes = $order->getOrderTaxrates();
		$vars->shipping = $order->getOrderShippingRate();
		$vars->coupons = $order->getOrderCoupons();
		$vars->vouchers = $order->getOrderVouchers();


		$vars->name = $name = $data['first_name'].' '.$data['last_name'];
		$vars->site_name = $sitename = $config->get('sitename');
		$body = $this->_getLayout('body', $vars);
		$body = $this->processInlineImages($body);
		$mailfrom = $config->get('mailfrom');
		$fromname = $config->get('fromname');
		$subject = $this->params->get('subject','');
		$subject = str_replace('[user_name]', $vars->name, $subject);
		$subject = str_replace('[site_name]', $vars->site_name, $subject);
		//$subject = JText::sprintf('PLG_J2STORE_EMAILBASKET_SUBJECT',$name,$sitename);
		$admin_emails = $params->get('admin_email') ;
		$admin_emails = explode(',',$admin_emails ) ;
		if(isset($data['email']) && !empty($data['email'])){
			$mailer->addRecipient($data['email']);
			$is_send_to_admin = $this->params->get('send_admin_email',0);
			if($is_send_to_admin){
				$mailer->addCC($admin_emails);
			}
			$recipient_email = $data['email'];
			$mailer->setSubject($subject );
			$mailer->setBody($body);
			$mailer->IsHTML(1);
			$mailer->setSender(array( $mailfrom, $fromname ));
			if(!$mailer->send()){
				$json['error']['msg'] =  JText::_('PLG_J2STORE_EMAILBASKET_SENDING_FAILED');
			}else{
				$json['success']['msg'] =JText::sprintf('PLG_J2STORE_EMAILBASKET_SENDING_SUCCESS',$recipient_email);
				if($this->params->get('empty_cart', 1)) {
					$session = JFactory::getSession();
					$session->set('j2store_cart', array());
					$json['redirect'] = JRoute::_('index.php?option=com_j2store');
				}
			}
		}else{
			$json['error']['msg'] =  JText::_('PLG_J2STORE_EMAILBASKET_EMAIL_ID_REQUIRED');
		}


		return $json;
	}

	public function processInlineImages($templateText) {

		$baseURL = str_replace('/administrator', '', JURI::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');
		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
				$url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					$ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						// Relative path, make absolute
						$url = $baseURL.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {

						if ($this->params->get('image_path_source','absolute') == 'url' )
						{
							// Image found, substitute
							if(!array_key_exists($url, $imageSubs)) {
								// First time I see this image, add as embedded image and push to
								// $imageSubs array.
								$imgidx++;
								//$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
								$imageSubs[$url] = $imgidx;
							}
							// Do the substitution of the image
							$temp .= 'cid:img'.$imageSubs[$url];
						} else {
							// providing absolute path for images sometimes solves the issue.
							// get the temp path from joomla config to predict the home folder
							$path_suffix = substr(JFactory::getConfig()->get('tmp_path'), 0, -4);

							// provide absolute image path
							$temp .= $path_suffix.'/'.$url;
						}
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		}
		return $templateText;
	}

	/**
	 * Method to get Country name
	 * @param int  $country_id
	 * @return string country name
	 */
	public function getCountryName($country_id){
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__j2store_countries AS a');
		$query->where('a.j2store_country_id='.$country_id);
		$db->setQuery($query);
		return $db->loadObject()->country_name;
	}

	public function getZoneName($zone_id){
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('z.*');
		$query->from('#__j2store_zones AS z');
		$query->where('z.j2store_zone_id='.$zone_id);
		$db->setQuery($query);
		return $db->loadObject()->zone_name;
	}

	/**
	 * Method to disable the cart view
	 * @param string $url
	 */
	public function onJ2StoreGetCheckoutLink(&$url){
		if($this->params->get('disable_checkout_btn',1)){
			$url ='';
		}
	}
}


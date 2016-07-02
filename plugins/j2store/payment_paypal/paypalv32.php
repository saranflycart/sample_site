<?php
/*
 * @package		Paypal Standard Plugin for J2Store
* @subpackage	J2Store
* @author    	Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
* @copyright	Copyright (c) 2014 Weblogicx India Ltd. All rights reserved.
* @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
* --------------------------------------------------------------------------------
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
class plgJ2StorePayment_paypal extends J2StorePaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'payment_paypal';
	var $_isLog      = false;
	var $_j2version = null;


	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		if($this->params->get('debug', 0)) {
			$this->_isLog = true;
		}
	}

	function onJ2StoreCalculateFees($order) {
		//is customer selected this method for payment ? If yes, apply the fees
		$payment_method = $order->get_payment_method();

		if($payment_method == $this->_element) {
			$total = $order->order_subtotal + $order->order_shipping + $order->order_shipping_tax;
			$surcharge = 0;
			$surcharge_percent = $this->params->get('surcharge_percent', 0);
			$surcharge_fixed = $this->params->get('surcharge_fixed', 0);
			if((float) $surcharge_percent > 0 || (float) $surcharge_fixed > 0) {
				//percentage
				if((float) $surcharge_percent > 0) {
					$surcharge += ($total * (float) $surcharge_percent) / 100;
				}

				if((float) $surcharge_fixed > 0) {
					$surcharge += (float) $surcharge_fixed;
				}

				$name = $this->params->get('surcharge_name', JText::_('J2STORE_CART_SURCHARGE'));
				$tax_class_id = $this->params->get('surcharge_tax_class_id', '');
				$taxable = false;
				if($tax_class_id && $tax_class_id > 0) $taxable = true;
				if($surcharge > 0) {
					$order->add_fee($name, round($surcharge, 2), $taxable, $tax_class_id);
				}

			}


		}

	}


	/**
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _prePayment( $data )
	{
		// get component params
		$params = J2Store::config();
		$currency = J2Store::currency();

		// prepare the payment form

		$vars = new JObject();
		$vars->order_id = $data['order_id'];
		$vars->orderpayment_id = $data['orderpayment_id'];

		F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$order = F0FTable::getInstance('Order', 'J2StoreTable')->getClone();
		$order->load(array('order_id'=>$data['order_id']));

		$currency_values= $this->getCurrency($order);
		$line_items = $order->get_line_items();
		foreach($line_items as &$line_item) {
			$line_item['price'] = $currency->format($line_item['amount'], $currency_values['currency_code'], $currency_values['currency_value'], false);
		}

		$vars->currency_code =$currency_values['currency_code'];
		$vars->orderpayment_amount = $currency->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);

		$vars->orderpayment_type = $this->_element;

		$vars->cart_session_id = JFactory::getSession()->getId();

		$vars->display_name = $this->params->get('display_name', 'PAYMENT_PAYPAL');
		$vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
		$vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');

		//$vars->products = $products;
		$vars->products = $line_items;
		if($params->get('checkout_price_display_options', 1) == 0) {
			$vars->tax_cart = $currency->format($order->order_tax, $currency_values['currency_code'], $currency_values['currency_value'], false);
		}

		$vars->discount_amount_cart = $currency->format($order->get_total_discount($params->get('checkout_price_display_options', 1)), $currency_values['currency_code'], $currency_values['currency_value'], false);

		// set payment plugin variables
		// set payment plugin variables
		if($this->params->get('sandbox', 0)) {
			$vars->merchant_email = trim($this->_getParam( 'sandbox_merchant_email' ));
		}else {
			$vars->merchant_email = trim($this->_getParam( 'merchant_email' ));
		}

		$rootURL = rtrim(JURI::base(),'/');
		$subpathURL = JURI::base(true);
		if(!empty($subpathURL) && ($subpathURL != '/')) {
			$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
		}


		$vars->post_url = $this->_getPostUrl();
		$vars->return_url = $rootURL.JRoute::_("index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display");
		$vars->cancel_url = $rootURL.JRoute::_("index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel");
		$vars->notify_url = JURI::root()."index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
		//$vars->currency_code = $this->_getParam( 'currency', 'USD' );

		$orderinfo = $order->getOrderInformation();

		// set variables for user info
		$vars->first_name   = $orderinfo->billing_first_name;
		$vars->last_name    = $orderinfo->billing_last_name;
		$vars->email        = $order->user_email;
		$vars->address_1    = $orderinfo->billing_address_1;
		$vars->address_2    = $orderinfo->billing_address_2;
		$vars->city         = $orderinfo->billing_city;
		$vars->country      = $this->getCountryById($orderinfo->billing_country_id)->country_name;
		$vars->region       = $this->getZoneById($orderinfo->billing_zone_id)->zone_name;
		$vars->postal_code  = $orderinfo->billing_zip;


		$vars->invoice = $order->getInvoiceNumber();

		$html = $this->_getLayout('prepayment', $vars);
		return $html;
	}

	/**
	 * Processes the payment form
	 * and returns HTML to be displayed to the user
	 * generally with a success/failed message
	 *
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _postPayment( $data )
	{
		// Process the payment
		$app = JFactory::getApplication();
		$paction = $app ->input->getString('paction');

		$vars = new JObject();

		switch ($paction)
		{
			case "display":
				$vars->message = JText::_($this->params->get('onafterpayment', ''));
				$html = $this->_getLayout('message', $vars);
				$html .= $this->_displayArticle();
				break;
			case "process":
				$vars->message = $this->_process();
				$html = $this->_getLayout('message', $vars);
				echo $html; // TODO Remove this
				$app->close();
				break;
			case "cancel":
				$vars->message = JText::_($this->params->get('oncancelpayment', ''));
				$html = $this->_getLayout('message', $vars);
				break;
			default:
				$vars->message = JText::_($this->params->get('onerrorpayment', ''));
				$html = $this->_getLayout('message', $vars);
				break;
		}

		return $html;
	}

	/**
	 * Prepares variables for the payment form
	 *
	 * @return unknown_type
	 */
	function _renderForm( $data )
	{
		$user = JFactory::getUser();
		$vars = new JObject();
		$vars->onselection_text = $this->params->get('onselection', '');
		$html = $this->_getLayout('form', $vars);

		return $html;
	}

	/************************************
	 * Note to 3pd:
	*
	* The methods between here
	* and the next comment block are
	* specific to this payment plugin
	*
	************************************/

	/**
	 * Gets the Paypal gateway URL
	 *
	 * @param boolean $full
	 * @return string
	 * @access protected
	 */
	function _getPostUrl($full = true)
	{
		$url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		if ($full)
		{
			$url = 'https://' . $url . '/cgi-bin/webscr';
		}

		return $url;
	}


	/**
	 * Gets the value for the Paypal variable
	 *
	 * @param string $name
	 * @return string
	 * @access protected
	 */
	function _getParam( $name, $default='' )
	{
		$return = $this->params->get($name, $default);

		$sandbox_param = "sandbox_$name";
		$sb_value = $this->params->get($sandbox_param);
		if ($this->params->get('sandbox') && !empty($sb_value))
		{
			$return = $this->params->get($sandbox_param, $default);
		}

		return $return;
	}

	/**
	 * Validates the IPN data
	 *
	 * @param array $data
	 * @return string Empty string if data is valid and an error message otherwise
	 * @access protected
	 */
	function _validateIPN( $data, $order)
	{
		$paypal_url = $this->_getPostUrl(true);

		$request = 'cmd=_notify-validate';

		foreach ($data as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}

		$curl = curl_init($paypal_url);

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($curl);

		if (!$response) {
			$this->_log('CURL failed ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
		}

		$this->_log('IPN Validation REQUEST: ' . $request);
		$this->_log('IPN Validation RESPONSE: ' . $response);

		if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0)) {
			return '';
		}elseif (strcmp ($response, 'INVALID') == 0) {
			return JText::_('J2STORE_PAYPAL_ERROR_IPN_VALIDATION');
		}
		return '';
	}

	/**
	 *
	 * @return HTML
	 */
	function _process()
	{
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);

		$error = '';

		// prepare some data
		$validate_ipn = $this->params->get('validate_ipn', 1);
		if($validate_ipn) {
			$custom = $data['custom'];
			$custom_array = explode('|', $custom);

			$order_id  = $custom_array[0];

			// load the orderpayment record and set some values

			$order = F0FTable::getInstance('Order', 'J2StoreTable')->getClone();

			$result = $order->load( array('order_id'=>$order_id));
			if($result && !empty($order->order_id) && ($order->order_id == $order_id) ) {
				// validate the IPN info
				$error = $this->_validateIPN($data, $order);
				if (!empty($error))
				{
					// ipn Validation failed
					$data['ipn_validation_results'] = $error;
				}

			}
		}

		$data['transaction_details'] = $this->_getFormattedTransactionDetails( $data );

		$this->_log($data['transaction_details']);

		// process the payment based on its type
		if ( !empty($data['txn_type']) )
		{
			$payment_error = '';

			if ($data['txn_type'] == 'cart') {
				// Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
				$payment_error = $this->_processSale( $data, $error );
			}
			else {
				// other methods not supported right now
				$payment_error = JText::_( "J2STORE_PAYPAL_ERROR_INVALID_TRANSACTION_TYPE" ).": ".$data['txn_type'];
			}

			if ($payment_error) {
				// it seems like an error has occurred during the payment process
				$error .= $error ? "\n" . $payment_error : $payment_error;
			}
		}

		if ($error) {
			$sitename = $config->get('sitename');
			//send error notification to the administrators
			$subject = JText::sprintf('J2STORE_PAYPAL_EMAIL_PAYMENT_NOT_VALIDATED_SUBJECT', $sitename);

			$receivers = $this->_getAdmins();
			foreach ($receivers as $receiver) {
				$body = JText::sprintf('J2STORE_PAYPAL_EMAIL_PAYMENT_FAILED_BODY', $receiver->name, $sitename, JURI::root(), $error, $data['transaction_details']);
				J2Store::email()->sendErrorEmails($receiver->email, $subject, $body);
			}
			return $error;
		}


		// if here, all went well
		$error = 'processed';
		return $error;
	}

	/**
	 * Processes the sale payment
	 *
	 * @param array $data IPN data
	 * @return boolean Did the IPN Validate?
	 * @access protected
	 */
	function _processSale($data, $ipnValidationFailed = '') {
		/*
		 * validate the payment data
		 */
		$errors = array ();

		if (! empty ( $ipnValidationFailed )) {
			$errors [] = $ipnValidationFailed;
		}

		if ($this->params->get ( 'sandbox', 0 )) {
			$merchant_email = trim ( $this->_getParam ( 'sandbox_merchant_email' ) );
		} else {
			$merchant_email = trim ( $this->_getParam ( 'merchant_email' ) );
		}
		// is the recipient correct?
		if (empty ( $data ['receiver_email'] ) || JString::strtolower ( $data ['receiver_email'] ) != JString::strtolower ( trim ( $merchant_email ) )) {
			$errors [] = JText::_ ( 'J2STORE_PAYPAL_MESSAGE_RECEIVER_INVALID' );
		}

		$custom = $data ['custom'];
		$custom_array = explode ( '|', $custom );

		$order_id = $custom_array [0];

		// load the orderpayment record and set some values
		$order = F0FTable::getInstance ( 'Order', 'J2StoreTable' )->getClone ();

		$result = $order->load ( array (
				'order_id' => $order_id
		) );
		if ($result && ! empty ( $order->order_id ) && ($order->order_id == $order_id)) {

			$order->add_history(JText::_('J2STORE_PAYPAL_CALLBACK_IPN_RESPONSE_RECEIVED'));

			$order->transaction_details = $data ['transaction_details'];
			$order->transaction_id = $data ['txn_id'];
			$order->transaction_status = $data ['payment_status'];

			// check the stored amount against the payment amount

			// check the payment status
			if (empty ( $data ['payment_status'] ) || ($data ['payment_status'] != 'Completed' && $data ['payment_status'] != 'Pending')) {
				$errors [] = JText::sprintf ( 'J2STORE_PAYPAL_MESSAGE_STATUS_INVALID', @$data ['payment_status'] );
			}

			$currency = J2Store::currency();
			$currency_values= $this->getCurrency($order);
			$gross = $currency->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);

			$mc_gross = floatval($data['mc_gross']);
			if ($mc_gross > 0)
			{
				// A positive value means "payment". The prices MUST match!
				// Important: NEVER, EVER compare two floating point values for equality.
				$isValid = ($gross - $mc_gross) < 0.05;
				if(!$isValid) {
					$errors[] = 'Paid amount does not match the order total';
				}
			}

			// save the data
			if (! $order->store ()) {
				// $errors [] = $order->getError ();
			}

			// set the order's new status
			if (count ( $errors )) {
				// mark as failed
				$order->update_status ( 3 );

			} elseif (strtoupper($data ['payment_status']) == 'PENDING') {

				// set order to pending. Also notify the customer that it is pending
				$order->update_status ( 4, true );
				// reduce the order stock. Because the status is pending.
				$order->reduce_order_stock ();

			} elseif(strtoupper($data ['payment_status']) == 'COMPLETED') {
				$order->payment_complete ();
			}

			//clear cart
			$order->empty_cart();
		}

		return count ( $errors ) ? implode ( "\n", $errors ) : '';
	}

		/**
		 * Formatts the payment data for storing
		 *
		 * @param array $data
		 * @return string
		 */
		function _getFormattedTransactionDetails( $data )
		{
			$separator = "\n";
			$formatted = array();

			foreach ($data as $key => $value)
			{
				if ($key != 'view' && $key != 'layout')
				{
					$formatted[] = $key . ' = ' . $value;
				}
			}

			return count($formatted) ? implode("\n", $formatted) : '';
		}

		/**
		 * Simple logger
		 *
		 * @param string $text
		 * @param string $type
		 * @return void
		 */
		function _log($text, $type = 'message')
		{
			if ($this->_isLog) {
				$file = JPATH_ROOT . "/cache/{$this->_element}.log";
				$date = JFactory::getDate();

				$f = fopen($file, 'a');
				fwrite($f, "\n\n" . $date->format('Y-m-d H:i:s'));
				fwrite($f, "\n" . $type . ': ' . $text);
				fclose($f);
			}
		}

		/**
		 * Gets admins data
		 *
		 * @return array|boolean
		 * @access protected
		 */
		function _getAdmins()
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('u.email');
			$query->from('#__users AS u');
			$query->join('LEFT', '#__user_usergroup_map AS ug ON u.id=ug.user_id');
			$query->where('u.sendEmail = 1');
			$query->where('ug.group_id = 8');

			$db->setQuery($query);
			$admins = $db->loadColumn();
			if ($error = $db->getErrorMsg()) {
				JError::raiseError(500, $error);
				return false;
			}

			return $admins;
		}

		function getCurrency($order, $convert=false) {
			$results = array();
			$convert = false;

				$currencyObject = J2Store::currency ();

				$currency_code = $order->currency_code;
				$currency_value = $order->currency_value;

				//accepted currencies
				$currencies = $this->getAcceptedCurrencies();
				if(!in_array($order->currency_code, $currencies)) {
					$default_currency = 'USD';
					if($currencyObject->has($default_currency)) {
						$currencyObject->set($default_currency);
						$currency_code = $default_currency;
						$currency_value = $currencyObject->getValue($default_currency);
						$convert = true;
					}
				}

			$results['currency_code'] = $currency_code;
			$results['currency_value'] = $currency_value;
			$results['convert'] = $convert;

			return $results;
		}

		function getAcceptedCurrencies() {
			$currencies = array(
					'AUD',
					'BRL',
					'CAD',
					'CZK',
					'DKK',
					'EUR',
					'HKD',
					'HUF',
					'ILS',
					'JPY',
					'MYR',
					'MXN',
					'NOK',
					'NZD',
					'PHP',
					'PLN',
					'GBP',
					'RUB',
					'SGD',
					'SEK',
					'CHF',
					'TWD',
					'THB',
					'TRY',
					'USD'
			);
			return $currencies;
		}

	}

	/* TYPICAL RESPONSE FROM PAYPAL INCLUDES:
	 * mc_gross=49.99
	* &protection_eligibility=Eligible
	* &address_status=confirmed
	* &payer_id=Q5HTJ93G8FQKC
	* &tax=0.00
	* &address_street=10101+Some+Street
	* &payment_date=12%3A13%3A19+Dec+05%2C+2008+PST
	* &payment_status=Completed
	* &charset=windows-1252
	* &address_zip=11259
	* &first_name=John
	* &mc_fee=1.75
	* &address_country_code=US
	* &address_name=John+Doe
	* &custom=some+custom+value
	* &payer_status=verified
	* &business=receiver%40domain.com
	* &address_country=United+States
	* &address_city=Some+City
	* &quantity=1
	* &payer_email=sender%40emaildomain.com
	* &txn_id=3JK16594EX581780W
	* &payment_type=instant
	* &payer_business_name=John+Doe
	* &last_name=Doe
	* &address_state=CA
	* &receiver_email=receiver%40domain.com
	* &payment_fee=1.75
	* &receiver_id=YG9UDRP6DE45G
	* &txn_type=web_accept
	* &item_name=Name+of+item
	* &mc_currency=USD
	* &item_number=Number+of+Item
	* &residence_country=US
	* &handling_amount=0.00
	* &transaction_subject=Subject+of+Transaction
	* &payment_gross=49.99
	* &shipping=0.00
	* &=
	*/

	/**
	 * VALID PAYMENT_STATUS VALUES returned from Paypal
	*
	* Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
	* Completed: The payment has been completed, and the funds have been added successfully to your account balance.
	* Created: A German ELV payment is made using Express Checkout.
	* Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
	* Expired: This authorization has expired and cannot be captured.
	* Failed: The payment has failed. This happens only if the payment was made from your customer�s bank account.
	* Pending: The payment is pending. See pending_reason for more information.
	* Refunded: You refunded the payment.
	* Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
	* Processed: A payment has been accepted.
	* Voided: This authorization has been voided.
	*/

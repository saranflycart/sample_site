<?php
/**
 * --------------------------------------------------------------------------------
 * System - RsEvent as product
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
jimport('joomla.plugin.plugin');
class plgSystemJ2rsevents extends JPlugin {
	// Main constructor
	public function __construct( &$subject, $config ) {
		parent::__construct( $subject, $config );
		$this->loadLanguage();
	}

	// Check if we can run this plugin
	protected static function canRun() {
		if (file_exists(JPATH_ADMINISTRATOR.'/components/com_j2store/j2store.php') 
		&& file_exists(JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php')) {
			return true;
		}
		return false;
	}

	// RSEvents!Pro add custom script
	public function rsepro_onAfterEventDisplay($itemObject) {
		$j2params = J2Store::config();
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
		$html = '';
		if(isset( $itemObject['event'] ) && !empty( $itemObject['event'] )){
			$show_image = $j2params->get('item_display_j2store_images', 1);
			$images = '';
			if($product->get_product_by_source( 'com_rseventspro',$itemObject['event']->id )){

				$html = $product->get_product_html();
				//$image_type = $j2params->get('item_image_type', 'thumbnail');
				//$images = $product->get_product_images_html($image_type,$this->params);
			}

			if($show_image  && !empty( $images )) {
				$image_html = $images;
			}
			if($html === false) {
				$html = '';
			}
			$html = $image_html.$html;
		}

		echo $html;
	}
	
	// RSEvents!Pro add custom script
	public function rsepro_addCustomScripts() {		
		if (!self::canRun()) return;	
	}
		
	//Add menu for "J2STORE"
	public function rsepro_addMenuOption() {
		if (!self::canRun()) return;
		echo "<li>
				<a data-toggle='tab' href='javascript:void(0)' data-target='#rspro-edit-j2store'>
					<span>".JText::_('COM_J2STORE')."</span>
				</a>
			</li>";
		$doc = JFactory::getDocument();

		require_once ((dirname(__FILE__).'/fields/').strtolower('j2store').'.php');
		$jFormField =  new JFormFieldJ2Store();
		$html = $jFormField->getControlGroup();
		$j2html = json_encode($html);
		$script = "
		if(typeof(j2store) == 'undefined') {
		var j2store = {};
		}
		if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
		}

		(function($) {
		$(document).ready(function() {
	
		var form = $('#adminForm');
		var string ={$j2html};					
			form.find('.tab-content').append('<div class=\'tab-pane\' id=\'rspro-edit-j2store\'></div>');
			var elements = $(string).map(function() {
	 	 		return $('#rspro-edit-j2store').append(this).html();
	 	 	});
		var active = form.find('li.active').find('a').data('target');
		if(active =='#rspro-edit-j2store'){
			var active_div = form.find('.tab-content .active');
			active_div.removeClass('active');			
			var j2store_div = form.find('.tab-content #rspro-edit-j2store');			
			j2store_div.addClass('active');
			
		}
			form.find('#rspro-edit-j2store .container').removeClass('container');
			form.find('#rspro-edit-j2store .container').addClass('j2store-container');
		});
		
		})(j2store.jQuery);
		";

		$doc->addScriptDeclaration($script);
	}
    //save price data
	public function rsepro_afterEventStore($table){

     	//~ if (!self::canRun()) return;
		 
		$app=JFactory::getApplication();

		$format = array('jform'=>'');
		
		// getting the attribs from input
		$inp_data = $app->input->getArray($format);
		$id = isset( $inp_data['jform']['id'] ) ? $inp_data['jform']['id']:0;

		if(isset( $inp_data['jform']['j2rsform']['j2store'] ) && !empty( $inp_data['jform']['j2rsform']['j2store'] )){
			$j2store_attribs = $inp_data['jform']['j2rsform']['j2store'];
			$j2store_attribs['product_source'] = "com_rseventspro";
			$j2store_attribs['product_source_id'] = $id;
			$j2store_attribs_obj = json_decode ( json_encode( $j2store_attribs ) ) ;
			F0FModel::getTmpInstance('Products', 'J2StoreModel')->save($j2store_attribs);
		}
		return true;
	}


	function onJ2StoreAfterGetProduct(&$product) {

		if(isset($product->product_source) && $product->product_source == 'com_rseventspro' ) {
			static $sets;
			if(!is_array($sets)) {
				$sets = array();
			}
			require_once JPATH_SITE."/components/com_rseventspro/helpers/events.php";
			require_once JPATH_SITE."/components/com_rseventspro/helpers/rseventspro.php";
			$event = new RSEvent($product->product_source_id);
			$content =  $event->getEvent();

			//$content = $this->getArticle($product->product_source_id);
			if(isset($content->id) && $content->id) {
				//assign
				$product->source = $content;
				$product->product_name = $content->name;
				$product->product_short_desc = "";//$content->introtext;
				$product->product_long_desc = $content->description;
				$product->product_edit_url = JRoute::_('index.php?option=com_rseventspro&view=event&layout=edit&id='.$content->id);
				/*$com_path = JPATH_SITE.'/components/com_rseventspro/';
				if (!class_exists('ContentHelperRoute')) {
					require_once $com_path.'helpers/route.php';
				}
				if (!class_exists('ContentRouter')) {
					include $com_path.'router.php';
				}*/
				//echo "<pre>";print_r ( $content );exit;
				//$content->slug    = $content->id ;//. ':' . $content->alias;
				//$cat_alias = isset($content->category_alias) ? $content->category_alias : '';
				//$content->catslug = $content->catid . ':' .$cat_alias;
				$product->product_view_url = rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($content->id,$content->name));
				//$link = ContentHelperRoute::getArticleRoute($content->slug, $content->catslug, $content->language);
				//$product->product_view_url = JRoute::_($link);

				if($content->published == 1 ) {
					$product->exists = 1;
				} else {
					$product->exists = 0;
				}

				$sets[$product->product_source][$product->product_source_id] = $content;
			} else {
				$product->exists = 0;
			}

		}
	}

	function _updateCurrency() {
		$session = JFactory::getSession();
		//if auto update currency is set, then call the update function
		$store_config = J2Store::storeProfile();
		//session based check. We dont want to update currency when we load each and every item.
		if($store_config->get('config_currency_auto') && !$session->has('currency_updated', 'j2store')) {
			F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->updateCurrencies();
			$session->set('currency_updated', '1', 'j2store');
		}

	}

	function onJ2StoreAfterGetCartItems(&$items) {
		foreach($items as $key=>$item) {
			if($item->product_source == 'com_rseventspro') {
				require_once JPATH_SITE."/components/com_rseventspro/helpers/events.php";
				require_once JPATH_SITE."/components/com_rseventspro/helpers/rseventspro.php";
				$event = new RSEvent($item->product_source_id);
				$content =  $event->getEvent();
				//$article = J2Store::article()->getArticle($item->product_source_id);
				if($content->published != 1) {
					unset($items[$key]);
				}
			}
		}
	}
}

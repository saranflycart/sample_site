<?php
/**
* @package J2Store
* @copyright Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license GNU GPL v3 or later
*/
defined('_JEXEC') or die;
jimport( 'joomla.application.module.helper' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

$com_path = JPATH_SITE.'/components/com_content/';
if (!class_exists('ContentHelperRoute')) {
		require_once $com_path.'helpers/route.php';
	}
if (!class_exists('ContentRouter')) {
	include $com_path.'router.php';
}

/**
 * Helper for mod_j2products
 * @package     J2Store
 * @subpackage  mod_j2products
 */
class ModJ2ProductsHelper
{
	/**
	 * Method to include the CSS files
	 * */
	public static function includeAssets($params){
		$subTemplate = $params->get('module_subtemplate', 'Default');
		$module_layout_path =  JModuleHelper::getLayoutPath('mod_j2products', $subTemplate.'/default');
		$module_layout_path =  str_replace("default.php", "", $module_layout_path);
		$module_layout_path .= 'assets';
		$path_prefix = str_replace(JPATH_SITE, "", $module_layout_path );
		$files = JFolder::files($module_layout_path);
		$css_files = array();
		$js_files = array();
		if (is_dir($module_layout_path)){
			foreach($files as $file){
				if( self::endsWith($file,'.css') ) {
					$css_files[] = $path_prefix.'/'.$file;
				}
				if( self::endsWith($file,'.js') ) {
					$js_files[] = $path_prefix.'/'.$file;
				}
			}
		}
		$document = JFactory::getDocument();
		// include css files
		if ( count($css_files) > 0 ) {
			foreach ($css_files as $css_file) {
				if (JFile::exists(JPATH_SITE.$css_file)) {
					$document->addStyleSheet(JURI::root().$css_file);
				}
			}
		}
		// include js files
		if ( count($js_files) > 0 ) {
			foreach ($js_files as $js_file) {
				if (JFile::exists(JPATH_SITE.$js_file)) {
					$document->addScript(JURI::root().$js_file);
				}
			}
		}
	}

	public static function getList(&$params)
	{
		// based on product source get the item or item ids
		$obj = new ModJ2ProductsHelper();
		$list  = array();
		$product_ids = $obj->getProductIds($params);
		if (!empty($product_ids)) {
			// pre-process the items or result array
			$list  = $obj->prepareProducts($params, $product_ids);	
		}
		
		return $list;
	}

	/**
	 * Method to get the products by source
	 * @param JRegistry Object $params module parameters
	 * */
	function getProductIds($params){
		$source = $params->get('product_source_type','category');
		switch ($source) {
			case 'category': // get the product ids from the categories selected in module params
				$product_ids = array();
				$integration = $params->get('content_integration','joomla');
				// check if file exists and class exists and include the product source file
				$class_path = JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php' ;
				$product_source_class = 'ProductSource'.ucfirst($integration);
				if (file_exists($class_path) ) {
					require_once JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php';
				}elseif(!class_exists($product_source_class)) {
					return $product_ids ;
				}
				$product_source = new $product_source_class();
				$product_ids = $product_source->getProductIdsByCategory( $params );

				return $product_ids;
							break;
			case 'selected_products': // get the product ids from the selected list
							$product_ids = array();
							$limit = $params->get('number_of_items',6);
							$params_product_ids = $params->get('product_ids','');
							if ( !empty($params_product_ids) ) {
								$product_ids = explode(',', $params_product_ids);
							}
							// remove duplicates
							$product_ids = array_unique($product_ids);
							$product_ids = array_slice($product_ids, 0, $limit+1);
							return $product_ids;
							break;
			case 'best_selling': // get the product ids of best selling products
							$db = JFactory::getDBO();
							$query = $db->getQuery(true);
							$query->select('product_id')
									->from('( SELECT product_id, count( product_id ) bestsell_count
											FROM #__j2store_orderitems
											GROUP BY product_id
											ORDER BY bestsell_count DESC ) as a');
							$limit = $params->get('number_of_items',6);
							$db->setQuery( $query, 0, $limit );
							$product_ids = $db->loadColumn();
							return $product_ids;
							break;
			case 'up_sells': // get the product ids from upsells of the current product
							break;
			case 'cross_sells': // get the product ids from cross sells of the current product
							break;
			case 'related_products': // get the product ids of both upsells and cross sells of current product or for the product in the cart
							break;
			case 'related_buys': // get the product ids from the related purchases of the current product
							break;
			default:
				// by default show some products
				break;
		}
	}

	/**
	 * Method to prepare the products and return in a list
	 * @param 	array 	$product_ids 	product ids
	 * @return 	list            		list of product objects
	 * */
	function prepareProducts($params,$product_ids){
		//static $sets=array();
		$sets=array();
		// prapare a hash of product ids and load in static set
		$product_ids_src = $product_ids ;
		sort($product_ids_src);
		$hash = implode('.',$product_ids_src);
		// if already present in set, return the data
		if ( isset($sets[$hash]) ) {
			return $sets[$hash];
		}

		$product_helper = J2Store::product();

		$integration = $params->get('content_integration','joomla');
		$class_path = JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php' ;
		$product_source_class = 'ProductSource'.ucfirst($integration);
		$product_source_obj = '';
		if (file_exists($class_path) && !class_exists($product_source_class) ) {
			require_once JPATH_SITE.'/modules/mod_j2products/library/source/'.$integration.'.php';
		}
		if ( class_exists($product_source_class) ) {
			$product_source_obj = new $product_source_class();
		}

		$list = array();
		foreach ($product_ids as $k => $pid) {
			// prepare the product
			$prod_table = F0FTable::getAninstance('Product', 'J2StoreTable')->getClone();
			$prod_table->load(array('j2store_product_id' => $pid));

			// check if the item exists
			if ( isset($prod_table->j2store_product_id) && $prod_table->j2store_product_id > 0 ) {
				$product = $product_helper->setId( $prod_table->j2store_product_id)->getProduct();
				F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);

				// prepare the product to have all the show flags, links, titles, data needed to be displayed in the module layout
				$product->show_title 		= $params->get('show_title',1);
				$product->link_title 		= $params->get('link_title',1);
				$product->show_category		= $params->get('show_category',0);
				$product->show_sku			= $params->get('show_sku',0);

				$product->show_price		= $params->get('show_price',1);
				$product->show_price_taxinfo= $params->get('show_price_taxinfo',1);
				$product->show_special_price= $params->get('show_special_price',1);
				$product->show_offers		= $params->get('show_offers',1);
				$product->show_stock		= $params->get('show_stock',0);

				$product->show_options		= $params->get('show_options',0);
				$product->show_cart			= $params->get('show_cart',1);
				$product->show_introtext	= $params->get('show_introtext',1);
				$product->introtext_limit	= $params->get('introtext_limit',50);
				$product->show_readmore		= $params->get('show_readmore',1);
				$product->show_quickview	= $params->get('show_quickview',0);

				$product->show_image		= $params->get('show_image',1);
				$product->link_image		= $params->get('link_image',1);
				$product->image_type		= $params->get('image_type','thumbimage');
				$product->image_size_width	= $params->get('image_size_width',80);
				$product->image_size_height	= $params->get('image_size_height',80);
				$product->image_position	= $params->get('image_position','left');

				$product->show_navigation	= $params->get('show_navigation',0);
				$product->show_pagination	= $params->get('show_pagination',0);
				// define below properties on prepare product
				$product->content_link = '';
				$product->content_image = '';
				$product->category_name = '';
				$product->category_link = '';

				if ( !empty($product_source_obj) ) {
					$product_source_obj->prepareProduct( $params, $product);
				}

				//TODO: change the link based on integration type and module settings
				$product->module_display_link = $product->product_link;
				$product->module_introtext = self::truncate($product->product_short_desc, $product->introtext_limit); // truncated intro text
				if(!empty($product->addtocart_text)) {
					$product->cart_button_text = JText::_($product->addtocart_text);
				} else {
					$product->cart_button_text = JText::_('J2STORE_ADD_TO_CART');
				}

				if($product->variant->availability || J2Store::product()->backorders_allowed($product->variant)) {
					$product->display_cart_block = true;
				} else {
					$product->display_cart_block = false;
				}

				if($product->product_type == 'variable') {
					$product->display_cart_block = true;
				}

				if( isset($product) && $product->enabled && $product->visibility ){
					$list[$pid] = $product ;
				}
			}
			$prod_table->reset();
		}

		$sets[$hash] = $list ;
		return $sets[$hash];
	}

	///////////////////// 	Utility functions 	////////////////////////

	/**
	 * Method to truncate introtext
	 *
	 * The goal is to get the proper length plain text string with as much of
	 * the html intact as possible with all tags properly closed.
	 *
	 * @param string   $html       The content of the introtext to be truncated
	 * @param integer  $maxLength  The maximum number of charactes to render
	 *
	 * @return  string  The truncated string
	 */
	public static function truncate($html, $maxLength = 0)
	{
		$baseLength = strlen($html);
		$diffLength = 0;

		// First get the plain text string. This is the rendered text we want to end up with.
		$ptString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

		for ($maxLength; $maxLength < $baseLength;)
		{
			// Now get the string if we allow html.
			$htmlString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

			// Now get the plain text from the html string.
			$htmlStringToPtString = JHtml::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

			// If the new plain text string matches the original plain text string we are done.
			if ($ptString == $htmlStringToPtString)
			{
				return $htmlString;
			}
			// Get the number of html tag characters in the first $maxlength characters
			$diffLength = strlen($ptString) - strlen($htmlStringToPtString);

			// Set new $maxlength that adjusts for the html tags
			$maxLength += $diffLength;
			if ($baseLength <= $maxLength || $diffLength <= 0)
			{
				return $htmlString;
			}
		}
		return $html;
	}

	public static function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}
}
<?php
/**
* @package 		J2Store
* @copyright 	Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license 		GNU GPL v3 or later
*/
defined('_JEXEC') or die;

class ProductSourceJoomla {

	private $type = 'joomla';

	function getProductIdsByCategory( $module_params ){
		$params = $module_params ;
		$cat_ids = $params->get('catids','');
		$include_subcats = $params->get('include_subcategories',0); // do not include subcategories by default
		$include_subcat_level = $params->get('include_subcat_level',0);
		$product_ids = array();

		if(!is_array($cat_ids)) {
			$cat_ids = (array) $cat_ids;
		}

		if (!empty($cat_ids)){
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$levels = (int) $include_subcat_level;
			$query->select('p.j2store_product_id');
			$query->from('#__j2store_products as p');
			$query->join('LEFT','#__content as a ON a.id=p.product_source_id AND p.product_source='.$db->q('com_content') );
			$query->where( 'a.state=1' );
			if(!in_array('*', $cat_ids)) {
				$categoryEquals  = 'a.catid IN ( ' . implode(',', $cat_ids).' )'  ;
				$cat_ids_regexp = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $cat_ids) .'[[:>:]]';
				$categoryEquals = 'a.catid REGEXP BINARY '. $db->q($cat_ids_regexp) ;

				if ($include_subcats)
				{
					// Create a subquery for the subcategory list
					$subQuery = $db->getQuery(true)
						->select('sub.id')
						->from( '#__categories as sub')
						->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
						->where('this.id IN ( ' . implode(',', $cat_ids).' )');

					if ($levels >= 0)
					{
						$subQuery->where('sub.level <= (this.level + ' . $db->q($levels).')');
					}

					// Add the subquery to the main query
					$query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
				}
				else
				{
					$query->where( $categoryEquals );
				}
			}
			$limit = $params->get('number_of_items',6);
			$db->setQuery( $query, 0, $limit );
			$product_ids = $db->loadColumn();
		}

		return $product_ids;
	}

	function prepareProduct( $module_params, &$product ){
		// after title, content events, product links
		if (isset($product->source) && isset($product->source->category_title)) {
			$product->category_name = $product->source->category_title;
		}
	}

	function getCategoryList(){
		// get the list of categories to select in params
	}

	/**
	 * Method to get the category table name
	 * TODO: add support for other component categories like zoo, dj catlog, easyblog, rs events, sobipro
	 * */
	function getTableData(){

		$table = new JObject();
		$table->category_key_field 	= 'id';
		$table->category_name_field = 'title';
		$table->category_table_name = '#__categories';
		$table->item_key_field 		= 'id';
		$table->item_name_field 	= 'title';
		$table->item_table_name 	= '#__content';
		$table->item_cat_rel_field 	= 'catid';
		$table->item_cat_rel_table 	= '#__content';

		return $cat_table;
	}

}
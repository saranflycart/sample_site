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
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');

class JFormFieldJ2Store extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'J2Store';

	protected function getInput()
	 {
	 	$app = JFactory::getApplication();
	 	$id = $app->input->getInt('id');

	 	/*if($app->isSite()){
	 		$id = $app->input->getInt('a_id');
	 	}*/

	 	$productTable = F0FTable::getAnInstance('Product' ,'J2StoreTable');
		$productTable->load(array('product_source'=>'com_rseventspro', 'product_source_id' =>$id));

		$product_id = (isset($productTable->j2store_product_id)) ? $productTable->j2store_product_id : '';

	 	$inputvars = array(
	 			'task' =>'edit',
	 			'render_toolbar'        => '0',
	 			'product_source_id'=>$id,
	 			'id' =>$product_id,
	 			'product_source'=>'com_rseventspro',
	 			'product_source_view'=>'event',
	 			'form_prefix'=>'jform[j2rsform][j2store]'
	 	);
	 	$input = new F0FInput($inputvars);

	 	@ob_start();
		F0FDispatcher::getTmpInstance('com_j2store', 'product', array('layout'=>'form', 'tmpl'=>'component', 'input' => $input))->dispatch();
		$html = ob_get_contents();
		ob_end_clean();
		$html .= "<div class=\"form-actions\">
	<button type=\"submit\" class=\"btn btn-success rsepro-event-update\">Update event</button>
	<button type=\"submit\" class=\"btn btn-danger rsepro-event-cancel\">Cancel</button>
</div>";
		return $html;

	}

	protected function getLabel()
	{

		return '';
	}
	public function getControlGroup()
	{
		return '<div class="j2store_catalog_article">'.$this->getInput().'</div>';
	}
}

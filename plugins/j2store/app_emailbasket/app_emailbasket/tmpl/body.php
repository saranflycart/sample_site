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
$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';

$html = $this->params->get('body', '');

/*if(empty($html) || JString::strlen($html) < 5) {
	//we dont have a profile set in the store profile. So use the default one.
	$html = '<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [email] [phone_1] [phone_2] [company] [tax_number]</div>
		<div class="span6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';
}*/

preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

$allFields = $vars->fields;
$status = false;
?>
<?php if(isset($vars->data)): ?>
<h3>
	<?php  echo JText::_('PLG_J2STORE_EMAILBASKET_CUSTOMER_DETAILS')?>
</h3>
	<?php
	foreach($vars->fields as $key=>$field): ?>
	<?php

		if($key != 'country_id' && $key != 'zone_id') {
			if(!empty($vars->data[$key])) { 
		 		$html = str_replace('['.$key.']', JText::_($field->field_name).' : '.$vars->data[$key].'<br>',$html);
		 	}
		 }
		 
		 if($key=='country_id' && !empty($vars->data[$key])) {
		 	$html = str_replace('['.$key.']', JText::_($field->field_name).' : '.$this->getCountryName($vars->data[$key]).'<br>',$html);
		 }
		 if($key=='zone_id' && !empty($vars->data[$key])) {
		 	$html = str_replace('['.$key.']', JText::_($field->field_name).' : '.$this->getZoneName($vars->data[$key]).'<br>',$html);
		 }
	?>
	<?php endforeach; ?>

	 <?php

  //check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
  $unprocessedFields = array();
  foreach($vars->fields as $fieldName => $oneExtraField) {
  	if(!in_array($fieldName, $checkoutFields[1])) {
  		$unprocessedFields[$fieldName] = $oneExtraField;
  	}
  }

  //now we have unprocessed fields. remove any other square brackets found.
  preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
  foreach($removeFields[1] as $fieldName) {
	  if($fieldName == 'cart_item'){
	 	$item = $this->_getLayout('body_item', $vars);
		  $html = str_replace('['.$fieldName.']', $item, $html);
	  }elseif($fieldName == 'user_name'){
		  $html = str_replace('['.$fieldName.']', $vars->name, $html);
	  }elseif($fieldName == 'site_name'){
		  $html = str_replace('['.$fieldName.']', $vars->site_name, $html);
	  }else{
		  $html = str_replace('['.$fieldName.']', '', $html);
	  }

  }

  ?>
<?php echo $html; ?>

  <?php $uhtml = '';?>
 <?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
						<?php
							if($fieldName != 'country_id' && $fieldName != 'zone_id') {
								if(!empty($vars->data[$fieldName])) {
						 			$uhtml .= JText::_($oneExtraField->field_name).' : '.$vars->data[$fieldName];
						 		}	
						 	}
						 	if( $fieldName =='country_id' && !empty($vars->data[$fieldName])) {
								$uhtml .= JText::_($oneExtraField->field_name).' : '.$this->getCountryName($vars->data[$fieldName]);
							}

							if( $fieldName =='zone_id' && !empty($vars->data[$fieldName])) {
								$uhtml .= JText::_($oneExtraField->field_name).' : '.$this->getZoneName($vars->data[$fieldName]);
							}	
						 	$uhtml .='<br>';
						?>
  <?php endforeach; ?>
  <?php //echo $uhtml; ?>
<?php else:?>
	<?php $html = "[cart_item]";?>
	<?php
	//now we have unprocessed fields. remove any other square brackets found.
	preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
	foreach($removeFields[1] as $fieldName) {
		if($fieldName == 'cart_item'){
			$item = $this->_getLayout('body_item', $vars);
			$html = str_replace('['.$fieldName.']', $item, $html);
		}
	}
	?>
	<?php echo $html;?>
<?php endif; ?>

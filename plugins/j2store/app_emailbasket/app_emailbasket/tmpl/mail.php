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
$user = JFactory::getUser();
$delay = (int) $this->params->get('empty_cart_delay', 2000);
JFactory::getDocument()->addStyleSheet(JUri::root().'/plugins/j2store/app_emailbasket/app_emailbasket/assets/css/style.css');
require_once JPATH_SITE . '/components/com_users/helpers/route.php';
?>
<?php if($this->params->get('disable_checkout_btn',1)):?>
<style type="text/css">
.cart-checkout-button {
	display:none;
}
</style>
<?php endif;?>
<style>
	<?php echo $this->params->get('modal_style',''); ?>
</style>

<div class="j2store-basket-email">
	<div id="emailBasketNotice"></div>&nbsp;
	<a href="#j2storeSendEmailBasket" id="sendEmailBasket" class="btn btn-success" role="button" class="btn" data-toggle="modal">
		<i class="icon icon-mail"></i>
		<?php echo JText::_('PLG_J2STORE_EMAILBASKET_SEND_TO_EMAIL');?>
	</a>

	<?php if((JFactory::getUser()->id <= 0) && $this->params->get('show_save_cart',0) ):?>
	<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration&Itemid=' . UsersHelperRoute::getRegistrationRoute()); ?>" class="btn btn-primary"><?php echo JText::_('J2STORE_EMAILBASKET_SAVE_BUCKET');?></a>
	<?php endif;?>

	<!-- Modal -->
		<div class="j2store-modal">
			<div id="j2storeSendEmailBasket" class="modal <?php echo $this->params->get('modal_box_extra_class',' fade hide '); ?>"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" 
					style="<?php echo $this->params->get('modal_box_inline_style','display: none;'); ?>" >
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">
						<i class="icon icon-cart"> </i>  <?php echo JText::_('J2STORE_EMAIL')?>
					</h3>
				</div>
				<div class="modal-body" id="emailForm">
					<form id="j2storeBasketForm" action="index.php" class="form-horizontal" method="post">
					<?php
					$html = $vars->storeProfile->get('store_billing_layout');
					if(empty($html) || JString::strlen($html) < 5) {
						//we dont have a profile set in the store profile. So use the default one.
						$html = '<div class="row-fluid row">
							<div class="span6 col-sm-6 col-lg-6 col-sm-12 col-xs-12">[first_name] [last_name] [phone_1] [phone_2][email] [company] [tax_number]</div>
							<div class="span6 col-sm-6 col-lg-6 col-sm-12 col-xs-12 ">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
							</div>';
					}
					//first find all the checkout fields
					preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

					$allFields = $vars->fields;
					$status = false;
				?>
	  				<?php foreach ($vars->fields as $fieldName => $oneExtraField): ?>
							<?php
							if($fieldName == 'email') {
								$status = true;
							}
							$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
							if(property_exists($vars->address, $fieldName)) {
							 	$html = str_replace('['.$fieldName.']',$vars->fieldsClass->getFormatedDisplay($oneExtraField,$vars->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null).'</br />',$html);
							}
							?>
					  <?php endforeach; ?>
					 <?php
					 $email ='<span class="j2store_field_required">*</span>'.JText::_('J2STORE_EMAIL');
					 $email .='<br /><input type="text" name="email" value="" class="large-field" /> <br />';
					if($status == false) {
						//email not found. manually add it
						$html = str_replace('[email]',$email,$html);
					}

					$phtml = '';
					//re-check
					if(!in_array('email', $checkoutFields[1])) {
						//it seems deleted. so process them
						$phtml .= $email;
					}

					?>

				  <?php
				  //check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
				  $unprocessedFields = array();
				  foreach($vars->fields as $fieldName => $oneExtraField) {
				  	if(!in_array($fieldName, $checkoutFields[1]) &&  $fieldName != 'email') {
				  		$unprocessedFields[$fieldName] = $oneExtraField;
				  	}
				  }

				    //now we have unprocessed fields. remove any other square brackets found.
				  preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
				  foreach($removeFields[1] as $fieldName) {
				  	$html = str_replace('['.$fieldName.']', '', $html);
				  }
				  $html = $html.$phtml;
				  ?>
				  <?php echo $html; ?>
				  <?php if(count($unprocessedFields)): ?>
				 <div class="row-fluid row">
				  	<div class="span12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
				  		<?php $uhtml = '';?>
				 			<?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
								<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
										if(property_exists($vars->address, $fieldName)) {
										 	$uhtml .= $vars->fieldsClass->getFormatedDisplay($oneExtraField,$vars->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
										 	$uhtml .='<br />';
										}
								?>
				  			<?php endforeach; ?>
				  		<?php echo $uhtml; ?>
				  	</div>
				</div>
				<?php endif; ?>
				<br/>
				<div class="row-fluid">
					<div class="span12">
						<h5><?php echo JText::_('PLG_J2STORE_EMAILBASKET_BODY');?></h5>
							<?php if (!empty($vars->body)) : ?>
								<div id="cartBody">
									<?php echo $vars->body;	?>
								</div>
							<?php endif;?>
						<div class="modal-body" id="emailSuccess">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">
						<?php echo JText::_('J2STORE_CLOSE');?>
				</button>
				<input type="button" id="hideSend" onclick="getBasketDetails()" class="btn btn-primary" value="<?php echo JText::_('PLG_J2STORE_SEND'); ?>" />
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	function getBasketDetails(){
		(function($){
			var data = $('form#j2storeBasketForm :input').serializeArray();
			$(".email-basket-error, .email-basket").remove();
			var html = jQuery("#cartBody").html();
			$.ajax({
					url : 'index.php?option=com_j2store&view=callback&task=callback&method=app_emailbasket',
					method:'post',
					data : data,
					dataType: 'json',
					success: function(json){
							$('.j2error').remove(); // remove old error messages

						 	if(json['error']){
						 		$.each( json['error'], function( key, value ) {
									if (value) {
										$('form#j2storeBasketForm #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
									}
								});
						 	}

						 	if(json['success']){
						 		$('.warning, .j2error').remove();
						 		$("#j2storeSendEmailBasket").modal("hide");
								$("#emailBasketNotice").append("<p class='email-basket text text-success'><strong>"+json['success']['msg']+"</strong></p>");
								$(".email-basket" ).fadeIn('slow').delay(<?php echo $delay; ?>).fadeOut('slow');
								setTimeout(function() {
									if(json['redirect']) {
										location = json['redirect'];

									}
								}, <?php echo $delay; ?>);
						 	}
					}

				});



		})(j2store.jQuery);
	}
	(function($){
	var adjustModal = function($modal) {
		  var top;
		  if ($(window).width() <= 480) {
		    if ($modal.hasClass('modal-fullscreen')) {
		      if ($modal.height() >= $(window).height()) {
		        top = $(window).scrollTop();
		      } else {
		        top = $(window).scrollTop() + ($(window).height() - $modal.height()) / 2;
		      }
		    } else if ($modal.height() >= $(window).height() - 10) {
		      top = $(window).scrollTop() + 10;
		    } else {
		      top = $(window).scrollTop() + ($(window).height() - $modal.height()) / 2;
		    }
		  } else {
		    top = '50%';
		    if ($modal.hasClass('modal-fullscreen')) {
		      $modal.stop().animate({
		          marginTop  : -($modal.outerHeight() / 2)
		        , marginLeft : -($modal.outerWidth() / 2)
		        , top        : top
		      }, "fast");
		      return;
		    }
		  }
		  $modal.stop().animate({ 'top': top }, "fast");
		};

		var show = function() {
		  var $modal = $(this);
		  adjustModal($modal);
		};

		var checkShow = function() {
		  $('.modal').each(function() {
		    var $modal = $(this);
		    if ($modal.css('display') !== 'block') return;
		    adjustModal($modal);
		  });
		};

		var modalWindowResize = function() {
		  $('.modal').not('.modal-gallery').on('show', show);
		  $('.modal-gallery').on('displayed', show);
		  checkShow();
		};

		$(modalWindowResize);
		$(window).resize(modalWindowResize);
		$(window).scroll(checkShow);
	})(j2store.jQuery);
</script>

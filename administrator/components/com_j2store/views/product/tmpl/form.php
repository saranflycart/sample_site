<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/input.php');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root(true).'/media/j2store/css/font-awesome.min.css');
$doc->addStyleSheet(JURI::root(true).'/media/j2store/css/j2store_admin.css');
$app = JFactory::getApplication();
$option = $app->input->getString('option');

JHtml::_('behavior.modal');
?>

<script type="text/javascript">
<!--
jQuery(function($) {
	SqueezeBox.initialize({});
	SqueezeBox.assign($('a.modal').get(), {
		parse: 'rel'
	});
});
//-->
</script>
<style>
.control-group input[type="text"]{
	min-height:34px;
	width:200px;
	max-width:100%;
}

#pricingTab .control-group input[type="text"]{
	min-height:28px;
}
.form-inline .radio label.isChecked{
	background:none;
	color:#565656;
}
.input-group .input-group-btn a{
	color:#fff;
}
.input-group  input[type="text"]{
	min-height:34px;
	width:200px;
	max-width:100%;
}
#k2AdminContainer .adminlist .modal{ 
	color:rgb(48, 113, 169);
}
#k2AdminContainer .adminlist .modal:hover{
	text-decoration:underline;
}
.itemAdditionalField input[type="text"]{
	width:210px;
	min-height:34px;
	line-height:2;
}
.ui-widget-header{
	background:none;
}
ul.k2TabsNavigation li.ui-tabs-selected a{
	padding:0.5em 1em;
}
#attribute_options_table .input-small{
	width:100px;
}
</style>
<div class="j2store">
	<div class="j2store-product-edit-form">

	<div class="row-fluid">
		<div class="span<?php echo ($this->item->j2store_product_id) ?'4':'12'; ?>">
			<div class="panel panel-default">
				<div class="panel-heading">
                     <h3 class="panel-title"><?php echo JText::_('J2STORE_PRODUCT_INFORMATION'); ?></h3>
                 </div>
			<div class="panel-body">
				<div class="control-group form-group form-inline" id="j2store-product-enable">
					<?php echo J2Html::label(JText::_('J2STORE_TREAT_AS_PRODUCT'), 'enabled',array('class'=>'control-label'));?>
					<?php echo J2Html::radio($this->form_prefix.'[enabled]', $this->item->enabled, array('id'=>'j2store-product-enabled-radio-group', 'class'=>'radio'));?>
				</div>
					<div class="control-group form-group" id="j2store-product-type">
						<?php if(!empty($this->item->product_type)): ?>
							<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_TYPE'), 'product_type',array('class'=>'control-label')); ?>
							<span class="label label-success"><?php echo JText::_('J2STORE_PRODUCT_TYPE_'.JString::strtoupper($this->item->product_type)) ?></span></label>
							<?php echo J2Html::hidden($this->form_prefix.'[product_type]', $this->item->product_type); ?>
						<?php else: ?>
							<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_TYPE'), 'product_type',array('class'=>'control-label')); ?>
							<div class="controls"><?php echo $this->product_types; ?></div>
						<?php endif; ?>
						</div>
						<?php if(!$this->item->enabled): ?>
							<!-- Show this only when this was not a product -->
							<?php if($option == 'com_content' && $app->isAdmin()): ?>
							<div class="control-group form-group">
								<input type="button" onclick="Joomla.submitbutton('article.apply')" class="btn btn-large btn-success" value="<?php echo JText::_('J2STORE_SAVE_AND_CONTINUE'); ?>" />
							</div>
							<?php endif; ?>
						<?php endif; ?>

				<?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
					<div class="j2store-confirm-cont">
						<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#j2storeConfirmChange">
				  			<?php echo  JText::_('J2STORE_CHANGE_PRODUCT_TYPE');?>
						</button>
						<!-- here load the confim modal -->
						<?php echo $this->loadTemplate('confirm_change'); ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
		<div class="span7">
			<div class="panel panel-solid-success">
				 <div class="panel-body">
					<p class="lead">
					<?php echo JText::_('J2STORE_PRODUCT_ID'); ?> : <strong><?php echo $this->item->j2store_product_id; ?></strong>
					</p>
				<h3><?php echo JText::_('J2STORE_PLUGIN_SHORTCODE')?></h3>
				<p class="shortcode">
				{j2store}<?php echo $this->item->j2store_product_id; ?>|cart{/j2store}
				</p>
				<small>
					<?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_HELP_TEXT');?>
				</small>
				<br />
				<h4><?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_ADDITIONAL')?></h4>
				<p>
					<?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_HELP_TEXT_ADDITIONAL');?> <strong style="color:black;"> {j2store}<?php echo $this->item->j2store_product_id; ?>|upsells|crosssells{/j2store}</strong>
				</p>
				<p class="shortcode">price|thumbnail|mainimage|mainadditional|upsells|crosssells</p>
				</div>
				<div class="panel-footer">
					<strong>
					<?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_FOOTER_WARNING');?>
					</strong>
				</div>
			</div>
		</div>
		<?php endif;?>
	</div>
	<input type="hidden" name="<?php echo $this->form_prefix.'[j2store_product_id]'?>" value="<?php echo $this->item->j2store_product_id; ?>" />

<!-- @TODO should fix with css -->
<hr>
<?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
<div class="panel panel-default">
	 <div class="panel-body">
	<?php echo $this->loadTemplate($this->item->product_type); ?>
	<input type="hidden" name="<?php echo $this->form_prefix.'[product_type]'?>" value="<?php echo $this->item->product_type; ?>" />
	</div>
</div>
<?php endif; ?>
	</div> <!--  end of J2Store Product Form -->
</div>
<?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
<script type="text/javascript">

(function($) {
	$("#j2storeConfirmChange").on("show", function() { // wire up the OK button to dismiss the modal when shown
		$("#j2storeConfirmChange #changeTypeBtn").on("click", function(e) {
			$.ajax({
				url :'index.php?option=com_j2store&view=products&task=changeProductType',
				type: 'post',
				data:{'product_id' :<?php echo $this->item->j2store_product_id; ?> ,'product_type' : '<?php echo $this->item->product_type; ?>' },
				dataType: 'json',
				beforeSend:function(){
					$('#changeTypeBtn').html('<i class="icon-spin icon-refresh glyphicon glyphicon-refresh glyphicon-spin"></i> Changing type...');
				},
				success: function(json) {
					if(json['success']){
						location.reload();
					}
				}
			});

		});
	});

})(jQuery);
</script>
<?php endif;?>
<script type="text/javascript">
(function($) {
$(document).ready(function() {
    $("div.j2store-tab-menu>div.list-group>a").click(function(e) {
        e.preventDefault();
        $(this).siblings('a.active').removeClass("active");
        $(this).addClass("active");
        var index = $(this).index();
        $("div.j2store-tab>div.j2store-tab-content").removeClass("active");
        $("div.j2store-tab>div.j2store-tab-content").eq(index).addClass("active");
    });
});
})(jQuery);

(function($) {
$('#j2store-product-enable').bind('change', function() {
var enabled = $('#j2store-product-enable input[type=radio]:checked').val();
console.log(enabled);
if(enabled == 1) {
	$("#j2store-product-type").show();
}else {
	$("#j2store-product-type").hide();
}
});

$('#j2store-product-enable').trigger('change');

})(jQuery);


</script>


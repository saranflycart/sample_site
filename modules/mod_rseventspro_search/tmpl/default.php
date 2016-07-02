<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access'); 
$today = JFactory::getDate()->format('Y-m-d');
$cal_attribs = array('size' => '10', 'class' => 'input-small', 'clear' => false); ?>

<?php if ($start || $end) { ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		rs_check_dates();
	});
</script>
<?php } ?>

<div class="rsepro_search_form<?php echo $suffix; ?>">
	<form method="post" action="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=search',true,$itemid); ?>">
		<?php if ($categories) { ?>
		<div>
			<label for="rscategories"><?php echo JText::_('MOD_RSEVENTSPRO_SEARCH_CATEGORIES_LABEL'); ?></label>
			<?php echo $categorieslist; ?>
		</div>
		<?php } ?>
		<?php if ($locations) { ?>
		<div>
			<label for="rslocations"><?php echo JText::_('MOD_RSEVENTSPRO_SEARCH_LOCATIONS_LABEL'); ?></label>
			<?php echo $locationslist; ?>
		</div>
		<?php } ?>
		<?php if ($start) { ?>
		<div class="rs_date">
			<label for="rsstart"><?php echo JText::_('MOD_RSEVENTSPRO_SEARCH_START_LABEL'); ?></label>
			<input type="checkbox" class="<?php echo rseventsproHelper::tooltipClass(); ?>" id="enablestart" value="1" title="<?php echo rseventsproHelper::tooltipText(JText::_('MOD_RSEVENTSPRO_SEARCH_START_DATE_INFO')); ?>" onchange="rs_check_dates();" name="enablestart"/> 
			<?php echo JHTML::_('rseventspro.rscalendar', 'rsstart', $today, true, false, null, $cal_attribs); ?>
		</div>
		<?php } ?>
		<?php if ($end) { ?>
		<div class="rs_date">
			<label for="rsend"><?php echo JText::_('MOD_RSEVENTSPRO_SEARCH_END_LABEL'); ?></label>
			<input type="checkbox" class="<?php echo rseventsproHelper::tooltipClass(); ?>" id="enableend" value="1" title="<?php echo rseventsproHelper::tooltipText(JText::_('MOD_RSEVENTSPRO_SEARCH_END_DATE_INFO')); ?>" onchange="rs_check_dates();" name="enableend"/>
			<?php echo JHTML::_('rseventspro.rscalendar', 'rsend', $today, true, false, null, $cal_attribs); ?>
		</div>
		<?php } ?>
		<?php if ($archive) { ?>
		<div>
			<label for="rsarchive"><?php echo JText::_('MOD_RSEVENTSPRO_SEARCH_ARCHIVE_LABEL'); ?></label>
			<?php echo $archivelist; ?>
		</div>
		<?php } ?>
		<div>
			<label for="rskeyword"><?php echo JText::_('MOD_RSEVENTSPRO_SEARCH_KEYWORDS_LABEL'); ?></label>
			
			<?php if (rseventsproHelper::isJ3()) { ?>			
				<div class="input-append">
					<input type="text" name="rskeyword" id="rse_keyword" value="" autocomplete="off" class="input-small" />
					<button type="submit" class="btn">
						<i class="icon-search"></i>
					</button>
				</div>
			<?php } else { ?>
				<input type="text" name="rskeyword" id="rskeyword" value="" autocomplete="off" />
				<button type="submit" class="rsepro_search_form_button"></button>
			<?php } ?>
			
		</div>
		<input type="hidden" name="option" value="com_rseventspro" />
		<input type="hidden" name="layout" value="search" />
		<input type="hidden" name="repeat" value="<?php echo $params->get('repeat',1); ?>" />
	</form>
</div>
<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<div class="rse_popular_module<?php echo $params->get('moduleclass_sfx'); ?>">
	<ul class="rse_popular_list">
	<?php foreach ($events as $event) { ?>
	<?php $image = JURI::root().'components/com_rseventspro/assets/images/blank.png'; ?>
	<?php if (!empty($event->icon) && file_exists(JPATH_SITE.'/components/com_rseventspro/assets/images/events/thumbs/b_'.$event->icon)) { ?>
	<?php $image = JURI::root().'components/com_rseventspro/assets/images/events/thumbs/s_'.$event->icon.'?nocache='.uniqid(''); } ?>
		<li class="rs_box">
			<span><a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name),false,$itemid) ?>"><img src="<?php echo $image; ?>" alt="<?php echo $event->name; ?>" width="70" /></a></span>
			<span><a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name),false,$itemid) ?>"><?php echo $event->name; ?></a></span>
			<span><?php echo JText::plural('MOD_RSEVENTSPRO_POPULAR_HITS',$event->hits); ?></span>
		</li>
	<?php } ?>
	</ul>
</div>
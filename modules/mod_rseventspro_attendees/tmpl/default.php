<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<ul class="rsepro_attendees<?php echo $suffix; ?>">
<?php foreach ($guests as $guest) { ?>
	<li>
		<?php if (!empty($guest->url)) { ?><a href="<?php echo $guest->url; ?>"><?php } ?>
		<?php echo $guest->avatar; ?>
		<?php echo $guest->name; ?>
		<?php if (!empty($guest->url)) { ?></a><?php } ?>
	</li>
<?php } ?>
</ul>
<span class="attendees_clear"></span>
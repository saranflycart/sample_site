<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$utm_query ='utm_source=free&utm_medium=basic&utm_campaign=inline&utm_content=prolink';
$domain = 'http://j2store.org';
?>
<div class="pro-feature inline-content">
	<div class="row-fluid">
		<div class="span12">
			<div class="hero-unit" style="color: #666666;">
				<p class="lead"><?php echo JText::_('J2STORE_PART_OF_PRO_FEATURE'); ?></p>
				<h2>Upgrade for awesome features. Go Pro</h2>
				<p class="lead">
					And enjoy more features.
				</p>


			<div class="row-fluid">
				<div class="span12">
					<a target="_blank" class="btn btn-large btn-success" href="<?php echo $domain; ?>/six-months/new.html?<?php echo $utm_query; ?>">Upgrade now</a>
					<a target="_blank" class="btn btn-warning btn-large" href="<?php echo $domain; ?>/get-j2store.html?<?php echo $utm_query; ?>">I need more information</a>
				</div>

			</div>
			<br />

		</div>
		</div>
	</div>

</div>

<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$utm_query ='utm_source=free&utm_medium=basic&utm_campaign=inline&utm_content=gateways';
$domain = 'http://j2store.org';
?>

<?php echo $this->getRenderedForm(); ?>

<div class="payment-content inline-content">
	<div class="row-fluid">

		<div class="span4">

			<div class="hero-unit">
				<h2>Need help in setting up payment methods ?</h2>
				<p class="lead">
					Check our comprehensive user guide
				</p>
				<a target="_blank" class="btn btn-large btn-warning" href="<?php echo $domain;?>/support/user-guide.html?<?php echo $utm_query; ?>">User guide</a>
				<a target="_blank" class="btn btn-large btn-info" href="<?php echo $domain;?>/support.html?<?php echo $utm_query; ?>">Support center</a>

			</div>

		</div>
		<div class="span5">
			<div class="hero-unit">
				<h2>Looking for more payment options? Check our extensions directory</h2>
				<p class="lead">
					J2Store is integrated with 65+ payment gateways across the world.
					<br />
					Find more at our extensions directory
				</p>
				<a target="_blank" class="btn btn-large btn-success" href="<?php echo $domain;?>/extensions/payment-plugins.html?<?php echo $utm_query; ?>">Get more payment plugins</a>
			</div>
		</div>

	</div>
</div>
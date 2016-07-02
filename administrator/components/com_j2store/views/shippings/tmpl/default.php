<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$utm_query ='utm_source=free&utm_medium=basic&utm_campaign=inline&utm_content=shipping';
$domain = 'http://j2store.org';
?>

<?php echo $this->getRenderedForm(); ?>

<div class="shipping-content inline-content">
	<div class="row-fluid">

		<div class="span4">

			<div class="hero-unit">
				<h2>Need help in setting up shipping methods ?</h2>
				<p class="lead">
					Check our comprehensive user guide.
				</p>
				<a target="_blank" class="btn btn-large btn-warning" href="<?php echo $domain;?>/support/user-guide.html?<?php echo $utm_query; ?>">User guide</a>
				<br />
				<p class="lead">
					Shipping is not working?  Check the troubleshooting guide
				</p>
				<a target="_blank" class="btn btn-large btn-danger" href="<?php echo $domain;?>/support/user-guide/troubleshooting-shipping-methods.html?<?php echo $utm_query; ?>">Troubleshooting Guide</a>
				<a target="_blank" class="btn btn-large btn-info" href="<?php echo $domain;?>/support.html?<?php echo $utm_query; ?>">Support center</a>

			</div>

		</div>
		<div class="span5">
			<div class="hero-unit">
				<h2>Need more shipping methods? Check our extensions directory</h2>
				<p class="lead">
					J2Store has integrations 10+ shipping carriers.
					<br />
					Find more at our extensions directory
				</p>
				<a target="_blank" class="btn btn-large btn-success" href="<?php echo $domain;?>/extensions/shipping-plugins.html?<?php echo $utm_query; ?>">Get more shipping plugins </a>
			</div>
		</div>

	</div>



</div>
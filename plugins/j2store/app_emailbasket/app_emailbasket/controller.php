<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/**
 * ensure this file is being included by a parent file
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appcontroller.php');
class J2StoreControllerAppEmailBasket extends J2StoreAppController {
	var $_element = 'app_emailbasket';



	protected function onBeforeGenericTask($task)
	{

		//echo $this->component . '.views.' .F0FInflector::singularize($this->view) . '.acl.' . $task;
		//exit;
		$privilege = $this->configProvider->get(
				$this->component . '.views.' .
				F0FInflector::singularize($this->view) . '.acl.' . $task, ''
		);
		//var_dump($privilege); exit;

		return $this->allowedTasks($task);
	}
}

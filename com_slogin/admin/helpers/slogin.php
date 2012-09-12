<?php
/**
 * SMLogin
 * 
 * @version 	1.0	
 * @author		SmokerMan
 * @copyright	© 2012. All rights reserved. 
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

/**
 * SMLogin helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_smlogin
 */
class SMLoginHelper
{

	
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$assetName = 'com_smlogin';
		$actions = array('core.admin', 'core.manage');

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;

	}	

}

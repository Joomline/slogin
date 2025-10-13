<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('(@)|(@)');

/**
 * SLogin helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SLoginHelper
{
	
	public static function getActions()
	{
		$user	= Factory::getUser();
		$result	= new Registry;
		$assetName = 'com_slogin';
		$actions = array('core.admin', 'core.manage');
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
		return $result;
	}
}

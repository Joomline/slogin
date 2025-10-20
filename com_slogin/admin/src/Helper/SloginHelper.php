<?php

namespace Joomline\Component\Slogin\Administrator\Helper;

/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * SLogin helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SloginHelper
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
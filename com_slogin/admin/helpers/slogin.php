<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

/**
 * SLogin helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SLoginHelper
{
    public static function addSubmenu($vName)
    {
        JSubMenuHelper::addEntry(
            JText::_('COM_SLOGIN_MAIN'),
            'index.php?option=com_slogin&view=settings',
            $vName == 'settings'
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_SLOGIN_USERS'),
            'index.php?option=com_slogin&view=users',
            $vName == 'users'
        );
    }
	
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$assetName = 'com_slogin';
		$actions = array('core.admin', 'core.manage');

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;

	}	

}

<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;

class plgUserSlogin extends CMSPlugin
{
	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data
	 * @param	boolean		$succes	True if user was succesfully stored in the database
	 * @param	string		$msg	Message
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		if (!$succes) {
			return false;
		}
		
		PluginHelper::importPlugin('slogin_integration');
		Factory::getApplication()->triggerEvent('onAfterSloginDeleteUser',array((int)$user['id']));
		
		$db = Factory::getDbo();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__slogin_users') .
			' WHERE '.$db->quoteName('user_id').' = '.(int) $user['id']
		);
		$db->execute();
		return true;
	}
}

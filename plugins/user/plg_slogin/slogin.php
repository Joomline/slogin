<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgUserSlogin extends JPlugin
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
		
		JPluginHelper::importPlugin('slogin_integration');
        $dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterSloginDeleteUser',array((int)$user['id']));
		
		$db = JFactory::getDbo();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__slogin_users') .
			' WHERE '.$db->quoteName('user_id').' = '.(int) $user['id']
		);
		$db->Query();
		return true;
	}
}

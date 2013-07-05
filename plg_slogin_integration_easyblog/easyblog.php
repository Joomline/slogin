<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgSlogin_integrationEasyblog extends JPlugin
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
	public function onAfterSloginStoreUser($user)
	{
        $issetUser = $this->getEasyUser($user->id);
        if(!$issetUser){
             $this->createEasyUser($user);
        }
	}

    public function onAfterSloginLoginUser($user)
    {
        $issetUser = $this->getEasyUser($user->id);
        if(!$issetUser){
            $this->createEasyUser($user);
        }
    }

    private function getEasyUser($userId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__easyblog_users'));
        $query->where($db->quoteName('id') . ' = ' . $db->quote($userId));
        $db->setQuery($query, 0, 1);
        return (int)$db->loadResult();
    }

    private function createEasyUser($user)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->insert('#__easyblog_users');
        $query->columns(array($db->quoteName('id'), $db->quoteName('nickname'), $db->quoteName('avatar'), $db->quoteName('published'), $db->quoteName('permalink')));
        $query->values($db->quote($user->id). ', '. $db->quote($user->name). ', '. $db->quote('default_blogger.png'). ', '. $db->quote('1'). ', '. $db->quote($user->username));
        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum())
        {
            echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
            return;
        }
    }
}

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

class plgSlogin_integrationCombuilder extends JPlugin
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
        $issetUser = $this->getUser($user->id);
        if(!$issetUser){
             $this->createUser($user);
        }
	}

    public function onAfterSloginLoginUser($user)
    {
        $issetUser = $this->getUser($user->id);
        if(!$issetUser){
            $this->createUser($user);
        }
    }

    private function getUser($userId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__comprofiler'));
        $query->where($db->quoteName('id') . ' = ' . $db->quote($userId));
        $db->setQuery($query, 0, 1);
        return (int)$db->loadResult();
    }

    private function createUser($user)
    {
        $name = explode(' ', $user->get('name'));
        $name[1] = (!empty($name[1])) ? $name[1] : '';

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->insert('#__comprofiler');
        $query->columns(array(
            $db->quoteName('id'),
            $db->quoteName('user_id'),
            $db->quoteName('firstname'),
            $db->quoteName('lastname'),
            $db->quoteName('approved'),
            $db->quoteName('confirmed'),
            $db->quoteName('banned')
            )
        );
        $query->values(
            $db->quote($user->id). ', '
          . $db->quote($user->id). ', '
          . $db->quote($name[0]). ', '
          . $db->quote($name[1]). ', '
          . $db->quote(1). ', '
          . $db->quote(1). ', '
          . $db->quote(0)
        );
        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum())
        {
            echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $db->getErrorNum(), $db->getErrorMsg()).'<br />';
            return;
        }
    }
}

<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');
 
/**
 * Hello Table class
 */
class SLoginTableUsers extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
		parent::__construct('#__slogin_users', 'id', $db);
	}

    function deleteUserRows($userId){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__slogin_users');
        $query->where('user_id = '.(int)$userId);
        $db->setQuery((string)$query);
        $db->query();

        if ($db->getErrorNum()) {
            $this->setError($db->getErrorMsg());
            return false;
        }
        else{
            return true;
        }
    }
}

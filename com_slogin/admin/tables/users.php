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
//	    $db = JFactory::getContainer()->get('DatabaseDriver');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__slogin_users');
        $query->where('user_id = '.(int)$userId);
        $db->setQuery((string)$query);
        $db->execute();
		if (version_compare(JVERSION, '4.0.0', '>=')) {
			if ($db->get('errorNum')) {
				$this->setError($db->get('ErrorMsg'));
				return false;
			}
	    } else {
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
	    return true;
    }
}

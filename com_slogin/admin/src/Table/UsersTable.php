<?php

namespace Joomline\Component\Slogin\Administrator\Table;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
 
/**
 * SLogin Users Table class
 */
class UsersTable extends Table
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
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__slogin_users');
        $query->where('user_id = '.(int)$userId);
        $db->setQuery((string)$query);
        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
	    return true;
    }
}
<?php
/**
* @version      2.7.3 26.01.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.model');

class SloginTableSlogin_users extends JTable
{
    function __construct( &$_db )
    {
        parent::__construct('#__slogin_users', 'id', $_db );
    }

    function confirm_email($user_id, $slogin_id, $provider){

        $query = "SELECT `id` FROM #__slogin_users WHERE `user_id`='".$user_id."'";
        $this->_db->setQuery($query);
        $ids = $this->_db->loadColumn();

        if(empty($ids)){
            $this->user_id = $user_id;
            $this->slogin_id = $slogin_id;
            $this->provider = $provider;
            $this->confirmed = 1;
            $this->store();
        }

        foreach($ids as $id){
            $this->load($id);
            $this->confirmed = 1;
            $this->store();
        }
    }
}
?>
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
}
?>
<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Table\Table;

class SloginTableSlogin_users extends Table
{
    function __construct( &$_db )
    {
        parent::__construct('#__slogin_users', 'id', $_db );
    }
}
?>
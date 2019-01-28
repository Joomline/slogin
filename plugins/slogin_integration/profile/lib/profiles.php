<?php
/**
 * Social Login Integration Plugin Profile
 *
 * @version 	2.8.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2013. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

class PlgSloginProfilesTable extends JTable{

    function __construct(&$db)
    {
        parent::__construct('#__plg_slogin_profile', 'id', $db);
    }
}
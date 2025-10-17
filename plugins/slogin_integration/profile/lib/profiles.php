<?php
/**
 * SLogin Integration Plugin Profile
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

use Joomla\CMS\Table\Table;

class PlgSloginProfilesTable extends Table{

    function __construct(&$db)
    {
        parent::__construct('#__plg_slogin_profile', 'id', $db);
    }
}
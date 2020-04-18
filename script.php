<?php

/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// no direct access
defined('_JEXEC') or die ;

class pkg_sloginInstallerScript
{
	public function postflight($type, $parent)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->update('`#__extensions`')
            ->set('`enabled` = 1')
            ->where('`element` = '.$db->quote('slogin'))
            ->where('`type` = '.$db->quote('plugin'))
            ->where('(`folder` = '.$db->quote('authentication').' OR `folder` = '.$db->quote('user').')')
           ;
        $db->setQuery($query)->execute();
        
    }
}
<?php

/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
 
/**
 * Script file of HelloWorld component
 */
class plgSlogin_integrationProfileInstallerScript
{
	/**
	 * method to install the component
	 * $parent is the class calling this method
	 * @return void
	 */
	function install($parent) 
	{
		//$parent->getParent()->setRedirectURL('index.php?option=com_helloworld');
	}
 
	/**
	 * method to uninstall the component
	 * $parent is the class calling this method
	 * @return void
	 */
	function uninstall($parent) 
	{

	}
 
	/**
	 * method to update the component
	 * $parent is the class calling this method
	 * @return void
	 */
	function update($parent) 
	{

	}
 
	/**
	 * method to run before an install/update/uninstall method
	 * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
	 * @return void
	 */
	function preflight($type, $parent) 
	{

	}
 
	/**
	 * method to run after an install/update/uninstall method
	 * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
	 * @return void
	 */
	function postflight($type, $parent) 
	{
        $db = Factory::getDbo();

		switch($type){
            case 'install' :
            case 'update' :
                $q = $db->getQuery(true);
                $q->select('COUNT(*)')
                    ->from('#__extensions')
                    ->where('`element` = "slogin_avatar"')
                    ->where('`folder` = "slogin_integration"')
                    ->where('`type` = "plugin"');
                $db->setQuery($q);
                $res = (int)$db->loadResult();
                if($res){
                    $q = $db->getQuery(true);
                    $q->update('#__extensions')
                        ->set('`enabled` = 0')
                        ->where('`element` = "slogin_avatar"')
                        ->where('`folder` = "slogin_integration"')
                        ->where('`type` = "plugin"');
                    $db->setQuery($q);
                    $db->execute();
                }
                $q = $db->getQuery(true);
                $q->update('#__extensions')
                    ->set('`enabled` = 1')
                    ->where('`element` = "profile"')
                    ->where('`folder` = "slogin_integration"')
                    ->where('`type` = "plugin"');
                $db->setQuery($q);
                $db->execute();
                break;
            default :
                break;

        }
	}
}

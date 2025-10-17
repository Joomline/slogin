<?php
/**
 * SLogin Integration Plugin Profile
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class plgProfileHelper
{
	public static function getProfile($userid)
    {
        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->select('*');
        $q->from('#__plg_slogin_profile');
        $q->where('`user_id` = '.(int)$userid);
        $q->where('`current_profile` = 1');
        $db->setQuery($q,0,1);
        $profile = $db->loadObject();

		if (!$profile){
            $q = $db->getQuery(true);
            $q->select('*');
            $q->from('#__plg_slogin_profile');
            $q->where('`user_id` = '.(int)$userid);
            $db->setQuery($q,0,1);
            $profile = $db->loadObject();
        }

		if (!$profile)
            return false;

        if(!empty($profile->avatar)){
            //Получаем папку с изображениями
            $plugin = PluginHelper::getPlugin('slogin_integration', 'profile');
            $pluginParams = new Registry();
            $pluginParams->loadString($plugin->params);
            $paramFolder = $pluginParams->get('rootfolder', 'images/avatar');

            $profile->avatar = preg_replace("/.*?\//","",$profile->avatar);
            $profile->avatar = $paramFolder.'/'.$profile->avatar;
        }
		return $profile;
	}
}
	
?>
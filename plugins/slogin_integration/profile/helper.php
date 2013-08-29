<?php
/**
 * Social Login Integration Plugin Profile
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2013. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgProfileHelper
{
	public static function getProfile($userid)
    {
        $db = JFactory::getDbo();
        $q = $db->getQuery(true);
        $q->select('*');
        $q->from('#__plg_slogin_profile');
        $q->where('`user_id` = '.(int)$userid);
        $q->where('`current_profile` = 1');
        $db->setQuery($q);
        $profile = $db->loadObject();
		if (!$profile)
            return false;

        if(!empty($profile->avatar)){
		//Получаем папку с изображениями
		$plugin = JPluginHelper::getPlugin('slogin_integration', 'slogin_avatar');
		$pluginParams = new JRegistry();
		$pluginParams->loadString($plugin->params);
		$paramFolder = $pluginParams->get('rootfolder', 'images/avatar');

        $profile->avatar = preg_replace("/.*?\//","",$profile->avatar);
        $profile->avatar = $paramFolder.'/'.$profile->avatar;
        }
		return $profile;
	}
}
	
?>
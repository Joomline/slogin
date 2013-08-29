<?php
/**
 * Social Login Avatar
 *
 * @version 	1.4
 * @author		Andrew Zahalski
 * @copyright	© 2013. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class Slogin_avatarHelper {

	public static function getavatar($userid) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$q = "SELECT photo_src, provider, profile FROM #__plg_slogin_avatar WHERE userid=".$userid." AND main=1";
		$db->setQuery($q);
		$avatar = $db->loadAssoc();
		if (!$avatar) return false;
		
		//Получаем папку с изображениями
		$plugin = JPluginHelper::getPlugin('slogin_integration', 'slogin_avatar');
		$pluginParams = new JRegistry();
		$pluginParams->loadString($plugin->params);
		$paramFolder = $pluginParams->get('rootfolder', 'images/avatar');
		//обратная совместимость со старыми версиями
		$avatar['photo_src'] = preg_replace("/.*?\//","",$avatar['photo_src']);
		//путь до аватара
		$avatar['photo_src'] = $paramFolder.'/'.$avatar['photo_src'];

		return $avatar;
	}
	
}
	
?>
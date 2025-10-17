<?php
/**
 * SLogin Avatar
 *
 * @version 	5.0.0
 * @author		Andrew Zahalski
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class Slogin_avatarHelper {

	public static function getavatar($userid) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$q = "SELECT photo_src, provider, profile FROM #__plg_slogin_avatar WHERE userid=".$userid." AND main=1";
		$db->setQuery($q);
		$avatar = $db->loadAssoc();
		if (!$avatar) return false;
		
		//Получаем папку с изображениями
		$plugin = PluginHelper::getPlugin('slogin_integration', 'slogin_avatar');
		$pluginParams = new Registry();
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
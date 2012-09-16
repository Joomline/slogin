<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');


//подключаем helper стандартного модуля авторизации, для ридеректа
require_once JPATH_BASE.DS.'modules'.DS.'mod_login'.DS.'helper.php';
$type	= modLoginHelper::getType();
$return	= modLoginHelper::getReturnURL($params, $type);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_slogin', $params->get('layout', 'default'));


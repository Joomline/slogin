<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// Подключаем библеотеку контроллера Joomla
jimport('joomla.application.component.controller');
//костыль для поддержки 2 и  3 джумлы
$className = (class_exists('JControllerLegacy')) ? 'JControllerLegacy' : 'JController';
// Получаем экземпляр класса основного контроллера компонента
$controller = call_user_func(array($className, 'getInstance'), 'SLogin');
// Обрабатываем запрос (task)
$controller->execute(JFactory::getApplication()->input->get('task'));
// Переадресуем, если установлено контроллером
$controller->redirect();

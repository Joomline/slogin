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

// Подключаем библеотеку контроллера Joomla
jimport('joomla.application.component.controller');
// Получаем экземпляр класса основного контроллера компонента
$controller = JController::getInstance('SLogin');
// Обрабатываем запрос (task)
$controller->execute(JRequest::getCmd('task'));
// Переадресуем, если установлено контроллером
$controller->redirect();

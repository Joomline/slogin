<?php
/**
 * SMLogin
 * 
 * @package		Joomla.Administrator
 * @subpackage	com_smlogin
 * @version 	1.0	
 * @author		SmokerMan
 * @copyright	© 2012. All rights reserved. 
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// Подключаем библеотеку контроллера Joomla
jimport('joomla.application.component.controller');
// Получаем экземпляр класса основного контроллера компонента
$controller = JController::getInstance('SMLogin');
// Обрабатываем запрос (task)
$controller->execute(JRequest::getCmd('task'));
// Переадресуем, если установлено контроллером
$controller->redirect();

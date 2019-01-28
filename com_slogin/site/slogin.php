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

//костыль для поддержки 2 и  3 джумлы
$className = (class_exists('JControllerLegacy')) ? 'JControllerLegacy' : 'JController';

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by SLogin
//$controller = JController::getInstance('SLogin');
$controller = call_user_func(array($className, 'getInstance'), 'SLogin');

$app = JFactory::getApplication();
$task = $app->input->get('task');
$view = $app->input->get('view');

if(!$view && !$task)
{
    $app->redirect(JURI::root());
}

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
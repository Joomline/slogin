<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

// Get an instance of the controller prefixed by SLogin
$controller = BaseController::getInstance('SLogin');

$app = Factory::getApplication();
$task = $app->input->get('task');
$view = $app->input->get('view');

if(!$view && !$task)
{
    $app->redirect(Uri::root());
}

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
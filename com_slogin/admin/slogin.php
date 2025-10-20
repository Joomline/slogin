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

// Get an instance of the controller prefixed by SLogin (legacy compatibility)
$controller = BaseController::getInstance('SLogin');
// Execute the requested task
$controller->execute(Factory::getApplication()->input->get('task'));
// Redirect if set by the controller
$controller->redirect();

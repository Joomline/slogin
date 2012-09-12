<?php
/**
 * SMLogin
 * 
 * @package		Joomla.Site
 * @subpackage	com_smlogin
 * @version 	1.0	
 * @author		SmokerMan
 * @copyright	© 2012. All rights reserved. 
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by SMLogin
$controller = JController::getInstance('SMLogin');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
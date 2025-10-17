<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewMail extends HtmlView
{
	protected $name, $username, $email, $action, $user;
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $app	= Factory::getApplication();

        $data = $app->getUserState('com_slogin.provider.data');
        $app = Factory::getApplication();

        $msg = $app->getUserState('com_slogin.msg', '');
        $msgType = $app->getUserState('com_slogin.msgType', '');
        $app->setUserState('com_slogin.msg', '');
        $app->setUserState('com_slogin.msgType', '');

        if(!empty($msg)){
            $msgType = (!empty($msgType)) ? $msgType : 'message';
            $app->enqueueMessage($msg, $msgType);
        }

        //костыль для поддержки 2 и  3 джумлы
        $className = (class_exists('BaseController')) ? 'BaseController' : 'JController';

        // Get an instance of the controller prefixed by SLogin
        $controller = call_user_func(array($className, 'getInstance'), 'SLogin');
        $controller->setVars('first_name', $data['first_name']);
        $controller->setVars('last_name', $data['last_name']);
        $controller->setVars('email', $data['email']);
        $controller->setVars('slogin_id', $data['slogin_id']);
        $controller->setVars('provider', $data['provider']);

        $this->name = $controller->setUserName();
        $this->username = $controller->setUserUserName();
        $this->email = $data['email'];
		$this->user = Factory::getUser();
        $this->action = JRoute::_('index.php?option=com_slogin&task=check_mail');

		// Display the view
		parent::display($tpl);
	}
}

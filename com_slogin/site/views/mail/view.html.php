<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

//костыль для поддержки 2 и  3 джумлы
if(!class_exists('SloginViewMailParent')){
    if(class_exists('JViewLegacy')){
        class SloginViewMailParent extends JViewLegacy{}
    }
    else{
        class SloginViewMailParent extends JView{}
    }
}

// import joomla controller library
jimport('joomla.application.component.controller');

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewMail extends SloginViewMailParent
{
	protected $name, $username, $email, $action, $user;
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $app	= JFactory::getApplication();

        $data = $app->getUserState('com_slogin.provider.data');
        $app = JFactory::getApplication();

        $msg = $app->getUserState('com_slogin.msg', '');
        $msgType = $app->getUserState('com_slogin.msgType', '');
        $app->setUserState('com_slogin.msg', '');
        $app->setUserState('com_slogin.msgType', '');

        if(!empty($msg)){
            $msgType = (!empty($msgType)) ? $msgType : 'message';
            $app->enqueueMessage($msg, $msgType);
        }

        //костыль для поддержки 2 и  3 джумлы
        $className = (class_exists('JControllerLegacy')) ? 'JControllerLegacy' : 'JController';

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
		$this->user = JFactory::getUser();
        $this->action = JRoute::_('index.php?option=com_slogin&task=check_mail');

		// Display the view
		parent::display($tpl);
	}
}

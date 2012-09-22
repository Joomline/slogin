<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
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

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewMail extends SloginViewMailParent
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $app	= JFactory::getApplication();

        $data = $app->getUserState('com_slogin.provider.data');
        $app->setUserState('com_slogin.provider.data', array());

        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->email = $data['email'];
        $this->slogin_id = $data['slogin_id'];
        $this->provider = $data['provider'];

        $this->action = JRoute::_('index.php?option=com_slogin&task=check_mail');


		// Display the view
		parent::display($tpl);
	}
}

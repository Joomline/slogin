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

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewLinking_user extends JView
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $input = new JInput;

        $this->params       = JComponentHelper::getParams('com_users');
        $this->user		    = JFactory::getUser();

        $this->form		    = $this->get('Form');

        $this->email        = $input->Get('email', '', 'STRING');
        $this->id           = $input->Get('id', 0, 'INT');
        $this->provider     = $input->Get('provider', '', 'STRING');
        $this->slogin_id    = $input->Get('slogin_id', '', 'STRING');

		// Display the view
		parent::display($tpl);
	}
}

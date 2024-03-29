<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewComparison_user extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $app	= JFactory::getApplication();
		$data = $app->getUserState('com_slogin.comparison_user.data');

        $this->params       = JComponentHelper::getParams('com_users');
        $this->user		    = JFactory::getUser();

        $this->form		    = $this->get('Form');

        $this->email        = $data['email'];
        $this->id           = $data['id'];
        $this->provider     = $data['provider'];
        $this->slogin_id    = $data['slogin_id'];

		// Display the view
		parent::display($tpl);
	}
}

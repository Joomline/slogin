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
if(!class_exists('SloginViewComparisonParent')){
    if(class_exists('JViewLegacy')){
        class SloginViewComparisonParent extends JViewLegacy{}
    }
    else{
        class SloginViewComparisonParent extends JView{}
    }
}

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewComparison_user extends SloginViewComparisonParent
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $input = new JInput;
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

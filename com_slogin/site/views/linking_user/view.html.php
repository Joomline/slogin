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
class SloginViewLinking_user extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $document = JFactory::getDocument();
        $document->addStyleSheet( JURI::root().'components/com_slogin/views/linking_user/tmpl/linking.css');

        $app	= JFactory::getApplication();
        $model = $this->getModel();

        $data = $app->getUserState('com_slogin.comparison_user.data');

        $this->params       = JComponentHelper::getParams('com_users');
        $this->user		    = JFactory::getUser();

        $this->sloginParams       = JComponentHelper::getParams('com_slogin');

        $this->form		    = $this->get('Form');

        $this->email        = $data['email'];
        $this->id           = $data['id'];
        $this->provider     = $data['provider'];
        $this->slogin_id    = $data['slogin_id'];

        $this->failure_redirect = $model->getReturnURL($this->sloginParams, 'failure_redirect');
        $this->after_reg_redirect = $model->getReturnURL($this->sloginParams, 'after_reg_redirect');

		// Display the view
		parent::display($tpl);
	}
}

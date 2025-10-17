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
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewComparison_user extends HtmlView
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $app	= Factory::getApplication();
		$data = $app->getUserState('com_slogin.comparison_user.data');

        $this->params       = ComponentHelper::getParams('com_users');
        $this->user		    = Factory::getUser();

        $this->form		    = $this->get('Form');

        $this->email        = $data['email'];
        $this->id           = $data['id'];
        $this->provider     = $data['provider'];
        $this->slogin_id    = $data['slogin_id'];

		// Display the view
		parent::display($tpl);
	}
}

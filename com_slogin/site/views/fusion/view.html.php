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
if(class_exists('JViewLegacy')){
    class SloginViewFusionParemt extends JViewLegacy{}
}
else{
    class SloginViewFusionParemt extends JView{}
}

/**
 * HTML View class for the HelloWorld Component
 */
class SloginViewFusion extends SloginViewFusionParemt
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $input = new JInput;

        $this->params       = JComponentHelper::getParams('com_users');
        $this->user		    = JFactory::getUser();

        $this->form		    = $this->get('Form');

        $this->providers = array(
            'vk' => 'vkontakte',
            'google' => 'google',
            'fb' => 'facebook',
            'tw' => 'twitter',
            'mail' => 'mail',
            'odnoklassniki' => 'odnoklassniki',
        );

        $this->action = ($this->user->get('id') == 0) ? '' : 'fusion';
        $this->fusionProviders = $this->get('Providers');

        $this->return = urlencode(JRoute::_('index.php?option=com_slogin&view=fusion'));

        $document = JFactory::getDocument();
        $document->addStyleSheet( JURI::root().'modules/mod_slogin/media/slogin.css');
		// Display the view
		parent::display($tpl);
	}
}

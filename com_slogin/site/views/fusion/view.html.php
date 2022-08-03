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
class SloginViewFusion extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $input = new JInput;

        $this->params       = JComponentHelper::getParams('com_users');
        $this->user		    = JFactory::getUser();

        $this->form		    = $this->get('Form');

        $providers = $this->get('Providers');

        $this->attachedProviders = array();
        $this->unattachedProviders = array();

        $fusionProviders = $this->get('FusionProviders');

        foreach($providers as $v){
            if(!in_array($v['plugin_name'], $fusionProviders)){
               $this->attachedProviders[] = $v;
            }
            else{
                $this->unattachedProviders[] = $v;
            }
        }

        $document = JFactory::getDocument();
     //   $document->addStyleSheet( JURI::root().'modules/mod_slogin/tmpl/compact/slogin.css');
        $document->addScript(JURI::root().'modules/mod_slogin/media/slogin.js?v=1');
		// Display the view
		parent::display($tpl);
	}
}

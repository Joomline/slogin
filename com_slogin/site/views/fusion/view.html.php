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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

/**
 * HTML View class for the SLogin Component
 */
class SloginViewFusion extends HtmlView
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $input = new Input;

        $this->params       = ComponentHelper::getParams('com_users');
        $this->user		    = Factory::getUser();

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

        $document = Factory::getDocument();
     //   $document->addStyleSheet( Uri::root().'modules/mod_slogin/tmpl/compact/slogin.css');
        $document->addScript(Uri::root().'modules/mod_slogin/media/slogin.js?v=1');
		// Display the view
		parent::display($tpl);
	}
}

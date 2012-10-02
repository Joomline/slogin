<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

//костыль для поддержки 2 и  3 джумлы
if(!class_exists('SLoginViewSettingsParent')){
    if(class_exists('JViewLegacy')){
        class SLoginViewSettingsParent extends JViewLegacy{}
    }
    else{
        class SLoginViewSettingsParent extends JView{}
    }
}

/**
 * View class for a list of SLogins
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SLoginViewSettings extends SLoginViewSettingsParent
{
	protected $component;
	protected $module;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		
		$this->component = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT.'/slogin.xml');
		$this->module = JApplicationHelper::parseXMLInstallFile(JPATH_SITE.'/modules/mod_slogin/mod_slogin.xml');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-generic {background: url("../media/com_slogin/icon_48x48.png")}');
		//include helper file
		require_once JPATH_COMPONENT.'/helpers/slogin.php';
		//actions example
		$canDo	= SLoginHelper::getActions();
		
		//set title
		JToolBarHelper::title(JText::_('COM_SLOGIN'));
		
		//config
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_slogin');
		}

	}
}

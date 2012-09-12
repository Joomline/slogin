<?php
/**
 * SMLogin
 * 
 * @version 	1.0	
 * @author		SmokerMan
 * @copyright	© 2012. All rights reserved. 
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of SMLogins
 *
 * @package		Joomla.Administrator
 * @subpackage	com_smlogin
 */
class SMLoginViewSettings extends JView
{
	protected $component;
	protected $module;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		
		$this->component = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT.DS.'smlogin.xml');
		$this->module = JApplicationHelper::parseXMLInstallFile(JPATH_SITE.DS.'modules'.DS.'mod_smlogin'.DS.'mod_smlogin.xml');

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
		$doc->addStyleDeclaration('.icon-48-generic {background: url("../media/com_smlogin/icon_48x48.png")}');
		//include helper file
		require_once JPATH_COMPONENT.'/helpers/smlogin.php';
		//actions example
		$canDo	= SMLoginHelper::getActions();
		
		//set title
		JToolBarHelper::title(JText::_('COM_SMLOGIN'));
		
		//config
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_smlogin');
		}

	}
}

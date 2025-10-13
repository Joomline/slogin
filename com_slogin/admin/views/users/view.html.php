<?php
// No direct access to this file
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\Input\Input;

defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');

class SloginViewUsers extends JViewLegacy
{

	function display($tpl = null) 
	{
        $this->loadHelper('slogin');
		$input = new Input;

		// Assign data to the view
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        $this->state		= $this->get('State');
        $this->providers		= $this->get('Providers');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
	        throw new GenericDataException(implode('<br />', $errors), 500);
        }

		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
        $this->loadHelper( 'slogin' );
        $canDo = SLoginHelper::getActions();

        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration('.icon-48-users {background: url("../media/com_slogin/icon_48x48.png")}');

        JToolBarHelper::title(JText::_('COM_SLOGIN_USERS'), 'users');

        if($canDo->get('core.admin')){
            JToolBarHelper::deleteList(JText::_('COM_SLOGIN_CONFIRM'), 'remove_slogin_users', 'COM_SLOGIN_DELETE_USERS');
            JToolBarHelper::deleteList(JText::_('COM_SLOGIN_CONFIRM'), 'remove_joomla_users', 'COM_SLOGIN_DELETE_J_USERS');
        }
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	public function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SLOGIN'));
	}
}

<?php
// No direct access to this file
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\ToolbarHelper;
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

        $doc = Factory::getDocument();
        $doc->addStyleDeclaration('.icon-48-users {background: url("../media/com_slogin/icon_48x48.png")}');

        ToolbarHelper::title(Text::_('COM_SLOGIN_USERS'), 'users');

        if($canDo->get('core.admin')){
            ToolbarHelper::deleteList(Text::_('COM_SLOGIN_CONFIRM'), 'remove_slogin_users', 'COM_SLOGIN_DELETE_USERS');
            ToolbarHelper::deleteList(Text::_('COM_SLOGIN_CONFIRM'), 'remove_joomla_users', 'COM_SLOGIN_DELETE_J_USERS');
        }
	}
	/**
	 * Method to set up the document properties
	 *
	 * @param   \Joomla\CMS\Document\Document  $document  The document object
	 * @return void
	 */
	public function setDocument(?\Joomla\CMS\Document\Document $document = null): void
	{
		if ($document === null) {
			$document = Factory::getDocument();
		}
		$document->setTitle(Text::_('COM_SLOGIN'));
	}
}

<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * SLogin Users View
 */
class SloginViewUsers extends HtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  void
	 */
	public function display($tpl = null) 
	{
        $this->loadHelper('slogin');
		$input = new Input;

		// Assign data to the view
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->providers = $this->get('Providers');

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
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolBar() 
	{
        $this->loadHelper('slogin');
        $canDo = SLoginHelper::getActions();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_SLOGIN_USERS'), 'users');

        if ($canDo->get('core.admin')) {
            ToolbarHelper::deleteList(Text::_('COM_SLOGIN_CONFIRM'), 'remove_slogin_users', 'COM_SLOGIN_DELETE_USERS');
            ToolbarHelper::deleteList(Text::_('COM_SLOGIN_CONFIRM'), 'remove_joomla_users', 'COM_SLOGIN_DELETE_J_USERS');
        }

        // Add settings button
        ToolbarHelper::preferences('com_slogin');
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
        
        // Add styles and scripts
        HTMLHelper::_('jquery.framework');
        HTMLHelper::_('bootstrap.framework');
        
		$document->setTitle(Text::_('COM_SLOGIN'));
	}
}

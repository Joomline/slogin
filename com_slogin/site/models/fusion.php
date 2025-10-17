<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Input\Input;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\MVC\View\GenericDataException;
require_once JPATH_ROOT.'/components/com_slogin/helpers/providers.php';
/**
 * Rest model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class SloginModelFusion extends FormModel
{
	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_slogin.login', 'login', array('load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

    public function getFusionProviders()
	{
        $userId = Factory::getUser()->id;

        $query =  $this->_db->getQuery(true);
        $query->select($this->_db->quoteName('provider'));
        $query->from($this->_db->quoteName('#__slogin_users'));
        $query->where($this->_db->quoteName('user_id') . ' = ' . (int)$userId);
        $this->_db->setQuery($query);

		$providers = $this->_db->loadColumn();
        return $providers;
	}

    public function getProviders()
	{
		$config = ComponentHelper::getParams('com_slogin');
        $action = (Factory::getUser()->id == 0) ? '' : '&action=fusion';
        $plugins = array();

		PluginHelper::importPlugin('slogin_auth');
		Factory::getApplication()->triggerEvent('onCreateSloginLink', array(&$plugins, $action));

        return $plugins;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	array	The default data is an empty array.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered login form data.
		$app	= Factory::getApplication();
		$input =  new Input();
		$data	= $app->getUserState('slogin.login.form.data', array());

		// check for return URL from the request first
		if ($return = $input->Get('return', '', 'BASE64')) {
			$data['return'] = base64_decode($return);
			if (!Uri::isInternal($data['return'])) {
				$data['return'] = '';
			}
		}

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return'])) {
			$data['return'] = 'index.php?option=com_users&view=profile';
		}
		$app->setUserState('users.login.form.data', $data);

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$params	= Factory::getApplication()->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @param	string	The name of the plugin group to import (defaults to "content").
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		// Import the approriate plugin group.
		PluginHelper::importPlugin($group);

		// Trigger the form preparation event.
		$results = Factory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			// Get the last error.
			$error = $this->getError();

			// Convert to a JException if necessary.
			if ($error) {
				throw new GenericDataException($error, 500);
			}
		}
	}


}

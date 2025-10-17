<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

use Joomla\CMS\MVC\View\GenericDataException;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Event\Dispatcher;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Rest model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class SloginModelLinking_user extends FormModel
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
        $input = new JInput;
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
		JPluginHelper::importPlugin($group);

		// Trigger the form preparation event.
		$results = Joomla\CMS\Factory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $data));

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

    static function getReturnURL($params, $type)
    {
        $app	= Factory::getApplication();
        $router = $app->getRouter();
        $url = null;
        if ($itemid =  $params->get($type))
        {
            $db		= Factory::getDbo();
            $query	= $db->getQuery(true);

            $query->select($db->quoteName('link'));
            $query->from($db->quoteName('#__menu'));
            $query->where($db->quoteName('published') . '=1');
            $query->where($db->quoteName('id') . '=' . $db->quote($itemid));

            $db->setQuery($query);
            if ($link = $db->loadResult()) {
                if (Factory::getApplication()->getConfig()->getValue('config.sef', false)) {
                    $url = 'index.php?Itemid='.$itemid;
                }
                else {
                    $url = $link.'&Itemid='.$itemid;
                }
            }
        }
        if (!$url)
        {
            $app = Factory::getApplication();
            $url = base64_decode($app->getUserState('com_slogin.return_url'));
        }
        if (!$url)
        {
            // stay on the same page
            $uri = clone Factory::getApplication()->getUri();
            $vars = $router->parse($uri);
            unset($vars['lang']);
            if (Factory::getApplication()->getConfig()->getValue('config.sef', false))
            {
                if (isset($vars['Itemid']))
                {
                    $itemid = $vars['Itemid'];
                    $menu = $app->getMenu();
                    $item = $menu->getItem($itemid);
                    unset($vars['Itemid']);
                    if (isset($item) && $vars == $item->query) {
                        $url = 'index.php?Itemid='.$itemid;
                    }
                    else {
                        $url = 'index.php?'.Uri::buildQuery($vars).'&Itemid='.$itemid;
                    }
                }
                else
                {
                    $url = 'index.php?'.Uri::buildQuery($vars);
                }
            }
            else
            {
                $url = 'index.php?'.Uri::buildQuery($vars);
            }
        }

        return base64_encode($url);
    }
}
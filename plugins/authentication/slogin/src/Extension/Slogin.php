<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

namespace Joomline\Plugin\Authentication\Slogin\Extension;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Plugin\CMSPlugin;

class Slogin extends CMSPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	array	Array holding the user credentials
	 * @param	array	Array of extra options
	 * @param	object	Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onUserAuthenticate($credentials, $options, &$response)
	{
        if(is_file(JPATH_ROOT . '/components/com_slogin/helpers/password.php')){
            include_once JPATH_ROOT . '/components/com_slogin/helpers/password.php';
        }
        else{
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = Text::sprintf('JLIB_USER_ERROR_AUTHENTICATION_FAILED_LOAD_PLUGIN', 'plgAuthenticationSlogin');
            return false;
        }

		$response->type = 'Slogin';

		// Joomla does not like blank passwords
		if (empty($credentials['password']))
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');
			return false;
		}

		// Get a database object
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id')
			->from('#__users')
			->where('username=' . $db->quote($credentials['username']));
		$uid = $db->setQuery($query,0,1)->loadResult();

		if ($uid)
		{
			$passwords = \SloginPasswordHelper::getPasswords($uid);

			if (is_array($passwords) && in_array($credentials['password'], $passwords))
			{
				$user = User::getInstance($uid); // Bring this in line with the rest of the system
				$response->email = $user->email;
				$response->fullname = $user->name;

				if (Factory::getApplication()->isClient('administrator'))
				{
					$response->language = $user->getParam('admin_language');
				}
				else
				{
					$response->language = $user->getParam('language');
				}
				$response->status = Authentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				$response->status = Authentication::STATUS_FAILURE;
				$response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		}
		else
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
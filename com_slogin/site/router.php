<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

function SLoginBuildRoute(& $query)
{
	// Declare static variables.
	static $items;
	static $itemId;

	$segments = array();

	if (isset($query['task'])) {
		switch($query['task']) {
			case 'sredirect':
				$segments[] = 'redirect';
				break;

			case 'check':
				$segments[] = 'callback';
				$segments[] = $query['plugin'];
				unset($query['plugin']);
				break;

			case 'check_mail':
				$segments[] = 'mail';
				$segments[] = 'check';
				break;

			case 'join_mail':
				$segments[] = 'mail';
				$segments[] = 'join';
				break;

			case 'user.login':
				$segments[] = 'user';
				$segments[] = 'login';
				break;

			case 'auth':
				$segments[] = 'provider';

				if (isset($query['plugin'])) {
					$segments[] = $query['plugin'];
					unset($query['plugin']);
				}
				$segments[] = 'auth';

				if (isset($query['return'])) {
					$segments[] = $query['return'];
					unset($query['return']);
				}

				break;

			case 'detach_provider':
				$segments[] = 'provider';

				if (isset($query['plugin'])) {
					$segments[] = $query['plugin'];
					unset($query['plugin']);
				} else {
					$segments[] = 'unknown';
				}

				$segments[] = 'detach';
				break;


			default:
				$segments[] = $query['task'];
		}

		unset($query['task']);
	}

	if (isset($query['view'])) {

		if (empty($items)) {
			// Get all relevant menu items.
			$app	= Factory::getApplication();
			$menu	= $app->getMenu();
			$items	= $menu->getItems('component', 'com_slogin');

			// Build an array of serialized query strings to menu item id mappings.
			for ($i = 0, $n = count($items); $i < $n; $i++) {
				// Check to see if we have found the reset menu item.
				if (!empty($items[$i]->query['view']) && ($items[$i]->query['view'] == $query['view'])) {
					$itemId = $items[$i]->id;
					break;
				}
			}
		}
		switch($query['view']) {
			case 'comparison_user':
				$segments[] = 'user';
				$segments[] = 'comparison';
				break;

			case 'linking_user':
				$segments[] = 'user';
				$segments[] = 'link';
				break;

			case 'fusion':
				if($itemId){
					unset ($query['view']);
					$query['Itemid'] = $itemId;
				}
				else{
					$segments[] = 'user';
					$segments[] = 'fusion';
				}
				break;

			case 'mail':
				$segments[] = 'mail';
				$segments[] = 'view';
				break;
		}

		unset($query['view']);
	}

	return $segments;
}

function SLoginParseRoute(&$segments)
{
	$vars = array();
	if (isset($segments[0])) {
		// Check if this is a provider name directly (for menu item based redirects)
		// Get all enabled slogin_auth plugins dynamically
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('element'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('slogin_auth'))
			->where($db->quoteName('enabled') . ' = 1');
		$db->setQuery($query);
		$providers = $db->loadColumn();
		
		if (in_array($segments[0], $providers)) {
			$vars['task'] = 'check';
			$vars['plugin'] = $segments[0];
			unset($segments[0]);
		} else {
			switch($segments[0]) {
				case 'redirect':
					$vars['task'] = 'sredirect';
					unset($segments[0]);
					break;

				case 'callback':
					$vars['task'] = 'check';
					if (isset($segments[1])) {
						$vars['plugin'] = $segments[1];
						unset($segments[1]);
					}
					unset($segments[0]);
					break;

			case 'user':
				switch($segments[1]) {
					case 'login':
						$vars['task'] = 'user.login';
						unset($segments[0]);
						unset($segments[1]);
						break;

					case 'link':
						$vars['view'] = 'linking_user';
						unset($segments[0]);
						unset($segments[1]);
						break;

					case 'comparison':
						$vars['view'] = 'comparison_user';
						unset($segments[0]);
						unset($segments[1]);
						break;

					case 'fusion':
						$vars['view'] = 'fusion';
						unset($segments[0]);
						unset($segments[1]);
						break;
				}

				break;

			case 'mail':
				switch($segments[1]) {
					case 'check':
						$vars['task'] = 'check_mail';
						unset($segments[0]);
						unset($segments[1]);
						break;

					case 'join':
						$vars['task'] = 'join_mail';
						unset($segments[0]);
						unset($segments[1]);
						break;

					case 'view':
					default:
						unset($segments[0]);
						unset($segments[1]);
						$vars['view'] = 'mail';
						break;
				}
				break;

			case 'provider':
				if (isset($segments[2])) {
					$vars['plugin'] = $segments[1];

					switch($segments[2]) {
						case 'detach':
							unset($segments[0]);
							unset($segments[1]);
							unset($segments[2]);
							$vars['task'] = 'detach_provider';
							break;

						default:
							$vars['task'] = $segments[2];
							unset($segments[0]);
							unset($segments[1]);
							unset($segments[2]);
							break;
					}

					if (isset($segments[3])) {
						$vars['return'] = $segments[3];
						unset($segments[0]);
						unset($segments[1]);
						unset($segments[2]);
						unset($segments[3]);
					}
				}
				break;
			default:
				$vars['task'] = $segments[0];
				unset($segments[0]);
				break;
			}
		}
	}
	return $vars;
}

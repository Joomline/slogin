<?php

// task=check&plugin=google -> /provider/google/check
// task=auth&plugin=google -> /provider/google/auth
// task=detach_provider&plugin=google -> /provider/google/detach
// task=auth&plugin=google&return=12345 -> /auth/google/12345

// task=join_email -> /mail/join
// task=check_mail -> /mail/check
// view=mail -> /mail/view

// task=user.login -> /user/login
// &view=comparison_user -> /user/comparisson
// view=linking_user -> /user/link

// view=fusion -> /fussion

// task=sredirect -> /redirect

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

			case 'check':
				$segments[] = 'provider';

				if (isset($query['plugin'])) {
					$segments[] = $query['plugin'];
					unset($query['plugin']);
				}

				$segments[] = 'check';
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
            $app	= JFactory::getApplication();
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

function SLoginParseRoute($segments)
{
	$vars = array();

	if (isset($segments[0])) {
		switch($segments[0]) {
			case 'redirect':
				$vars['task'] = 'sredirect';
				break;

			case 'user':
				switch($segments[1]) {
					case 'login':
						$vars['task'] = 'user.login';
						break;
				
					case 'link':
						$vars['view'] = 'linking_user';
						break;

					case 'comparison':
						$vars['view'] = 'comparison_user';
						break;

					case 'fusion':
						$vars['view'] = 'fusion';
						break;
				}

				break;

			case 'mail':
				switch($segments[1]) {
					case 'check':
						$vars['task'] = 'check_mail';
						break;
				
					case 'join':
						$vars['task'] = 'join_mail';
						break;

                    case 'view':
                    default:
						$vars['view'] = 'mail';
						break;
				}
				break;
			
			case 'provider':
				if (isset($segments[2])) {
					$vars['plugin'] = $segments[1];

					switch($segments[2]) {
						case 'detach':
							$vars['task'] = 'detach_provider';
							break;
					
						default:
							$vars['task'] = $segments[2];
							break;
					}

					if (isset($segments[3])) {
						$vars['return'] = $segments[3];
					}
				}
				break;
            default:
                $vars['task'] = $segments[0];
                break;
		}
	}

	return $vars;
}

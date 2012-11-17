<?php

// task=check&plugin=google -> /auth/google/check
// task=auth&plugin=google -> /auth/google
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
				$segments[] = 'auth';

				if (isset($query['plugin'])) {
					$segments[] = $query['plugin'];
					unset($query['plugin']);
				}

				$segments[] = 'check';
				break;

			case 'auth':
				$segments[] = 'auth';

				if (isset($query['plugin'])) {
					$segments[] = $query['plugin'];
					unset($query['plugin']);
				}
				break;

			default:
				$segments[] = $query['task'];
		}

		unset($query['task']);
	}

	if (isset($query['view'])) {
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
				$segments[] = 'fusion';
				break;

			case 'mail':
				$segments[] = 'mail';
				$segments[] = 'view';
				break;
		}

		unset($query['view']);
	}

	if (isset($query['return'])) {
		$segments[] = $query['return'];
		unset($query['return']);
	}

    if (isset($query['action'])) {
        $segments[] = $query['action'];
        unset($query['action']);
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
						$vars['view'] = 'mail';
						break;
				}
				break;

			case 'check':
				if (isset($segments[1])) {
					$vars['plugin'] = $segments[1];
				}
				break;

			case 'auth':
				if (isset($segments[2])) {
					if ($segments[2] == 'check') {
						$vars['task'] = 'check';
						$vars['plugin'] = $segments[1];
					} else {
						$vars['task'] = 'auth';
						$vars['plugin'] = $segments[1];

                        if($segments[2] == 'fusion'){
                            $vars['action'] = $segments[2];
                        }
                        else{
                            $vars['return'] = $segments[2];
                        }
					}
				}
				break;
		}
	}

	return $vars;
}

<?php
/**
* SLogin
*
* @version 	1.0
* @author		SmokerMan
* @copyright	© 2012. All rights reserved.
* @license 	GNU/GPL v.3 or later.
*/

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

require_once JPATH_COMPONENT_SITE.DS.'controller.php';

class SLoginControllerGoogle extends SLoginController
{
	protected $client_id;
	protected $client_secret;
	
	public function __construct()
	{
	
		$cofig = array();
		parent::__construct($cofig);
		$this->client_id =  $this->config->get('google_client_id');
		$this->client_secret = $this->config->get('google_client_secret');
	
	}	
	
	/**
	* Аутентификация пользователя
	*/
	public function auth()
	{
		parent::auth();

        $app	= JFactory::getApplication();
        $input = $app->input;
        $app->setUserState('com_slogin.action.data', $input->getString('action', ''));

        $redirect = JURI::base().'?option=com_slogin&task=google.check';

        $this->localAuthDebug($redirect);

		$scope = urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email');
	
		$params = array(
			        'response_type=code',
			        'redirect_uri=' . urlencode($redirect),
			        'client_id=' . $this->client_id,
			        'scope=' . $scope
			        //,'access_type=offline'
					//,			        'approval_prompt=force'
		);
		$params = implode('&', $params);
	
		$url = 'https://accounts.google.com/o/oauth2/auth?'.$params;
	
		header('Location:' . $url);
	}

	/**
	* Проверка аутентификации на сайте донора
	* Создание новой учетной записи на сайте или утентификация, если такой пользователь уже есть
	*/	
	public function check()
	{
        $app	= JFactory::getApplication();
        $input = $app->input;
        $provider = 'google';

        $this->localCheckDebug($provider);

		if ($code = $input->get('code', null, 'STRING')) {
				
			// get access_token for google API
			$redirect = urlencode(JURI::base().'?option=com_slogin&task=google.check');
			$scope = urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email');

			$params = array(
					'client_id=' . $this->client_id,
					'client_secret=' . $this->client_secret,
					'grant_type=authorization_code',
			        'code=' . $code,
	            	'redirect_uri=' . $redirect,
					'scope=' . $scope

			);
			$params = implode('&', $params);
			$url = 'https://accounts.google.com/o/oauth2/token';
			$request = json_decode($this->open_http($url, true, $params));
			

				
			//Get user info
			//
			// id 				The value of this field is an immutable identifier for the logged-in user
			// email 			The email address of the logged in user
			// verified_email 	A flag that indicates whether or not Google has been able to verify the email address.
			// name 			The full name of the logged in user
			// given_name 		The first name of the logged in user
			// family_name 		The last name of the logged in user
			// picture 			The URL to the user's profile picture. If the user has no public profile, this field is not included.
			// locale 			The user's registered locale. If the user has no public profile, this field is not included.
			// timezone 		the default timezone of the logged in user
			// gender 			the gender of the logged in user (other|female|male)

			$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$request->access_token;
			$request = json_decode($this->open_http($url));

            $this->storeOrLogin($request->given_name, $request->family_name, $request->email, $request->id, $provider);
		}
	
	}	
}
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

class SLoginControllerOdnoklassniki extends SLoginController
{
	protected $client_id;
	protected $client_secret;
	protected $application_key;
	
	public function __construct() 
	{
		$cofig = array();
		parent::__construct($cofig);
		$this->client_id =  $this->config->get('odnoklassniki_client_id');
		$this->client_secret = $this->config->get('odnoklassniki_client_secret');
		$this->application_key =  $this->config->get('odnoklassniki_application_key');		

		

	}
	
	
	/**
	* Аутентификация пользователя
	*/
	public function auth()
	{
        $app	= JFactory::getApplication();
        $input = $app->input;
        $app->setUserState('com_slogin.action.data', $input->getString('action', ''));

        parent::auth();
		
		$redirect = JURI::base().'?option=com_slogin&task=odnoklassniki.check';

        $this->localAuthDebug($redirect);

		$params = array(
				'client_id=' . $this->client_id,
			    'response_type=code',
			    'redirect_uri=' . urlencode($redirect),
				'scope=VALUABLE ACCESS'
		);
		$params = implode('&', $params);
	
		$url = 'http://www.odnoklassniki.ru/oauth/authorize?'.$params;

		header('Location:' . $url);
	}

	/**
	* Проверка аутентификации на сайте донора
	* Создание новой учетной записи на сайте или утентификация, если такой пользователь уже есть
	*/	
	public function check()
	{
        $provider = 'odnoklassniki';
        $app	= JFactory::getApplication();
        $input = $app->input;

        $this->localCheckDebug($provider);

		$input = JFactory::getApplication()->input;
		if ($code = $input->get('code', null, 'STRING')) {
			// get access_token from mail  API
			$redirect = urlencode(JURI::base().'?option=com_slogin&task=odnoklassniki.check');
			$params = array(
					'client_id=' . $this->client_id,
					'client_secret=' . $this->client_secret,
					'grant_type=authorization_code',
			        'code=' . $code,
	            	'redirect_uri=' . $redirect
			 
	
			);
			$params = implode('&', $params);

			$url = 'http://api.odnoklassniki.ru/oauth/token.do';
			$request = json_decode($this->open_http($url, true, $params));

            if (!empty($request->error)) {
                echo '<h3>token.do</h3>';
                echo '<p>'.$request->error.'</p>';
                echo '<p>'.$request->error_description.'</p>';
                die();
            }

			/*
			Объект $request содержит следующие поля:
			uid - уникальный номер пользователя
			first_name - имя пользователя
			last_name - фамилия пользователя
			birthday - дата рождения пользователя
			gender - пол пользователя
			pic_1 - маленькое фото
			pic_2 - большое фото
			*/
			
			$request_params = array(
					'application_key=' . $this->application_key,
					'access_token=' .  $request->access_token,
					'method=users.getCurrentUser',
					'sig=' . md5('application_key=' . $this->application_key . 'method=users.getCurrentUser' . md5($request->access_token . $this->client_secret))
			);
			
			$params = implode('&', $request_params);

			$url = 'http://api.odnoklassniki.ru/fb.do?'.$params;
			$request = json_decode($this->open_http($url));

            if (!empty($request->error_code)) {
                echo '<h3>fb.do</h3>';
                echo '<p>'.$request->error_data.'</p>';
                echo '<p>'.$request->error_msg.'</p>';
                die();
            }

            $this->storeOrLogin($request->first_name, $request->last_name, $request->email, $request->uid, $provider, true);
	
		} elseif ($err = $input->get('error')) {
			die($err);
		}
	
	}
}
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

class SLoginControllerFb extends SLoginController
{
	protected $client_id;
	protected $client_secret;
	
	public function __construct()
	{
	
		$cofig = array();
		parent::__construct($cofig);
		$this->client_id =  $this->config->get('fb_client_id');
		$this->client_secret = $this->config->get('fb_client_secret');
	
	}
	
	/**
	 * Аутентификация пользователя
	 */
	public function auth()
	{

		$redirect = urlencode(JURI::base().'?option=com_slogin&task=fb.check');

		$params = array(
				'client_id=' . $this->client_id,
			    'redirect_uri=' . $redirect,
				'scope=email'
		);
		$params = implode('&', $params);

		$url = 'http://www.facebook.com/dialog/oauth?' . $params;

		header('Location:' . $url);
	}

	/**
	* Проверка аутентификации на сайте донора
	* Создание новой учетной записи на сайте или утентификация, если такой пользователь уже есть
	*/	
	public function check()
	{
		$input = JFactory::getApplication()->input;

		if ($code = $input->get('code')) {
			$redirect = urlencode(JURI::base().'?option=com_slogin&task=fb.check');
			//подключение к API
			$params = array(
							'client_id=' . $this->client_id,
						    'client_secret=' . $this->client_secret,
						    'code=' . $code,
						    'redirect_uri='. $redirect
			);
			$params = implode('&', $params);			
			$url = 'https://graph.facebook.com/oauth/access_token?' . $params;
			$data = $this->open_http($url);
			parse_str($data, $data_array);
			

			
// 			Получение данных о пользователе 
// 			id, name, first_name, last_name, link, gender, timezone, locale, verified, updated_time
// 			email смотреть параметр scope в методе auth()!
			
			$ResponseUrl = 'https://graph.facebook.com/me?access_token='.$data_array['access_token'];
			$request = json_decode($this->open_http($ResponseUrl));


            $provider = 'fb';
            $uid = $request->id;

			$username = $this->getUserName($provider, $uid);
			//проверяем существует ли пользователь с таким именем
            $user_id = $this->GetUserId($uid, $provider);
			
			if (!$user_id) {
				$email = $request->email;
				$name = $this->setUserName($request->first_name,  $request->last_name);
				$this->storeUser($username, $name, $email, $uid, $provider);
			} else {
				$this->loginUser($user_id);
			}

		}

	}

}

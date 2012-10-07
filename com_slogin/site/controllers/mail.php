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

require_once JPATH_COMPONENT_SITE.'/controller.php';

class SLoginControllerMail extends SLoginController
{
	protected $client_id;
	protected $client_secret;
	
	public function __construct() 
	{
		
		$cofig = array();
		parent::__construct($cofig);
		$this->client_id =  $this->config->get('mail_client_id');
		$this->client_secret = $this->config->get('mail_client_secret');

	}
	
	
	/**
	* Аутентификация пользователя
	*/
	public function auth()
	{
        $app	= JFactory::getApplication();
        $input = $app->input;
        $app->setUserState('com_slogin.action.data', $input->getString('action', ''));

        $app->setUserState('com_slogin.return_url', $input->getString('return', ''));

        $redirect = JURI::base().'?option=com_slogin&task=mail.check';

        $this->localAuthDebug($redirect);
	
		$params = array(
			        'response_type=code',
			        'redirect_uri=' . urlencode($redirect),
			        'client_id=' . $this->client_id
		);
		$params = implode('&', $params);
	
		$url = 'https://connect.mail.ru/oauth/authorize?'.$params;
	
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
        $provider = 'mail';

        $this->localCheckDebug($provider);

        $input = JFactory::getApplication()->input;
		if ($code = $input->get('code', null, 'STRING')) {
				
			// get access_token from mail  API
			$redirect = urlencode(JURI::base().'?option=com_slogin&task=mail.check');
			$params = array(
					'client_id=' . $this->client_id,
					'client_secret=' . $this->client_secret,
					'grant_type=authorization_code',
			        'code=' . $code,
	            	'redirect_uri=' . $redirect
			 
	
			);
			$params = implode('&', $params);
			
			$url = 'https://connect.mail.ru/oauth/token';
			$request = json_decode($this->open_http($url, true, $params));
			
			/* Получение информации о пользователе
				"uid": "15410773191172635989",
			    "first_name": "Евгений",
			    "last_name": "Маслов",
			    "nick": "maslov",
			    "email": "emaslov@mail.ru", // только в users.getInfo и только для внешних сайтов
			    "sex": 0, // 0 - мужчина, 1 - женщина
			    "birthday": "15.02.1980", // дата рождения в формате dd.mm.yyyy
			    "has_pic": 1, // есть ли аватар у пользователя (1 - есть, 0 - нет)
			    "pic": "http://avt.appsmail.ru/mail/emaslov/_avatar",
				// уменьшенный аватар - размер по большей стороне не более 45px
			    "pic_small": "http://avt.appsmail.ru/mail/emaslov/_avatarsmall",
				// большой аватар - размер по большей стороне не более 600px
			    "pic_big": "http://avt.appsmail.ru/mail/emaslov/_avatarbig",
			    "link": "http://my.mail.ru/mail/emaslov/",
			    "referer_type": "", // тип реферера (см. ниже)
			    "referer_id": "", // идентификатор реферера (см. ниже)
			    "is_online": 1,
			    "is_verified": 1, //статус верификации пользователя (1 – телефон подтвержден, 0 – не подтвержден)
			    "vip" : 0, // 0 - не вип, 1 - вип
			    "app_installed": 1, // установлено ли у пользователя текущее приложение
			    "location": {
			      "country": {
			        "name": "Россия",
			        "id": "24"
			      },
			      "city": {
			        "name": "Москва",
			        "id": "25"
			      },
			      "region": {
			        "name": "Москва",
			        "id": "999999"
			      }	 */		
			
			$request_params = array(
					'app_id' => $this->client_id,
					'session_key' =>  $request->access_token,
					'method' => 'users.getInfo',
					'secure' => '1'
			);
			
			$params = $this->get_server_params($request_params);

			$url = 'http://www.appsmail.ru/platform/api?'.$params;
			$request = json_decode($this->open_http($url));

			$request = $request[0];

            $this->storeOrLogin($request->first_name, $request->last_name, $request->email, $request->uid, $provider, true, $request);
		}
	
	}	
	
	protected function get_server_params(array $request_params) {
        $params = '';
		ksort($request_params);
		foreach ($request_params as $key => $value) {
			$params .= "$key=$value&";
		}
		
		$sig_params = str_replace('&', '', $params);
		$signature = md5($sig_params . $this->client_secret);
		$params = $params .'sig=' . $signature;
		
		return  $params;
	}
}
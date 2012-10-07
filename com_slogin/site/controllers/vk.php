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

class SLoginControllerVk extends SLoginController
{
	protected $client_id;
	protected $client_secret;

	public function __construct()
	{
	
		$cofig = array();
		parent::__construct($cofig);
		$this->client_id =  $this->config->get('vk_client_id');
		$this->client_secret = $this->config->get('vk_client_secret');

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

        $app->setUserState('com_slogin.return_url', $input->getString('return', ''));

        $redirect = JURI::base().'?option=com_slogin&task=vk.check';

        $this->localAuthDebug($redirect);

		$params = array(
				'client_id=' . $this->client_id,
			    'response_type=code',
			    'redirect_uri=' . urlencode($redirect),
				'scope=offline'
		);
		$params = implode('&', $params);

		$url = 'http://oauth.vk.com/authorize?' . $params;

		header('Location:' . $url);
	}

	/**
	* Проверка аутентификации на сайте донора
	* Создание новой учетной записи на сайте или утентификация, если такой пользователь уже есть
	*/	
	public function check()
	{
		$input = JFactory::getApplication()->input;
        $code = $input->get('code');
        $redirect = urlencode(JURI::base().'?option=com_slogin&task=vk.check');
        $provider = 'vk';
        $app	= JFactory::getApplication();

        $this->localCheckDebug($provider);

		if ($code) {
			//подключение к API
			$params = array(
							'client_id=' . $this->client_id,
						    'client_secret=' . $this->client_secret,
						    'code=' . $code,
                            'redirect_uri=' . $redirect
			);
			$params = implode('&', $params);			

			$url = 'https://oauth.vk.com/access_token?' . $params;

			$data = json_decode($this->open_http($url));

			if (empty($data->access_token) && $data->error) {
                $error = (!empty($data->error_description)) ? $data->error_description : $data->info;
				die($error);
			}
			
// 			Получение данных о пользователе поле fields
// 			Нужное можно указать!
// 			uid, first_name, last_name, nickname, screen_name, sex, bdate (birthdate), city, country, 
// 			timezone, photo, photo_medium, photo_big, has_mobile, rate, contacts, education, online, counters.
// 			По умолчанию возвращает uid, first_name и last_name 
			
// 			name_case - дополнительный параметр
// 			падеж для склонения имени и фамилии пользователя. 
// 			Возможные значения: 
// 			именительный – nom, 
// 			родительный – gen, 
// 			дательный – dat, 
// 			винительный – acc, 
// 			творительный – ins, 
// 			предложный – abl. 
// 			По умолчанию nom.
			
			$ResponseUrl = 'https://api.vk.com/method/getProfiles?uid='.$data->user_id.'&access_token='.$data->access_token.'&fields=nickname,contacts';
			$request = json_decode($this->open_http($ResponseUrl))->response[0];

            $this->storeOrLogin($request->first_name, $request->last_name, $request->email, $request->uid, $provider, true, $request);
		}

	}
}

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

class SLoginControllerTw extends SLoginController
{
	protected $client_id;
	protected $client_secret;

	public function __construct()
	{
		$cofig = array();
		parent::__construct($cofig);
		$this->client_id =  $this->config->get('tw_client_id');
		$this->client_secret = $this->config->get('tw_client_secret');
	
	}	

	/**
	 * Аутентификация пользователя
	 */
	public function auth()
	{
        $app	= JFactory::getApplication();
        $input = $app->input;
        $app->setUserState('com_slogin.action.data', $input->getString('action', ''));

        $redirect = JURI::base().'?option=com_slogin&task=tw.check';
        $this->localAuthDebug($redirect);

		parent::auth();
		//получение oauth_token OAuth 1.0
		$config['key'] = $this->client_id;
		$config['secret'] = $this->client_secret;
		$twitauth = new Twitauth($config);
		$req_url = $twitauth->getRequestUrl();
		parse_str($req_url, $data_array);
		$oauth_signature = $data_array['oauth_signature'];

		$req_token = $this->open_http($req_url);
		
		parse_str($req_token, $data_array);
		
		if (!isset($data_array['oauth_token'])) {
			die('Error: oauth_token not set');
		}
		
		//установка значений в сессию
		$session = JFactory::getSession();
		$session->set('oauth_token', $data_array['oauth_token']);
		$session->set('oauth_signatur', $oauth_signature);
		
		//редирект на страницу авторизации
		$url = 'http://api.twitter.com/oauth/authenticate?oauth_token=' . $data_array['oauth_token'];
		header('Location:' . $url);
	}

	/**
	 * Проверка аутентификации на сайте донора
	 * Создание новой учетной записи на сайте или утентификация, если такой пользователь уже есть
	 */
	public function check()
	{
        $provider = 'tw';
        $app	= JFactory::getApplication();
        $input = $app->input;


        $this->localCheckDebug($provider);

		$input = JFactory::getApplication()->input;

		if ($code = $input->get('oauth_verifier')) {
			//получение значений из сессии
			$session = JFactory::getSession();
			$oauth_token = $session->get('oauth_token');
			$oauth_signature = $session->get('oauth_signature');
			
			$redirect = urlencode(JURI::base().'?option=com_slogin&task=tw.check');
			//подключение к API
			$params = array(
							'oauth_consumer_key=' . $this->client_id,
						    'oauth_token=' . $oauth_token,
							'oauth_verifier=' . $code,
						    'oauth_timestamp=' . time(),
							'oauth_nonce=' . time(),
							'oauth_signature=' . $oauth_signature,
						    'oauth_signature_method=HMAC-SHA1',
						    'oauth_version=1.0',
						    
			);
			$params = implode('&', $params);
			
			//отправка POST запроса
			$url = 'http://api.twitter.com/oauth/access_token';
			$request = $this->open_http($url, true, $params);

			parse_str($request, $data);

			//получение данных о пользователе
// 			$url = 'https://api.twitter.com/1/users/show.json?screen_name='.$data['screen_name'];
// 			$info = json_decode($this->open_http($url));

			//удаляем данные из сессии, уже не нужны
			$session->clear('oauth_token');
			$session->clear('oauth_signature');

            $this->storeOrLogin($provider, $data['user_id'], $data['email'], $data['user_id'], $provider, true);
		}

	}

}


/**
 * Класс для OAuth 1.0
 *
 */
class Twitauth
{
	protected $key = '';
	protected $secret = '';
	protected $request_token = "https://twitter.com/oauth/request_token";

	public function Twitauth($config)
	{
		$this->key = $config['key']; // consumer key from twitter
		$this->secret = $config['secret']; // secret from twitter
	}

	public function getRequestUrl()
	{
		// Default params
		$params = array(
            "oauth_version" => "1.0",
            "oauth_nonce" => time(),
            "oauth_timestamp" => time(),
            "oauth_consumer_key" => $this->key,
            "oauth_signature_method" => "HMAC-SHA1"
		);

		// BUILD SIGNATURE
		// encode params keys, values, join and then sort.
		$keys = $this->_urlencode_rfc3986(array_keys($params));
		$values = $this->_urlencode_rfc3986(array_values($params));
		$params = array_combine($keys, $values);
		uksort($params, 'strcmp');

		// convert params to string
		foreach ($params as $k => $v) {
			$pairs[] = $this->_urlencode_rfc3986($k).'='.$this->_urlencode_rfc3986($v);
		}
		$concatenatedParams = implode('&', $pairs);

		// form base string (first key)
		$baseString= "GET&".$this->_urlencode_rfc3986($this->request_token)."&".$this->_urlencode_rfc3986($concatenatedParams);
		// form secret (second key)
		$secret = $this->_urlencode_rfc3986($this->secret)."&";

		// make signature and append to params
		$params['oauth_signature'] = $this->_urlencode_rfc3986(base64_encode($this->custom_hmac('sha1', $baseString, $secret, TRUE)));

		// BUILD URL
		// Resort
		uksort($params, 'strcmp');
		// convert params to string
		foreach ($params as $k => $v) {
			$urlPairs[] = $k."=".$v;
		}
		$concatenatedUrlParams = implode('&', $urlPairs);

		// form url
		$url = $this->request_token."?".$concatenatedUrlParams;
		
		return $url;

	}


	protected function _urlencode_rfc3986($input)
	{
		if (is_array($input)) {
			return array_map(array('Twitauth', '_urlencode_rfc3986'), $input);
		}
		else if (is_scalar($input)) {
			return str_replace('+',' ',str_replace('%7E', '~', rawurlencode($input)));
		}
		else{
			return '';
		}
	}
	
	protected function custom_hmac($algo, $data, $key, $raw_output = false)
	{
		$algo = strtolower($algo);
		$pack = 'H'.strlen($algo('test'));
		$size = 64;
		$opad = str_repeat(chr(0x5C), $size);
		$ipad = str_repeat(chr(0x36), $size);
	
		if (strlen($key) > $size) {
			$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
		} else {
			$key = str_pad($key, $size, chr(0x00));
		}
	
		for ($i = 0; $i < strlen($key) - 1; $i++) {
			$opad[$i] = $opad[$i] ^ $key[$i];
			$ipad[$i] = $ipad[$i] ^ $key[$i];
		}
	
		$output = $algo($opad.pack($pack, $algo($ipad.$data)));
	
		return ($raw_output) ? pack($pack, $output) : $output;
	}	
}

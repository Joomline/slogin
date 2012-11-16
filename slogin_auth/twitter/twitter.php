<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT_SITE.'/controller.php';

class plgSlogin_authTwitter extends JPlugin
{
    public function onAuth($url)
    {
        $controller = new SLoginController();

        $config['key'] = $this->params->get('id');
        $config['secret'] = $this->params->get('password');
        $twitauth = new Twitauth($config);
        $req_url = $twitauth->getRequestUrl();
        parse_str($req_url, $data_array);
        $oauth_signature = $data_array['oauth_signature'];

        $req_token = $controller->open_http($req_url);

        parse_str($req_token, $data_array);

        if (!isset($data_array['oauth_token'])) {
            die('Error: oauth_token not set');
        }

        //установка значений в сессию
        $session = JFactory::getSession();
        $session->set('oauth_token', $data_array['oauth_token']);
        $session->set('oauth_signature', $oauth_signature);

        //редирект на страницу авторизации
        $url = 'http://api.twitter.com/oauth/authenticate?oauth_token=' . $data_array['oauth_token'];

        return $url;
    }

    public function onCheck()
    {
        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $request = null;

        $code = $input->get('oauth_verifier', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            //получение значений из сессии
            $session = JFactory::getSession();
            $oauth_token = $session->get('oauth_token');
            $oauth_signature = $session->get('oauth_signature');

            //подключение к API
            $params = array(
                'oauth_consumer_key=' . $this->params->get('id'),
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
            $request = $controller->open_http($url, true, $params);

            parse_str($request, $data);

            //получение данных о пользователе
            $url = 'https://api.twitter.com/1/users/show.json?screen_name='.$data['screen_name'];
            $request = json_decode($controller->open_http($url));

            //удаляем данные из сессии, уже не нужны
            $session->clear('oauth_token');
            $session->clear('oauth_signature');

            if(!empty($request->error)){
                echo 'Error - '. $request->error;
                exit;
            }

            $returnRequest->first_name  = $request->name;
            $returnRequest->last_name   = $request->screen_name;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $request->name.' '.$request->screen_name;
            $returnRequest->display_name = $request->screen_name;
            $returnRequest->all_request  = $request;
        }
        return $returnRequest;
    }

    public function onCreateLink($links)
    {
//        $document = JFactory::getDocument();
//        $document->addStyleDeclaration('
//           .slogin-buttons .yandex {
//                background-position: 0 -426px;
//            }
//        ');
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=twitter';
        $links[$i]['class'] = 'twitter';
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

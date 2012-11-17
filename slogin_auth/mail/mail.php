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

class plgSlogin_authMail extends JPlugin
{
    public function onAuth($url)
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=mail';

        $params = array(
            'response_type=code',
            'redirect_uri=' . urlencode($redirect),
            'client_id=' . $this->params->get('id')
        );
        $params = implode('&', $params);

        $url = 'https://connect.mail.ru/oauth/authorize?'.$params;

        return $url;
    }

    public function onCheck()
    {
        require_once JPATH_COMPONENT_SITE.'/controller.php';

        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $request = null;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            $redirect = urlencode(JURI::base().'?option=com_slogin&task=check&plugin=mail');

            // get access_token from mail  API
            $params = array(
                'client_id=' . $this->params->get('id'),
                'client_secret=' . $this->params->get('password'),
                'grant_type=authorization_code',
                'code=' . $code,
                'redirect_uri=' . $redirect


            );
            $params = implode('&', $params);

            $url = 'https://connect.mail.ru/oauth/token';
            $request = json_decode($controller->open_http($url, true, $params));

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
                'app_id' => $this->params->get('id'),
                'session_key' =>  $request->access_token,
                'method' => 'users.getInfo',
                'secure' => '1'
            );

            $params = $this->get_server_params($request_params);

            $url = 'http://www.appsmail.ru/platform/api?'.$params;
            $request = json_decode($controller->open_http($url));

            if(!empty($request->error)){
                echo 'Error - '. $request->error->error_msg;
                exit;
            }

            $request = $request[0];

            $returnRequest->first_name  = $request->first_name;
            $returnRequest->last_name   = $request->last_name;
            $returnRequest->email       = $request->email;
            $returnRequest->id          = $request->uid;
            $returnRequest->real_name   = $request->first_name.' '.$request->last_name;
            $returnRequest->sex         = $request->sex;
            $returnRequest->display_name = $request->nick;
            $returnRequest->all_request  = $request;
        }
        return $returnRequest;
    }

    public function onCreateLink($links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=mail' . $add;
        $links[$i]['class'] = 'mailslogin';
        $links[$i]['plugin_name'] = 'mail';
    }

    protected function get_server_params(array $request_params) {
        $params = '';
        ksort($request_params);
        foreach ($request_params as $key => $value) {
            $params .= "$key=$value&";
        }

        $sig_params = str_replace('&', '', $params);
        $signature = md5($sig_params . $this->params->get('password'));
        $params = $params .'sig=' . $signature;

        return  $params;
    }
}

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

class plgSlogin_authFacebook extends JPlugin
{
    public function onSloginAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=facebook';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'scope=email,user_photos,user_about_me,user_hometown,user_photos'
        );
        $params = implode('&', $params);

        $url = 'http://www.facebook.com/dialog/oauth?' . $params;
        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $request = null;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            $redirect = urlencode(JURI::base().'?option=com_slogin&task=check&plugin=facebook');
            //подключение к API
            $params = array(
                'client_id=' . $this->params->get('id'),
                'client_secret=' . $this->params->get('password'),
                'code=' . $code,
                'redirect_uri='. $redirect
            );
            $params = implode('&', $params);
            $url = 'https://graph.facebook.com/oauth/access_token?' . $params;
            $data = $controller->open_http($url);
            parse_str($data, $data_array);

            if(empty($data_array['access_token'])){
                echo 'Error - empty access tocken';
                exit;
            }

// 			Получение данных о пользователе
// 			id, name, first_name, last_name, link, gender, timezone, locale, verified, updated_time
// 			email смотреть параметр scope в методе auth()!

            $ResponseUrl = 'https://graph.facebook.com/me?access_token='.$data_array['access_token'];
            $request = json_decode($controller->open_http($ResponseUrl));

            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            else if(!empty($request->error)){
                echo 'Error - '. $request->error;
                exit;
            }
            //var_dump($request); die;
            $returnRequest->first_name  = $request->first_name;
            $returnRequest->last_name   = $request->last_name;
            $returnRequest->email       = $request->email;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $request->first_name.' '.$request->last_name;
            $returnRequest->sex         = $request->gender;
            $returnRequest->display_name = $request->name;
            $returnRequest->all_request  = $request;

            return $returnRequest;
        }
        else{
            echo 'Error - empty code';
            exit;
        }
    }
    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=facebook' . $add;
        $links[$i]['class'] = 'facebookslogin';
        $links[$i]['plugin_name'] = 'facebook';
    }
}

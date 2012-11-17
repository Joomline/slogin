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

class plgSlogin_authYandex extends JPlugin
{
	public function onAuth()
	{
        return 'https://oauth.yandex.ru/authorize?response_type=code&display=popup&client_id='.$this->params->get('id');
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
            // get access_token from API
            $params = array(
                'grant_type=authorization_code',
                'code=' . $code,
                'client_id=' . $this->params->get('id'),
                'client_secret=' . $this->params->get('password')
            );
            $params = implode('&', $params);

            $url = 'https://oauth.yandex.ru/token';


            $request = $controller->open_http($url, true, $params);

            $request = json_decode($request);

            if(!empty($request->error)){
                echo 'Error - '. $request->error;
                exit;
            }

            if(empty($request->access_token)){
                echo 'Error - empty access tocken';
                exit;
            }

            $url = 'https://login.yandex.ru/info?format=json&oauth_token='.$request->access_token;

            $request = json_decode($controller->open_http($url));

            $name = explode(' ', $request->real_name);

            $returnRequest->first_name = (isset($name[1])) ? $name[1] : '';
            $returnRequest->last_name = (isset($name[0])) ? $name[0] : '';
            $returnRequest->email = $request->default_email;
            $returnRequest->id = $request->id;
            $returnRequest->real_name = $request->real_name;
            $returnRequest->sex = $request->sex;
            $returnRequest->display_name = $request->display_name;
            $returnRequest->birthday = $request->birthday;
            $returnRequest->all_request  = $request;
        }
        return $returnRequest;
	}

    public function onCreateLink($links, $add = '')
    {
//        $document = JFactory::getDocument();
//        $document->addStyleDeclaration('
//           .slogin-buttons .yandexslogin {
//                background-position: 0 -426px;
//            }
//        ');
       $i = count($links);
       $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=yandex' . $add;
       $links[$i]['class'] = 'yandexslogin';
       $links[$i]['plugin_name'] = 'yandex';
    }
}

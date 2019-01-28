<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgSlogin_authInstagram extends JPlugin
{
    private $provider = 'instagram';

    public function onSloginAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=instagram';

        $scope = 'basic';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'scope=' . $scope,
            'response_type=code'
        );

        $params = implode('&', $params);

        $url = 'https://api.instagram.com/oauth/authorize/?' . $params;
        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $request = null;

        $code = $input->getString('code', '');

        $returnRequest = new SloginRequest();

        if ($code)
        {
            $data = $this->getToken($code);

            /*
             object(stdClass)#592 (2) {
                ["access_token"]=> string(51) "1422961258.a24a2c5.fff48defcfd"
                ["user"]=> object(stdClass)#591 (6) {
                    ["username"]=> string(12) "a.ssdfsov"
                    ["bio"]=> string(0) ""
                    ["website"]=> string(0) ""
                    ["profile_picture"]=> string(91) "http://photos-a.ak.instagram.com/hphotos-ak-xpa1/10518061_1509554482591320_1078900681_a.jpg"
                    ["full_name"]=> string(7) "Arkadiy"
                    ["id"]=> string(15) "11242291311258321"
                }
             }
             */

            //сохраняем данные токена в сессию
            //expire - время устаревания скрипта, метка времени Unix
            JFactory::getApplication()->setUserState('slogin.token', array(
                'provider' => $this->provider,
                'token' => $data->access_token,
                'expire' => '',
                'repost_comments' => 0,
                'slogin_user' => $data->user->id,
                'app_id' => $this->params->get('id', 0),
                'app_secret' => $this->params->get('password', 0)
            ));
            
            $returnRequest->first_name      = $data->user->full_name;
            $returnRequest->id              = $data->user->id;
            $returnRequest->real_name       = $data->user->full_name;
            $returnRequest->display_name    = $data->user->username;
            $returnRequest->all_request     = $data;

            return $returnRequest;
        }
        else{
            $config = JComponentHelper::getParams('com_slogin');
            JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = JModelLegacy::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = JControllerLegacy::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }
    }

    public function getToken($code)
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';
        $controller = new SLoginController();

        $redirect = urlencode(JURI::base().'?option=com_slogin&task=check&plugin=instagram');

        //подключение к API
        $params = array(
            'client_id=' . $this->params->get('id'),
            'client_secret=' . $this->params->get('password'),
            'grant_type=authorization_code',
            'redirect_uri='. $redirect,
            'code=' . $code
        );

        $params = implode('&', $params);

        $url = 'https://api.instagram.com/oauth/access_token';
        $response = $controller->open_http($url, true, $params);
        $data = json_decode($response);

        if(empty($data->access_token))
        {
            echo 'Error - empty access tocken';
            echo '<pre>';
            var_dump($response);
            echo '</pre>';
            exit;
        }

        return $data;
    }

    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=instagram' . $add;
        $links[$i]['class'] = 'instagramslogin';
        $links[$i]['plugin_name'] = $this->provider;
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_INSTAGRAM');
    }
}

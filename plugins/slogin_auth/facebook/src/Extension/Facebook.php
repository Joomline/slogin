<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

namespace Joomline\Plugin\SloginAuth\Facebook\Extension;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\CMSPlugin;

class Facebook extends CMSPlugin
{
    private $provider = 'facebook';

    public function onSloginAuth()
    {
        $redirect = Uri::base().'?option=com_slogin&task=check&plugin=facebook';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'response_type=code'
        );

        $params = implode('&', $params);

        $url = 'https://www.facebook.com/v13.0/dialog/oauth?' . $params;
        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new \SLoginController();

        $input = Factory::getApplication()->input;

        $request = null;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new \SloginRequest();

        if ($code)
        {
            $token = $this->getToken($code);

// 			Получение данных о пользователе
// 			id, name, first_name, last_name, link, gender, timezone, locale, verified, updated_time
// 			email смотреть параметр scope в методе auth()!

            $ResponseUrl = 'https://graph.facebook.com/v2.12/me?access_token='.$token['access_token'].'&fields=id,name,first_name,last_name,link,email';
            $request = json_decode($controller->open_http($ResponseUrl));

            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            else if(!empty($request->error)){
                echo 'Error - '. $request->error;
                exit;
            }

            //сохраняем данные токена в сессию
            //expire - время устаревания скрипта, метка времени Unix
            Factory::getApplication()->setUserState('slogin.token', array(
                'provider' => $this->provider,
                'token' => $token['access_token'],
                'expire' => (time() + $token['expires_in']),
                'repost_comments' => $this->params->get('repost_comments', 0),
                'slogin_user' => $request->id,
                'app_id' => $this->params->get('id', 0),
                'app_secret' => $this->params->get('password', 0)
            ));

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
            $config = ComponentHelper::getParams('com_slogin');
            BaseDatabaseModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = BaseDatabaseModel::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = BaseController::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }
    }

    public function getToken($code)
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';
        $controller = new \SLoginController();

        $redirect = urlencode(Uri::base().'?option=com_slogin&task=check&plugin=facebook');

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
        $data_array = json_decode($data,true);

        if(empty($data_array['access_token'])){
            echo 'Error - empty access tocken';
            exit;
        }

        return $data_array;
    }

    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=facebook' . $add;
        $links[$i]['class'] = 'facebookslogin';
        $links[$i]['plugin_name'] = $this->provider;
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_FACEBOOK');
    }
}
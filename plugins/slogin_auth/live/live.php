<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

class plgSlogin_authLive extends CMSPlugin
{
    public function onSloginAuth()
    {
        $uri = Route::_('index.php?option=com_slogin&task=check&plugin=live');
        $uri = mb_substr($uri, 1);
        $redirect = Uri::base().$uri;

        $scope = urlencode('wl.signin wl.basic wl.emails wl.photos');

        $params = array(
            'response_type=code',
            'redirect_uri=' . urlencode($redirect),
            'client_id=' . $this->params->get('id'),
            'scope=' . $scope
            //,'access_type=offline'
            //,'approval_prompt=force'
        );
        $params = implode('&', $params);

        $url = 'https://login.live.com/oauth20_authorize.srf?'.$params;
        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = Factory::getApplication()->input;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            // get access_token for google API
            $uri = Route::_('index.php?option=com_slogin&task=check&plugin=live');
            $uri = mb_substr($uri, 1);
            $redirect = urlencode(Uri::base().$uri);


            $params = array(
                'client_id=' . $this->params->get('id'),
                'client_secret=' . $this->params->get('password'),
                'grant_type=authorization_code',
                'code=' . $code,
                'redirect_uri=' . $redirect
            );

            $params = implode('&', $params);
            $url = 'https://login.live.com/oauth20_token.srf';
            $request = json_decode($controller->open_http($url, true, $params));

            if(empty($request)){
                echo 'Error - empty access tocken';
                exit;
            }

            if(!empty($request->error))
            {
                echo '<pre>'; $request->error; echo '</pre>';
                if(!empty($request->error_description)){
                    echo '<pre>'; $request->error_description; echo '</pre>';
                }
                exit;
            }

            $url = 'https://apis.live.net/v5.0/me?access_token='.$request->access_token;

            $request = json_decode($controller->open_http($url));

            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            else if(!empty($request->error)){
                echo 'Error - '. $request->error;
                exit;
            }
            /*
            object(stdClass)#534 (9) {
            ["id"]=> string(16) "d5f1256f7b5322c7"
            ["name"]=> string(37) "Аркадий Седельников"
            ["first_name"]=> string(14) "Аркадий"
            ["last_name"]=> string(22) "Седельников"
            ["link"]=> string(25) "https://profile.live.com/"
            ["gender"]=> NULL
            ["emails"]=> object(stdClass)#533 (4) {
                ["preferred"]=> string(22) "a.sedelnikov@gmail.com"
                ["account"]=> string(22) "a.sedelnikov@gmail.com"
                ["personal"]=> NULL
                ["business"]=> NULL
            }
            ["locale"]=> string(5) "ru_RU"
            ["updated_time"]=> string(24) "2013-02-09T04:50:26+0000"
            }
            */

            if(!empty($request->emails->preferred))
                $email = $request->emails->preferred;
            else if(!empty($request->emails->account))
                $email = $request->emails->account;
            else if(!empty($request->emails->personal))
                $email = $request->emails->personal;
            else if(!empty($request->emails->business))
                $email = $request->emails->business;
            else
                $email = '';

            $returnRequest->first_name  = $request->first_name;
            $returnRequest->last_name   = $request->last_name;
            $returnRequest->email       = $email;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $request->name;
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
    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=live' . $add;
        $links[$i]['class'] = 'liveslogin';
        $links[$i]['plugin_name'] = 'live';
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_LIVE');
    }
}

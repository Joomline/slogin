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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

class plgSlogin_authOdnoklassniki extends CMSPlugin
{
    public function onSloginAuth()
    {
        $redirect = Uri::base().'?option=com_slogin&task=check&plugin=odnoklassniki';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'response_type=code',
            'redirect_uri=' . urlencode($redirect),
            'scope=VALUABLE ACCESS'
        );
        $params = implode('&', $params);

        $url = 'https://connect.ok.ru/oauth/authorize?'.$params;

        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = Factory::getApplication()->input;

        $request = null;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            $redirect = urlencode(Uri::base().'?option=com_slogin&task=check&plugin=odnoklassniki');

            $params = array(
                'client_id=' . $this->params->get('id'),
                'client_secret=' . $this->params->get('password'),
                'grant_type=authorization_code',
                'code=' . $code,
                'redirect_uri=' . $redirect


            );
            $params = implode('&', $params);

            $url = 'https://api.ok.ru/oauth/token.do';
            $request = json_decode($controller->open_http($url, true, $params));

            if (empty($request)) {
                echo 'Error - empty access tocken';
                die();
            }
            else if (!empty($request->error)) {
                echo '<h3>token.do</h3>';
                echo '<p>'.$request->error.'</p>';
                echo '<p>'.$request->error_description.'</p>';
                die();
            }

            /*
               Объект $request содержит следующие поля:
               uid - уникальный номер пользователя
               first_name - имя пользователя
               last_name - фамилия пользователя
               birthday - дата рождения пользователя
               gender - пол пользователя
               pic_1 - маленькое фото
               pic_2 - большое фото
               */

            $request_params = array(
                'application_key=' . $this->params->get('public_key'),
                'access_token=' .  $request->access_token,
                'method=users.getCurrentUser',
                'sig=' . md5(
                        'application_key='
                        . $this->params->get('public_key')
                        . 'method=users.getCurrentUser'
                        . md5($request->access_token
                        . $this->params->get('password'))
                )
            );

            $params = implode('&', $request_params);

            $url = 'https://api.ok.ru/fb.do?'.$params;
            $request = json_decode($controller->open_http($url));

            if (empty($request)) {
                echo 'Error - empty user data';
                die();
            }
            else if (!empty($request->error_code)) {
                echo '<h3>fb.do</h3>';
                echo '<p>'.$request->error_data.'</p>';
                echo '<p>'.$request->error_msg.'</p>';
                die();
            }

            if(!empty($request->error)){
                echo 'Error - '. $request->error;
                exit;
            }

            $returnRequest->first_name  = $request->first_name;
            $returnRequest->last_name   = $request->last_name;
            $returnRequest->email       = $request->email;
            $returnRequest->id          = $request->uid;
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

    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=odnoklassniki' . $add;
        $links[$i]['class'] = 'odnoklassnikislogin';
        $links[$i]['plugin_name'] = 'odnoklassniki';
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_ODNOKLASSNIKI');
    }
}

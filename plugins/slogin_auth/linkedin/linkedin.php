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
include_once JPATH_ROOT."/components/com_slogin/controller.php";

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

class plgSlogin_authLinkedin extends CMSPlugin
{
    private $redirect;
    function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->redirect = Uri::base().'?option=com_slogin&task=check&plugin=linkedin';
    }

    public function onSloginAuth()
    {
        $redirect = urlencode($this->redirect);
        $state = md5(time().'slogin');
        $scope = urlencode('r_liteprofile r_emailaddress');
        $url = 'https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id='
            .$this->params->get('api_key').'&redirect_uri='.$redirect.'&state='.$state.'&scope='.$scope;
        $app = Factory::getApplication('site');
        $app->setUserState('linkedInState', $state);
        return $url;
    }

    public function onSloginCheck()
    {
        $redirect = urlencode($this->redirect);

        $app = Factory::getApplication();
        $input = $app->input;
        $code = $input->getString('code', '');
        $state = $input->getString('state', '');

        if($code)
        {
            $app = Factory::getApplication('site');
            $linkedInState = $app->getUserState('linkedInState', '');
            if($state != $linkedInState){
                die('Error: wrong state');
            }

            $oauth_problem = $input->getString('oauth_problem', '');
            if($oauth_problem == 'user_refused'){
                $config = ComponentHelper::getParams('com_slogin');

                BaseDatabaseModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
                $model = BaseDatabaseModel::getInstance('Linking_user', 'SloginModel');

                $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));

                $controller = BaseController::getInstance('SLogin');
                $controller->displayRedirect($redirect, true);
            }

            include_once JPATH_BASE.'/components/com_slogin/controller.php';
            $controller = new SLoginController();

            $params = array(
                'grant_type=authorization_code',
                'code=' . $code,
                'redirect_uri=' . urlencode($this->redirect),
                'client_id=' . $this->params->get('api_key', ''),
                'client_secret=' . $this->params->get('secret_key', '')
            );

            $params = implode('&', $params);

            $url = 'https://www.linkedin.com/oauth/v2/accessToken';
            $token = $controller->open_http($url, true, $params);
            $token = json_decode($token, true);

            if(empty($token['access_token'])){
                echo 'Error - empty access tocken';
                exit;
            }

            $profile = $this->getUserData($token['access_token']);
            $request = json_decode($profile);

            if (empty($request->id)) {
                die('Error: profile empty');
            }

            $returnRequest = new SloginRequest();

            $firstName = isset($request->localizedFirstName) ? $request->localizedFirstName : '';
            $lastName = isset($request->localizedLastName) ? $request->localizedLastName : '';
            $emailAddress = isset($request->emailAddress) ? $request->emailAddress : '';
            $display_name = '';
            if(!empty($firstName)){
                $display_name .= $firstName;
            }
            if(!empty($lastName)){
                $display_name .= !empty($firstName) ? ' ' : '';
                $display_name .= $lastName;
            }
            $returnRequest->first_name  = $firstName;
            $returnRequest->last_name   = $lastName;
            $returnRequest->email       = $emailAddress;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $display_name;
            $returnRequest->display_name = $firstName;
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
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=linkedin' . $add;
        $links[$i]['class'] = 'linkedinslogin';
        $links[$i]['plugin_name'] = 'linkedin';
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_LINKEDIN');
    }

    private function getUserData($token)
    {
        if (!function_exists('curl_init')) {
            die('ERROR: CURL library not found!');
        }
        $url = 'https://api.linkedin.com/v2/me/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch,  CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '. $token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

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
include_once JPATH_ROOT."/components/com_slogin/controller.php";


class plgSlogin_authLinkedin extends JPlugin
{
    private $redirect;
    function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->redirect = JURI::base().'?option=com_slogin&task=check&plugin=linkedin';
    }

    public function onSloginAuth()
    {
        $redirect = urlencode($this->redirect);
        $state = md5(time().'slogin');
        $scope = urlencode('r_basicprofile r_emailaddress');
        $url = 'https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id='
            .$this->params->get('api_key').'&redirect_uri='.$redirect.'&state='.$state.'&scope='.$scope;
        $app = JFactory::getApplication('site');
        $app->setUserState('linkedInState', $state);
        return $url;
    }

    public function onSloginCheck()
    {
        $redirect = urlencode($this->redirect);

        $app = JFactory::getApplication();
        $input = $app->input;
        $code = $input->getString('code', '');
        $state = $input->getString('state', '');

        if($code)
        {
            $app = JFactory::getApplication('site');
            $linkedInState = $app->getUserState('linkedInState', '');
            if($state != $linkedInState){
                die('Error: wrong state');
            }

            $oauth_problem = $input->getString('oauth_problem', '');
            if($oauth_problem == 'user_refused'){
                $config = JComponentHelper::getParams('com_slogin');

                JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
                $model = JModelLegacy::getInstance('Linking_user', 'SloginModel');

                $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));

                $controller = JControllerLegacy::getInstance('SLogin');
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

            $firstName = isset($request->firstName) ? $request->firstName : '';
            $lastName = isset($request->lastName) ? $request->lastName : '';
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
            $config = JComponentHelper::getParams('com_slogin');
            JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = JModelLegacy::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = JControllerLegacy::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }
    }
    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=linkedin' . $add;
        $links[$i]['class'] = 'linkedinslogin';
        $links[$i]['plugin_name'] = 'linkedin';
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_LINKEDIN');
    }

    private function getUserData($token)
    {
        if (!function_exists('curl_init')) {
            die('ERROR: CURL library not found!');
        }
        $url = 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,headline,picture-url,email-address)?format=json';
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

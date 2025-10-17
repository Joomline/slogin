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

class plgSlogin_authYahoo extends CMSPlugin
{
    var $key,
        $secret,
        $app_id,
        $callback;
    function __construct(&$subject, $params)
	{
		parent::__construct( $subject, $params );
		$this->key = $this->params->get('key');
		$this->secret = $this->params->get('secret');
		$this->app_id = '';
		$this->callback = Uri::base().'?option=com_slogin&task=check&plugin=yahoo';
	}
    public function onSloginAuth()
    {
        $params = array(
            'client_id=' . $this->key,
            'redirect_uri=' . urlencode($this->callback),
            'response_type=code'
        );

        $params = implode('&', $params);

        $url = 'https://api.login.yahoo.com/oauth2/request_auth?' . $params;
        return $url;
    }

    public function onSloginCheck()
    {
        $input = Factory::getApplication()->input;
        $code = $input->getString('code', null);


        if($code)
        {
            include_once JPATH_BASE.'/components/com_slogin/controller.php';
            $controller = new SLoginController();

            $params = array(
                'client_id=' . $this->key,
                'client_secret=' . $this->secret,
                'redirect_uri=' . urlencode($this->callback),
                'code=' . $code,
                'grant_type=authorization_code'
            );

            $params = implode('&', $params);

            $url = 'https://api.login.yahoo.com/oauth2/get_token';
            $token = $controller->open_http($url, true, $params);
            $token = json_decode($token, true);

            if(empty($token['access_token'])){
                echo 'Error - empty access tocken';
                exit;
            }
            if(empty($token["xoauth_yahoo_guid"])){
                echo 'Error - empty yahoo guid';
                exit;
            }

            $profile = $this->getUserData($token);
            $profile = json_decode($profile);

            if (empty($profile->profile)) {
                die('Error: profile empty');
            }

            $returnRequest = new SloginRequest();
            $returnRequest->first_name  = $profile->profile->givenName ?? $profile->profile->nickname;
            $returnRequest->last_name   = $profile->profile->familyName ?? '';
            $returnRequest->email       = isset($profile->profile->emails)
                && isset($profile->profile->emails[0])
                && isset($profile->profile->emails[0]->handle)
                ? $profile->profile->emails[0]->handle : '';
            $returnRequest->id          = $profile->profile->guid ?? '';
            $returnRequest->real_name   = $profile->profile->givenName ?? $profile->profile->nickname;
            $returnRequest->sex         = $profile->profile->gender ?? '';
            $returnRequest->display_name = $profile->profile->nickname;
            $returnRequest->all_request  = $profile->profile;
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
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=yahoo' . $add;
        $links[$i]['class'] = 'yahooslogin';
        $links[$i]['plugin_name'] = 'yahoo';
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_YAHOO');
    }

    private function getUserData($token)
    {
        if (!function_exists('curl_init')) {
            die('ERROR: CURL library not found!');
        }
        $url = 'https://social.yahooapis.com/v1/user/'.$token["xoauth_yahoo_guid"].'/profile?format=json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch,  CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '. $token["access_token"]
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

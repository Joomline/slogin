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

class plgSlogin_authGitHub extends CMSPlugin
{
    public function onSloginAuth()
    {
        $redirect = Uri::base().'?option=com_slogin&task=check&plugin=github';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'response_type=code'
        );
        $params = implode('&', $params);

        $url = 'https://github.com/login/oauth/authorize?'.$params;

        return $url;
    }

    public function onSloginCheck()
    {
        $input = Factory::getApplication()->input;

        $error = $input->getString('error', '');

        if($error == 'access_denied'){
            die('ERROR: access_denied.');
        }

        $code = $input->getString('code', '');

        $returnRequest = new SloginRequest();

        if ($code) {

            if (!function_exists('curl_init')) {
                die('ERROR: CURL library not found!');
            }

            // get access_token for google API
            $redirect = Uri::base().'?option=com_slogin&task=check&plugin=github';

            $params = array(
                'client_id' => $this->params->get('id'),
                'redirect_uri' => $redirect,
                'client_secret' => $this->params->get('password'),
                'code' => $code
            );

            $curl = curl_init( "https://github.com/login/oauth/access_token" );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $auth = curl_exec( $curl );
            curl_close($curl);

            parse_str($auth, $secret);
            $secret = (object)$secret;

            if(empty($secret)){
                echo 'Error - empty access token';
                exit;
            }
            if(!empty($secret->error)){
                echo 'Error - '. $secret->error_description;
                exit;
            }

            $curl = curl_init("https://api.github.com/user");
            curl_setopt($curl,  CURLOPT_HTTPHEADER, array(
                'Authorization: token ' . $secret->access_token,
                'User-Agent: Devopedia'
            ));
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $auth = curl_exec( $curl );
            curl_close($curl);

            $request = json_decode($auth);

            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            if(!empty($request->error)){
                echo 'Error - '. $request->error_description;
                exit;
            }

            $returnRequest->id              = $request->id;
            $returnRequest->display_name    = $request->login;
            $returnRequest->first_name      = $request->name;
            $returnRequest->last_name       = '';
            $returnRequest->real_name       =  $request->name;
            $returnRequest->email           = $request->email;
            $returnRequest->all_request     = $request;
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
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=github' . $add;
        $links[$i]['class'] = 'githubslogin';
        $links[$i]['plugin_name'] = 'github';
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_GITHUB');
    }
}

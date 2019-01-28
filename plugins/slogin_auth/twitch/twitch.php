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

class plgSlogin_authTwitch extends JPlugin
{
    public function onSloginAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=twitch';

        $scope = 'user_read';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'response_type=code',
            'scope=' . $scope
        );

        $params = implode('&', $params);

        $url = 'https://id.twitch.tv/oauth2/authorize?'.$params;

        return $url;
    }

    public function onSloginCheck()
    {
        $input = JFactory::getApplication()->input;

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

            $redirect = JURI::base().'?option=com_slogin&task=check&plugin=twitch';

            $params = array(
                'client_id' => $this->params->get('id'),
                'client_secret' => $this->params->get('password'),
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirect
            );

            $curl = curl_init( "https://id.twitch.tv/oauth2/token?" );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $auth = curl_exec( $curl );
            curl_close($curl);

            $secret = json_decode($auth);

            if(empty($secret)){
                echo 'Error - empty access tocken';
                exit;
            }
            if(!empty($secret->error)){
                echo 'Error - '. $secret->error_description;
                exit;
            }

            $access_key = $secret->access_token;


            $curl = curl_init( "https://api.twitch.tv/kraken/user" );
            $header = ['Client-ID: u84mjtwyz3rid5rni5iqayofht4nxt', 'Authorization: OAuth ' . $access_key, 'Accept: application/vnd.twitchtv.v5+json'];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec( $curl );
            $request = curl_exec($curl);
            curl_close($curl);
            $request = json_decode($request);

            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            if(!empty($request->error)){
                echo 'Error - '. $request->error_description;
                exit;
            }

            $returnRequest->id              = $request->_id;
            $returnRequest->display_name    = $request->display_name;
            $returnRequest->first_name      = $request->name;
            $returnRequest->email           = $request->email;
            $returnRequest->all_request     = $request;
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
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=twitch' . $add;
        $links[$i]['class'] = 'twitchslogin';
        $links[$i]['plugin_name'] = 'twitch';
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_TWITCH');
    }
}

<?php
/**
 * SLogin
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgSlogin_authBitBucket extends JPlugin
{
    public function onSloginAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=bitbucket';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'response_type=code'
        );
        $params = implode('&', $params);

        $url = 'https://bitbucket.org/site/oauth2/authorize?'.$params;

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

            // get access_token for google API
            $redirect = JURI::base().'?option=com_slogin&task=check&plugin=bitbucket';

            $params = array(
                'client_id' => $this->params->get('id'),
                'redirect_uri' => $redirect,
                'client_secret' => $this->params->get('password'),
                'code' => $code,
                'grant_type' => 'authorization_code'
            );

            $curl = curl_init( "https://bitbucket.org/site/oauth2/access_token" );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $auth = curl_exec( $curl );
            curl_close($curl);

            $secret = json_decode($auth);

            if(empty($secret)){
                echo 'Error - empty access token';
                exit;
            }
            if(!empty($secret->error)){
                echo 'Error - '. $secret->error_description;
                exit;
            }

            $curl = curl_init("https://api.bitbucket.org/2.0/user");
            curl_setopt($curl,  CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $secret->access_token,
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

            $returnRequest->id              = preg_replace("/\W/", "", $request->uuid);
            $returnRequest->display_name    = $request->username;
            $returnRequest->first_name      = $request->display_name;
            $returnRequest->last_name       = '';
            $returnRequest->real_name       =  $request->display_name;
            $returnRequest->email           = '';
            $returnRequest->all_request     = $request;
            return $returnRequest;
        }
        else{
            $config = JComponentHelper::getParams('com_slogin');
            JModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = JModel::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = JControllerLegacy::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }
    }

    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=bitbucket' . $add;
        $links[$i]['class'] = 'bitbucketslogin';
        $links[$i]['plugin_name'] = 'bitbucket';
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_BITBUCKET');
    }
}

<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgSlogin_authWordpress extends JPlugin
{
    public function onSloginAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=wordpress';

        $params = array(
            'client_id=' . $this->params->get('id'),
            'redirect_uri=' . urlencode($redirect),
            'response_type=code'
        );
        $params = implode('&', $params);

        $url = 'https://public-api.wordpress.com/oauth2/authorize?'.$params;

        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $error = $input->getString('error', '');
        if($error == 'access_denied'){
            $config = JComponentHelper::getParams('com_slogin');

            JModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = JModel::getInstance('Linking_user', 'SloginModel');

            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));

            $controller = JControllerLegacy::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            if (!function_exists('curl_init')) {
                die('ERROR: CURL library not found!');
            }

            // get access_token for google API
            $redirect = urlencode(JURI::base().'?option=com_slogin&task=check&plugin=wordpress');

            $params = array(
                'client_id' => $this->params->get('id'),
                'redirect_uri' => $redirect,
                'client_secret' => $this->params->get('password'),
                'code' => $code, // The code from the previous request
                'grant_type' => 'authorization_code'
            );

            $url = 'https://public-api.wordpress.com/oauth2/token';

            $curl = curl_init( "https://public-api.wordpress.com/oauth2/token" );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
            $auth = curl_exec( $curl );
            $secret = json_decode($auth);
            $access_key = $secret->access_token;

            if(empty($secret)){
                echo 'Error - empty access tocken';
                exit;
            }
            if(!empty($secret->error)){
                echo 'Error - '. $secret->error_description;
                exit;
            }

            $curl = curl_init( "https://public-api.wordpress.com/rest/v1/me/?pretty=1" );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_key ) );
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
            /*
            Response Body
            {
                +"ID": 1001000100,
                "display_name": "Mr. Test",
                "username": "test",
                "email": "test@example.com",
                "primary_blog": 415,
                "avatar_URL": "http:\/\/0.gravatar.com\/avatar\/a178ebb1731d432338e6bb0158720fcc?s=96&d=identicon&r=G",
                "profile_URL": "http:\/\/en.gravatar.com\/test",
                "verified" : true,
                "meta": {
                    "links": {
                        "self": "https:\/\/public-api.wordpress.com\/rest\/v1\/me",
                        "help": "https:\/\/public-api.wordpress.com\/rest\/v1\/me\/help",
                        "site": "https:\/\/public-api.wordpress.com\/rest\/v1\/sites\/30434183"
                    }
                }
            }
            */

            $returnRequest->id              = $request->ID;
            $returnRequest->display_name    = $request->username;
            $returnRequest->first_name      = $request->display_name;
            $returnRequest->email           = $request->email;
            $returnRequest->all_request     = $request;
            return $returnRequest;
        }
        else{
            echo 'Error - empty code';
            exit;
        }
    }

    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=wordpress' . $add;
        $links[$i]['class'] = 'wordpressslogin';
        $links[$i]['plugin_name'] = 'wordpress';
    }
}

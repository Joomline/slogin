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
            $redirect = JURI::base().'?option=com_slogin&task=check&plugin=wordpress';

            $params = array(
                'client_id' => $this->params->get('id'),
                'redirect_uri' => $redirect,
                'client_secret' => $this->params->get('password'),
                'code' => $code, // The code from the previous request
                'grant_type' => 'authorization_code'
            );

            $curl = curl_init( "https://public-api.wordpress.com/oauth2/token" );
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


            $curl = curl_init( "https://public-api.wordpress.com/rest/v1/me/?raw=1" );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_key ) );
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
            /*
        object(stdClass)#387 (10) {
            ["ID"]=> int(45759137)
            ["display_name"]=> string(11) "asd"
            ["username"]=> string(11) "asd"
            ["email"]=> string(22) "asd@gmail.com"
            ["primary_blog"]=> int(123)
            ["token_site_id"]=> int(123)
            ["avatar_URL"]=> string(83) "https://0.gravatar.com/avatar/9f3ceeb379f3db5f82bcdc4c5a0a51c7?s=96&d=identicon&r=G"
            ["profile_URL"]=> string(34) "http://en.gravatar.com/asedelnikov" ["verified"]=> bool(true)
            ["meta"]=> object(stdClass)#386 (1) {
                ["links"]=> object(stdClass)#385 (3) {
                    ["self"]=> string(43) "https://public-api.wordpress.com/rest/v1/me"
                    ["help"]=> string(48) "https://public-api.wordpress.com/rest/v1/me/help"
                    ["site"]=> string(55) "https://public-api.wordpress.com/rest/v1/sites/46631777"
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
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=wordpress' . $add;
        $links[$i]['class'] = 'wordpressslogin';
        $links[$i]['plugin_name'] = 'wordpress';
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_WP');
    }
}

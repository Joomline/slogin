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

include_once JPATH_ROOT.DS."plugins/slogin_auth/linkedin/assets/oauth/linkedin.php";


class plgSlogin_authLinkedin extends JPlugin
{
    public function onAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=linkedin';

        $app = JFactory::getApplication('site');

        # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
        $linkedin = new LinkedIn($this->params->get('api_key'), $this->params->get('secret_key'), $redirect);
        //$linkedin->debug = true;

        # Now we retrieve a request token. It will be set as $linkedin->request_token
        $linkedin->getRequestToken();

        $app->setUserState('requestToken', serialize($linkedin->request_token));

        $url = $linkedin->generateAuthorizeUrl();

        return $url;
    }

    public function onCheck()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=linkedin';

        $app = JFactory::getApplication();
        $input = $app->input;

        # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
        $linkedin = new LinkedIn($this->params->get('api_key'), $this->params->get('secret_key'), $redirect);
        //$linkedin->debug = true;

        $oauth_verifier  = $input->getString('oauth_verifier', '');

        $requestToken = unserialize($app->getUserState('requestToken'));
        $linkedin->request_token    =   $requestToken;
        $linkedin->oauth_verifier   =   $app->getUserState('oauth_verifier');

        if (!empty($oauth_verifier)){
            $app->setUserState('oauth_verifier', $oauth_verifier);
            $linkedin->getAccessToken($oauth_verifier);
            $app->setUserState('oauth_access_token', serialize($linkedin->access_token));
            header("Location: " . $redirect);
            exit;
        }
        else{
            $linkedin->access_token = unserialize($app->getUserState('oauth_access_token'));
        }


        # You now have a $linkedin->access_token and can make calls on behalf of the current member
        //$request = $linkedin->getProfile("~:(id,first-name,last-name,headline,picture-url)");
        $request = $linkedin->getProfile("~:(id,first-name,last-name,headline,picture-url,email-address)?format=json");
        $request = json_decode($request);

        if(!empty($request->errorCode)){
            echo 'Error - '.$request->message;
            exit;
        }

        $returnRequest = new SloginRequest();

            $returnRequest->first_name  = $request->firstName;
            $returnRequest->last_name   = $request->lastName;
//            $returnRequest->email       = $request->email;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $request->firstName.' '.$request->lastName;
            $returnRequest->display_name = $request->firstName;
            $returnRequest->all_request  = $request;

        return $returnRequest;
    }
    public function onCreateLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=linkedin' . $add;
        $links[$i]['class'] = 'linkedinslogin';
        $links[$i]['plugin_name'] = 'linkedin';
    }
}

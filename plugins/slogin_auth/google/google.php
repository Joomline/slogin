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

class plgSlogin_authGoogle extends JPlugin
{
    public function onSloginAuth()
    {
        $redirect = JURI::base().'?option=com_slogin&task=check&plugin=google';

        $scope = urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email');

        $params = array(
            'response_type=code',
            'redirect_uri=' . urlencode($redirect),
            'client_id=' . $this->params->get('id'),
            'scope=' . $scope
            //,'access_type=offline'
            //,'approval_prompt=force'
        );
        $params = implode('&', $params);

        $url = 'https://accounts.google.com/o/oauth2/auth?'.$params;

        return $url;
    }

    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {

            // get access_token for google API
            $redirect = urlencode(JURI::base().'?option=com_slogin&task=check&plugin=google');
            $scope = urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email');

            $params = array(
                'client_id=' . $this->params->get('id'),
                'client_secret=' . $this->params->get('password'),
                'grant_type=authorization_code',
                'code=' . $code,
                'redirect_uri=' . $redirect,
                'scope=' . $scope

            );
            $params = implode('&', $params);
            $url = 'https://accounts.google.com/o/oauth2/token';
            $request = json_decode($controller->open_http($url, true, $params));

            if(empty($request)){
                echo 'Error - empty access tocken';
                exit;
            }

            //Get user info
            //
            // id 				The value of this field is an immutable identifier for the logged-in user
            // email 			The email address of the logged in user
            // verified_email 	A flag that indicates whether or not Google has been able to verify the email address.
            // name 			The full name of the logged in user
            // given_name 		The first name of the logged in user
            // family_name 		The last name of the logged in user
            // picture 			The URL to the user's profile picture. If the user has no public profile, this field is not included.
            // locale 			The user's registered locale. If the user has no public profile, this field is not included.
            // timezone 		the default timezone of the logged in user
            // gender 			the gender of the logged in user (other|female|male)

            $url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$request->access_token;
            $request = json_decode($controller->open_http($url));

            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            else if(!empty($request->error))
			{
                echo '<pre>';
				var_dump($request->error);
				echo '</pre>';
                exit;
            }

            $returnRequest->first_name  = $request->given_name;
            $returnRequest->last_name   = $request->family_name;
            $returnRequest->email       = $request->email;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $request->given_name.' '.$request->family_name;
            $returnRequest->sex         = $request->gender;
            $returnRequest->display_name = $request->name;
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
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=google' . $add;
        $links[$i]['class'] = 'googleslogin';
        $links[$i]['plugin_name'] = 'google';
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_GOOGLE');
    }
}

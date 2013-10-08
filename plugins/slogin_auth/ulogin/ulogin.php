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

class plgSlogin_authUlogin extends JPlugin
{
	public function onSloginAuth()
	{
        return '';
	}

	public function onSloginCheck()
	{
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = JFactory::getApplication()->input;

        $request = null;

        $token = $input->get('token', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($token) {
            // get access_token from API
            $params = array(
                'token=' . $token,
                'host=' . JURI::base(true)
            );
            $params = implode('&', $params);
            $url = 'http://ulogin.ru/token.php?' . $params;
            $request = $controller->open_http($url);
            $request = json_decode($request);

            if(!isset($request->uid)){
                echo 'Error - empty user data';
                exit;
            }

            $returnRequest->first_name = (isset($request->first_name)) ? $request->first_name : '';
            $returnRequest->last_name = (isset($request->last_name)) ? $request->last_name : '';
            $returnRequest->email = $request->email;
            $returnRequest->id = 'ulogin_' . $request->network . '_' . $request->uid;
            $returnRequest->real_name = $request->nickname;
            $returnRequest->sex = $request->sex;
            $returnRequest->display_name = $request->nickname;
            $returnRequest->birthday = $request->bdate;
            $returnRequest->all_request  = $request;

            $app = JFactory::getApplication();
            $app->setUserState('com_slogin.popup', 'none');

            return $returnRequest;
        }
        else{
            echo 'Error - empty tocken';
            exit;
        }
	}

    public function onCreateSloginLink(&$links, $add = '')
    {
        $doc = JFactory::getDocument();
        $doc->addScript('//ulogin.ru/js/ulogin.js');

        $redirect = urlencode(JURI::base().'?option=com_slogin&task=check&plugin=ulogin');

        $i = count($links);
        $links[$i]['link'] = '#';
        $links[$i]['class'] = 'uloginslogin';
        $links[$i]['plugin_name'] = 'ulogin';
        $links[$i]['params'] = array(
           'id'=>'uLogin',
           'data-ulogin'=>'display=window;fields=first_name,last_name;redirect_uri=' . $redirect
        );
    }
}

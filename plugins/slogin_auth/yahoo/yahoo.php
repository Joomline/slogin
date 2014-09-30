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

require_once JPATH_BASE.'/plugins/slogin_auth/yahoo/assets/yahoo-yos-social/lib/Yahoo/YahooOAuthApplication.class.php';

class plgSlogin_authYahoo extends JPlugin
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
		$this->app_id = $this->params->get('app_id');
		$this->callback = JURI::base().'?option=com_slogin&task=check&plugin=yahoo';
	}

    public function onSloginAuth()
    {
        $oauthapp      = new YahooOAuthApplication($this->key, $this->secret, $this->app_id, $this->callback);
        # Fetch request token
        $request_token = $oauthapp->getRequestToken($this->callback);

        $session = JFactory::getSession();
        $session->set('request_token', serialize($request_token));

        # Redirect user to authorization url
        $redirect_url  = $oauthapp->getAuthorizationUrl($request_token);

        return $redirect_url;
    }

    public function onSloginCheck()
    {
        //получение значений из сессии
        $session = JFactory::getSession();
        $request_token = unserialize($session->get('request_token'));

        $oauthapp = new YahooOAuthApplication($this->key, $this->secret, $this->app_id, $this->callback);

        $input = JFactory::getApplication()->input;

        $oauth_verifier = $input->getString('oauth_verifier', '');

        # Exchange request token for authorized access token
        $access_token  = $oauthapp->getAccessToken($request_token, $oauth_verifier);

        # update access token
        $oauthapp->token = $access_token;

        # fetch user profile
        $profile = $oauthapp->getProfile();

        if (empty($profile)) {
            die('Error: profile empty');
        }

        $returnRequest = new SloginRequest();
        $returnRequest->first_name  = $profile->profile->givenName;
        $returnRequest->last_name   = $profile->profile->familyName;
//        $returnRequest->email       = $profile->profile->emails->handle;
        $returnRequest->id          = $profile->profile->guid;
        $returnRequest->real_name   = $profile->profile->givenName.' '.$profile->profile->familyName;
//        $returnRequest->sex         = $profile->profile->gender;
        $returnRequest->display_name = $profile->profile->nickname;
        $returnRequest->all_request  = $profile->profile;

        return $returnRequest;

    }

    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=yahoo' . $add;
        $links[$i]['class'] = 'yahooslogin';
        $links[$i]['plugin_name'] = 'yahoo';
    }
}

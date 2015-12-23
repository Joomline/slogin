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
		$this->app_id = '';
		$this->callback = JURI::base().'?option=com_slogin&task=check&plugin=yahoo';
	}
    public function onSloginAuth()
    {
        if($this->params->get('allow_remote_check', 1))
        {
            $remotelUrl = JURI::getInstance($_SERVER['HTTP_REFERER'])->toString(array('host'));
            $localUrl = JURI::getInstance()->toString(array('host'));
            if($remotelUrl != $localUrl){
                die('Remote authorization not allowed');
            }
        }

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
		
		//var_dump($profile);
		/*
		object(stdClass)#594 (1) { 
		["profile"]=> object(stdClass)#593 (10) { 
			["guid"]=> string(26) "YHDVXZ4H4LBJKT6NGXU5JQ537E" 
			["ageCategory"]=> string(1) "A" 
			["created"]=> string(20) "2014-11-24T05:48:59Z" 
			["image"]=> object(stdClass)#592 (4) { ["height"]=> string(3) "192" 
			["imageUrl"]=> string(56) "https://s.yimg.com/dh/ap/social/profile/profile_b192.png" 
			["size"]=> string(7) "192x192" ["width"]=> string(3) "192" } 
			["lang"]=> string(5) "en-US" 
			["memberSince"]=> string(20) "2014-09-30T12:10:41Z" 
			["nickname"]=> string(7) "Arkadiy" 
			["profileUrl"]=> string(51) "http://profile.yahoo.com/YHDVXZ4H4LBJKT6NGXU5JQ537E" 
			["isConnected"]=> string(5) "false" 
			["bdRestricted"]=> string(4) "true" } } 
		*/
        $returnRequest = new SloginRequest();
        $returnRequest->first_name  = isset($profile->profile->givenName) ? $profile->profile->givenName : $profile->profile->nickname;
        $returnRequest->last_name   = isset($profile->profile->familyName) ? $profile->profile->familyName : '';
        $returnRequest->email       = isset($profile->profile->emails->handle) ? $profile->profile->emails->handle : '';
        $returnRequest->id          = isset($profile->profile->guid) ? $profile->profile->guid : '';
        $returnRequest->real_name   = isset($profile->profile->givenName) ? $profile->profile->givenName : $profile->profile->nickname;
        $returnRequest->sex         = isset($profile->profile->gender) ? $profile->profile->gender : '';
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
        $links[$i]['plugin_title'] = JText::_('COM_SLOGIN_PROVIDER_YAHOO');
    }
}
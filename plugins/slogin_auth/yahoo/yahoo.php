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

        //echo '<pre>'; var_dump($profile);
        /*
object(stdClass)#328 (1) {
  ["profile"]=>
  object(stdClass)#327 (18) {
    ["guid"]=>
    string(26) "H6KKGGLX5Z4QQFUO6JQGESGWJU"
    ["aboutMe"]=>
    string(0) ""
    ["disclosures"]=>
    array(2) {
      [0]=>
      object(stdClass)#326 (4) {
        ["acceptance"]=>
        string(1) "1"
        ["name"]=>
        string(2) "bd"
        ["seen"]=>
        string(20) "2014-03-31T12:46:55Z"
        ["version"]=>
        string(1) "1"
      }
      [1]=>
      object(stdClass)#325 (4) {
        ["acceptance"]=>
        string(1) "1"
        ["name"]=>
        string(5) "ccard"
        ["seen"]=>
        string(20) "2014-03-31T12:46:55Z"
        ["version"]=>
        string(1) "1"
      }
    }
    ["emails"]=>
    object(stdClass)#324 (3) {
      ["handle"]=>
      string(28) "arkadiy_sedelnikov@yahoo.com"
      ["id"]=>
      string(1) "1"
      ["type"]=>
      string(4) "HOME"
    }
    ["familyName"]=>
    string(22) "Седельников"
    ["gender"]=>
    string(1) "M"
    ["givenName"]=>
    string(14) "Аркадий"
    ["image"]=>
    object(stdClass)#323 (4) {
      ["height"]=>
      string(3) "192"
      ["imageUrl"]=>
      string(80) "https://socialprofiles.zenfs.com/images/ccd1f5680c57f107485ab31ee8eabb08_192.png"
      ["size"]=>
      string(7) "192x192"
      ["width"]=>
      string(3) "192"
    }
    ["interests"]=>
    array(11) {
      [0]=>
      object(stdClass)#322 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(13) "prfFavHobbies"
      }
      [1]=>
      object(stdClass)#321 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(11) "prfFavMusic"
      }
      [2]=>
      object(stdClass)#320 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(12) "prfFavMovies"
      }
      [3]=>
      object(stdClass)#319 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(18) "prfFavFutureMovies"
      }
      [4]=>
      object(stdClass)#318 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(11) "prfFavBooks"
      }
      [5]=>
      object(stdClass)#317 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(17) "prfFavFutureBooks"
      }
      [6]=>
      object(stdClass)#316 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(12) "prfFavQuotes"
      }
      [7]=>
      object(stdClass)#315 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(11) "prfFavFoods"
      }
      [8]=>
      object(stdClass)#314 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(12) "prfFavPlaces"
      }
      [9]=>
      object(stdClass)#313 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(18) "prfFavFuturePlaces"
      }
      [10]=>
      object(stdClass)#312 (2) {
        ["declaredInterests"]=>
        string(0) ""
        ["interestCategory"]=>
        string(11) "prfFavAelse"
      }
    }
    ["lang"]=>
    string(5) "en-US"
    ["location"]=>
    string(0) ""
    ["memberSince"]=>
    string(20) "2014-03-31T08:19:34Z"
    ["nickname"]=>
    string(14) "Аркадий"
    ["phones"]=>
    object(stdClass)#311 (3) {
      ["id"]=>
      string(2) "10"
      ["number"]=>
      string(12) "7-9059450590"
      ["type"]=>
      string(6) "MOBILE"
    }
    ["profileUrl"]=>
    string(51) "http://profile.yahoo.com/H6KKGGLX5Z4QQFUO6JQGESGWJU"
    ["timeZone"]=>
    string(19) "America/Los_Angeles"
    ["isConnected"]=>
    string(4) "true"
    ["bdRestricted"]=>
    string(4) "true"
  }
}
        */


        if (empty($profile)) {
            die('Error: profile empty');
        }

        $returnRequest = new SloginRequest();
        $returnRequest->first_name  = $profile->profile->givenName;
        $returnRequest->last_name   = $profile->profile->familyName;
        $returnRequest->email       = $profile->profile->emails->handle;
        $returnRequest->id          = $profile->profile->guid;
        $returnRequest->real_name   = $profile->profile->givenName.' '.$profile->profile->familyName;
        $returnRequest->sex         = $profile->profile->gender;
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

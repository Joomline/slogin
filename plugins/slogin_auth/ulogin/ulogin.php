<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

class plgSlogin_authUlogin extends CMSPlugin
{
	public function onSloginAuth()
	{
        return '';
	}

	public function onSloginCheck()
	{
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $input = Factory::getApplication()->input;

        $app	= Factory::getApplication();

        $request = null;

        $app->setUserState('com_slogin.action.data', $input->getString('action', ''));

        $token = $input->get('token', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($token) {
            // get access_token from API
            $params = array(
                'token=' . $token,
                'host=' . Uri::base(true)
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
            $returnRequest->email = isset($request->email) ? $request->email: '';
            $returnRequest->id = 'ulogin_' . $request->network . '_' . $request->uid;
            $returnRequest->real_name = isset($request->nickname) ? $request->nickname: $request->first_name;
            $returnRequest->sex = isset($request->sex) ? $request->sex: 0;
            $returnRequest->display_name = isset($request->nickname) ? $request->nickname: $request->first_name;
            $returnRequest->birthday = isset($request->bdate) ? $request->bdate: '';
            $returnRequest->network = isset($request->network) ? $request->network: '';
            $returnRequest->all_request  = $request;

            $app = Factory::getApplication();
            $app->setUserState('com_slogin.popup', 'none');

            return $returnRequest;
        }
        else{
            $config = ComponentHelper::getParams('com_slogin');
            BaseDatabaseModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = BaseDatabaseModel::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = BaseController::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }
	}

    public function onCreateSloginLink(&$links, $add = '')
    {
        $doc = Factory::getDocument();
        $doc->addScript('//ulogin.ru/js/ulogin.js');

        $redirect = urlencode(Uri::base().'?option=com_slogin&task=check&plugin=ulogin'.$add);

        $i = count($links);
        $links[$i]['link'] = '#';
        $links[$i]['class'] = 'uloginslogin';
        $links[$i]['plugin_name'] = 'ulogin';
        $links[$i]['params'] = array(
           'id'=>'uLogin',
           'data-ulogin'=>'display=window;fields=first_name,last_name,email,photo,sex;redirect_uri=' . $redirect
        );
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_ULOGIN');
    }
}

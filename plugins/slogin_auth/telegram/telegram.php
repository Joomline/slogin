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

class plgSlogin_authTelegram extends CMSPlugin
{
    private $provider = 'telegram';


    public function onCreateSloginLink(&$links, $add = '')
    {
        $action = '';
        $doc = Factory::getDocument();
        $app	= Factory::getApplication('site');

        if(!empty($add)){
            $parts = explode('&', $add);
            $part = end($parts);
            $parts = explode('=', $part);
            if($parts[0] == 'action' && count($parts) == 2){
                $action = end($parts);

                $app->setUserState('com_slogin.action.data', $action);
            }
        }
        if(!defined('TWIDGETLOGINLOADED')){
            define('TWIDGETLOGINLOADED', 1);
            $url = Uri::root().'?option=com_slogin&task=check&plugin=telegram';
            $js = '
                TWidgetLogin.init("twidget_login", '.$this->params->get('id').', {"origin":"'.Uri::base().'","embed":1}, false, "ru");
                SloginTelegram.url = "'.$url.'";
            ';
            $doc->addScriptDeclaration($js);
        }
        $doc->addScript('plugins/slogin_auth/telegram/assets/widget-frame.js', array('version' => 'auto'));
        $doc->addScript('plugins/slogin_auth/telegram/assets/script.js', array('version' => 'auto'));

        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=' . $this->provider . $add;
        $links[$i]['class'] = 'telegramslogin';
        $links[$i]['plugin_name'] = $this->provider;
        $links[$i]['params'] = array(
            'id'=>'twidget_login',
            'onclick'=>'TWidgetLogin.auth(); return false;',
        );
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_TELEGRAM');
    }

    public function onSloginAuth(){}

    public function onSloginCheck()
    {
        $input = Factory::getApplication()->input;
        $data = $input->getString('data', '');
        $request = json_decode($data, true);
        $error = 0;

        if (empty($request['event']) || $request['event'] != 'auth_user' || empty($request['auth_data']))
        {
            $error = 1;
        }

        if(!$this->checkTelegramAuthorization($request['auth_data'])){
            $error = 1;
        }

        if (!$error)
        {
            $app = Factory::getApplication();
            $app->setUserState('com_slogin.popup', 'none');
            $returnRequest = new SloginRequest();
            $returnRequest->first_name      = $request['auth_data']['first_name'];
            $returnRequest->last_name       = $request['auth_data']['last_name'];
            $returnRequest->avatar          = $request['auth_data']['photo_url'];
            $returnRequest->id              = $request['auth_data']['id'];
            $returnRequest->real_name       = $request['auth_data']['first_name'].' '.$request['auth_data']['last_name'];
            $returnRequest->display_name    = $request['auth_data']['username'];
            $returnRequest->all_request     = $request['auth_data'];
            return $returnRequest;
        }
        else{
            $config = ComponentHelper::getParams('com_slogin');
            BaseDatabaseModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = BaseDatabaseModel::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = BaseController::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
            return false;
        }
    }

    function checkTelegramAuthorization($auth_data) {
        $app = Factory::getApplication('site');
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $this->params->get('token'), true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        if (strcmp($hash, $check_hash) !== 0) {
            $app->enqueueMessage('Контрольная сумма не прошла проверку');
            return false;
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
            $app->enqueueMessage('Время авторизации прошло');
            return false;
        }
        return true;
    }
}

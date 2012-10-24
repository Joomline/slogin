<?php
/**
 * SLogin
 *
 * @version 	1.0
 * @author		SmokerMan
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

require_once JPATH_COMPONENT_SITE.'/controller.php';

class SLoginControllerPlugins extends SLoginController
{
    protected $conf;

    public function __construct()
    {
        $cofig = array();
        parent::__construct($cofig);
    }


    /**
     * Аутентификация пользователя
     */
    public function auth()
    {
        $app	= JFactory::getApplication();

        $input = $app->input;

        $plugin = $input->getString('plugin', '');

        $app->setUserState('com_slogin.action.data', $input->getString('action', ''));

        $app->setUserState('com_slogin.return_url', $input->getString('return', ''));

        $redirect = JURI::base().'?option=com_slogin&task=plugins.check&plugin=yandex';

        $this->localAuthDebug($redirect);

        if(JPluginHelper::isEnabled('slogin_auth', $plugin))
        {
            $dispatcher	= JDispatcher::getInstance();

            JPluginHelper::importPlugin('slogin_auth', $plugin);

            $url = '';

            $dispatcher->trigger('onAuth', array(&$url));
        }
        else{
            echo 'Plufin ' . $plugin . ' not published or not installed.';
            exit;
        }

        header('Location:' . $url);
    }

    /**
     * Проверка аутентификации на сайте донора
     * Создание новой учетной записи на сайте или утентификация, если такой пользователь уже есть
     */
    public function check()
    {
        $input = JFactory::getApplication()->input;

        $plugin = $input->getString('plugin', '');

        $provider = $plugin;

        $this->localCheckDebug($provider);

        if(JPluginHelper::isEnabled('slogin_auth', $plugin))
        {
            $dispatcher	= JDispatcher::getInstance();

            JPluginHelper::importPlugin('slogin_auth', $plugin);

            $request = '';

            $dispatcher->trigger('onCheck', array(&$request));
        }
        else{
            echo 'Plufin ' . $plugin . ' not published or not installed.';
            exit;
        }


        if (isset($request->first_name))
        {
            $this->storeOrLogin($request->first_name, $request->last_name, $request->email, $request->id, $plugin, true, $request);
        }
    }

}
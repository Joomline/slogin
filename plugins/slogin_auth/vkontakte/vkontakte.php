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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class plgSlogin_authVkontakte extends CMSPlugin
{
    private $provider = 'vkontakte';

    /**
     * Constructor
     *
     * @param  object  &$subject  The object to observe
     * @param  array   $config    An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Generate the authentication URL for VKontakte
     *
     * @return string The authentication URL
     */
    public function onSloginAuth()
    {
        // Check if we should use menu item for redirect
        $useMenuItem = (int)$this->params->get('use_menu_item', 0);
        $menuItemId = (int)$this->params->get('menu_item_id', 0);

        if ($useMenuItem && $menuItemId) {
            // Generate redirect URL from menu item
            $redirect = $this->getMenuItemUrl($menuItemId);
        } else {
            // Use default redirect URL
            $redirect = Uri::base().'index.php?option=com_slogin&task=check&plugin=vkontakte';
        }

        $scope = 'offline';
        $scope .= ',email';

        if($this->params->get('repost_comments', 0))
        {
            $scope .= ',wall';
            //$scope .= ',groups';
        }

        $params = array(
            'client_id=' . $this->params->get('id'),
            'response_type=code',
            'redirect_uri=' . urlencode($redirect),
            'scope=' . $scope
        );
        $params = implode('&', $params);

        $url = 'https://oauth.vk.com/authorize?' . $params;

        return $url;
    }

    /**
     * Handle the authentication callback from VKontakte
     *
     * @return object|void The user data or void if error
     */
    public function onSloginCheck()
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';

        $controller = new SLoginController();

        $app = Factory::getApplication();
        $input = $app->input;

        $code = $input->get('code', null, 'STRING');

        $returnRequest = new SloginRequest();

        if ($code) {
            $data = $this->getToken($code);

            if (empty($data->access_token) || isset($data->error)) {
                echo '<pre>';
                var_dump($data);
                echo '</pre>';
                die();
            }

            $returnRequest->email = (!empty($data->email)) ? $data->email : '';
            
// 			Получение данных о пользователе поле fields
// 			Нужное можно указать!
// 			uid, first_name, last_name, nickname, screen_name, sex, bdate (birthdate), city, country,
// 			timezone, photo, photo_medium, photo_big, has_mobile, rate, contacts, education, online, counters.
// 			По умолчанию возвращает uid, first_name и last_name

// 			name_case - дополнительный параметр
// 			падеж для склонения имени и фамилии пользователя.
// 			Возможные значения:
// 			именительный – nom,
// 			родительный – gen,
// 			дательный – dat,
// 			винительный – acc,
// 			творительный – ins,
// 			предложный – abl.
// 			По умолчанию nom.

            $ResponseUrl = 'https://api.vk.com/method/getProfiles?uid='.$data->user_id.'&access_token='.$data->access_token.'&fields=nickname,contacts,photo_big,bdate&v=5.131';
            $request = json_decode($controller->open_http($ResponseUrl));


            if(empty($request)){
                echo 'Error - empty user data';
                exit;
            }
            else if(!empty($request->error)){
                if(!empty($request->error->error_msg)){
                    echo 'Error - '.$request->error->error_msg;
                    exit;
                }
                echo 'Error - request error.';
                exit;
            }
            
            $request = $request->response[0];
            
            //сохраняем данные токена в сессию
            //expire - время устаревания скрипта, метка времени Unix
            Factory::getApplication()->setUserState('slogin.token', array(
                'provider' => $this->provider,
                'token' => $data->access_token,
                'expire' => $data->expires_in,
                'repost_comments' => $this->params->get('repost_comments', 0),
                'slogin_user' => $data->user_id,
                'app_id' => $this->params->get('id', 0),
                'app_secret' => $this->params->get('password', 0)
            ));

            $returnRequest->provider = $this->provider;
            $returnRequest->first_name  = $request->first_name;
            $returnRequest->last_name   = $request->last_name;
            $returnRequest->id          = $request->id;
            $returnRequest->real_name   = $request->first_name.' '.$request->last_name;
            $returnRequest->display_name = $request->nickname;
            $returnRequest->all_request  = $request;
            return $returnRequest;
        }
        else{
            $config = \Joomla\CMS\Component\ComponentHelper::getParams('com_slogin');
            \Joomla\CMS\MVC\Model\BaseDatabaseModel::addIncludePath(JPATH_ROOT.'/components/com_slogin/models');
            $model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Linking_user', 'SloginModel');
            $redirect = base64_decode($model->getReturnURL($config, 'failure_redirect'));
            $controller = \Joomla\CMS\MVC\Controller\BaseController::getInstance('SLogin');
            $controller->displayRedirect($redirect, true);
        }
    }

    /**
     * Get access token from VKontakte
     *
     * @param string $code The authorization code
     * @return object The token data
     */
    public function getToken($code)
    {
        require_once JPATH_BASE.'/components/com_slogin/controller.php';
        $controller = new SLoginController();

        // Check if we should use menu item for redirect
        $useMenuItem = (int)$this->params->get('use_menu_item', 0);
        $menuItemId = (int)$this->params->get('menu_item_id', 0);

        if ($useMenuItem && $menuItemId) {
            // Generate redirect URL from menu item
            $redirect = $this->getMenuItemUrl($menuItemId);
        } else {
            // Use default redirect URL
            $redirect = Uri::base().'index.php?option=com_slogin&task=check&plugin=vkontakte';
        }

        //подключение к API
        $params = array(
            'client_id=' . $this->params->get('id'),
            'client_secret=' . $this->params->get('password'),
            'code=' . $code,
            'redirect_uri=' . urlencode($redirect)
        );
        $params = implode('&', $params);

        $url = 'https://oauth.vk.com/access_token?' . $params;

        $data = json_decode($controller->open_http($url));

        return $data;
    }

    /**
     * Create social login link
     *
     * @param array $links Array of links
     * @param string $add Additional query string
     * @return void
     */
    public function onCreateSloginLink(&$links, $add = '')
    {
        $i = count($links);
        $links[$i]['link'] = 'index.php?option=com_slogin&task=auth&plugin=vkontakte' . $add;
        $links[$i]['class'] = 'vkontakteslogin';
        $links[$i]['plugin_name'] = $this->provider;
        $links[$i]['plugin_title'] = Text::_('COM_SLOGIN_PROVIDER_VK');
    }

    /**
     * AJAX handler for getting menu item URL
     *
     * @return void
     */
    public function onAjaxSlogin_auth_vkontakte()
    {
        $app = Factory::getApplication();
        $menuItemId = $app->input->getInt('menuItemId', 0);

        if ($menuItemId) {
            try {
                $url = $this->getMenuItemUrl($menuItemId);
                return $url;
            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
                return false;
            }
        }

        return false;
    }

    /**
     * Get the URL for a menu item
     *
     * @param int $menuItemId The menu item ID
     * @return string The URL
     */
    protected function getMenuItemUrl($menuItemId)
    {
        // Получаем базовый домен
        $baseUrl = rtrim(Uri::root(), '/');
        
        // Default URL (without menu item)
        $defaultUrl = $baseUrl . '/index.php?option=com_slogin&task=check&plugin=' . $this->provider;

        try {
            // Используем прямой запрос к базе данных для получения информации о пункте меню
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('a.alias, a.path')
                ->from('#__menu AS a')
                ->where('a.id = ' . (int)$menuItemId);
            
            $db->setQuery($query);
            $menuData = $db->loadObject();
            
            if (!$menuData) {
                throw new Exception(Text::_('PLG_SLOGIN_AUTH_VKONTAKTE_ERROR_MENU_ITEM_NOT_FOUND'));
            }
            
            if (!empty($menuData->alias)) {
                // Формируем SEF URL напрямую из базы данных
                $menuPath = $menuData->path;
                
                // Если есть путь меню, используем его
                if (!empty($menuPath)) {
                    return $baseUrl . '/' . $menuPath . '/' . $this->provider;
                } else {
                    // Иначе используем только алиас
                    return $baseUrl . '/' . $menuData->alias . '/' . $this->provider;
                }
            }
        } catch (Exception $e) {
            // В случае ошибки возвращаем URL по умолчанию
            return $defaultUrl;
        }

        return $defaultUrl;
    }
}
<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// import Joomla controller library
jimport('joomla.application.component.controller');

jimport('joomla.environment.http');

//костыль для поддержки 2 и  3 джумлы
if(!class_exists('SLoginControllerParent')){
    if(class_exists('JControllerLegacy')){
        class SLoginControllerParent extends JControllerLegacy{}
    }
    else{
        class SLoginControllerParent extends JController{}
    }
}

/**
 * SLogin Controller
 *
 * @package        Joomla.Site
 * @subpackage    com_slogin
 */
class SLoginController extends SLoginControllerParent
{
    protected $config;

    public function __construct()
    {

        $cofig = array();
        parent::__construct($cofig);

        $this->config = JComponentHelper::getParams('com_slogin');
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

        $redirect = JURI::base().'?option=com_slogin&task=check&plugin='.$plugin;

        $this->localAuthDebug($redirect);

        if(JPluginHelper::isEnabled('slogin_auth', $plugin))
        {
            $dispatcher	= JDispatcher::getInstance();

            JPluginHelper::importPlugin('slogin_auth', $plugin);

            $url = $dispatcher->trigger('onAuth');
            $url = $url[0];
        }
        else{
            echo 'Plugin ' . $plugin . ' not published or not installed.';
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

        $this->localCheckDebug($plugin);

        if(JPluginHelper::isEnabled('slogin_auth', $plugin))
        {
            $dispatcher	= JDispatcher::getInstance();

            JPluginHelper::importPlugin('slogin_auth', $plugin);

            $request = $dispatcher->trigger('onCheck');
            $request = $request[0];
        }
        else{
            echo 'Plugin ' . $plugin . ' not published or not installed.';
            exit;
        }


        if (isset($request->first_name))
        {
            $this->storeOrLogin($request->first_name, $request->last_name, $request->email, $request->id, $plugin, true, $request->all_request);
        }
    }

    /**
     * Метод для отправки запросов
     * @param string     $url    УРЛ
     * @param boolean     $method    false - GET, true - POST
     * @param string     $params    Параметры для POST запроса
     * @return string    Результат запроса
     */
    function open_http($url, $method = false, $params = null)
    {

        if (!function_exists('curl_init')) {
            die('ERROR: CURL library not found!');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method);
        if ($method == true && isset($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch,  CURLOPT_HTTPHEADER, array('Content-Length: '.strlen($params)));
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

// 		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Установка имени для пользователя
     * @param string $first_name    Имя
     * @param string $last_name        Фамилия
     * @return string    Имя пользователя, в зовизимости от параметров компонента
     */
    protected function setUserName($first_name, $last_name)
    {
        if ($this->config->get('user_name', 1)) {
            $name = $first_name . ' ' . $last_name;
        } else {
            $name = $first_name;
        }

        return $name;
    }

    private function CheckUniqueName($username){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $uname = $username;
        $i = 0;
        while($uname){
            $name = ($i == 0) ? $username : $username.'-'.$i;

            $query->clear();
            $query->select($db->quoteName('username'));
            $query->from($db->quoteName('#__users'));
            $query->where($db->quoteName('username') . ' = ' . $db->quote($name));
            $db->setQuery($query, 0, 1);
            $uname = $db->loadResult();

            $i++;
        }
        return $name;
    }

    private function deleteSloginUser($slogin_id, $provider){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete();
        $query->from($db->quoteName('#__slogin_users'));
        $query->where($db->quoteName('slogin_id') . ' = ' . $db->quote($slogin_id));
        $query->where($db->quoteName('provider') . ' = ' . $db->quote($provider));
        $db->setQuery($query);
        $db->query();
    }

    /**
     * Метод для добавления новой учетной записи
     * @param string $username        Username учетной записи (не больше 150)
     * @param string $name            Имя учетной записи
     * @param strind $email            Email
     * @param $uid                      Идентификатор пользователя у провайдера
     * @param $provider                 идентификатор провайдера
     * @throws Exception
     */
    protected function storeUser($username, $name, $email, $slogin_id, $provider, $popup=false, $info=array())
    {
        $app	= JFactory::getApplication();

        //отсылаем на подверждение владения мылом если разрешено и найдено
        $userId = $this->CheckEmail($email);
        //если в настройках установлено подтверждать права на почту и почта есть в базе пользователей
        if($userId && $this->config->get('collate_users', 0)){
            $data = array(
               'email' => $email,
               'id' => $userId,
               'provider' => $provider,
               'slogin_id' => $slogin_id,
            );
            $app->setUserState('com_slogin.comparison_user.data', $data);

            $this->displayRedirect('index.php?option=com_slogin&view=comparison_user', $popup);
        }
        elseif($userId){
            $name = explode(' ', $name);
            if(!isset($name[1])) $name[1] = '';

            $data = array(
                'email' => $email,
                'first_name' => $name[0],
                'last_name' => $name[1],
                'provider' => $provider,
                'slogin_id' => $slogin_id,
            );
            $app->setUserState('com_slogin.provider.data', $data);

            $this->displayRedirect('index.php?option=com_slogin&view=mail', $popup, JText::_('COM_SLOGIN_MAIL_NOT_FREE'), 'error');
        }

        //установка групп для нового пользователя
        $user_config = JComponentHelper::getParams('com_users');
        $defaultUserGroup = $user_config->get('new_usertype', 2);

        $user['username'] = $this->CheckUniqueName($username);
        $user['name'] = $name;
        $user['email'] = $email;
        $user['registerDate'] = JFactory::getDate()->toSQL();
        $user['usertype'] = 'deprecated';
        $user['groups'] = array($defaultUserGroup);

        $user_object = new JUser;

        if (!$user_object->bind($user)) {
            $this->setError($user_object->getError());
            return false;
            //throw new Exception($user_object->getError());
        }

        if (!$user_object->save()) {
            $this->setError($user_object->getError());
            return false;
            //throw new Exception($user_object->getError());
        }

        $this->storeSloginUser($user_object->id, $slogin_id, $provider);

        //вставка нового пользователя в таблицы других компонентов
        JPluginHelper::importPlugin('slogin_integration');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onAfterStoreUser',array($user_object, $provider, $info));

        return $user_object->id;
    }

    /**
     * Метод для авторизцаии пользователя
     * @param int $id    ID пользователя в Joomla
     */
    protected function loginUser($id, $provider, $info=array())
    {
        $instance = JUser::getInstance($id);
        $app = JFactory::getApplication();
        $session = JFactory::getSession();
        $db = JFactory::getDBO();

        JPluginHelper::importPlugin('slogin_integration');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeLoginUser',array($instance, $provider, $info));

        // If _getUser returned an error, then pass it back.
        if ($instance instanceof Exception) {
            return false;
        }

        // If the user is blocked, redirect with an error
        if ($instance->get('block') == 1) {
            $this->setError(JText::_('JERROR_NOLOGIN_BLOCKED'));
            return false;
        }

        // Mark the user as logged in
        $instance->set('guest', 0);

        $instance->set('usertype', 'deprecated');

        // Register the needed session variables
        $session->set('user', $instance);

        // Check to see the the session already exists.
        $app->checkSession();

        // Update the user related fields for the Joomla sessions table.
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__session') .
                ' SET ' . $db->quoteName('guest') . ' = ' . $db->quote($instance->get('guest')) . ',' .
                '	' . $db->quoteName('username') . ' = ' . $db->quote($instance->get('username')) . ',' .
                '	' . $db->quoteName('userid') . ' = ' . (int)$instance->get('id') .
                ' WHERE ' . $db->quoteName('session_id') . ' = ' . $db->quote($session->getId())
        );
        $db->query();

        // Hit the user last visit field
        $instance->setLastVisit();

        $dispatcher->trigger('onAfterLoginUser',array($instance, $provider, $info));

    }

    /**
     * Метод для отображения специального редиректа, с закрытием окна
     */
    protected function displayRedirect($redirect='index.php', $popup=false, $msg = '', $msgType = 'message')
    {
        if($popup){
            $session = JFactory::getSession();
            $redirect = base64_encode(JRoute::_($redirect));
            $session->set('slogin_return', JRoute::_($redirect));
            $view = $this->getView('Redirect', 'html');
            $view->display();
            exit;
        }
        else{
            $app = JFactory::getApplication();
            $app->redirect(JRoute::_($redirect), $msg, $msgType);
        }
    }

    /**
     * Метод для установки ошибки
     * @param string $error    ошибка
     */
    public function setError($error)
    {
        $session = JFactory::getSession();
        $error = $session->set('slogin_errors', $error);
        $this->displayRedirect();
        return false;
    }

    /**
     * Специальный редирект, берет сообщения из сессии
     * @return boolean
     */
    public function sredirect()
    {
        $session = JFactory::getSession();
        $app = JFactory::getApplication();

        $redirect = JRoute::_(base64_decode($session->get('slogin_return', '')));
        $session->clear('slogin_return');
        if ($error = $session->get('slogin_errors', null)) {
            $session->clear('slogin_errors');
            $app->redirect($redirect, $error, 'error');
            return false;
        } else {
            $app->redirect($redirect);
            return false;
        }


    }

    // проверить, не зарегистрирован ли уже пользователь с таким email
    public function CheckEmail($email)
    {
        // Initialise some variables
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('email') . ' = ' . $db->quote($email));
        $db->setQuery($query, 0, 1);
        $userId = $db->loadResult();

        if (!$userId) {
            return false;
        } else {
            return $userId;
        }
    }

    // проверить, не зарегистрирован ли уже пользователь с таким email
    public function GetUserId($id, $provider)
    {
        // Initialise some variables
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('user_id'));
        $query->from($db->quoteName('#__slogin_users'));
        $query->where($db->quoteName('slogin_id') . ' = ' . $db->quote($id));
        $query->where($db->quoteName('provider') . ' = ' . $db->quote($provider));
        $db->setQuery($query, 0, 1);
        $userId = $db->loadResult();
        return $userId;
    }

    public function GetSloginStringId($slogin_id, $user_id, $provider)
    {
        // Initialise some variables
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__slogin_users'));
        $query->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
        $query->where($db->quoteName('slogin_id') . ' = ' . $db->quote($slogin_id));
        $query->where($db->quoteName('provider') . ' = ' . $db->quote($provider));
        $db->setQuery($query, 0, 1);
        $userId = $db->loadResult();
        return $userId;
    }

    /**
     * Привязка логина к существующему пользователю если совпал емайл
     */
    public function join_mail()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $input = new JInput;

        $app = JFactory::getApplication();

        $user_id = $input->Get('user_id', 0, 'INT');
        $slogin_id = $input->Get('slogin_id', '', 'STRING');
        $provider = $input->Get('provider', '', 'STRING');

        // Populate the data array:
        $data = array();
        $data['return'] = base64_decode($input->Get('return', '', 'BASE64'));
        $data['username'] = $input->Get('username', '', 'username');
        $data['password'] = $input->Get('password', '', JREQUEST_ALLOWRAW);

        // Set the return URL if empty.
        if (empty($data['return'])) {
            $data['return'] = 'index.php?option=com_users&view=profile';
        }

        // Get the log in options.
        $options = array();
        $options['remember'] = $input->Get('remember', false);
        $options['return'] = $data['return'];

        // Get the log in credentials.
        $credentials = array();
        $credentials['username'] = $data['username'];
        $credentials['password'] = $data['password'];

        // Perform the log in.
        // Check if the log in succeeded.
        if (true === $app->login($credentials, $options)) {

            $joomlaUserId = JFactory::getUser()->id;

            //удаляем ненужного пользователя
            if($user_id != $joomlaUserId){

                //запрашиваем есть-ли у пользователя другие провайдеры
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('COUNT(*)');
                $query->from($db->quoteName('#__slogin_users'));
                $query->where($db->quoteName('user_id') . ' = ' . $db->quote($joomlaUserId));
                $query->where($db->quoteName('provider') . ' != ' . $db->quote($provider));
                $db->setQuery($query);
                $count = (int)$db->loadResult();

                //если других провайдеров нет, то удаляем пользователя
                if($count == 0){
                    $user_object = new JUser;
                    $user_object->id = $user_id;
                    $user_object->delete();
                }
            }
            //удаляем старую строку пользователя
            $this->deleteSloginUser($slogin_id, $provider);
            //вносим данные в #__slogin_user
            $this->storeSloginUser($joomlaUserId, $slogin_id, $provider);

            $app->setUserState('users.login.form.data', array());

            $app->redirect(JRoute::_($data['return'], false));
        } else {
            $data['remember'] = (int)$options['remember'];
            $app->setUserState('users.login.form.data', $data);

            $app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
        }
    }

    private function storeSloginUser($user_id, $slogin_id, $provider){
        JTable::addIncludePath(JPATH_COMPONENT . '/tables');
        $SloginUser = &JTable::getInstance('slogin_users', 'SloginTable');
        $SloginUser->user_id = $user_id;
        $SloginUser->slogin_id = $slogin_id;
        $SloginUser->provider = $provider;
        $SloginUser->store();
    }

    //проверка майла после ручного заполнения пользователем
    public function check_mail()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $input = new JInput;
        $first_name =   $input->Get('first_name',   '', 'STRING');
        $last_name =    $input->Get('last_name',    '', 'STRING');
        $email =        $input->Get('email',        '', 'STRING');
        $slogin_id =    $input->Get('slogin_id',    '', 'STRING');
        $provider =     $input->Get('provider',     '', 'STRING');

        //маленькая валидация
        if(empty($email)|| filter_var($email, FILTER_VALIDATE_EMAIL) === false){
             $this->queryEmail($first_name, $last_name, $email, $slogin_id, $provider);
        }
        else{
             $this->storeOrLogin($first_name, $last_name, $email, $slogin_id, $provider);
        }
    }

    protected function storeOrLogin($first_name, $last_name, $email, $slogin_id, $provider, $popup=false, $info = array())
    {
        //проверка на пустую запись ида пользователя
        if(empty($slogin_id)){
            echo '<p>Provider return empty user code.</p>';
            die;
        }

        $app = JFactory::getApplication();

        //если разрешено слияние - сливаем
        if($app->getUserState('com_slogin.action.data') == 'fusion'){
            $this->fusion($slogin_id, $provider, $popup);
        }

        //проверяем существует ли пользователь с таким уидом и провайдером
        $sloginUserId = $this->GetUserId($slogin_id, $provider);

        //Переадресация пользователя из модуля
        $return = base64_decode($app->getUserState('com_slogin.return_url'));

        //если такого пользователя нет, то создаем
        if (!$sloginUserId) {

            //проверка пустого мыла
            if($this->config->get('query_email', 0)==1 && empty($email)){
                $this->queryEmail($first_name, $last_name, $email, $slogin_id, $provider, $popup);
            }
            else if(empty($email)){
                $email = (strpos($provider, '.') === false) ? $slogin_id.'@'.$provider.'.com' : $slogin_id.'@'.$provider;
            }

            //логин пользователя
            $username = $this->transliterate($first_name.'-'.$last_name.'-'.$provider);

            //имя пользователя
            $name = $this->setUserName($first_name,  $last_name);

            //записываем пользователя в таблицу джумлы и компонента
            $joomlaUserId = $this->storeUser($username, $name, $email, $slogin_id, $provider, $popup, $info);

            if($joomlaUserId > 0){
                //логинимся если ид пользователя верный
                $this->loginUser($joomlaUserId, $provider, $info);

                //если настроено показать после регистрации изменение профиля
                if($this->config->get('add_info_new_user', 0) == 1){
                    $return = 'index.php?option=com_users&view=profile&layout=edit';
                }
                //если настроено после регистрации привязать пользователя к существующему
                else if($this->config->get('add_info_new_user', 0) == 2){
                    $user = JFactory::getUser();
                    $app	= JFactory::getApplication();
                    $data = array(
                        'email' => $user->get('email'),
                        'id' => $user->get('id'),
                        'provider' => $provider,
                        'slogin_id' => $slogin_id,
                    );
                    $app->setUserState('com_slogin.comparison_user.data', $data);
                    $return = 'index.php?option=com_slogin&view=linking_user';
                }
            }
        }
        else {   //или логинимся
            $this->loginUser($sloginUserId, $provider, $info);
        }
        $this->displayRedirect($return, $popup);
    }

    /**  Слияние пользователей
     * @param null $slogin_id - ид выдаваемый провайдером
     * @param null $provider  - провайдер
     */
    protected function fusion($slogin_id= null, $provider= null, $popup=false)
    {
        $app = JFactory::getApplication();
        $app->setUserState('com_slogin.action.data', '');

        //ид текущего пользователя
        $user_id = JFactory::getUser()->id;

        if((int)$user_id == 0 || !$slogin_id || !$provider){
            return;
        }

        //удаляем старые записи пользователя из #__slogin_users
        $this->deleteSloginUser($slogin_id, $provider);

        //добавляем новую запись пользователя в #__slogin_users
        $this->storeSloginUser($user_id, $slogin_id, $provider);

        $this->displayRedirect('index.php?option=com_slogin&view=fusion', $popup);
    }

    public function detach_provider()
    {
        $input = new JInput;
        $provider = $input->Get('plugin', '', 'STRING');
        //ид текущего пользователя
        $user_id = JFactory::getUser()->id;

        if((int)$user_id == 0 ){
            $this->displayRedirect('index.php?option=com_slogin&view=fusion');
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete();
        $query->from($db->quoteName('#__slogin_users'));
        $query->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
        $query->where($db->quoteName('provider') . ' = ' . $db->quote($provider));
        $db->setQuery($query);
        $db->query();

        $this->displayRedirect('index.php?option=com_slogin&view=fusion');
    }

    private function transliterate($str){

        $trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e",
            "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t",
            "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"shh",
            "ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya","ї"=>"i","'"=>"","ь"=>"","Ь"=>"",
            "ъ"=>"","Ъ"=>"","і"=>"i","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
            "Е"=>"E", "Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"I","К"=>"K", "Л"=>"L",
            "М"=>"M","Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R","С"=>"S","Т"=>"T","У"=>"Y",
            "Ф"=>"F", "Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Sh", "Ы"=>"I","Э"=>"E",
            "Ю"=>"U","Я"=>"Ya","Ї"=>"I","І"=>"I");

        $res=str_replace(" ","-",strtr($str,$trans));

        //если надо, вырезаем все кроме латинских букв, цифр и дефиса (например для формирования логина)
        $res=preg_replace("|[^a-zA-Z0-9-]|","",$res);

        return $res;
    }

    protected function localAuthDebug($redirect){
        if($this->config->get('local_debug', 0) == 1){
            $app = JFactory::getApplication();
            $app->redirect(JRoute::_($redirect));
        }
    }

    protected function localCheckDebug($provider){
        if($this->config->get('local_debug', 0) == 1){
            $slogin_id =  '12345678910';
            $this->storeOrLogin('Вася', 'Пупкин', '', $slogin_id, $provider, true);
        }
    }

    protected function queryEmail($first_name, $last_name, $email, $slogin_id, $provider, $popup=false)
    {
        $app	= JFactory::getApplication();

        $data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'slogin_id' => $slogin_id,
            'provider' => $provider
        );
        $app->setUserState('com_slogin.provider.data', $data);

        $this->displayRedirect('index.php?option=com_slogin&view=mail', $popup);
    }
}

class SloginRequest {
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $id = 0;
    public $real_name = '';
    public $sex = '';
    public $display_name = '';
    public $birthday = '';
    public $avatar = '';
    public $all_request = null;
}

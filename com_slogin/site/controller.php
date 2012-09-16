<?php
/**
 * SLogin
 *
 * @version     1.0
 * @author        SmokerMan
 * @copyright    © 2012. All rights reserved.
 * @license     GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// import Joomla controller library
jimport('joomla.application.component.controller');


jimport('joomla.environment.http');
/**
 * SLogin Controller
 *
 * @package        Joomla.Site
 * @subpackage    com_slogin
 */
class SLoginController extends JController
{
    protected $config;

    public function __construct()
    {

        $cofig = array();
        parent::__construct($cofig);

        $this->config = JComponentHelper::getParams('com_slogin');
    }

    /**
     * Устанавливаем редиректа в сессию
     */
    protected function auth()
    {
        $input = JFactory::getApplication()->input;
        $return = $input->get('return', null, 'BASE64');

        $session = JFactory::getSession();
        //устанавливаем страницу возврата в сессию
        $session->set('slogin_return', $return);
    }

    /**
     * Метод для отправки запросов
     * @param string     $url    УРЛ
     * @param boolean     $method    false - GET, true - POST
     * @param string     $params    Параметры для POST запроса
     * @return string    Результат запроса
     */
    protected function open_http($url, $method = false, $params = null)
    {

        if (!function_exists('curl_init')) {
            die('ERROR: CURL library not found!');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method);
        if ($method == true && isset($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
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

    /**
     * Метод для добавления новой учетной записи
     * @param string $username        Username учетной записи (не больше 150)
     * @param string $name            Имя учетной записи
     * @param strind $email            Email
     * @param $uid                      Идентификатор пользователя у провайдера
     * @param $provider                 идентификатор провайдера
     * @throws Exception
     */
    protected function storeUser($username, $name, $email, $uid, $provider)
    {
        $app = JFactory::getApplication();

        //отсылаем на подверждение владения мылом если разрешено и найдено
        $userId = $this->CheckEmail($email);
        if($userId){
            $session = JFactory::getSession();
            $redirect = base64_encode(JRoute::_('index.php?option=com_slogin&view=comparison_user&email='.$email.'&id='.$userId.'&provider='.$provider.'&slogin_id='.$uid));
            $session->set('slogin_return', $redirect);
            $this->displayRedirect();
        }

        $username = $this->CheckUniqueName($username);
        $user['username'] = $username;
        $user['name'] = $name;
        $user['email'] = $email;
        $user['registerDate'] = JFactory::getDate()->toSQL();

        //установка групп для нового пользователя
        $user_config = JComponentHelper::getParams('com_users');
        $defaultUserGroup = $user_config->get('new_usertype', 2);
        $user['groups'] = array($defaultUserGroup);

        $user_object = new JUser;

        if (!$user_object->bind($user)) {
            $this->setError($user_object->getError());
            return;
            //throw new Exception($user_object->getError());
        }

        if (!$user_object->save()) {
            $this->setError($user_object->getError());
            return;
            //throw new Exception($user_object->getError());
        }

        $this->storeSloginUser($user_object->id, $uid, $provider);

        $this->loginUser($user_object->id, true);

    }

    /**
     * Метод для авторизцаии пользователя
     * @param int $id    ID пользователя в Joomla
     */
    protected function loginUser($id, $firstLogin = false)
    {
        $instance = JUser::getInstance($id);
        $app = JFactory::getApplication();
        $session = JFactory::getSession();

        // If the user is blocked, redirect with an error
        if ($instance->get('block') == 1) {
            $this->setError(JText::_('JERROR_NOLOGIN_BLOCKED'));
        }

        // Mark the user as logged in
        $instance->set('guest', 0);

        // Register the needed session variables
        $session = JFactory::getSession();
        $session->set('user', $instance);

        $db = JFactory::getDBO();

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

        if($firstLogin && $this->config->get('add_info_new_user', 0) == 1){
            $return = base64_encode(JRoute::_('index.php?option=com_users&view=profile&layout=edit', false));
            $session = JFactory::getSession();
            //устанавливаем страницу возврата в сессию
            $session->set('slogin_return', $return);
        }
        $this->displayRedirect();
    }

    /**
     * Метод для отображения специального редиректа, с закрытием окна
     */
    protected function displayRedirect()
    {
        $view = $this->getView('Redirect', 'html');
        $view->display();
        exit;
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
        $redirect = base64_decode($session->get('slogin_return', ''));
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
        //если в настройках установлено подтверждать права на почту
        if ($this->config->get('collate_users', 0)) {
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
        } else {
            return false;
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

    public function join_email(){
        $input = new JInput;
        JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

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
            $app->setUserState('users.login.form.data', array());
            $this->storeSloginUser(JFactory::getUser()->id, $slogin_id, $provider);
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

    protected function storeOrLogin($first_name= null, $last_name= null, $email= null, $uid= null, $provider= null)
    {
        $username = $this->transliterate($first_name.'-'.$last_name);
        //проверяем существует ли пользователь с таким именем
        $user_id = $this->GetUserId($uid, $provider);

        if (!$user_id) {
            $name = $this->setUserName($first_name,  $last_name);
            $this->storeUser($username, $name, $email, $uid, $provider);
        } else {
            $this->loginUser($user_id);
        }
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
}

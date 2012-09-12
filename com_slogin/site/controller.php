<?php
/**
 * SMLogin
 * 
 * @version 	1.0	
 * @author		SmokerMan
 * @copyright	© 2012. All rights reserved. 
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

// import Joomla controller library
jimport('joomla.application.component.controller');


jimport('joomla.environment.http');
/**
 * SMLogin Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_smlogin
 */
class SMLoginController extends JController
{
	protected $config;
	
	public function __construct() 
	{

		$cofig = array();
		parent::__construct($cofig);
		
		$this->config = JComponentHelper::getParams('com_smlogin');
	}
	
	/**
	 * Устанавливаем редиректа в сессию
	 */
	protected function auth() {		
		$input = JFactory::getApplication()->input;
		$return = $input->get('return', null, 'BASE64');
		
		$session = JFactory::getSession();
		//устанавливаем страницу возврата в сессию
		$session->set('smlogin_return', $return);
	}	
	/**
	 * Метод для отправки запросов
	 * @param string 	$url	УРЛ
	 * @param boolean 	$method	false - GET, true - POST
	 * @param string 	$params	Параметры для POST запроса
	 * @return string	Результат запроса
	 */
	protected function open_http($url, $method = false, $params = null ) 
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

		$result = curl_exec ($ch);
		curl_close ($ch);
		return $result;
	}	
	
	
	/**
	 * Метод для получения имени пользователя
	 * @param string 	$type	Тип vk, google, etc.
	 * @param int 		$id		ID пользователя на сервисе
	 * @return string
	 */
	protected function getUserName($type, $id)
	{
		$username = $type . '-' . $id;
		return $username;
	}

	/**
	 * Установка имени для пользователя
	 * @param string $first_name	Имя
	 * @param string $last_name		Фамилия
	 * @return string	Имя пользователя, в зовизимости от параметров компонента 
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
	/**
	 * Метод для добавления новой учетной записи
	 * @param string $username		Username учетной записи (не больше 150)
	 * @param string $name			Имя учетной записи
	 * @param strind $email			Email
	 * @throws Exception
	 */
	protected function storeUser($username, $name, $email)
	{
		$app = JFactory::getApplication();
		
		$user['username'] = $username;
        $user['name'] = $name;
        $user['email'] = $email;
        $user['registerDate'] = JFactory::getDate()->toSQL();
        
        //установка групп для нового пользователя
        $user_config	= JComponentHelper::getParams('com_users');
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
        
        $this->loginUser($user_object->id);

	}	
	
	/**
	 * Метод для авторизцаии пользователя
	 * @param int $id	ID пользователя в Joomla
	 */
	protected function loginUser($id)
	{
		$instance = JUser::getInstance($id);
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		// If the user is blocked, redirect with an error
		if ($instance->get('block') == 1) {
			$this->setError(JText::_('JERROR_NOLOGIN_BLOCKED'));
		}

		// Authorise the user based on the group information
// 		if (!isset($options['group'])) {
// 			$options['group'] = 'USERS';
// 		}

		// Chek the user can login.
// 		$result	= $instance->authorise($options['action']);
// 		if (!$result) {

// 			JError::raiseWarning(401, JText::_('JERROR_LOGIN_DENIED'));
// 			return false;
// 		}

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
			'UPDATE '.$db->quoteName('#__session') .
			' SET '.$db->quoteName('guest').' = '.$db->quote($instance->get('guest')).',' .
			'	'.$db->quoteName('username').' = '.$db->quote($instance->get('username')).',' .
			'	'.$db->quoteName('userid').' = '.(int) $instance->get('id') .
			' WHERE '.$db->quoteName('session_id').' = '.$db->quote($session->getId())
		);
		$db->query();

		// Hit the user last visit field
		$instance->setLastVisit();
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
	 * @param string $error	ошибка
	 */
	public function setError($error) {
		$session = JFactory::getSession();
		$error =  $session->set('smlogin_errors', $error);
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
		$redirect = base64_decode($session->get('smlogin_return', ''));
		$session->clear('smlogin_return');
		if ($error =  $session->get('smlogin_errors', null)) {
			$session->clear('smlogin_errors');
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
		return $db->loadResult();
	}
	
}

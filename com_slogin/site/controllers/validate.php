<?php

class SLoginControllerValidate
{
    function validate(){
        $return = array('error'=>0, 'msg'=>array());

        $input = new JInput();
        $ajax = $input->getInt('ajax', 0);
        $email = $input->getString('email', '');
        $name = $input->getString('name', '');
        $username = $input->getString('username', '');

        if($email && !$this->validateEmail($email)){
            $return['error'] =+ 1;
            $return['msg'][] = JText::_('COM_SLOGIN_ERROR_VALIDATE_MAIL');
        }
        if($email && !$this->checkUniqueEmail($email)){
            $return['error'] =+ 1;
            $return['msg'][] = JText::_('COM_SLOGIN_ERROR_NOT_UNIQUE_MAIL');
        }
        if($name && !$this->validateName($name)){
            $return['error'] =+ 1;
            $return['msg'][] = JText::_('COM_SLOGIN_ERROR_VALIATE_NAME');
        }
        if($username && !$this->validateUserName($username)){
            $return['error'] =+ 1;
            $return['msg'][] = JText::_('COM_SLOGIN_ERROR_VALIATE_USERNAME');
        }

        if($ajax){
            echo json_encode($return);
            die();
        }
        return $return;
    }

    function validateEmail($email)
    {
        //by Femi Hasani [www.vision.to]
        if(!preg_match ("/^[\w\.-]{1,}\@([\da-zA-Z-]{1,}\.){1,}[\da-zA-Z-]+$/", $email)){
            return false;
        }


        list($prefix, $domain) = explode("@",$email);

        $mxHosts = array();

        if(function_exists("getmxrr") && getmxrr($domain, $mxhosts))
        {
            return true;
        }
        elseif (@fsockopen($domain, 25, $errno, $errstr, 5))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function checkUniqueEmail($email)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('email') . ' = ' . $db->quote($email));
        $db->setQuery($query, 0, 1);
        return (bool)!$db->loadResult();
    }

    function validateName($name)
    {
        if(!is_string($name) || strlen($name) < 2)
            return false;
        return true;
    }

    function validateUserName($username)
    {
        if(!is_string($username) || strlen($username) < 2)
            return false;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('username') . ' = ' . $db->quote($username));
        $db->setQuery($query, 0, 1);
        return (bool)!$db->loadResult();
    }
}

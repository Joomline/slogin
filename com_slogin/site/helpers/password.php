<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

class SloginPasswordHelper
{
    static function generatePassword($slogin_id, $provider, $secret)
    {
        if(empty($secret))
        {
            $secret = self::createSecret();
        }

        return md5($slogin_id.$provider.$secret);
    }

    static function getPasswords($user_id)
    {
        $passwords = array();
        $secret = JComponentHelper::getParams('com_slogin')->get('secret','');

        if(empty($secret))
        {
            $secret = self::createSecret();
        }

        if(empty($secret))
        {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('`#__slogin_users`')
            ->where('`user_id` = '.$db->quote($user_id));
        $result = $db->setQuery($query)->loadObjectList();

        if(is_array($result) && count($result))
        {
            foreach($result as $v)
            {
                $passwords[] = self::generatePassword($v->slogin_id, $v->provider, $secret);
            }
        }

        return $passwords;
    }

    private static function createSecret()
    {
        $component = 'com_slogin';
        $secret = self::generate_hash(15);
        // получаем параметры компонента com_users
        $params = JComponentHelper::getParams($component);
        // устанавливаем требуемое значение
        $params->set('secret', $secret);

        // записываем измененные параметры в БД
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__extensions'));
        $query->set($db->quoteName('params') . '= ' . $db->quote((string)$params));
        $query->where($db->quoteName('element') . ' = ' . $db->quote($component));
        $query->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        $db->setQuery($query);

        if($db->execute())
        {
            return $secret;
        }
        return false;
    }

    private static function generate_hash($number)
    {
        $arr = array('a','b','c','d','e','f',
            'g','h','i','j','k','l',
            'm','n','o','p','r','s',
            't','u','v','x','y','z',
            'A','B','C','D','E','F',
            'G','H','I','J','K','L',
            'M','N','O','P','R','S',
            'T','U','V','X','Y','Z');
        $hash = '';
        // Генерируем хэш
        for($i = 0; $i < $number; $i++)
        {
            $index = rand(0, count($arr) - 1);
            $hash .= $arr[$index];
        }
        return $hash;
    }
}
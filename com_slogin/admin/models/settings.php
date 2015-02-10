<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of search terms.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @since		1.6
 */
class sloginModelSettings  extends JModelList
{
    public function getPieChartData()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('provider')
            ->from('#__slogin_users');
        $res = $db->setQuery($query)->loadColumn();

        $allUsers = count($res);

        if(!$allUsers)
        {
            return '[]';
        }

        $onePercent = $allUsers/100;
        $prov = array();

        foreach($res as $v)
        {
            if(!isset($prov[$v]))
            {
                $prov[$v] = 1;
            }
            else
            {
                $prov[$v]++;
            }
        }
        $i = 1;
        $providers = array();
        foreach($prov as $k => $v)
        {
            $providers[$k]['count'] = $v;
            $providers[$k]['percent'] = $v/$onePercent;
            $providers[$k]['order'] = $i;
            $i++;
        }

        $array = array();
        $i = 0;
        foreach($providers as $key => $data)
        {
            $val = new stdClass();
            $val->name = $key;
            $val->order = $data['order'];
            $val->value = $data['count'];
            $val->percents = round($data['percent'], 2);
            //$val->color = $data->color;

            $array[$i] = $val;
            $i++;
        }

        if(count($array) == 0)
        {
            return '[]';
        }

        return json_encode($array);
    }

    private function getPlugins($folder)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('element')
            ->select('extension_id')
            ->select('name')
            ->select('params')
            ->select('enabled')
            ->from('#__extensions')
            ->where('`type` = '.$db->quote('plugin'))
            ->where('`folder` = '.$db->quote($folder));
        $res = $db->setQuery($query)->loadObjectList();

        if(!is_array($res) || !count($res))
        {
            return array();
        }

        $plugins = array();

        foreach($res as $k => $v)
        {
            $v->params = json_decode($v->params);
            $v->installed = true;
            $v->set = true;

            if((isset($v->id) && empty($v->id)) || (isset($v->password) && empty($v->password)))
                $v->set = false;

            unset($v->params);

            $plugins[] = $v;
        }

        return $plugins;
    }

    public function getAuthPlugins()
    {
        $plugins = $this->getPlugins('slogin_auth');
        return $plugins;
    }

    public function getIntegrationPlugins()
    {
        $plugins = $this->getPlugins('slogin_integration');
        return $plugins;
    }

    public function getComPlugins()
    {
        $plugins = array();

        if(JFolder::exists(JPATH_ROOT.'/components/com_comprofiler'))
        {
            $plg = new stdClass();
            $plg->name = 'Social Login Integration Community Builder';
            $plg->element = 'combuilder';
            $plg->link = 'http://argens.ru/avtorizatsiya-social-login/23-plagin-integratsii-slogin-community-builder';
            $plugins[] = $plg;
        }

        if(JFolder::exists(JPATH_ROOT.'/components/com_jshopping'))
        {
            $plg = new stdClass();
            $plg->name = 'Social Login Integration JoomShopping';
            $plg->element = 'joomshopping';
            $plg->link = 'http://argens.ru/rasshireniya-dlya-joomshopping/12-plagin-avtorizatsii-cherez-sotsialnye-seti-dlya-joomshopping';
            $plugins[] = $plg;
        }

        if(JFolder::exists(JPATH_ROOT.'/components/com_jomsocial'))
        {
            $plg = new stdClass();
            $plg->name = 'Social Login Integration JoomSocial';
            $plg->element = 'joomsocial';
            $plg->link = 'http://argens.ru/avtorizatsiya-social-login/24-plagin-integratsii-slogin-joomsocial';
            $plugins[] = $plg;
        }

        if(JFolder::exists(JPATH_ROOT.'/components/com_k2'))
        {
            $plg = new stdClass();
            $plg->name = 'Social Login Integration k2';
            $plg->element = 'k2';
            $plg->link = 'http://argens.ru/avtorizatsiya-social-login/21-plagin-integratsii-slogin-k2';
            $plugins[] = $plg;
        }

        if(JFolder::exists(JPATH_ROOT.'/components/com_kunena'))
        {
            $plg = new stdClass();
            $plg->name = 'Social Login Integration Kunena';
            $plg->element = 'kunena';
            $plg->link = 'http://argens.ru/avtorizatsiya-social-login/38-plagin-integratsii-slogin-kunena';
            $plugins[] = $plg;
        }

        return $plugins;
    }
}

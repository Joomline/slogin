<?php
/**
 * SLogin Integration Plugin Profile
 *
 * @version 	5.0.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

namespace Joomla\Plugin\SloginIntegration\Profile\Extension;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;

require_once JPATH_ROOT.'/plugins/slogin_integration/profile/lib/profiles.php';
require_once JPATH_ROOT.'/plugins/slogin_integration/profile/lib/geo.php';
require_once JPATH_BASE.'/components/com_slogin/controller.php';

class Profile extends CMSPlugin
{
    public function onAfterSloginStoreUser($user, $provider, $info)
    {
        $this->createProfile($user, $provider, $info);
    }

    public function onAfterSloginLoginUser($user, $provider, $info)
    {
        $conf = ComponentHelper::getParams('com_slogin');

        if(!method_exists($this, $provider."GetData")) return;

        if($this->issetProfile($user, $provider))
        {
			$data = call_user_func_array(array($this, $provider."GetData"), array($user, $provider, $info));
            $this->updateAvatar($user, $provider, $data->picture, $info);
            $this->updateCurrentProfile($user, $provider);
        }
        else{
            $this->createProfile($user, $provider, $info);
        }
    }

    public function onBeforeSloginDeleteSloginUser($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__slogin_users');
        $query->where($db->quoteName('id') . ' = ' . $db->quote($id));
        $db->setQuery((string)$query, 0, 1);
        $res = $db->loadObject();
        $this->deleteProfile($res);
    }

    public function onBeforeSloginDeleteUser($userId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__slogin_users');
        $query->where($db->quoteName('user_id') . ' = ' . $db->quote($userId));
        $db->setQuery((string)$query);
        $res = $db->loadObjectList();
        if(count($res)>0){
            foreach($res as $v){
                $this->deleteProfile($v);
            }
        }
    }

    // Остальные методы остаются такими же, но с обновленными вызовами классов
    // Для краткости показываю только основную структуру
    
    private function deleteProfile($row)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('avatar');
        $query->from($db->quoteName('#__plg_slogin_profile'));
        $query->where($db->quoteName('user_id') . ' = ' . $db->quote($row->user_id));
        $query->where($db->quoteName('provider') . ' = ' . $db->quote($row->provider));
        $db->setQuery($query,0,1);
        $avatar = $db->loadResult();

        $rootfolder = $this->params->get('rootfolder', 'images/avatar');
        $file = JPATH_ROOT . '/' . $rootfolder . '/' . $avatar;
        if (is_file($file))
        {
            File::delete($file);
        }
        $query->clear();
        $query->delete();
        $query->from($db->quoteName('#__plg_slogin_profile'));
        $query->where($db->quoteName('user_id') . ' = ' . $db->quote($row->user_id));
        $query->where($db->quoteName('provider') . ' = ' . $db->quote($row->provider));
        $db->setQuery($query);
        $db->execute();
    }

    private function facebookGetData($user, $provider, $info)
    {
        $controller = new \SLoginController();
        $data = new \StdClass();
        $data->user_id = $user->id;
        $data->slogin_id = $info->id;
        $data->provider = $provider;
        $data->social_profile_link = $info->link;
        if($info->gender == 'male')
            $data->gender = 1;
        elseif($info->gender == 'female')
            $data->gender = 2;
        else
            $data->gender = 0;
		if(!empty($info->birthday)){
			$date = new Date($info->birthday);
			$data->birthday = $date->toSql();
		}
        $data->f_name = $info->first_name;
        $data->l_name = $info->last_name;
        $data->email = $info->email;
        $this->getGeoInfo($data);
        $foto_url = 'https://graph.facebook.com/' . $info->id . '/picture?type=large&redirect=false';
        $request_foto = json_decode($controller->open_http($foto_url));
        $data->picture = '';
        if (empty($request_foto->error)){
            if ($request_foto->data->is_silhouette === false) { //если аватар загружен
                if ($request_foto->data->url) {
                    $data->picture = $request_foto->data->url;
                }
            }
        }
        return $data;
    }

    // Добавлю еще несколько ключевых методов для примера
    private function getGeoInfo(&$data){
        if($this->params->get('enable_geo', 0))
        {
            $geo = new \SloginGeo(array('charset'=>'UTF-8', 'ip'=>$_SERVER["REMOTE_ADDR"]));
            $geoData = $geo->get_geobase_data();
            $data->country = (!empty($geoData["country"])) ? $geoData["country"] : '';
            $data->region = (!empty($geoData["region"])) ? $geoData["region"] : '';
            $data->city = (!empty($geoData["city"])) ? $geoData["city"] : '';
            $data->lat = (!empty($geoData["lat"])) ? $geoData["lat"] : '';
            $data->lng = (!empty($geoData["lng"])) ? $geoData["lng"] : '';
        }
    }

    private function createProfile($user, $provider, $info)
    {
        $conf = ComponentHelper::getParams('com_slogin');

        if (!$provider) return;

        if(!method_exists($this, $provider."GetData")) return;

		$data = call_user_func_array(array($this, $provider."GetData"), array($user, $provider, $info));

        if (isset($data->picture)){
            $origimage = $data->picture;
            $new_image = $provider . '_' . $data->slogin_id . '.jpg';
            if ($this->createAvatar($origimage, $new_image))
            {
                $data->avatar = $new_image;
            }
        }

        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->update('#__plg_slogin_profile');
        $q->set('`current_profile` = 0');
        $q->where('`user_id` = '.(int)$user->id);
        $db->setQuery($q);
        $db->execute();

        $data->current_profile = 1;

		if (empty($data->email) && !empty($user->email)) {
			$data->email = $user->email;
		}

        $table = new \PlgSloginProfilesTable($db);
        $table->load();
        $table->bind($data);
        if (!$table->store()) {
			$app = Factory::getApplication();
	        $app->enqueueMessage($table->getError(), $app::MSG_ERROR);
        }

        if($this->params->get('enable_userfields_integration', 0)){
        	$this->createUserFields($user->id, $data);
        }
    }

    private function issetProfile($user, $provider)
    {
        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->select('COUNT(*)');
        $q->from('#__plg_slogin_profile');
        $q->where('`user_id` = '.(int)$user->id);
        $q->where('`provider` = '.$db->quote($provider));
        $db->setQuery($q);
        $res = $db->loadResult();
        return $res > 0;
    }

    private function updateCurrentProfile($user, $provider)
    {
        $db = Factory::getDbo();
        $q = $db->getQuery(true);
        $q->update('#__plg_slogin_profile');
        $q->set('`current_profile` = 0');
        $q->where('`user_id` = '.(int)$user->id);
        $db->setQuery($q);
        $db->execute();

        $q = $db->getQuery(true);
        $q->update('#__plg_slogin_profile');
        $q->set('`current_profile` = 1');
        $q->where('`user_id` = '.(int)$user->id);
        $q->where('`provider` = '.$db->quote($provider));
        $db->setQuery($q);
        $db->execute();
    }

    // Остальные методы аналогично...
}
<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\User;

/**
 * SLogin Main Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SLoginController extends BaseController
{
	/**
	 * @param $cachable
	 * @param $urlparams
	 *
	 * @return SLoginController|void
	 *
	 * @throws Exception
	 * @since version
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->default_view = 'settings';
		parent::display($cachable, $urlparams);
	}

    public function clean()
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $db = Factory::getDbo();
        $query = 'TRUNCATE TABLE  `#__slogin_users`';
        $db->setQuery($query);
        try {
            $db->execute();
            $msg = 'Table cleared';
            $msgType = 'message';
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $msgType = 'error';
        }

        $app = Factory::getApplication();
	    $app->enqueueMessage($msg, $msgType);
        $app->redirect('index.php?option=com_slogin&view=settings');
    }
    public function repair()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('s.user_id');
        $query->from('#__slogin_users as s');
        $query->leftJoin('#__users as u ON u.id = s.user_id');
        $query->where('u.id IS NULL');
        $db->setQuery($query);
        $uids = $db->loadColumn();
        if(is_array($uids) && count($uids)>0){
            $query = $db->getQuery(true);
            $query->delete('#__slogin_users');
            $query->where('user_id IN ('.implode(', ', $uids).')');
            $db->setQuery($query);
            try {
                $db->execute();
                $msg = 'Table repaired';
                $msgType = 'message';
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $msgType = 'error';
            }
        }else{
            $msg = 'Bad rows is not detected';
            $msgType = 'message';
        }
        $app = Factory::getApplication();
	    $app->enqueueMessage($msg, $msgType);
        $app->redirect('index.php?option=com_slogin&view=settings');
	}

    public function remove_slogin_users(){
        $input = Factory::getApplication()->input;
        \Joomla\CMS\Plugin\PluginHelper::importPlugin('slogin_integration');
        $app = Factory::getApplication();
        $ids = $input->get('cid', array(), 'ARRAY');
        $model = $this->getModel('User', 'SloginModel');
        $table = $model->getTable();
        $errors = array();
        if(count($ids) > 0){
            foreach($ids as $id){
	            Factory::getApplication()->triggerEvent('onBeforeSloginDeleteSloginUser',array((int)$id));
                if (!$table->delete((int)$id)) {
                    $errors[] = $table->getError();
                }
                else{
	                Factory::getApplication()->triggerEvent('onAfterSloginDeleteSloginUser',array((int)$id));
                }
            }
        }
        $msg = '';
        $msgType = Text::_('COM_SLOGIN_USERS_DELETED');
        if(count($errors)){
            $msg = implode('<br/>', $errors);
            $msgType = 'error';
        }
	    $app->enqueueMessage($msg, $msgType);
	    $app->redirect('index.php?option=com_slogin&view=users');
    }

    public function remove_joomla_users(){
        $input = Factory::getApplication()->input;
        \Joomla\CMS\Plugin\PluginHelper::importPlugin('slogin_integration');
        $app = Factory::getApplication();
        $ids = $input->get('cid', array(), 'ARRAY');
        $model = $this->getModel('User', 'SloginModel');
        $table = $model->getTable();
        $errors = array();
        $db = Factory::getDbo();
        $user = new User();
        if(count($ids) > 0){
            foreach($ids as $id){
                $query = $db->getQuery(true);
                $query->select('user_id');
                $query->from('#__slogin_users');
                $query->where('id = '.(int)$id);
                $db->setQuery((string)$query, 0, 1);
                $userId = $db->loadResult();
	            Factory::getApplication()->triggerEvent('onBeforeSloginDeleteUser',array($userId));

                if (!$table->delete((int)$id)) {
                    $errors[] = $table->getError();
                }
                else{
                    $user->id = $userId;
                    $user->delete();
                    if (!$table->deleteUserRows($userId)){
                        $errors[] = $table->getError();
                    }
                    else{
	                    Factory::getApplication()->triggerEvent('onAfterSloginDeleteUser',array($userId));
                    }
                }
            }
        }
        $msg = Text::_('COM_SLOGIN_USERS_DELETED');
        $msgType = 'msg';
        if(count($errors)){
            $msg = implode('<br/>', $errors);
            $msgType = 'error';
        }
	    $app->enqueueMessage($msg, $msgType);
	    $app->redirect('index.php?option=com_slogin&view=users');
    }
}

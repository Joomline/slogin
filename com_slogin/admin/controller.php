<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
use Joomla\CMS\Factory;

defined('_JEXEC') or die('(@)|(@)');

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
 * SLogin Main Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SLoginController extends SLoginControllerParent
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
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $db = JFactory::getDbo();
        $query = 'TRUNCATE TABLE  `#__slogin_users`';
        $db->setQuery($query);
        if (!$db->execute()) {
            $msg = $db->getErrorMsg();
            $msgType = 'error';
        }
        else{
            $msg = 'Table cleared';
            $msgType = 'message';
        }


        $app = JFactory::getApplication();
	    $app->enqueueMessage($msg, $msgType);
        $app->redirect('index.php?option=com_slogin&view=settings');
    }
    public function repair()
    {
        $db = JFactory::getDbo();
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
            if (!$db->execute()) {
                $msg = $db->getErrorMsg();
                $msgType = 'error';
            }
            else{
                $msg = 'Table repaired';
                $msgType = 'message';
            }
        }else{
            $msg = 'Bad rows is not detected';
            $msgType = 'message';
        }
        $app = JFactory::getApplication();
	    $app->enqueueMessage($msg, $msgType);
        $app->redirect('index.php?option=com_slogin&view=settings');
	}

    public function remove_slogin_users(){
        $input = new Joomla\Input\Input();
        JPluginHelper::importPlugin('slogin_integration');
        $app = JFactory::getApplication();
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
        $msgType = JText::_('COM_SLOGIN_USERS_DELETED');
        if(count($errors)){
            $msg = implode('<br/>', $errors);
            $msgType = 'error';
        }
	    $app->enqueueMessage($msg, $msgType);
	    $app->redirect('index.php?option=com_slogin&view=users');
    }

    public function remove_joomla_users(){
        $input = new Joomla\Input\Input();
        JPluginHelper::importPlugin('slogin_integration');
        $app = JFactory::getApplication();
        $ids = $input->get('cid', array(), 'ARRAY');
        $model = $this->getModel('User', 'SloginModel');
        $table = $model->getTable();
        $errors = array();
        $db = JFactory::getDbo();
        $user = new JUser();
        if(count($ids) > 0){
            foreach($ids as $id){
                $query = $db->getQuery(true);
                $query->select('user_id');
                $query->from('#__slogin_users');
                $query->where('id = '.(int)$id);
                $db->setQuery((string)$query, 0, 1);
                $userId = $db->loadResult();
	            Joomla\CMS\Factory::getApplication()->triggerEvent('onBeforeSloginDeleteUser',array($userId));

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
	                    Joomla\CMS\Factory::getApplication()->triggerEvent('onAfterSloginDeleteUser',array($userId));
                    }
                }
            }
        }
        $msg = JText::_('COM_SLOGIN_USERS_DELETED');
        $msgType = 'msg';
        if(count($errors)){
            $msg = implode('<br/>', $errors);
            $msgType = 'error';
        }
	    $app->enqueueMessage($msg, $msgType);
	    $app->redirect('index.php?option=com_slogin&view=users');
    }
}

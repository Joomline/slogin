<?php

namespace Joomline\Component\Slogin\Administrator\Model;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

class UserModel extends AdminModel
{
	public function getTable($type = 'Users', $prefix = 'Joomline\Component\Slogin\Administrator\Table\\', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
//		$form = $this->loadForm('com_keygen.extension', 'extension', array('control' => 'jform', 'load_data' => $loadData));
//		if (empty($form))
//		{
//			return false;
//		}
//		return $form;
	}

}
<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

jimport( 'joomla.application.component.view');

/**
 * Вид для редиректа и закрытия popup окна
 * @author Николай
 *
 */
class SLoginViewRedirect extends JViewLegacy
{

	/**
	 * Метод для отображения 
	 * @param string $tpl	шаблон
	 */
	public function display($tpl = null)
	{
        $session = JFactory::getSession();

        $this->url = JRoute::_('index.php?option=com_slogin&amp;task=sredirect');

		parent::display($tpl);
	}


}

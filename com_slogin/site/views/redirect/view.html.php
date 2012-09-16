<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
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
class SLoginViewRedirect extends JView
{

	/**
	 * Метод для отображения 
	 * @param string $tpl	шаблон
	 */
	public function display($tpl = null)
	{
		parent::display($tpl);
	}


}

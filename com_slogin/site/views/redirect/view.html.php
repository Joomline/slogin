<?php
/**
 * SMFAQ
 *
 * @package		component for Joomla 2.5+
 * @version		1.7 beta 2
 * @copyright	(C)2009 - 2011 by SmokerMan (http://joomla-code.ru)
 * @license		GNU/GPL v.3 see http://www.gnu.org/licenses/gpl.html
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

jimport( 'joomla.application.component.view');

/**
 * Вид для редиректа и закрытия popup окна
 * @author Николай
 *
 */
class SMLoginViewRedirect extends JView
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

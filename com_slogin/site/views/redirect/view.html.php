<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * Вид для редиректа и закрытия popup окна
 * @author Николай
 *
 */
class SLoginViewRedirect extends HtmlView
{

	/**
	 * Метод для отображения 
	 * @param string $tpl	шаблон
	 */
	public function display($tpl = null)
	{
        $session = Factory::getSession();

        $this->url = Route::_('index.php?option=com_slogin&amp;task=sredirect');

		parent::display($tpl);
	}


}

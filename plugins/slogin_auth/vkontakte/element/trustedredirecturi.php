<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2023. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

class JFormFieldTrustedRedirectUri extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'TrustedRedirectUri';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$class = $this->element['class'] ? ' class="form-control ' . (string) $this->element['class'] . '"' : ' class="form-control"';
		
		// Get the menu item ID
		$menuItemId = (int)$this->form->getValue('menu_item_id', 'params', 0);
		$useMenuItem = (int)$this->form->getValue('use_menu_item', 'params', 0);
		
		// Получаем базовый домен
		$baseUrl = rtrim(Uri::root(), '/');
		
		// Default URL (without menu item)
		$defaultUrl = $baseUrl . '/index.php?option=com_slogin&task=check&plugin=vkontakte';
		
		// Initialize the redirect URL
		$redirectUrl = $defaultUrl;
		
		// Если выбран пункт меню и включена опция использования пункта меню
		if ($useMenuItem && $menuItemId) {
			try {
				// Используем прямой запрос к базе данных для получения информации о пункте меню
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('a.alias, a.path')
					->from('#__menu AS a')
					->where('a.id = ' . (int)$menuItemId);
				
				$db->setQuery($query);
				$menuData = $db->loadObject();
				
				if ($menuData && !empty($menuData->alias)) {
					// Формируем SEF URL напрямую из базы данных
					$menuPath = $menuData->path;
					
					// Если есть путь меню, используем его
					if (!empty($menuPath)) {
						$redirectUrl = $baseUrl . '/' . $menuPath . '/vkontakte';
					} else {
						// Иначе используем только алиас
						$redirectUrl = $baseUrl . '/' . $menuData->alias . '/vkontakte';
					}
				}
			} catch (Exception $e) {
				// If there's an error, fall back to the default URL
				$redirectUrl = $defaultUrl;
			}
		}
		
		// Create the input field with Joomla 5 classes
		$html = '<div class="input-group">';
		$html .= '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . $redirectUrl . '" ' . $class . $readonly . ' />';
		$html .= '</div>';
		$html .= '<div class="form-text">' . Text::_('PLG_SLOGIN_AUTH_VKONTAKTE_TRUSTED_REDIRECT_URI_DESC') . '</div>';
		
		return $html;
	}
}

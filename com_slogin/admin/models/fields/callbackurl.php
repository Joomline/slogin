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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

class JFormFieldCallbackUrl extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'CallbackUrl';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$plugin = $this->element['plugin'] ? '&plugin=' . (string) $this->element['plugin'] : '';
		$task = 'index.php?option=com_slogin&task=check'.$plugin;

		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		$CallbackUrl = Uri::root().$task;

        if(substr($CallbackUrl, -1, 1) == '/'){
             $CallbackUrl = substr($CallbackUrl, 0, -1);
        }
		
		$html = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'.$CallbackUrl.'" style="width: 550px" '. $class . $readonly .' />';
		
		return $html;
	}
}
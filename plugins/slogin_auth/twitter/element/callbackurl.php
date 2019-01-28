<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

jimport('joomla.form.formfield');
jimport( 'joomla.application.router' );
if(is_file(JPATH_ROOT . '/includes/router.php'))
	require_once (JPATH_ROOT . '/includes/router.php');
else if(is_file(JPATH_ROOT . '/libraries/cms/router/site.php'))
	require_once (JPATH_ROOT . '/libraries/cms/router/site.php');

class JFormFieldCallbackUrl extends JFormField
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

		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

        $router = new JRouterSite(array('mode' => 1));

        $route = $router->build('index.php?option=com_slogin&task=check&plugin=' . (string) $this->element['value']);
        $CallbackUrl = JURI::root().str_replace('/administrator/', '', $route);
		
		$html = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'.$CallbackUrl.'" size="70%" '. $class . $readonly .' />';
		
		return $html;
	}
}
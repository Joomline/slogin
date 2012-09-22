<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
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
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 * @return	JController		This object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		//JRequest::setVar('view', JRequest::getCmd('view', '{viewname}s'));
		$this->default_view = 'settings';
		parent::display($cachable, $urlparams);
	}
	
}
<?php

namespace Joomline\Component\Slogin\Administrator\View\Settings;

/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

defined('_JEXEC') or die;

/**
 * View class for a list of SLogins
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class HtmlView extends BaseHtmlView
{
	protected $component;
	protected $module;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
        $this->loadHelper('slogin');
        $app = Factory::getApplication();
		$input = new Input;

		$this->component = Installer::parseXMLInstallFile(JPATH_COMPONENT.'/slogin.xml');

		$this->module = Installer::parseXMLInstallFile(JPATH_SITE.'/modules/mod_slogin/mod_slogin.xml');

        $pIds = array();

        $this->authPlugins = $this->get('AuthPlugins');

        foreach($this->authPlugins as $plugin)
        {
            $pIds[] = $plugin->extension_id;
        }

		$this->integrationPlugins = $this->get('IntegrationPlugins');

        $pluginNames = array();
        foreach($this->integrationPlugins as $plugin)
        {
            $pIds[] = $plugin->extension_id;
            $pluginNames[] = $plugin->name;
        }

        $comPlugins = $this->get('ComPlugins');

        if(count($comPlugins) && count($pluginNames))
        {
            for($i=0;$i<count($comPlugins);$i++)
            {
                if(in_array($comPlugins[$i]->element, $pluginNames))
                {
                    unset($comPlugins[$i]);
                }
            }
        }

        $this->comPlugins = $comPlugins;
		$this->config = ComponentHelper::getParams('com_slogin');

        $app->setUserState('com_plugins.edit.plugin.id', $pIds);

        $this->pieChartData = json_decode($this->get('PieChartData'));

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$doc = Factory::getApplication()->getDocument();
		// Remove old icon style - not needed in Joomla 5
		//include helper file
		require_once JPATH_COMPONENT.'/helpers/slogin.php';
		//actions example
		$canDo	= \SLoginHelper::getActions();
		
		//set title
		ToolbarHelper::title(Text::_('COM_SLOGIN'), 'users');
		
		//config
		if ($canDo->get('core.admin')) {
            ToolbarHelper::custom('repair', 'refresh', 'refresh', Text::_('COM_SLOGIN_REPAIR_TABLE'), false);
            ToolbarHelper::custom('clean', 'trash', 'trash', Text::_('COM_SLOGIN_CLEAN_TABLE'), false);
            ToolbarHelper::divider();
            ToolbarHelper::preferences('com_slogin');
		}
	}

}
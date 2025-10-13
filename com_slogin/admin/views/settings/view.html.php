<?php
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

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

//костыль для поддержки 2 и  3 джумлы
if(!class_exists('SLoginViewSettingsParent')){
    if(class_exists('JViewLegacy')){
        class SLoginViewSettingsParent extends JViewLegacy{}
    }
    else{
        class SLoginViewSettingsParent extends JView{}
    }
}

/**
 * View class for a list of SLogins
 *
 * @package		Joomla.Administrator
 * @subpackage	com_slogin
 */
class SLoginViewSettings extends SLoginViewSettingsParent
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

        $pieChartData = $this->get('PieChartData');
		$this->loadScripts($pieChartData);

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
		$doc->addStyleDeclaration('.icon-48-generic {background: url("../media/com_slogin/icon_48x48.png")}');
		//include helper file
		require_once JPATH_COMPONENT.'/helpers/slogin.php';
		//actions example
		$canDo	= SLoginHelper::getActions();
		
		//set title
		ToolbarHelper::title(Text::_('COM_SLOGIN'));
		
		//config
		if ($canDo->get('core.admin')) {
            ToolbarHelper::custom('repair', 'remove', 'remove', Text::_('COM_SLOGIN_REPAIR_TABLE'), false);
            ToolbarHelper::custom('clean', 'delete', 'delete', Text::_('COM_SLOGIN_CLEAN_TABLE'), false);
            ToolbarHelper::divider();
            ToolbarHelper::preferences('com_slogin');
		}
	}

	protected function loadScripts($pieChartData){
		$document = Factory::getApplication()->getDocument();
		$document->addScript(Uri::root().'libraries/amcharts/amcharts/amcharts.js');
		$document->addScript(Uri::root().'libraries/amcharts/amcharts/pie.js');

		$script = <<<SCRIPT
            var chart;
            var legend;

            var pie_chartData = $pieChartData;

            AmCharts.ready(function () {
                // PIE CHART
                //http://docs.amcharts.com/3/javascriptcharts/AmPieChart
                chart = new AmCharts.AmPieChart();

                // title of the chart
                //chart.addTitle("Visitors countries", 16);

                chart.dataProvider = pie_chartData;
                chart.titleField = "name";
                chart.valueField = "value";
                chart.colorField = "color";
                chart.sequencedAnimation = true;
                chart.startEffect = "easeOutSine";//easeOutSine, easeInSine, elastic, bounce
                chart.innerRadius = "30%";
                chart.startDuration = 1;
                chart.labelRadius = 20;
                chart.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                // the following two lines makes the chart 3D
                chart.depth3D = 10;
                chart.angle = 30;

                // LEGEND
                //http://docs.amcharts.com/3/javascriptcharts/AmLegend
                legend = new AmCharts.AmLegend();
                legend.align = "left";
                legend.position = "left";
                legend.markerType = "circle";
                chart.addLegend(legend);

                // WRITE
                chart.write("pie_chartdiv");
            });
SCRIPT;

		$document->addScriptDeclaration($script);
	}
}

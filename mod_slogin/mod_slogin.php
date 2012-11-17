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
//подключаем helper стандартного модуля авторизации, для ридеректа
require_once JPATH_BASE.'/modules/mod_login/helper.php';

$user = JFactory::getUser();
$doc = JFactory::getDocument();
$input = new JInput;

$type	= modLoginHelper::getType();
$return	= '&return=' . modLoginHelper::getReturnURL($params, $type);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$loadAfter = $params->get('load_after', 0);
$layout = $params->get('layout', 'default');
$layout = (strpos($layout, '_:') === false) ? $layout : substr($layout, 2);

$jsFramework = $params->get('js_framework', 'js');
$loadFramework = $params->get('load_framework', 0);

switch($jsFramework){
    case 'js':
        $doc->addScript(JURI::root().'modules/mod_slogin/media/slogin.js');
        break;
    case 'jq':
        if($loadFramework){
            $doc->addScript(JURI::root().'modules/mod_slogin/media/jquery-1.4.3.min.js');
        }
        $doc->addScript(JURI::root().'modules/mod_slogin/media/fancybox/jquery.mousewheel-3.0.4.pack.js');
        $doc->addScript(JURI::root().'modules/mod_slogin/media/fancybox/jquery.fancybox-1.3.4.pack.js');
        $doc->addStyleSheet(JURI::root().'modules/mod_slogin/media/fancybox/jquery.fancybox-1.3.4.css');

        $script = "
        jQuery(document).ready(function() {
                var parent =  jQuery('div.slogin-buttons');
                jQuery('a', parent).fancybox({
				'width'				: '75%',
				'height'			: '75%',
				'autoScale'			: true,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});
	    });";
        $doc->addScriptDeclaration($script);
        break;
    case 'moo':
        if($loadFramework){
            JHTML::_('behavior.mootools');
        }
        break;
}




$doc->addStyleSheet(JURI::root().'modules/mod_slogin/tmpl/'.$layout.'/slogin.css');

$dispatcher	= JDispatcher::getInstance();

JPluginHelper::importPlugin('slogin_auth');

$plugins = array();

$dispatcher->trigger('onCreateLink', array(&$plugins, $return));

if($loadAfter == 1){
    ob_start();
    require JModuleHelper::getLayoutPath('mod_slogin', $params->get('layout', 'default'));
    $content = ob_get_clean();
    ?>
    <div id="mod_slogin"></div>
    <script type="text/javascript">
        var sloginContent = '<?php echo $content?>';
        function sloginLoad() {
            document.getElementById('mod_slogin').innerHTML = sloginContent;
        }
        sloginLoad();
    </script>
    <?php
}
else{
    require JModuleHelper::getLayoutPath('mod_slogin', $params->get('layout', 'default'));
}



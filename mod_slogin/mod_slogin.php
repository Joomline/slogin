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

$doc = JFactory::getDocument();

$loadAfter = $params->get('load_after', 0);

$layout = $params->get('layout', 'default');

$layout = (strpos($layout, '_:') === false) ? $layout : substr($layout, 2);

if ($params->get('load_js') != '1') { $doc->addScript(JURI::root().'modules/mod_slogin/media/slogin.js'); }

if ($params->get('load_css') != '1') { $doc->addStyleSheet(JURI::root().'modules/mod_slogin/tmpl/'.$layout.'/slogin.css'); }

$type	= modLoginHelper::getType();

$return	= modLoginHelper::getReturnURL($params, $type);

if($loadAfter == 1 && $type != 'logout'){
    ?>
    <div id="mod_slogin">
        <img src="/modules/mod_slogin/media/ajax-loader.gif"/>
    </div>
    <script type="text/javascript">
        var sloginReturnUri = '<?php echo base64_encode($return);?>';
        SLogin.addListener(window, 'load', function () {
            SLogin.loadModuleAjax();
        });
    </script>
    <?php
}
else{
    $user = JFactory::getUser();
    $input = new JInput;

    $callbackUrl = '&return=' . $return;

    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

    $dispatcher	= JDispatcher::getInstance();

    JPluginHelper::importPlugin('slogin_auth');

    $plugins = array();

    $dispatcher->trigger('onCreateLink', array(&$plugins, $callbackUrl));

    require JModuleHelper::getLayoutPath('mod_slogin', $params->get('layout', 'default'));
}



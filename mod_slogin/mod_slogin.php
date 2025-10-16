<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2016. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;

// Load helper for standard login module for redirect functionality
$LoginHelperEnabled = false;
if (is_file(JPATH_BASE.'/modules/mod_login/src/Helper/LoginHelper.php')) {
	require_once JPATH_BASE.'/modules/mod_login/src/Helper/LoginHelper.php';
	$LoginHelperEnabled = true;
} else if(is_file(JPATH_BASE.'/modules/mod_login/helper.php')) {
	require_once JPATH_BASE.'/modules/mod_login/helper.php';
}
require_once dirname(__FILE__).'/helper.php';

$doc = Factory::getDocument();

//$loadAfter = $params->get('load_after', 0);

$layout = $params->get('layout', 'default');

$layout = (strpos($layout, '_:') === false) ? $layout : substr($layout, 2);

if ($params->get('load_js') != '1') { $doc->addScript(Uri::root().'media/com_slogin/slogin.min.js?v=4'); }

if ($params->get('load_css') != '1') { $doc->addStyleSheet(Uri::root().'media/com_slogin/comslogin.min.css?v=4'); }

if ($LoginHelperEnabled) {
	$type	= \Joomla\Module\Login\Site\Helper\LoginHelper::getType();
	$return	= \Joomla\Module\Login\Site\Helper\LoginHelper::getReturnURL($params, $type);
} else {
	$type	= modLoginHelper::getType();
	$return	= modLoginHelper::getReturnURL($params, $type);
}


$input = Factory::getApplication()->input;
$task = $input->getCmd('task', '');
$option = $input->getCmd('option', '');

$fusionProviders = null;
$show_fusion_form = $params->get('show_fusion_form', 0);
if($show_fusion_form)
{
    list($attachedProviders, $unattachedProviders) = modSLoginHelper::getFusionProviders();
    Factory::getLanguage()->load('com_slogin');
}

if(!($option == 'com_slogin' && ($task == 'auth' || $task == 'check')))
{
    Factory::getApplication()->setUserState('com_slogin.return_url', $return);
}


/*if($loadAfter == 1 && $type != 'logout'){
    ?>
    <div id="mod_slogin">
        <img src="/modules/mod_slogin/media/ajax-loader.gif" alt="Loader"/>
    </div>
    <script type="text/javascript">
        SLogin.addListener(window, 'load', function () {
            SLogin.loadModuleAjax();
        });
    </script>
    <?php
}*/
//else{
    $user = Factory::getUser();
    $input = Factory::getApplication()->input;
    $app = Factory::getApplication();
    $menu = $app->getMenu();

    // Найдем Itemid для представления fusion
    $fusionItemId = 0;
    $menuItems = $menu->getItems('component', 'com_slogin');
    if ($menuItems) {
        foreach ($menuItems as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'fusion') {
                $fusionItemId = $item->id;
                break;
            }
        }
    }

    $callbackUrl = '';

    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''));

    PluginHelper::importPlugin('slogin_auth');

    $plugins = array();
    $config = ComponentHelper::getParams('com_slogin');
	Factory::getApplication()->triggerEvent('onCreateSloginLink', array(&$plugins, $callbackUrl));

    $profileLink = $avatar = '';
    if(PluginHelper::isEnabled('slogin_integration', 'profile') && $user->id > 0){
        require_once JPATH_BASE.'/plugins/slogin_integration/profile/helper.php';
        $profile = plgProfileHelper::getProfile($user->id);
        $avatar = isset($profile->avatar) ? $profile->avatar : '';
        $profileLink = isset($profile->social_profile_link) ? $profile->social_profile_link : '';
    }
    else if(PluginHelper::isEnabled('slogin_integration', 'slogin_avatar') && $user->id > 0){
        require_once JPATH_BASE.'/plugins/slogin_integration/slogin_avatar/helper.php';
        $path = Slogin_avatarHelper::getavatar($user->id);
        if(!empty($path['photo_src'])){
            $avatar = $path['photo_src'];
            if(mb_strpos($avatar, '/') !== 0)
                $avatar = '/'.$avatar;
        }
		$profileLink = isset($path['profile']) ? $path['profile'] : '';
    }

    require ModuleHelper::getLayoutPath('mod_slogin', $params->get('layout', 'default'));
//}

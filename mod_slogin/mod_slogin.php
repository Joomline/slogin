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
require_once dirname(__FILE__).'/helper.php';

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

    $dispatcher->trigger('onCreateSloginLink', array(&$plugins, $callbackUrl));
	
	$allow = modSLoginHelper::getalw($params);
	
    $profileLink = $avatar = '';
    if(JPluginHelper::isEnabled('slogin_integration', 'profile') && $user->id > 0){
        require_once JPATH_BASE.'/plugins/slogin_integration/profile/helper.php';
        $profile = plgProfileHelper::getProfile($user->id);
        $avatar = isset($profile->avatar) ? $profile->avatar : '';
        $profileLink = isset($profile->social_profile_link) ? $profile->social_profile_link : '';
    }
    else if(JPluginHelper::isEnabled('slogin_integration', 'slogin_avatar') && $user->id > 0){
        require_once JPATH_BASE.'/plugins/slogin_integration/slogin_avatar/helper.php';
        $path = Slogin_avatarHelper::getavatar($user->id);
        if(!empty($path['photo_src'])){
            $avatar = $path['photo_src'];
            if(JString::strpos($avatar, '/') !== 0)
                $avatar = '/'.$avatar;
        }
		$profileLink = isset($path['profile']) ? $path['profile'] : '';
    }

    require JModuleHelper::getLayoutPath('mod_slogin', $params->get('layout', 'default'));
}
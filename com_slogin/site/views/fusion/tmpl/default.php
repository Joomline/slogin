<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.keepalive');
$doc = Factory::getDocument();
$doc->addStyleSheet(Uri::root().'media/com_slogin/comslogin.min.css?v=4');

?>
<div class="login">

    <h1>
        <?php echo Text::_('COM_SLOGIN_FUSION'); ?>
        <?php echo $this->user->get('username'); ?>.
    </h1>

    <div class="login-description">
        <?php echo Text::_('COM_SLOGIN_FUSION_DESC'); ?>
    </div>
    <?php if ($this->user->get('id') == 0): ?>

    <div class="login">
        <form action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>" method="post">

            <fieldset>
                <?php foreach ($this->form->getFieldset('credentials') as $field): ?>
                <?php if (!$field->hidden): ?>
                    <div class="login-fields"><?php echo $field->label; ?>
                        <?php echo $field->input; ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
                <div class="login-fields">
                    <label id="remember-lbl" for="remember"><?php echo Text::_('JGLOBAL_REMEMBER_ME') ?></label>
                    <input id="remember" type="checkbox" name="remember" class="inputbox" value="yes"  alt="<?php echo Text::_('JGLOBAL_REMEMBER_ME') ?>" />
                </div>
                <?php endif; ?>
                <button type="submit" class="button btn btnslogin"><?php echo Text::_('JLOGIN'); ?></button>
                <input type="hidden" name="return" value="<?php echo base64_encode(Route::_('index.php?option=com_slogin&view=fusion')); ?>" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </fieldset>
        </form>
    </div>

    <?php else : ?>

    <h2><?php echo Text::_('COM_SLOGIN_ATTACH_PROVIDERS')?></h2>
    <div id="slogin-buttons-attach-component" class="slogin-buttons">
        <?php
        foreach($this->attachedProviders as $provider) :

            if($provider['plugin_name'] == 'ulogin')
                continue;

            $linkParams = '';
            if(isset($provider['params'])){
                foreach($provider['params'] as $k => $v){
                    $linkParams .= ' ' . $k . '="' . $v . '"';
                }
            }
            ?>
        <a <?php echo $linkParams;?> href="<?php echo Route::_($provider['link']);?>" title="<?php echo $provider['plugin_title'];?>">
            <span class="<?php echo $provider['class'];?>">&nbsp;</span>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="slogin-clear"></div>
    <h2><?php echo Text::_('COM_SLOGIN_DETACH_PROVIDERS')?></h2>
    <div id="slogin-buttons-unattach-component" class="slogin-buttons">
        <?php foreach($this->unattachedProviders as $provider) : ?>
        <a href="<?php echo Route::_('index.php?option=com_slogin&task=detach_provider&plugin='.$provider['plugin_name']);?>" title="<?php echo $provider['plugin_title'];?>">
            <span class="<?php echo $provider['class'];?>">&nbsp;</span>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="slogin-clear"></div>

    <?php endif; ?>
</div>
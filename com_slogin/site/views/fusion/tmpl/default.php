<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root().'media/com_slogin/comslogin.css')

?>
<div class="login">

    <h1>
        <?php echo JText::_('COM_SLOGIN_FUSION'); ?>
        <?php echo $this->user->get('username'); ?>.
    </h1>

    <div class="login-description">
        <?php echo JText::_('COM_SLOGIN_FUSION_DESC'); ?>
    </div>
    <?php if ($this->user->get('id') == 0): ?>

    <div class="login">
        <form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post">

            <fieldset>
                <?php foreach ($this->form->getFieldset('credentials') as $field): ?>
                <?php if (!$field->hidden): ?>
                    <div class="login-fields"><?php echo $field->label; ?>
                        <?php echo $field->input; ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                <div class="login-fields">
                    <label id="remember-lbl" for="remember"><?php echo JText::_('JGLOBAL_REMEMBER_ME') ?></label>
                    <input id="remember" type="checkbox" name="remember" class="inputbox" value="yes"  alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" />
                </div>
                <?php endif; ?>
                <button type="submit" class="button"><?php echo JText::_('JLOGIN'); ?></button>
                <input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php?option=com_slogin&view=fusion')); ?>" />
                <?php echo JHtml::_('form.token'); ?>
            </fieldset>
        </form>
    </div>

    <?php else : ?>

    <h2><?php echo JText::_('COM_SLOGIN_ATTACH_PROVIDERS')?></h2>
    <div id="slogin-buttons" class="slogin-buttons">
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
        <a <?php echo $linkParams;?> href="<?php echo JRoute::_($provider['link']);?>">
            <span class="<?php echo $provider['class'];?>">&nbsp;</span>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="slogin-clear"></div>
    <h2><?php echo JText::_('COM_SLOGIN_DETACH_PROVIDERS')?></h2>
    <div id="slogin-buttons" class="slogin-buttons">
        <?php foreach($this->unattachedProviders as $provider) : ?>
        <a href="<?php echo JRoute::_('index.php?option=com_slogin&task=detach_provider&plugin='.$provider['plugin_name']);?>">
            <span class="<?php echo $provider['class'];?>">&nbsp;</span>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="slogin-clear"></div>

    <?php endif; ?>
</div>
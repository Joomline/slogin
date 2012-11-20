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
?>
<div class="login">

    <h1>
        <?php echo JText::_('COM_SLOGIN_LINKING'); ?>
    </h1>

    <div class="login-description">
        <?php echo JText::sprintf('COM_SLOGIN_LINKING_DESC', $this->email); ?>
    </div>

    <form action="<?php echo JRoute::_('index.php?option=com_slogin&task=join_mail'); ?>" method="post">
        <fieldset>
            <div class="login-fields">
                <label id="username-lbl" for="username" class=" required">
                    <?php echo JText::_('COM_SLOGIN_USERNAME_LABEL'); ?>
                    <span class="star">&nbsp;*</span>
                </label>
                <input type="text" name="username" id="username" value="" class="validate-username required" size="25">
            </div>
            <div class="login-fields">
                <label id="password-lbl" for="password" class=" required">
                    <?php echo JText::_('COM_SLOGIN_PASS'); ?>
                    <span class="star">&nbsp;*</span>
                </label>
                <input type="password" name="password" id="password" value=""
                       class="validate-password required" size="25">
            </div>
            <button type="submit" class="button"><?php echo JText::_('COM_SLOGIN_JOIN'); ?></button>
            <a class="button" href="<?php echo $this->params->get('login_redirect_url', $this->form->getValue('return')) ?>"><?php echo JText::_('COM_SLOGIN_CHANCEL'); ?></a>
            <input type="hidden" name="return"
                   value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>"/>
            <input type="hidden" name="user_id" value="<?php echo $this->id; ?>"/>
            <input type="hidden" name="provider" value="<?php echo $this->provider; ?>"/>
            <input type="hidden" name="slogin_id" value="<?php echo $this->slogin_id; ?>"/>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </form>
</div>
<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root().'media/com_slogin/comslogin.min.css')
?>
<div class="login">

    <h1>
        <?php echo JText::_('COM_SLOGIN_COMPARISON'); ?>
    </h1>

    <div class="login-description">
        <?php echo JText::sprintf('COM_SLOGIN_COMPARISON_DESC', $this->email); ?>
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
            <button type="submit" class="button btn btnslogin"><?php echo JText::_('COM_SLOGIN_JOIN'); ?></button>
            <input type="hidden" name="return"
                   value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>"/>
            <input type="hidden" name="user_id" value="<?php echo $this->id; ?>"/>
            <input type="hidden" name="provider" value="<?php echo $this->provider; ?>"/>
            <input type="hidden" name="slogin_id" value="<?php echo $this->slogin_id; ?>"/>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </form>

    <?php echo JText::_('COM_SLOGIN_LOST_PASS'); ?>

    <form id="user-registration" action="<?php echo JRoute::_('/index.php?option=com_users&task=reset.request') ?>" method="post"
          class="form-validate">
        <p><?php echo JText::_('COM_SLOGIN_LOST_PASS_DESC'); ?></p>
        <fieldset>
            <input type="text" name="jform[email]" id="jform_email" value="<?php echo $this->email ?>" readonly="readonly" class="validate-email required invalid" size="30" aria-required="true" required="required" aria-invalid="true" />
        </fieldset>
        <button type="submit" class="validate btn btnslogin"><?php echo JText::_('COM_SLOGIN_SUBMIT'); ?></button>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
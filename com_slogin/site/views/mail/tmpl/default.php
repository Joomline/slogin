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
JHTML::_('behavior.formvalidation');

?>
<div class="login">

    <h1>
        <?php echo JText::_('COM_SLOGIN_PROVIDER_DATA'); ?>
    </h1>

    <div class="login-description">
        <?php echo JText::_('COM_SLOGIN_BAD_EMAIL'); ?>
    </div>

    <div class="login">
        <form action="<?php echo $this->action; ?>" method="post" class="form-validate">
            <fieldset>
                <div class="login-fields">
                    <label id="first_name-lbl" for="first_name" class=" required">
                        <?php echo JText::_('COM_SLOGIN_NAME')?>
                    </label>
                    <input type="text" name="first_name" id="first_name"
                           value="<?php echo $this->first_name; ?>" readonly="readonly" size="25">
                </div>
                <div class="login-fields">
                    <label id="last_name-lbl" for="last_name" class=" required">
                        <?php echo JText::_('COM_SLOGIN_FAMILY')?>
                    </label>
                    <input type="text" name="last_name" id="last_name"
                           value="<?php echo $this->last_name; ?>" readonly="readonly" size="25">
                </div>
                <div class="login-fields">
                    <label id="email-lbl" for="email" class="validate-email required">
                        <?php echo JText::_('COM_SLOGIN_MAIL')?>
                    </label>
                    <input type="text" name="email" id="email"
                           value="<?php echo $this->email; ?>" class="validate-email required" size="25">
                </div>

                <button type="submit" class="button"><?php echo JText::_('COM_SLOGIN_SUBMIT'); ?></button>

                <input type="hidden" name="provider" value="<?php echo $this->provider; ?>">
                <input type="hidden" name="slogin_id" value="<?php echo $this->slogin_id; ?>">
                <?php echo JHtml::_('form.token'); ?>
            </fieldset>
        </form>
    </div>
</div>
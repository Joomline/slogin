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



    <div class="login-description" id="slogin_error_mesages"></div>

    <div class="login">
        <form action="<?php echo $this->action; ?>" method="post" class="form-validate" id="sloginUserForm">
            <fieldset>
                <div class="login-fields">
                    <label id="name-lbl" for="name" class=" required">
                        <?php echo JText::_('COM_SLOGIN_NAME')?>
                    </label>
                    <input type="text" name="name" id="name"
                           value="<?php echo $this->name; ?>" size="25"/>
                </div>
                <div class="login-fields">
                    <label id="username-lbl" for="username" class=" required">
                        <?php echo JText::_('COM_SLOGIN_USERNAME_LABEL')?>
                    </label>
                    <input type="text" name="username" id="username"
                           value="<?php echo $this->username; ?>" size="25"/>
                </div>
                <div class="login-fields">
                    <label id="email-lbl" for="email" class="required">
                        <?php echo JText::_('COM_SLOGIN_MAIL')?>
                    </label>
                    <input type="text" name="email" id="email"
                           value="<?php echo $this->email; ?>" class="required" size="25">
                </div>

                <button type="submit" class="button"><?php echo JText::_('COM_SLOGIN_SUBMIT'); ?></button>

                <?php echo JHtml::_('form.token'); ?>
            </fieldset>
        </form>
    </div>
</div>
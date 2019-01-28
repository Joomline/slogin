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
JHTML::_('behavior.formvalidation');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root().'media/com_slogin/comslogin.min.css');
if($this->user->id == 0){
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
                    <input type="text" name="name" id="name" class="validate-name required"
                           value="<?php echo $this->name; ?>" size="25"/>
                </div>
                <div class="login-fields">
                    <label id="username-lbl" for="username" class=" required">
                        <?php echo JText::_('COM_SLOGIN_USERNAME_LABEL')?>
                    </label>
                    <input type="text" name="username" id="username" class="validate-username required"
                           value="<?php echo $this->username; ?>" size="25"/>
                </div>
                <div class="login-fields">
                    <label id="email-lbl" for="email" class="required">
                        <?php echo JText::_('COM_SLOGIN_MAIL')?>
                    </label>
                    <input type="text" name="email" id="email"
                           value="<?php echo $this->email; ?>" class="validate-email required" size="25">
                </div>

                <input type="submit" class="button validate btn btnslogin" value="<?php echo JText::_('COM_SLOGIN_SUBMIT'); ?>"/>

                <?php echo JHtml::_('form.token'); ?>
            </fieldset>
        </form>
    </div>
</div>
<?php } ?>
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

?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task == 'repair' || task == 'clean') {
            if (confirm("<?php echo JText::_('COM_SLOGIN_CONFIRM'); ?>")){
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
    }
</script>

<div><?php echo JText::_('COM_SLOGIN_XML_DESCRIPTION'); ?></div>
<ul>
	<li><?php echo JText::sprintf('COM_SLOGIN_COMPONENT_VERSION', $this->component['version']); ?></li>
	<li><?php echo JText::sprintf('COM_SLOGIN_MODULE_VERSION', $this->module['version']); ?></li>
</ul>
<?php $date = JFactory::getDate()->format('Y') > '2012' ? '2012 - '. JFactory::getDate()->format('Y') : '2012'?>
<div>
    &copy; <?php echo $date;?> SmokerMan, Arkadiy, Joomline
    <?php echo JText::_('COM_SLOGIN_HELP'); ?>
</div>
<p></p>
<?php echo JText::_('COM_SLOGIN_DONITE'); ?>

<form
        action="<?php echo JRoute::_('index.php?option=com_slogin'); ?>"
        method="post"
        name="adminForm"
        id="adminForm"
        >
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
</form>
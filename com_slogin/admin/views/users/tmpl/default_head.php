<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="5%">
        <?php echo JText::_('ID'); ?>
	</th>
	<th width="5%">
        <?php if (version_compare(JVERSION, '3.0', 'ge')) : ?>
            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
        <?php else : ?>
            <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
        <?php endif; ?>
	</th>
	<th width="10%">
        <?php echo JText::_('COM_SLOGIN_USER_ID'); ?>
	</th>
	<th width="40%">
        <?php echo JText::_('COM_SLOGIN_USER_NAME'); ?>
	</th>
	<th width="20%">
        <?php echo JText::_('COM_SLOGIN_USER_USERNAME'); ?>
	</th>
	<th width="10%">
        <?php echo JText::_('COM_SLOGIN_PROVIDER'); ?>
	</th>
	<th width="10%">
        <?php echo JText::_('COM_SLOGIN_SLOGINID'); ?>
	</th>
</tr>

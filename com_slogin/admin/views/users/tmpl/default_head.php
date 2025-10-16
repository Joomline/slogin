<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<tr>
	<th scope="col" class="w-5 text-center d-none d-md-table-cell">
        <?php echo Text::_('ID'); ?>
	</th>
	<th scope="col" class="w-1 text-center">
        <?php echo HTMLHelper::_('grid.checkall'); ?>
	</th>
	<th scope="col" class="w-10 d-none d-md-table-cell">
        <?php echo Text::_('COM_SLOGIN_USER_ID'); ?>
	</th>
	<th scope="col" class="w-40">
        <?php echo Text::_('COM_SLOGIN_USER_NAME'); ?>
	</th>
	<th scope="col" class="w-20">
        <?php echo Text::_('COM_SLOGIN_USER_USERNAME'); ?>
	</th>
	<th scope="col" class="w-15 d-none d-md-table-cell">
        <?php echo Text::_('COM_SLOGIN_PROVIDER'); ?>
	</th>
	<th scope="col" class="w-10 d-none d-md-table-cell">
        <?php echo Text::_('COM_SLOGIN_SLOGINID'); ?>
	</th>
</tr>

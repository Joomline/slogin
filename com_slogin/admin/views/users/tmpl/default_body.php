<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\HTML\HTMLHelper;
?>
<?php foreach($this->items as $i => $item): ?>
	<tr>
		<td class="text-center d-none d-md-table-cell">
			<?php echo $item->id; ?>
		</td>
		<td class="text-center">
			<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->id); ?>
		</td>
		<td class="d-none d-md-table-cell">
			<?php echo $item->user_id; ?>
		</td>
		<td>
			<?php echo $item->name; ?>
		</td>
		<td>
			<?php echo $item->username; ?>
		</td>
		<td class="d-none d-md-table-cell">
			<?php echo $item->provider; ?>
		</td>
		<td class="d-none d-md-table-cell">
			<?php echo $item->slogin_id; ?>
		</td>
	</tr>
<?php endforeach; ?>

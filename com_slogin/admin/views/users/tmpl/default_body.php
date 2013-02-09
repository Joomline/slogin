<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php echo $item->user_id; ?>
		</td>
		<td>
			<?php echo $item->name; ?>
		</td>
		<td>
			<?php echo $item->username; ?>
		</td>
		<td>
			<?php echo $item->provider; ?>
		</td>
		<td>
			<?php echo $item->slogin_id; ?>
		</td>
	</tr>
<?php endforeach; ?>

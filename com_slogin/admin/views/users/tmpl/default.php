<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior

	if ( version_compare( JVERSION, '3.0', '<' ) == 1) {             
		JHtml::_('behavior.tooltip');	
	 }
	 else{
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.multiselect');
		JHtml::_('dropdown.init');
		JHtml::_('formbehavior.chosen', 'select');
	 }
?>
<form
        action="<?php echo JRoute::_('index.php?option=com_slogin&view=users'); ?>"
        method="post"
        name="adminForm"
        id="adminForm"
        >
    <?php echo $this->loadTemplate('filter');?>
    <div class="clr"> </div>
	<table class="adminlist table table-striped">
		<thead><?php echo $this->loadTemplate('head');?></thead>
		<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
		<tbody><?php echo $this->loadTemplate('body');?></tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

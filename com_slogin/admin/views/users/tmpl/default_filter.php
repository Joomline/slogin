<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

if ( version_compare( JVERSION, '3.0', '<' ) == 1) {             
?>
<fieldset id="filter-bar">
    <div class="filter-search fltlft">
        <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />

        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
        <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    </div>
    <div class="filter-select fltrt">

        <select name="filter_provider" class="inputbox" onchange="this.form.submit()">
            <option value="">- <?php echo JText::_('COM_SLOGIN_SELECT_PROVIDER');?> -</option>
            <?php echo JHtml::_('select.options', $this->providers, 'value', 'text', $this->state->get('filter.provider'));?>
        </select>
    </div>
</fieldset>
<?php } else{ ?>
<fieldset id="filter-bar">
    <div class="filter-search fltlft">
		<div class="filter-search-events btn-group pull-left">
			<input type="text" class="hasTooltip" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
		</div>
		<div class="btn-group pull-left hidden-phone">
			<button type="submit" class="btn hasTooltip"><i class="icon-search"></i></button>
			<button type="button" class="btn hasTooltip" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		 </div>
		<div class="filter-search-events btn-group pull-right">
		<select name="filter_provider" class="inputbox" onchange="this.form.submit()">
            <option value="">- <?php echo JText::_('COM_SLOGIN_SELECT_PROVIDER');?> -</option>
            <?php echo JHtml::_('select.options', $this->providers, 'value', 'text', $this->state->get('filter.provider'));?>
        </select>
		</div>
    </div>
    <div class="filter-select fltrt">
    </div>
</fieldset>
 <?php } ?>
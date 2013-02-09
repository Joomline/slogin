<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
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


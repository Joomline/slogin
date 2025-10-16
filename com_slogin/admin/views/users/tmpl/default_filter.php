<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="js-stools">
    <div class="js-stools-container-bar">
        <div class="btn-toolbar justify-content-end">
            <div class="btn-group me-2">
                <div class="input-group">
                    <input type="text" name="filter_search" id="filter_search" 
                           class="form-control" placeholder="<?php echo Text::_('JSEARCH_FILTER_LABEL'); ?>" 
                           value="<?php echo $this->escape($this->state->get('filter.search')); ?>" 
                           title="<?php echo Text::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-search" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.submit();">
                            <span class="icon-times" aria-hidden="true"></span>
                        </button>
                    </span>
                </div>
            </div>
            <div class="btn-group">
                <div class="input-group">
                    <select name="filter_provider" class="form-select" onchange="this.form.submit()">
                        <option value="">- <?php echo Text::_('COM_SLOGIN_SELECT_PROVIDER');?> -</option>
                        <?php echo HTMLHelper::_('select.options', $this->providers, 'value', 'text', $this->state->get('filter.provider'));?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.tooltip');
?>
<form
        action="<?php echo Route::_('index.php?option=com_slogin&view=users'); ?>"
        method="post"
        name="adminForm"
        id="adminForm"
        class="form-validate"
        >
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo $this->loadTemplate('filter');?>
                
                <div class="clearfix mb-3"></div>
                
                <div class="table-responsive mb-3">
                    <table class="table table-striped table-hover" id="sloginUsersList">
                        <thead><?php echo $this->loadTemplate('head');?></thead>
                        <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
                        <tbody><?php echo $this->loadTemplate('body');?></tbody>
                    </table>
                </div>
                
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

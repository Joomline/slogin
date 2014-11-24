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
$sess = JFactory::getSession();
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

<div class="span12">
    <div class="row">
        <div class="span7">
             <h2><?php echo JText::_('COM_SLOGIN_USER_STRUCTURE'); ?></h2>
            <div id="pie_chartdiv" style="width:100%; height:350px;"></div>
        </div>
        <div class="span5">
            <h2><?php echo JText::_('COM_SLOGIN'); ?></h2>
            <p><?php echo JText::_('COM_SLOGIN_XML_DESCRIPTION'); ?></p>
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
        </div>
    </div>
    <div class="row">
        	<?php	if ( version_compare( JVERSION, '3.0', '<' ) == 1) { ?>	
				<div class="width-50 fltlft">
			<?php } else{ ?>
				<div class="span6">
			<?php } ?>
            <h2><?php echo JText::_('COM_SLOGIN_AUTH_PLUGINS'); ?></h2>
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo JText::_('COM_SLOGIN_PLUGIN_NAME'); ?></th>
                    <th><?php echo JText::_('COM_SLOGIN_PLUGIN_PUBLISHED'); ?></th>
                    <th><?php echo JText::_('COM_SLOGIN_PLUGIN_SET'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach($this->authPlugins as $plugin) : ?>
                    <tr>
                        <td><?php echo $i ?></td>
                        <td>
                            <a target="_blank" href="<?php echo JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id); ?>">
                                <?php echo $plugin->name ?>
                            </a>
                        </td>
                        <td><?php echo $plugin->enabled ? JText::_('JYES') : JText::_('JNO'); ?></td>
                        <td><?php echo $plugin->set ? JText::_('JYES') : JText::_('JNO'); ?></td>
                    </tr>
                <?php $i++; endforeach; ?>
                </tbody>
            </table>
        </div>
        	<?php	if ( version_compare( JVERSION, '3.0', '<' ) == 1) { ?>	
				<div class="width-50 fltlft">
			<?php } else{ ?>
				<div class="span6">
			<?php } ?>
            <h2><?php echo JText::_('COM_SLOGIN_INTEGRATION_PLUGINS'); ?></h2>
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo JText::_('COM_SLOGIN_PLUGIN_NAME'); ?></th>
                    <th><?php echo JText::_('COM_SLOGIN_PLUGIN_PUBLISHED'); ?></th>
                    <th><?php echo JText::_('COM_SLOGIN_PLUGIN_INSTALLED'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach($this->integrationPlugins as $plugin) : ?>
                    <tr>
                        <td><?php echo $i ?></td>
                        <td>
                            <a target="_blank" href="<?php echo JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id); ?>">
                                <?php echo $plugin->name ?>
                            </a>
                        </td>
                        <td><?php echo $plugin->enabled ? JText::_('JYES') : JText::_('JNO'); ?></td>
                        <td><?php echo JText::_('JYES'); ?></td>
                    </tr>
                    <?php $i++; endforeach; ?>
                <?php foreach($this->comPlugins as $plugin) : ?>
                    <tr>
                        <td><?php echo $i ?></td>
                        <td><?php echo $plugin->name ?></td>
                        <td><?php echo $plugin->enabled ? JText::_('JYES') : JText::_('JNO'); ?></td>
                        <td>
                            <a target="_blank" href="<?php echo $plugin->link; ?>">
                                <?php echo JText::_('COM_SLOGIN_PLUGIN_BUY'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php $i++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>



    <div class="row">
        <div class="span2"></div>
        <div class="span3"></div>
        <div class="span4"></div>
    </div>
    <div class="row show-grid">
        <div class="span4" data-original-title="" title=""></div>
        <div class="span5"></div>
    </div>
    <div class="row show-grid">
        <div class="span9"></div>
    </div>
<div>


<?php

/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$sess = Factory::getSession();
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task == 'repair' || task == 'clean') {
            if (confirm("<?php echo Text::_('COM_SLOGIN_CONFIRM'); ?>")){
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
    }
</script>
<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-8">
                <h2 class="text-center"><?php echo Text::_('COM_SLOGIN_USER_STRUCTURE'); ?></h2>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col"><?php echo Text::_('COM_SLOGIN_PROVIDER_NAME'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_SLOGIN_PROVIDER_USERS'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_SLOGIN_PROVIDER_PERCENT'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($this->pieChartData)) : ?>
                            <?php $i = 1; foreach($this->pieChartData as $provider) : ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $provider->name; ?></td>
                                    <td><?php echo $provider->value; ?></td>
                                    <td><?php echo $provider->percents; ?>%</td>
                                </tr>
                            <?php $i++; endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center"><?php echo Text::_('COM_SLOGIN_NO_DATA'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-4">
                <h2><?php echo Text::_('COM_SLOGIN'); ?></h2>
                <p><?php echo Text::_('COM_SLOGIN_XML_DESCRIPTION'); ?></p>
                <ul class="list-unstyled">
                    <li><?php echo Text::sprintf('COM_SLOGIN_COMPONENT_VERSION', $this->component['version']); ?></li>
                    <li><?php echo Text::sprintf('COM_SLOGIN_MODULE_VERSION', $this->module['version']); ?></li>
                </ul>
                <?php $date = Factory::getDate()->format('Y') > '2012' ? '2012 - '. Factory::getDate()->format('Y') : '2012'?>
                <div>
                    <p>&copy; <?php echo $date;?> JoomLine</p>
                    <p><?php echo Text::_('COM_SLOGIN_HELP'); ?></p>
                </div>
               
                <?php echo Text::_('COM_SLOGIN_DONITE'); ?>

                <form
                    action="<?php echo Route::_('index.php?option=com_slogin'); ?>"
                    method="post"
                    name="adminForm"
                    id="adminForm"
                    >
                    <input type="hidden" name="task" value="" />
                    <input type="hidden" name="boxchecked" value="0" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <h2><?php echo Text::_('COM_SLOGIN_AUTH_PLUGINS'); ?></h2>
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col"><?php echo Text::_('COM_SLOGIN_PLUGIN_NAME'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_SLOGIN_PLUGIN_PUBLISHED'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_SLOGIN_PLUGIN_SET'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
			        <?php $i = 1; foreach($this->authPlugins as $plugin) : ?>
                        <tr>
                            <td><?php echo $i ?></td>
                            <td>
                                <a target="_blank" href="<?php echo Route::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id); ?>">
							        <?php echo $plugin->name ?>
                                </a>
                            </td>
                            <td><?php echo $plugin->enabled ? Text::_('JYES') : Text::_('JNO'); ?></td>
                            <td><?php echo $plugin->set ? Text::_('JYES') : Text::_('JNO'); ?></td>
                        </tr>
				        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h2><?php echo Text::_('COM_SLOGIN_INTEGRATION_PLUGINS'); ?></h2>
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col"><?php echo Text::_('COM_SLOGIN_PLUGIN_NAME'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_SLOGIN_PLUGIN_PUBLISHED'); ?></th>
                        <th scope="col"><?php echo Text::_('COM_SLOGIN_PLUGIN_INSTALLED'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; foreach($this->integrationPlugins as $plugin) : ?>
                        <tr>
                            <td><?php echo $i ?></td>
                            <td>
                                <a target="_blank" href="<?php echo Route::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id); ?>">
                                    <?php echo $plugin->name ?>
                                </a>
                            </td>
                            <td><?php echo $plugin->enabled ? Text::_('JYES') : Text::_('JNO'); ?></td>
                            <td><?php echo Text::_('JYES'); ?></td>
                        </tr>
                        <?php $i++; endforeach; ?>
                    <?php foreach($this->comPlugins as $plugin) : ?>
                        <tr>
                            <td><?php echo $i ?></td>
                            <td><?php echo $plugin->name ?></td>
                            <td><?php echo !empty($plugin->enabled) ? Text::_('JYES') : Text::_('JNO'); ?></td>
                            <td>
                                <a target="_blank" href="<?php echo $plugin->link; ?>" class="btn btn-sm btn-primary">
                                    <?php echo Text::_('COM_SLOGIN_PLUGIN_BUY'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
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

?>

<div><?php echo JText::_('COM_SLOGIN_XML_DESCRIPTION'); ?></div>
<ul>
	<li><?php echo JText::sprintf('COM_SLOGIN_COMPONENT_VERSION', $this->component['version']); ?></li>
	<li><?php echo JText::sprintf('COM_SLOGIN_MODULE_VERSION', $this->module['version']); ?></li>
</ul>
<?php $date = JFactory::getDate()->format('Y') > '2012' ? '2012 - '. JFactory::getDate()->format('Y') : '2012'?>
<div>&copy; <?php echo $date;?> </div>
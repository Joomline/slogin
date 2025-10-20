<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.slogin
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomline\Plugin\Authentication\Slogin\Extension\Slogin;

return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->set(
			PluginInterface::class,
			function (Container $container)
			{
				$plugin     = new Slogin(
					$container->get(DispatcherInterface::class),
					(array) PluginHelper::getPlugin('authentication', 'slogin')
				);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};
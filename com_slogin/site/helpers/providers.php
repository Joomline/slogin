<?php
/**
 * SLogin
 *
 * @version 	5.0.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2025. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

class SloginProvidersHelper
{
    static function getServiceProviders()
    {
        return array(
            'bitbucket',
            'facebook',
            'github',
            'google',
            'instagram',
            'linkedin',
            'live',
            'mail',
            'odnoklassniki',
            'twitter',
            'vkontakte',
            'wordpress',
            'yahoo',
            'yandex'
        );
    }

    static function compare($a, $b)
    {
        if ($a['order'] == $b['order'])
        {
            return 0;
        }
        return ($a['order'] < $b['order']) ? -1 : 1;
    }

    static function getOrderedEnabledServiceProviders()
    {
        $config = ComponentHelper::getParams('com_slogin');
        $providers = self::getServiceProviders();
        $enabled = array();
        $return = array();

        if(count($providers))
        {
            foreach($providers as $provider)
            {
                $enabledName = $provider.'_enabled';
                $orderName = $provider.'_order';
                if($config->get($enabledName, 1))
                {
                    $enabled[] = array('provider' => $provider, 'order' => $config->get($orderName, 0));
                }
            }
        }

        if(count($enabled))
        {
            uasort($enabled, array('self', 'compare'));

            foreach ($enabled as $item)
            {
                $return[] = $item['provider'];
            }
        }

        return $return;
    }


    static function loadProviderLinks($add){
        $providers = self::getOrderedEnabledServiceProviders();
        $return = array();
        foreach($providers as $provider){
            $return[$provider] = array(
                'link' => 'index.php?option=com_slogin&task=auth&plugin=' . $provider . $add,
                'class' => $provider.'slogin',
                'plugin_name' => $provider,
                'plugin_title' => Text::_('COM_SLOGIN_PROVIDER_'.strtoupper($provider))
            );
        }
        return $return;
    }
}
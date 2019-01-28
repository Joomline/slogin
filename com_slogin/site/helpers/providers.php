<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

class SloginProvidersHelper
{
    static function getServiceProviders()
    {
        return array(
            'facebook',
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
        $config = JComponentHelper::getParams('com_slogin');
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
                'plugin_title' => JText::_('COM_SLOGIN_PROVIDER_'.strtoupper($provider))
            );
        }
        return $return;
    }
}
<?php

// no direct access
defined('_JEXEC') or die ;

class pkg_sloginInstallerScript
{
	public function postflight($type, $parent)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->update('`#__extensions`')
            ->set('`enabled` = 1')
            ->where('`element` = '.$db->quote('slogin'))
            ->where('`type` = '.$db->quote('plugin'))
            ->where('(`folder` = '.$db->quote('authentication').' OR `folder` = '.$db->quote('user').')')
           ;
        $db->setQuery($query)->execute();
        
    }
}
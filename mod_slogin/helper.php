<?php
/**
 * Social Login
 *
 * @version 	1.7
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	2012-2016. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_slogin/helpers/providers.php';

class modSLoginHelper
{

    static function loadLinks(&$plugins, $add, $params)
    {
        $plugins = SloginProvidersHelper::loadProviderLinks($add);
    }

    static function gethk($input, $decrypt = false)
    {
        $o = $s1 = $s2 = array(); // Arrays for: Output, Square1, Square2
        $basea = array('?', '(', '@', ';', '$', '#', "]", "&", '*');  // base symbol set
        $basea = array_merge($basea, range('a', 'z'), range('A', 'Z'), range(0, 9));
        $basea = array_merge($basea, array('!', ')', '_', '+', '|', '%', '/', '[', '.', ' '));
        $dimension = 9; // of squares
        for ($i = 0; $i < $dimension; $i++) { // create Squares
            for ($j = 0; $j < $dimension; $j++) {
                $s1[$i][$j] = $basea[$i * $dimension + $j];
                $s2[$i][$j] = str_rot13($basea[($dimension * $dimension - 1) - ($i * $dimension + $j)]);
            }
        }
        unset($basea);
        $m = floor(strlen($input) / 2) * 2; // !strlen%2
        $symbl = $m == strlen($input) ? '' : $input[strlen($input) - 1]; // last symbol (unpaired)
        $al = array();
        // crypt/uncrypt pairs of symbols
        for ($ii = 0; $ii < $m; $ii += 2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii + 1]);
            $a1 = $a2 = array();
            for ($i = 0; $i < $dimension; $i++) { // search symbols in Squares
                for ($j = 0; $j < $dimension; $j++) {
                    if ($decrypt) {
                        if ($symb1 === strval($s2[$i][$j])) $a1 = array($i, $j);
                        if ($symb2 === strval($s1[$i][$j])) $a2 = array($i, $j);
                        if (!empty($symbl) && $symbl === strval($s2[$i][$j])) $al = array($i, $j);
                    } else {
                        if ($symb1 === strval($s1[$i][$j])) $a1 = array($i, $j);
                        if ($symb2 === strval($s2[$i][$j])) $a2 = array($i, $j);
                        if (!empty($symbl) && $symbl === strval($s1[$i][$j])) $al = array($i, $j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
                $symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1 . $symbn2;
        }
        if (!empty($symbl) && sizeof($al)) // last symbol
            $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
        return implode('', $o);
    }

    static function getalw(&$params)
    {
        $allowedHost = $params->def('secretkey', '');
        $allowedHost = (empty($allowedHost)) ? 'localhost' : $allowedHost;

        $allowedHost = explode('::', $allowedHost);
        $allow = false;

        foreach ($allowedHost as $allowed) {
            $allowed = modSLoginHelper::gethk($allowed, true);
            if (!empty($allowed)) {
                $allowed = explode('|', $allowed);
                $site = (!empty($allowed[0])) ? $allowed[0] : 'localhost';
                $extension = (!empty($allowed[1])) ? $allowed[1] : '';
                $expireDate = (!empty($allowed[2])) ? $allowed[2] : '';

                if (strpos($_SERVER['HTTP_HOST'], $site) !== false && $extension == 'slogin') {
                    $allow = true;
                    break;
                }
            }
        }
        return $allow;
    }

    static function getFusionProviders()
    {
        JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_slogin/models', 'SloginModel');
        $model = JModelLegacy::getInstance('fusion', 'SloginModel');
        $providers = $model->getProviders();

        $attachedProviders = array();
        $unattachedProviders = array();

        $fusionProviders = $model->getFusionProviders();

        foreach($providers as $v){
            if(!in_array($v['plugin_name'], $fusionProviders)){
                $attachedProviders[] = $v;
            }
            else{
                $v['link'] = 'index.php?option=com_slogin&task=detach_provider&plugin='.$v['plugin_name'];
                $unattachedProviders[] = $v;
            }
        }

        return array($attachedProviders, $unattachedProviders);
    }
}


?>

<?php
/**
 * Social Login Integration Plugin Profile
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2013. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access
defined('_JEXEC') or die;

class plgSlogin_integrationUnacceptable_emails extends JPlugin
{
    public function onSloginBeforeStoreOrLogin( $provider, &$first_name, &$last_name, &$email, &$slogin_id, &$rawRequest )
    {
	    $unacceptableEmails = $this->params->get('emails','');
	    $unacceptableEmails = explode("\n", $unacceptableEmails);
	    $unacceptableEmails = array_map('trim', $unacceptableEmails);
		$providers = $this->params->get('providers', array());
		if(!is_array($providers) || !count($providers) || !in_array($provider, $providers)){
			return;
		}

		if(!is_array($unacceptableEmails) || !count($unacceptableEmails)){
			return;
		}

	    foreach ( $unacceptableEmails as $unacceptableEmail ) {
		    if(empty($email)){
		    	continue;
		    }
		    if(strpos($email, $unacceptableEmail) !== false){
			    $email = '';
		    }
		}
    }

}

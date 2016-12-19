<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - Constants
 *
 * Central location for various values we need throughout the module.
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/constants.fbc.php
 */

if ( ! defined('FBC_VERSION'))
{
	define('FBC_VERSION',	'3.0.0');
	define('FBC_DOCS_URL',	'http://solspace.com/docs/facebook_connect/');
	define('FBC_ACTIONS',	'account_sync|activate_member|email_sync|facebook_post_authorize_callback|facebook_post_remove_callback|facebook_login|facebook_logout|register');
	define('FBC_PREFERENCES',	'fbc_app_id|fbc_secret|fbc_eligible_member_groups|fbc_member_group|fbc_account_activation|fbc_passive_registration|fbc_confirm_account_sync');
	define('FBC_PARAMS_LOCATION', 'db');	// db and cookie are the choices. db is the more secure but can definitely crash a high traffic site.
	define('FBC_LOADER_JS', '//connect.facebook.net/en_US/all.js');
	define('FBC_URI', 'd9i');	// We store a lot of return uris when someone executes a FB action. When they return, we choose from one of these. Sometimes there are duplicates so we reuse with this as a marker.
}
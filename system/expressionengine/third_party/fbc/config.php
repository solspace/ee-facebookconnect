<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - Config
 *
 * NSM Addon Updater config file.
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/config.php
 */

require_once 'constants.fbc.php';

$config['name']									= 'Facebook Connect';
$config['version']								= FBC_VERSION;
$config['nsm_addon_updater']['versions_xml'] 	= 'http://www.solspace.com/software/nsm_addon_updater/facebook_connect';
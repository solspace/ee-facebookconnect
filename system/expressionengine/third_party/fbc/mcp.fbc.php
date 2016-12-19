<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - Control Panel
 *
 * The control panel master class that handles all of the CP requests and displaying.
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/mcp.fbc.php
 */

require_once 'addon_builder/module_builder.php';

class Fbc_mcp extends Module_builder_fbc
{
	public $api;

	// -------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	bool		Enable calling of methods based on URI string
	 * @return	string
	 */

	public function __construct( $switch = TRUE )
	{
		parent::__construct();

		if ((bool) $switch === FALSE) return; // Install or Uninstall Request

		// --------------------------------------------
		//  Themes folder
		// --------------------------------------------

		$this->theme_url	= $this->sc->addon_theme_url;
		$this->cached_vars['theme_url']	= $this->theme_url;

		// --------------------------------------------
		//  Module Menu Items
		// --------------------------------------------

		$menu	= array(
			'module_preferences'	=> array(
				'name'	=> 'preferences',
				'link'  => $this->base . AMP . 'method=preferences',
				'title' => lang('preferences')
			),
			'module_diagnostics'	=> array(
				'name'	=> 'diagnostics',
				'link'  => $this->base . AMP . 'method=diagnostics',
				'title' => lang('diagnostics')
			),
			'module_demo_templates'		=> array(
				'link'	=> $this->base.'&method=code_pack',
				'title'	=> lang('demo_templates'),
			),
			'module_documentation'	=> array(
				'name'	=> 'documentation',
				'link'  => FBC_DOCS_URL,
				'title' => lang('online_documentation'),
				'new_window' => TRUE
			)
		);

		$this->cached_vars['lang_module_version'] 	= lang('fbc_module_version');
		$this->cached_vars['module_version'] 		= FBC_VERSION;
		$this->cached_vars['module_menu_highlight'] = 'module_preferences';
		$this->cached_vars['module_menu'] 			= $menu;

		// --------------------------------------------
		//  Sites
		// --------------------------------------------

		$this->cached_vars['sites']	= array();

		foreach( $this->data->get_sites() as $site_id => $site_label )
		{
			$this->cached_vars['sites'][$site_id] = $site_label;
		}
	}
	// END


	// -------------------------------------------------------------

	/**
	 * Api
	 *
	 * Invoke the api object
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function api()
	{
		if ( isset( $this->api->cached ) === TRUE ) return TRUE;

		// --------------------------------------------
		//  API Object
		// --------------------------------------------

		require_once $this->addon_path . 'api.fbc.php';

		$this->api = new Fbc_api();
	}

	/*	End api */

	// -------------------------------------------------------------

	/**
	 * Index
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function index( $message='' )
	{
		return $this->preferences( $message );
	}

	/* End index */


	// --------------------------------------------------------------------

	/**
	 * Code pack installer page
	 *
	 * @access public
	 * @param	string	$message	lang line for update message
	 * @return	string				html output
	 */

	public function code_pack($message = '')
	{
		//--------------------------------------------
		//	message
		//--------------------------------------------

		if ($message == '' AND ee()->input->get_post('msg') !== FALSE)
		{
			$message = lang(ee()->input->get_post('msg'));
		}

		$this->cached_vars['message'] = $message;

		// -------------------------------------
		//	load vars from code pack lib
		// -------------------------------------

		$lib_name = str_replace('_', '', $this->lower_name) . 'codepack';
		$load_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->lower_name))) . 'CodePack';

		ee()->load->library($load_name, $lib_name);

		ee()->$lib_name->autoSetLang = true;

		$cpt = ee()->$lib_name->getTemplateDirectoryArray(
			$this->addon_path . 'code_pack/'
		);

		$screenshot = ee()->$lib_name->getCodePackImage(
			$this->sc->addon_theme_path . 'code_pack/',
			$this->sc->addon_theme_url . 'code_pack/'
		);

		$this->cached_vars['screenshot'] = $screenshot;

		$this->cached_vars['prefix'] = $this->lower_name . '_';

		$this->cached_vars['code_pack_templates'] = $cpt;

		$this->cached_vars['form_url'] = $this->base . '&method=code_pack_install';

		//--------------------------------------
		//  menus and page content
		//--------------------------------------

		$this->cached_vars['module_menu_highlight'] = 'module_demo_templates';

		$this->add_crumb(lang('demo_templates'));

		$this->cached_vars['current_page'] = $this->view('code_pack.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	//END code_pack


	// --------------------------------------------------------------------

	/**
	 * Code Pack Install
	 *
	 * @access public
	 * @param	string	$message	lang line for update message
	 * @return	string				html output
	 */

	public function code_pack_install()
	{
		$prefix = trim((string) ee()->input->get_post('prefix'));

		if ($prefix === '')
		{
			ee()->functions->redirect($this->base . '&method=code_pack');
		}

		// -------------------------------------
		//	load lib
		// -------------------------------------

		$lib_name = str_replace('_', '', $this->lower_name) . 'codepack';
		$load_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->lower_name))) . 'CodePack';

		ee()->load->library($load_name, $lib_name);
		ee()->$lib_name->autoSetLang = true;

		// -------------------------------------
		//	¡Las Variables en vivo! ¡Que divertido!
		// -------------------------------------

		$variables = array();

		$variables['code_pack_name']	= $this->lower_name . '_code_pack';
		$variables['code_pack_path']	= $this->addon_path . 'code_pack/';
		$variables['prefix']			= $prefix;

		// -------------------------------------
		//	install
		// -------------------------------------

		$details = ee()->$lib_name->getCodePackDetails($this->addon_path . 'code_pack/');

		$this->cached_vars['code_pack_name'] = $details['code_pack_name'];
		$this->cached_vars['code_pack_label'] = $details['code_pack_label'];

		$return = ee()->$lib_name->installCodePack($variables);

		$this->cached_vars = array_merge($this->cached_vars, $return);

		//--------------------------------------
		//  menus and page content
		//--------------------------------------

		$this->cached_vars['module_menu_highlight'] = 'module_demo_templates';

		$this->add_crumb(lang('demo_templates'), $this->base . '&method=code_pack');
		$this->add_crumb(lang('install_demo_templates'));

		$this->cached_vars['current_page'] = $this->view('code_pack_install.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	//END code_pack_install


	// -------------------------------------------------------------

	/**
	 * Preferences
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function preferences( $message='' )
	{
		// --------------------------------------------
		//	Prep vars
		// --------------------------------------------

		$this->cached_vars['member_groups']			= $this->data->get_member_groups();
		$this->cached_vars['account_activation']	= array( 'fbc_no_activation', 'fbc_email_activation', 'fbc_admin_activation' );
		$this->cached_vars['prefs']['fbc_app_id']	= '';
		$this->cached_vars['prefs']['fbc_secret']	= '';
		$this->cached_vars['prefs']['fbc_eligible_member_groups']	= '';
		$this->cached_vars['prefs']['fbc_member_group']	= ( isset( $this->cached_vars['safe_member_groups'][5] ) === TRUE ) ? 5: 3;
		$this->cached_vars['prefs']['fbc_account_activation']	= 'fbc_no_activation';
		$this->cached_vars['prefs']['fbc_confirm_account_sync']	= 'n';
		$this->cached_vars['prefs']['fbc_passive_registration']	= 'y';

		// --------------------------------------------
		//	Set vars
		// --------------------------------------------

		foreach ( $this->cached_vars['prefs'] as $key => $val )
		{
			if ( ee()->config->item( $key ) !== FALSE )
			{
				$this->cached_vars['prefs'][$key]	= ee()->config->item( $key );
			}
		}

		// --------------------------------------------
		//	Are we updating / inserting?
		// --------------------------------------------

		if ( ee()->input->post('fbc_app_id') !== FALSE )
		{
			// --------------------------------------------
			//	Prep vars
			// --------------------------------------------

			foreach ( $this->cached_vars['prefs'] as $key => $val )
			{
				if ( ee()->input->post($key) !== FALSE )
				{
					$this->cached_vars['prefs'][$key]	= ee()->input->post($key);
				}
			}

			// --------------------------------------------
			//	Special handling for eligible member groups
			// --------------------------------------------

			$this->cached_vars['prefs']['fbc_eligible_member_groups']	= '';

			if ( ! empty( $_POST['fbc_eligible_member_groups'] ) AND is_array( $_POST['fbc_eligible_member_groups'] ) === TRUE )
			{
				$temp	= array();

				foreach ( $_POST['fbc_eligible_member_groups'] as $val )
				{
					if ( is_numeric( $val ) === FALSE ) continue;

					$temp[]	= $val;
				}

				$this->cached_vars['prefs']['fbc_eligible_member_groups']	= implode( "|", $temp );
			}

			// --------------------------------------------
			//	Check DB for insert / update
			// --------------------------------------------

			$message	= '';

			if ( $this->data->set_preference( $this->cached_vars['prefs'], ee()->config->item('site_id') ) !== FALSE )
			{
				$message	= lang( 'preferences_updated' );
			}
		}

		// --------------------------------------------
		//	Prepare eligible member groups
		// --------------------------------------------

		$this->cached_vars['prefs']['fbc_eligible_member_groups']	= explode( "|", $this->cached_vars['prefs']['fbc_eligible_member_groups'] );

		// --------------------------------------------
		//	Prep message
		// --------------------------------------------

		$this->_prep_message( $message );

		// --------------------------------------------
		//  Title and Crumbs
		// --------------------------------------------

		$this->add_crumb(lang('preferences'));
		$this->build_crumbs();

		// --------------------------------------------
		//  Load Homepage
		// --------------------------------------------

		return $this->ee_cp_view('preferences.html');
	}

	/* End preferences */

	// -------------------------------------------------------------

	/**
	 * Prep message
	 *
	 * @access	private
	 * @param	message
	 * @return	boolean
	 */

	public function _prep_message( $message = '' )
	{
		if ( $message == '' AND isset( $_GET['msg'] ) )
		{
			$message = lang( $_GET['msg'] );
		}

		$this->cached_vars['message']	= $message;

		return TRUE;
	}

	/*	End prep message */

	// -------------------------------------------------------------

	/**
	 * Diagnostics
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function diagnostics( $message='' )
	{
		// --------------------------------------------
		//	API Credentials present
		// --------------------------------------------

		$this->cached_vars['api_credentials_present']	= lang('api_credentials_are_present');

		if ( ee()->config->item('fbc_app_id') === FALSE OR ee()->config->item('fbc_app_id') == '' OR ee()->config->item('fbc_secret') === FALSE OR ee()->config->item('fbc_secret') == '' )
		{
			$this->cached_vars['api_credentials_present']	= lang('api_credentials_are_not_present');
		}

		// --------------------------------------------
		//	API successful connect
		// --------------------------------------------

		$this->api();

		$this->api->connect_to_api();

		$this->cached_vars['api_successful_connect']	= lang('api_connect_was_not_successful');

		if ( $this->api->user )
		{
			$this->cached_vars['api_successful_connect']	= lang('api_connect_was_successful');
		}

		// --------------------------------------------
		//	API login button
		// --------------------------------------------

		$this->cached_vars['facebook_loader_js']	= $this->data->get_facebook_loader_js();

		$this->cached_vars['api_successful_login']	= lang('api_login_was_successful');

		$this->cached_vars['fbc_app_id']	= ee()->config->item('fbc_app_id');

		// --------------------------------------------
		//	Try login
		// --------------------------------------------

		try
		{
			$appobj = $this->api->FB->api( ee()->config->item('fbc_app_id') );

			$app	= array();

			if ( is_object( $appobj ) === TRUE )
			{
				$app['connect_url']	= $appobj->connect_url;
				$app['app_id']		= $appobj->app_id;
			}
			elseif ( is_array( $appobj ) === TRUE )
			{
				$app	= $appobj;
			}

			if ( empty( $app['connect_url'] ) )
			{
				$this->cached_vars['api_connect_url_test']		= lang('api_connect_url_is_empty');
			}
			elseif ( $app['connect_url'] != $this->cached_vars['api_connect_url'] )
			{
				$this->cached_vars['api_connect_url_test']		= str_replace( array( '%incorrect_connect_url%', '%correct_connect_url%' ), array( $app['connect_url'], $this->cached_vars['api_connect_url'] ), lang('api_connect_url_incorrect') );
			}
			else
			{
				$this->cached_vars['api_connect_url_test']	= '';
			}

			if ( ! empty( $app['app_id'] ) )
			{
				$this->cached_vars['api_connect_url_facebook']	= str_replace( '%fbc_url%', 'http://www.facebook.com/developers/editapp.php?app_id=' . $app['app_id'], lang('api_connect_url_facebook') );
			}
		}
		catch (Exception $e)
		{
		}

		// --------------------------------------------
		//	Prep message
		// --------------------------------------------

		$this->_prep_message( $message );

		// --------------------------------------------
		//  Title and Crumbs
		// --------------------------------------------

		$this->add_crumb(lang('diagnostics'));
		$this->build_crumbs();

		// --------------------------------------------
		//  Load Homepage
		// --------------------------------------------

		$this->cached_vars['module_menu_highlight'] = 'module_diagnostics';
		return $this->ee_cp_view('diagnostics.html');
	}
	// End diagnostics


	// -------------------------------------------------------------

	/**
	 * Module Upgrading
	 *
	 * This function is not required by the 1.x branch of ExpressionEngine by default.  However,
	 * as the install and deinstall ones are, we are just going to keep the habit and include it
	 * anyhow.
	 *		- Originally, the $current variable was going to be passed via parameter, but as there might
	 *		  be a further use for such a variable throughout the module at a later date we made it
	 *		  a class variable.
	 *
	 *
	 * @access	public
	 * @return	bool
	 */

	public function fbc_module_update()
	{
		if ( ! isset($_POST['run_update']) OR $_POST['run_update'] != 'y')
		{
			$this->add_crumb(lang('update_fbc_module'));
			$this->cached_vars['form_url'] = $this->cached_vars['base_uri'] . '&method=fbc_module_update';
			return $this->ee_cp_view('update_module.html');
		}

		require_once $this->addon_path.'upd.fbc.php';

		$U = new Fbc_upd();

		if ($U->update() !== TRUE)
		{
			return ee()->functions->redirect($this->base . AMP . 'msg=update_failure');
		}
		else
		{
			return ee()->functions->redirect($this->base . AMP . 'msg=update_successful');
		}
	}
	// END fbc_module_update
}
// END CLASS Fbc

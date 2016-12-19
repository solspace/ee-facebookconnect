<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - User Side
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/mod.fbc.php
 */

require_once 'addon_builder/module_builder.php';

class Fbc extends Module_builder_fbc
{
	public $FB;
	public $api;
	public $disabled	= FALSE;
	public $from_fb	= FALSE;
	public $error		= array();
	public $facebook_xmlns_definition	= 'xmlns:fb="http://www.facebook.com/2008/fbml"';

	// -------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct();
	}
	/* END Fbc() */


	// --------------------------------------------------------------------

	/**
	 * Theme Folder URL
	 *
	 * Mainly used for codepack
	 *
	 * @access	public
	 * @return	string	theme folder url with ending slash
	 */

	public function theme_folder_url()
	{
		return $this->sc->addon_theme_url;
	}
	//END theme_folder_url


	// -------------------------------------------------------------

	/**
	 *	Member Self Activation Processing
	 *
	 *	ACTION Method
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function activate_member()
	{
		// --------------------------------------------
		//  Fetch the site name and URL
		// --------------------------------------------

		if (ee()->input->get_post('r') == 'f')
		{
			if (ee()->input->get_post('board_id') !== FALSE && is_numeric(ee()->input->get_post('board_id')))
			{
				$query	= ee()->db->query("SELECT board_forum_url, board_id, board_label FROM exp_forum_boards WHERE board_id = '".ee()->db->escape_str(ee()->input->get_post('board_id'))."'");
			}
			else
			{
				$query	= ee()->db->query("SELECT board_forum_url, board_id, board_label FROM exp_forum_boards WHERE board_id = '1'");
			}

			$site_name	= $query->row('board_label');
			$return		= $query->row('board_forum_url');
		}
		else
		{
			$return 	= ee()->functions->fetch_site_index();
			$site_name 	= ( ee()->config->item('site_name') == '' ) ? lang('back') : stripslashes( ee()->config->item('site_name') );
		}

		// --------------------------------------------
		//  No ID?  Tisk tisk...
		// --------------------------------------------

		$id  = ee()->input->get_post('id');

		if ($id == FALSE)
		{
			$data = array(	'title' 	=> lang('mbr_activation'),
							'heading'	=> lang('error'),
							'content'	=> lang('invalid_url'),
							'link'		=> array($return, $site_name)
						 );

			ee()->output->show_message($data);
		}

		// --------------------------------------------
		//  Set the member group
		// --------------------------------------------

		$group_id = ee()->config->item('fbc_member_group');

		// --------------------------------------------
		//	Is there even an account for this particular user?
		// --------------------------------------------

		$query = ee()->db->query("SELECT member_id, group_id, email, screen_name, username FROM exp_members WHERE authcode = '" . ee()->db->escape_str( $id ) . "'");

		if ( $query->num_rows() == 0 )
		{
			$data = array(	'title' 	=> lang('mbr_activation'),
							'heading'	=> lang('error'),
							'content'	=> lang('mbr_problem_activating'),
							'link'		=> array($return, $site_name)
						 );

			ee()->output->show_message($data);
		}

		$member_id = $query->row('member_id');

		// --------------------------------------------
		//	If the member group hasn't been switched we'll do it
		// --------------------------------------------

		if ($query->row('group_id') != $group_id)
		{
			ee()->db->query( "UPDATE exp_members SET group_id = '".ee()->db->escape_str( $group_id )."' WHERE authcode = '".ee()->db->escape_str( $id )."'" );
		}

		ee()->db->query("UPDATE exp_members SET authcode = '' WHERE authcode = '" . ee()->db->escape_str( $id ) . "'");

		// --------------------------------------------
		//	'fbc_activate_member_account_end' hook.
		// --------------------------------------------

		if ( ee()->extensions->active_hook('fbc_activate_member_account_end') === TRUE )
		{
			$edata = ee()->extensions->universal_call( 'fbc_activate_member_account_end', $member_id );
			if (ee()->extensions->end_script === TRUE) return FALSE;
		}
		// --------------------------------------------
		//	Upate Stats
		// --------------------------------------------

		ee()->stats->update_member_stats();

		// --------------------------------------------
		//  Show success message
		// --------------------------------------------

		$data = array(	'title' 	=> lang('mbr_activation'),
						'heading'	=> lang('thank_you'),
						'content'	=> lang('mbr_activation_success')."\n\n".lang('mbr_may_now_log_in'),
						'link'		=> array($return, $site_name)
					 );

		ee()->output->show_message($data);
	}

	/* End activate member */

	// -------------------------------------------------------------

	/**
	 * Allow email
	 *
	 * Facebook allows a site to know and use a user's email address when that user grants the site permission.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function allow_email()
	{
		$cond['fbc_allow_email']	= 'n';

		$this->api();

		if ( $this->api->has_app_permission( 'email' ) === TRUE )
		{
			$cond['fbc_allow_email']	= 'y';
		}

		// --------------------------------------------
		//	Parse conditionals
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		return $tagdata;
	}

	/*	End allow email */
	
	// -------------------------------------------------------------

	/**
	 * Allow user friends
	 *
	 * Facebook provides access the list of friends that also use your app. These friends can
	 * be found on the friends edge on the user object. In order for a person to show up in one
	 * person's friend list, both people must have decided to share their list of friends with
	 * your app and not disabled that permission during login. Also both friends must have been
	 * asked for user_friends during the login process.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function allow_user_friends()
	{
		$cond['fbc_allow_user_friends']	= 'n';

		$this->api();

		if ( $this->api->has_app_permission( 'user_friends' ) === TRUE )
		{
			$cond['fbc_allow_user_friends']	= 'y';
		}

		// --------------------------------------------
		//	Parse conditionals
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		return $tagdata;
	}

	/*	End allow user friends */

	// -------------------------------------------------------------

	/**
	 * Allow publish actions
	 *
	 * Facebook allows a site to publish user submitted data to Facebook on a user's behalf. The user must first grant the permission to do so. This method prepares a conditional to wrap content in a template to evaluate the permission settings.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function allow_publish_actions()
	{
		$cond['fbc_allow_publish_actions']	= 'n';

		$this->api();

		if ( $this->api->has_app_permission( 'publish_actions' ) === TRUE )
		{
			$cond['fbc_allow_publish_actions']	= 'y';
		}

		// --------------------------------------------
		//	Parse conditionals
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		return $tagdata;
	}

	/*	End allow publish_actions */

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
	 * Chars decode
	 *
	 * This little routine preps chars for forms
	 *
	 * @access	private
	 * @return	string
	 */

	public function _chars_decode( $str = '' )
	{
		if ( $str == '' ) return;

		if ( function_exists( 'htmlspecialchars_decode' ) === TRUE )
		{
			$str	= htmlspecialchars_decode( $str );
		}

		if ( function_exists( 'html_entity_decode' ) === TRUE )
		{
			$str	= html_entity_decode( $str );
		}

		$str	= str_replace( array( '&amp;', '&#47;', '&#39;', '\'' ), array( '&', '/', '', '' ), $str );

		$str	= stripslashes( $str );

		return $str;
	}

	/* End chars decode */

	// -------------------------------------------------------------

	/**
	 * Check form hash
	 *
	 * Makes sure that a valid XID is present in the form POST
	 *
	 * @access		private
	 * @return		boolean
	 */

	public function _check_form_hash()
	{
		if ( ! $this->check_secure_forms())
		{
			return $this->show_error(
				lang('not_authorized')
			);
		}

		return TRUE;
	}

	/*	End check form hash */

	// -------------------------------------------------------------

	/**
	 * Account sync
	 *
	 * Sometimes we want to ask the user to confirm before we connect a Facebook account to their existing EE member account.
	 *
	 * @access	public
	 * @return	string
	 */

	public function account_sync()
	{
		$this->actions();

		// --------------------------------------------
		//	Run security tests
		// --------------------------------------------

		if ( $this->actions->_security() === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//	You have to be logged in to an EE account to sync
		// --------------------------------------------

		if ( ee()->session->userdata('member_id') == 0 )
		{
			return $this->show_error(lang('not_logged_in' ) );
		}

		// --------------------------------------------
		//	Prepare returns
		// --------------------------------------------

		$returns		= array(
			'return_when_synced'		=> '',
			'return_when_unsynced'		=> '',
			'return_when_sync_fails'	=> ''
		);

		foreach ( $returns as $key => $val )
		{
			if ( ee()->input->post($key) !== FALSE AND ee()->input->post($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->input->post($key) );
			}

			$returns[$key]	= $val;
		}

		// --------------------------------------------
		//	Are we unsyncing?
		// --------------------------------------------
		//	People can basically reset their FB Connect sync. We don't care if they are logged in to an FB account when they submit this form. We care only to set their FB Connect id to 0 in the DB. It's just a clean reset as far as we're concerned.
		// --------------------------------------------

		if ( ee()->input->post('unsync') !== FALSE AND ee()->input->post('unsync') == 'yes' )
		{
			// --------------------------------------------
			//	Return an error if their EE account is synced to no FB account
			// --------------------------------------------

			if ( $this->data->get_facebook_user_id_from_member_id( ee()->session->userdata('member_id') ) === FALSE OR $this->data->get_facebook_user_id_from_member_id( ee()->session->userdata('member_id') ) == 0 )
			{
				return $this->show_error(lang('not_fb_synced' ) );
			}

			// --------------------------------------------
			//	Set the FB user id to 0
			// --------------------------------------------

			if ( $this->data->set_facebook_user_id_for_member_id( 0, ee()->session->userdata('member_id'), 'unsync' ) === FALSE )
			{
				return $this->show_error(lang('unsync_error' ) );
			}

			// --------------------------------------------
			//	Form security
			// --------------------------------------------

			if ( $this->_check_form_hash() === FALSE ) return FALSE;

			// --------------------------------------------
			//	Return as normal unsynced
			// --------------------------------------------

			$this->_redirect( $returns['return_when_unsynced'] );
			exit();
		}

		// --------------------------------------------
		//	Do we bother trying to sync?
		// --------------------------------------------

		if ( ee()->input->post('sync') === FALSE OR ee()->input->post('sync') != 'yes' )
		{
			$this->_redirect( $returns['return_when_sync_fails'] );
			exit();
		}

		// --------------------------------------------
		//	Is this member in an eligible group?
		// --------------------------------------------

		$groups	= explode( "|", ee()->config->item('fbc_eligible_member_groups') );

		if ( in_array( ee()->session->userdata('group_id'), $groups ) === FALSE )
		{
			$this->error[]	= lang( 'member_group_not_eligible' );
		}

		// --------------------------------------------
		//  Get the FB user id if we can
		// --------------------------------------------

		$this->api();

		if ( ( $uid = $this->api->get_user_id() ) === FALSE )
		{
			$this->error[]	= lang('facebook_not_logged_in');
		}

		// --------------------------------------------
		//	Do we already have a record for this facebook user id?
		// --------------------------------------------

		if ( $this->data->get_member_id_from_facebook_user_id( $uid ) !== FALSE )
		{
			$this->error[]	= lang( 'fb_user_already_exists' );
		}

		// --------------------------------------------
		//	Errors?
		// --------------------------------------------

		if ( count( $this->error ) > 0 )
		{
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Check form hash
		// --------------------------------------------

		if ( $this->_check_form_hash() === FALSE ) return FALSE;

		// --------------------------------------------
		//	Errors?
		// --------------------------------------------

		if ( count( $this->error ) > 0 )
		{
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Connect FB UID to local member id
		// --------------------------------------------

		if ( $this->data->set_facebook_user_id_for_member_id( $uid, ee()->session->userdata('member_id') ) === TRUE )
		{
			// --------------------------------------------
			//	Synch FB email
			// --------------------------------------------

			$this->api->synchronize_facebook_email_with_local_email( $uid );

			// --------------------------------------------
			//	Redirect
			// --------------------------------------------

			$this->_redirect( $returns['return_when_synced'] );
			exit();
		}
		else
		{
			$this->_redirect( $returns['return_when_sync_fails'] );
			exit();
		}
	}

	/*	End confirm account sync */

	// -------------------------------------------------------------

	/**
	 * Account sync form
	 *
	 * Register form for a new user using the abridged Facebook approach.
	 *
	 * @access	public
	 * @return	string
	 */

	public function account_sync_form()
	{
		// --------------------------------------------
		//	Prepare action
		// --------------------------------------------

		$act	= ee()->functions->fetch_action_id('Fbc', 'account_sync');

		$params	= array( 'ACT' => $act );

		// --------------------------------------------
		//	Prepare returns
		// --------------------------------------------

		$returns		= array(
			'return_when_synced'		=> ee()->uri->uri_string,
			'return_when_unsynced'		=> ee()->uri->uri_string,
			'return_when_sync_fails'	=> ee()->uri->uri_string
		);

		foreach ( $returns as $key => $val )
		{
			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->TMPL->fetch_param($key) );
			}

			$params[$key]	= $val;
		}

		// --------------------------------------------
		//	Prepare extra params
		// --------------------------------------------

		$extra		= array(
			'unsync'	=> 'no'
		);

		foreach ( $extra as $key => $val )
		{
			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) == 'yes' )
			{
				$val	= ee()->TMPL->fetch_param($key);
			}

			$params[$key]	= $val;
		}

		// --------------------------------------------
		//	Scraps
		// --------------------------------------------

		$params['RET']	= ee()->functions->create_url( ee()->uri->uri_string );

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $this->_form( $params );
	}

	/*	End confirm account sync form */

	// -------------------------------------------------------------

	/**
	 * Facebook login
	 *
	 * This method receives a Facebook login and processes it as best it can.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function facebook_login()
	{
		$this->actions();

		// --------------------------------------------
		//	Run security
		// --------------------------------------------

		if ( $this->actions->_security() === FALSE )
		{
			exit();
		}

		// --------------------------------------------
		//	We must have params
		// --------------------------------------------

		if ( empty( $_GET['params'] ) )
		{
			$this->_redirect( ee()->functions->create_url( '' ) );
			exit();
		}
		else
		{
			$_GET['params']	= ee()->security->xss_clean( rtrim( $_GET['params'], '/' ) );
		}

		// --------------------------------------------
		//	Prep for fun
		// --------------------------------------------

		$expected_params	= array(
			'return_for_passive_register',
			'return_on_failure',
			'return_to_confirm_account_sync',
			'return_to_register',
			'return_when_logged_in'
		);

		$params	= $this->_get_return_params( $this->_implode_explode_params( base64_decode( $_GET['params'] ) ), $expected_params );

		// --------------------------------------------
		//  Get the FB user id if we can
		// --------------------------------------------

		$this->api();

		if ( ( $uid = $this->api->get_user_id() ) !== FALSE )
		{
			// --------------------------------------------
			//	See if this FB user already has local member account
			// --------------------------------------------

			if ( ( $member_id = $this->data->get_member_id_from_facebook_user_id( $uid ) ) !== FALSE )
			{
				// --------------------------------------------
				//	Is this person already logged in locally?
				// --------------------------------------------

				if ( ee()->session->userdata('member_id') != 0 )
				{
					// --------------------------------------------
					//	Synch FB email
					// --------------------------------------------

					$this->api->synchronize_facebook_email_with_local_email( $uid );

					// --------------------------------------------
					//	Redirect
					// --------------------------------------------

					$this->_redirect( $params['return_when_logged_in'] );
					exit();
				}

				// --------------------------------------------
				//	Try to log this person into their account
				// --------------------------------------------

				elseif ( $this->actions->ee_login( $member_id ) === TRUE )
				{
					// --------------------------------------------
					//	Synch FB email
					// --------------------------------------------

					$this->api->synchronize_facebook_email_with_local_email( $uid );

					// --------------------------------------------
					//	Redirect
					// --------------------------------------------

					$this->_redirect( $params['return_when_logged_in'] );
					exit();
				}

				// --------------------------------------------
				//	Unable to login?
				// --------------------------------------------

				else
				{
					return $this->show_error(lang('unable_to_login'));
				}
			}

			// --------------------------------------------
			//	Is user logged in locally?
			// --------------------------------------------
			//	This user does not have an FB account connected to their local member id. If they are logged in, try and make that connection. Note that we prevent people from logging in using Facebook if they do not belong to an eligible member group. We do, however, connect someone's member id to their Facebook id without concern for that restriction. Someone's member group could change which would allow for Facebook login in the future or it could change to prevent Facebook login in the future. The security provision needs to be on the login routine, not here.
			// --------------------------------------------

			elseif ( ee()->session->userdata('member_id') != 0 )
			{
				if ( ee()->config->item('fbc_confirm_account_sync') == 'y' )
				{
					// --------------------------------------------
					//	Redirect
					// --------------------------------------------

					$this->_redirect( $params['return_to_confirm_account_sync'] );
					exit();
				}

				// --------------------------------------------
				//	Connect FB UID to local member id
				// --------------------------------------------

				elseif ( $this->data->set_facebook_user_id_for_member_id( $uid, ee()->session->userdata('member_id') ) === TRUE )
				{
					// --------------------------------------------
					//	Synch FB email
					// --------------------------------------------

					$this->api->synchronize_facebook_email_with_local_email( $uid );

					// --------------------------------------------
					//	Redirect
					// --------------------------------------------

					$this->_redirect( $params['return_when_logged_in'] );
					exit();
				}
			}

			// --------------------------------------------
			//	Passive registration?
			// --------------------------------------------
			//	A parameter attached to the fbc login button can be passed across to the ACT that executes facebook_login. This parameter, called passive_registration, tells this method to create a strawman member account for the FB user and log them into EE with it. It's the nuclear option. It's dangerous. But it's in demand so I include it here and hide my face in my hands.
			// --------------------------------------------

			elseif ( ee()->config->item('fbc_passive_registration') != 'n' )
			{
				if ( $this->actions->passive_registration( $uid ) !== FALSE )
				{
					$this->_redirect( $params['return_for_passive_register'] );
					exit();
				}
				else
				{
					return $this->show_error($this->actions->error );
				}
			}

			// --------------------------------------------
			//	Return to register
			// --------------------------------------------
			//	We have valid Facebook login, but we can't tell if this user belongs to the local site yet. We send them to a register page. On that page we assume that the EE site admin has provided a link to let the person login with existing credentials in order to link their FB id with their local site id.
			// --------------------------------------------

			else
			{
				$this->_redirect( $params['return_to_register'] );
				exit();
			}
		}

		// --------------------------------------------
		//	Facebook uid verification failed so we just redirect as best we can
		// --------------------------------------------

		$this->_redirect( $params['return_on_failure'] );
		exit();
	}

	/*	End facebook login */

	// -------------------------------------------------------------

	/**
	 * Facebook logout
	 *
	 * This method logs a user out of the EE site that they were logged into with Facebook.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function facebook_logout()
	{
		$is_ee_member	= '';
		$return			= '';

		// --------------------------------------------
		//	Do we have a return?
		// --------------------------------------------

		if ( ! empty( $_GET['return_when_logged_out'] ) )
		{
			$return	= ee()->security->xss_clean( rtrim( base64_decode( $_GET['return_when_logged_out'] ), '/' ) );
		}

		// --------------------------------------------
		//	Do we have params?
		// --------------------------------------------

		if ( ! empty( $_GET['params'] ) )
		{
			$_GET['params']	= ee()->security->xss_clean( rtrim( $_GET['params'], '/' ) );

			// --------------------------------------------
			//	Prep for fun
			// --------------------------------------------

			$expected_params	= array(
				'return_when_logged_out',
				'is_ee_member'
			);

			$params	= $this->_get_return_params( $this->_implode_explode_params( base64_decode( $_GET['params'] ) ), $expected_params );

			// --------------------------------------------
			//	Return value?
			// --------------------------------------------

			if ( $params['return_when_logged_out'] == '' AND $return == '' )
			{
				$this->_redirect( ee()->functions->create_url( '' ) );
				exit();
			}

			$return	= $params['return_when_logged_out'];

			// --------------------------------------------
			//	Is member?
			// --------------------------------------------
			//	This is legacy. I don't truly know why we would only log someone out of Facebook and not log them out of the EE site they are on. So I am commenting it out for now.
			// --------------------------------------------

			if ( ! empty( $params['is_ee_member'] ) )
			{
				// $this->_redirect( $return );
				// exit();
			}

			$is_ee_member	= $params['is_ee_member'];
		}

		// --------------------------------------------
		//	Valid logout?
		// --------------------------------------------
		//	A person can be logged in to Facebook. They can come to an EE site and click the FB logout button. Facebook will log them out and send them to this method. But there is not necessarily any connection between their FB id and this site. We store a flag in the DB at the time that we create the FB logout button on the page. We then check that flag here to make sure that they are logging out of a FB account that is connected to an EE account.
		// --------------------------------------------

		if ( ! empty( $_GET['is_ee_member'] ) )
		{
			$is_ee_member	= ee()->security->xss_clean( base64_decode( $_GET['is_ee_member'] ) );

			if ( $is_ee_member != 'y' )
			{
				$this->_redirect( $return );
				exit();
			}
		}
		elseif ( ! empty( $is_ee_member ) )
		{
			if ( $is_ee_member != 'y' )
			{
				$this->_redirect( $return );
				exit();
			}
		}
		else
		{
			$this->_redirect( $return );
			exit();
		}

		// --------------------------------------------
		//	Let's logout then
		// --------------------------------------------

		// Kill the session and cookies
		ee()->db->where('site_id', ee()->config->item('site_id'));
		ee()->db->where('ip_address', ee()->input->ip_address());
		ee()->db->where('member_id', ee()->session->userdata('member_id'));
		ee()->db->delete('online_users');

		ee()->session->destroy();

		ee()->input->delete_cookie('read_topics');

		// --------------------------------------------
		//	Redirect
		// --------------------------------------------

		$this->_redirect( $return );
		exit();
	}
	//	End Facebook logout


	// -------------------------------------------------------------

	/**
	 * Facebook member is EE member
	 *
	 * @access	private
	 * @return	string
	 */

	 function _facebook_member_is_ee_member()
	 {
		if ( ee()->session->userdata('member_id') == 0 )
		{
			return FALSE;
		}

		$this->api();

		$uid = $this->api->get_user_id();

		if ( empty($uid) )
		{
			return FALSE;
		}

		if ( ( $member_id = $this->data->get_member_id_from_facebook_user_id( $uid ) ) === FALSE )
		{
			return FALSE;
		}

		if ( ee()->session->userdata('member_id') != $member_id )
		{
			return FALSE;
		}

		return TRUE;
	 }

	 /*	End facebook member is EE member */

	// -------------------------------------------------------------

	/**
	 * Facebook post authorize callback
	 *
	 * @access	public
	 * @return	string
	 */

	public function facebook_post_authorize_callback()
	{
		$this->api();
		$this->actions();

		try
		{
			$userinfo	= $this->api->get_user_info();
		}
		catch (Exception $e)
		{
		}

		$this->actions->log_to_cp( 'Facebook came back to us' );
	}

	/*	End Facebook post authorize callback */

	// -------------------------------------------------------------

	/**
	 * Facebook post remove callback
	 *
	 * @access	public
	 * @return	string
	 */

	public function facebook_post_remove_callback()
	{
		$this->actions();
		$this->actions->log_to_cp( 'Facebook removed this app for a user. ' . print_r( $_POST, TRUE ) );
	}

	/*	End Facebook post remove callback */

	// -------------------------------------------------------------

	/**
	 * FB parse
	 *
	 * This is a catch-all for methods available through the FB.parse method of the Javascript SDK.
	 *
	 * @access	public
	 * @return	string
	 */

	public function activity() { return $this->_fb_parse( __FUNCTION__ ); }
	public function bookmark() { return $this->_fb_parse( __FUNCTION__ ); }
	public function comments() { return $this->_fb_parse( __FUNCTION__ ); }
	public function facepile() { return $this->_fb_parse( __FUNCTION__ ); }
	public function like() { return $this->_fb_parse( __FUNCTION__ ); }
	public function like_box() { return $this->_fb_parse( __FUNCTION__ ); }
	public function live_stream() { return $this->_fb_parse( __FUNCTION__ ); }
	public function pronoun() { return $this->_fb_parse( __FUNCTION__ ); }
	public function recommendations() { return $this->_fb_parse( __FUNCTION__ ); }
	public function profile_pic() { return $this->_fb_parse( __FUNCTION__ ); }
	public function user_status() { return $this->_fb_parse( __FUNCTION__ ); }

	public function _fb_parse( $method = '' )
	{
		// --------------------------------------------
		//  Validate
		// --------------------------------------------

		if ( $method == '' ) return $this->no_results('fbc');

		// --------------------------------------------
		//  Spin up API if needed
		// --------------------------------------------

		if ( in_array( $method, array( 'pronoun', 'profile_pic', 'user_status' ) ) === TRUE )
		{
			$this->api();
		}

		// --------------------------------------------
		//  Prep href
		// --------------------------------------------

		$href	= ee()->uri->uri_string;

		if ( ee()->TMPL->fetch_param('href') !== FALSE AND ee()->TMPL->fetch_param('href') != '' )
		{
			$href	= ee()->TMPL->fetch_param('href');
		}

		// --------------------------------------------
		//  Methods array
		// --------------------------------------------

		$methods	= array(
			'activity'	=> array(
				'template_params'		=> array(
					'colorscheme'		=> 'light',
					'filter'			=> '',
					'header'			=> 'true',
					'height'			=> 300,
					'linktarget'		=> '',
					'max_age'			=> '0',
					'recommendations'	=> 'true',
					'ref'				=> '',
					'site'				=> '',
					'width'				=> 300
				)
			),
			'bookmark'	=> array(
				'closing_tag'		=> 'n'
			),
			'comments'	=> array(
				'template_params'	=> array(
					'colorscheme'	=> 'light',
					'href'			=> $this->_prep_return( ee()->uri->uri_string ),
					'mobile'		=> 'false',
					'num_posts'		=> 5,
					'order_by'		=> '',
					'width'			=> '100%'
				)
			),
			'facepile'	=> array(
				'template_params'	=> array(
					'colorscheme'	=> 'light',
					'href'			=> $this->_prep_return( ee()->uri->uri_string ),
					'max_rows'		=> 1,
					'size'			=> 'medium',
					'width'			=> 200
				)
			),
			'like'	=> array(
				'template_params'	=> array(
					'action'		=> '',
					'colorscheme'	=> 'light',
					'font'			=> '',
					'href'			=> $this->_prep_return( ee()->uri->uri_string ),
					'ref'			=> '',
					'share'			=> '',
					'layout'		=> 'standard',
					'show_faces'	=> '',
					'width'			=> 200
				)
			),
			'like_box'	=> array(
				'tag'				=> 'like-box',
				'template_params'	=> array(
					'colorscheme'	=> 'light',
					'force_wall'	=> 'false',
					'header'		=> 'true',
					'height'		=> 63,
					'href'			=> $this->_prep_return( ee()->uri->uri_string ),
					'show_border'	=> 'true',
					'show_faces'	=> 'true',
					'stream'		=> 'false',
					'width'			=> 200
				)
			),
			'live_stream'	=> array(
				'tag'							=> 'live-stream',
				'template_params'				=> array(
					'always_post_to_friends'	=> 'true',
					'height'					=> 500,
					'via_url'					=> $this->_prep_return( ee()->uri->uri_string ),
					'width'						=> 400,
					'xid'						=> ''
				)
			),
			'pronoun'	=> array(
				'closing_tag'	=> 'n',
				'template_params'	=> array(
					'capitalize'	=> 'false',
					'objective'		=> 'false',
					'possessive'	=> 'false',
					'reflexive'		=> 'false',
					'uid'			=> '',
					'usethey'		=> 'true',
					'useyou'		=> 'true'
				)
			),
			'recommendations'	=> array(
				'template_params'	=> array(
					'border_color'		=> '',
					'colorscheme'		=> 'light',
					'font'				=> '',
					'header'			=> 'true',
					'height'			=> 300,
					'linktarget'		=> '_blank',
					'max_age'			=> '0',
					'ref'				=> '',
					'site'				=> '',
					'width'				=> 300
				)
			),
			'profile_pic'	=> array(
				'tag'				=> 'profile-pic',
				'template_params'	=> array(
					'height'			=> '',
					'type'				=> 'small',
					'width'				=> ''
				)
			),
			'user_status'	=> array(
				'tag'				=> 'user-status',
				'template_params'	=> array(
					'linked'			=> 'true',
					'uid'				=> '',
				)
			),
		);

		// --------------------------------------------
		//  Is the incoming method defined?
		// --------------------------------------------

		if ( isset( $methods[ $method ] ) === FALSE )
		{
			return $this->no_results('fbc');
		}

		// --------------------------------------------
		//  Prepare the arguments for the FBML string
		// --------------------------------------------

		$fb_arguments	= array();

		if ( ! empty( $methods[ $method ][ 'template_params' ] ) )
		{
			foreach ( $methods[ $method ][ 'template_params' ] as $param => $default )
			{
				if ( ee()->TMPL->fetch_param($param) !== FALSE )
				{
					if ($method == 'profile_pic')
					{
						$fb_arguments[]	= $param . '=' . $this->_chars_decode( ee()->TMPL->fetch_param($param) );
					}
					elseif ( in_array( $param, array( 'href', 'via_url' ) ) === TRUE )
					{
						$fb_arguments[]	= $param . '="' . $this->_prep_return( $this->_chars_decode( ee()->TMPL->fetch_param($param) ) ) . '"';
					}
					elseif ( is_numeric( $default ) === TRUE AND is_numeric( ee()->TMPL->fetch_param($param) ) === TRUE )
					{
						$fb_arguments[]	= $param . '="' . $this->_chars_decode( ee()->TMPL->fetch_param($param) ) . '"';
					}
					elseif ( is_string( $default ) === TRUE AND is_string( ee()->TMPL->fetch_param($param) ) === TRUE  )
					{
						$fb_arguments[]	= $param . '="' . $this->_chars_decode( ee()->TMPL->fetch_param($param) ) . '"';
					}
				}
				elseif ( ! empty( $default ) )
				{
					$fb_arguments[]	= $param . '="' . $default . '"';
				}
				elseif ( $param == 'uid' )
				{
					$fb_arguments[]	= $param . '="' . $this->api->get_user_id() . '"';
				}
			}
		}

		// --------------------------------------------
		//  Profile pic
		// --------------------------------------------
		
		if ($method == 'profile_pic')
		{
			return '<img src="//graph.facebook.com/' . $this->api->get_user_id() . '/picture?' . implode('&', $fb_arguments) . '" />';
		}

		// --------------------------------------------
		//  Prepare the FBML string
		// --------------------------------------------

		$tag	= $method;

		if ( ! empty( $methods[ $method ][ 'tag' ] ) )
		{
			$tag	= $methods[ $method ][ 'tag' ];
		}

		$return	= '<fb:' . $tag . ' ' . implode( ' ', $fb_arguments );

		if ( empty( $methods[ $method ][ 'closing_tag' ] ) OR $methods[ $method ][ 'closing_tag' ] == 'y' )
		{
			$return	.= '></fb:' . $tag . '>';
		}
		else
		{
			$return	.= '/>';
		}

		return $return;
	}

	/*	End FB parse */

	// -------------------------------------------------------------

	/**
	 * Form (sub)
	 *
	 * This method receives form config info and returns a properly formated EE form.
	 *
	 * @access	private
	 * @return	string
	 */

	public function _form( $arr = array() )
	{
		if ( empty( $arr ) ) return '';

		if ( empty( $arr['tagdata'] ) )
		{
			$tagdata	=	ee()->TMPL->tagdata;
		}
		else
		{
			$tagdata	= $arr['tagdata'];
			unset( $arr['tagdata'] );
		}

		$arr	= array(
			'hidden_fields'	=> $arr,
			'action'		=> $arr['RET'],
			'name'			=> ( ! empty( $arr['form_name'] ) ) ? $arr['form_name']: '',
			'id'			=> ( ! empty( $arr['form_id'] ) ) ? $arr['form_id']: '',
			'onsubmit'		=> ( ee()->TMPL->fetch_param('onsubmit') ) ? ee()->TMPL->fetch_param('onsubmit'): ''
		);

		// --------------------------------------------
		//  Override Form Attributes with form:xxx="" parameters
		// --------------------------------------------

		$extra_attributes = array();

		if (is_object(ee()->TMPL) AND ! empty(ee()->TMPL->tagparams))
		{
			foreach(ee()->TMPL->tagparams as $key => $value)
			{
				if (strncmp($key, 'form:', 5) == 0)
				{
					if (isset($arr[substr($key, 5)]))
					{
						$arr[substr($key, 5)] = $value;
					}
					else
					{
						$extra_attributes[substr($key, 5)] = $value;
					}
				}
			}
		}

		// --------------------------------------------
		//	Generate form
		// --------------------------------------------

		$r	= ee()->functions->form_declaration( $arr );

		$r	.= stripslashes($tagdata);

		$r	.= "</form>";

		// --------------------------------------------
		//	 Add <form> attributes from
		// --------------------------------------------

		$allowed = array(
			'accept', 'accept-charset', 'enctype', 'method', 'action', 'name', 'target', 'class', 'dir', 'id', 'lang', 'style', 'title', 'onclick', 'ondblclick', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onkeydown', 'onkeyup', 'onkeypress', 'onreset', 'onsubmit'
		);

		foreach($extra_attributes as $key => $value)
		{
			if ( in_array($key, $allowed) == FALSE AND strncmp($key, 'data-', 5) != 0) continue;

			$r = str_replace( "<form", '<form '.$key.'="'.htmlspecialchars($value).'"', $r );
		}

		return str_replace('&#47;', '/', $r);
	}

	/*	End form */

	// -------------------------------------------------------------

	/**
	 * Permissions
	 *
	 * Facebook allows users to grant a wide variety of permissions to a site. This method let's a site owner test which permissions have been granted to FBC.
	 * http://developers.facebook.com/docs/authentication/permissions/
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function permissions()
	{
		$this->api();

		$permissions	= $this->api->get_permissions();

		foreach ( $this->data->get_possible_permissions() as $val )
		{
			$cond['fbc_allow_' . $val]	= 'n';

			if (is_array($permissions) AND in_array($val, $permissions))
			{
				$cond['fbc_allow_' . $val]	= 'y';
			}
		}

		// --------------------------------------------
		//	Parse conditionals
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		return $tagdata;
	}

	/*	End has permission */

	// -------------------------------------------------------------

	/**
	 * Implode explode params
	 *
	 * @access	public
	 * @return	mixed
	 */

	public function _implode_explode_params( $params = array() )
	{
		$out	= array();

		// --------------------------------------------
		//	Implode?
		// --------------------------------------------

		if ( is_array( $params ) === TRUE )
		{
			foreach ( $params as $key => $val )
			{
				$out[]	= $key . '=' . $val;
			}

			return implode( '|', $out );
		}

		// --------------------------------------------
		//	Explode!
		// --------------------------------------------

		$params	= explode( '|', $params );

		foreach ( $params as $val )
		{
			$temp	= explode( '=', $val );

			if ( isset( $temp[1] ) === TRUE )
			{
				$out[ $temp[0] ]	= $temp[1];
			}
		}

		return $out;
	}

	/*	End implode explode params */

	// -------------------------------------------------------------

	/**
	 * Is facebook email
	 *
	 * @access	private
	 * @return	string
	 */

	 function _is_facebook_email( $name = '' )
	 {
		if ( $name == '' ) return FALSE;

		if ( preg_match( '/^[a-f0-9]{32}@facebook\.com/si', $name ) ) return TRUE;	// This is testing to see if the email address is an MD5 hash plus @facebook.com. It's the fake email format I use when someone passively registers.

		return FALSE;
	 }

	 /*	End is facebook email */

	// -------------------------------------------------------------

	/**
	 * Login
	 *
	 * This method creates login and logout buttons to help people login to FB and out of FB.
	 *
	 * @access	public
	 * @return	string
	 */

	public function login()
	{
		// --------------------------------------------
		//	Prep initial vars
		// --------------------------------------------

		$cond['fbc_login_button']			= '';
		$cond['fbc_logout_button']			= '';
		$cond['fbc_login_logout_button']	= '';
		$cond['fbc_logged_in']				= 'n';
		$cond['fbc_logged_out']				= 'y';

		// --------------------------------------------
		//	Prep params
		// --------------------------------------------

		$params		= array(
			'return_for_passive_register',
			'return_on_failure',
			'return_to_confirm_account_sync',
			'return_to_register',
			'return_when_logged_in',
			'return_when_logged_out'
		);

		$params	= $this->_set_return_params( $params );

		// --------------------------------------------
		//	Is the FB member an EE member? We'll need to know later in case we shouldn't actually log this person out of EE.
		// --------------------------------------------

		$params['is_ee_member']	= 'n';

		if ( $this->_facebook_member_is_ee_member() === TRUE )
		{
			$params['is_ee_member']	= 'y';
		}

		// --------------------------------------------
		//	Convert params into something portable
		// --------------------------------------------

		$params_string	= base64_encode( $this->_implode_explode_params( $params ) );

		// --------------------------------------------
		//	Button size
		// --------------------------------------------

		$button_size	= ' data-size="medium"';

		if ( ee()->TMPL->fetch_param('button_size') !== FALSE AND in_array( ee()->TMPL->fetch_param('button_size'), array( 'small', 'medium', 'large', 'xlarge' ) ) === TRUE )
		{
			$button_size	= ' data-size="' . ee()->TMPL->fetch_param('button_size') . '"';
		}

		// --------------------------------------------
		//	Logged out?
		// --------------------------------------------

		if ( $this->_passive_login_test() === FALSE )
		{
			// --------------------------------------------
			//	Prepare JS call.
			// --------------------------------------------

			$act		= ee()->functions->fetch_action_id('Fbc', 'facebook_login');

			$qs			= ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

			$url		= ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . $act . '&params=' . $params_string;

			$onlogin	= ' onlogin="window.location=\'' . $url . '\';"';

			// --------------------------------------------
			//	Show faces
			// --------------------------------------------

			$show_faces	= ' data-show-faces="false"';

			if ( ee()->TMPL->fetch_param('show_faces') !== FALSE AND in_array( ee()->TMPL->fetch_param('show_faces'), array( 'true', 'false' ) ) === TRUE )
			{
				$show_faces	= ' data-show-faces="' . ee()->TMPL->fetch_param('show_faces') . '"';
			}

			// --------------------------------------------
			//	Default Audience
			// --------------------------------------------

			$default_audience	= ' data-default-audience="everyone"';

			if (ee()->TMPL->fetch_param('default_audience'))
			{
				$default_audience	= ' data-default-audience="' . ee()->TMPL->fetch_param('default_audience') . '"';
			}

			// --------------------------------------------
			//	Max rows
			// --------------------------------------------

			$max_rows	= ' data-max-rows="1"';

			if ( ee()->TMPL->fetch_param('max_rows') !== FALSE AND is_numeric( ee()->TMPL->fetch_param('max_rows') ) === TRUE )
			{
				$max_rows	= ' data-max-rows="' . ee()->TMPL->fetch_param('max_rows') . '"';
			}

			// --------------------------------------------
			//	Permissions
			// --------------------------------------------
			//	http://developers.facebook.com/docs/authentication/permissions/
			// --------------------------------------------

			$permissions	= ' data-scope="public_profile"';

			if ( ee()->TMPL->fetch_param('permissions') !== FALSE )
			{
				$scope	= array_intersect( $this->data->get_possible_permissions(), explode( '|', ee()->TMPL->fetch_param('permissions') ) );

				$permissions	= ' data-scope="' . implode( ',', $scope ) . '"';
			}

			$login_button_label	= '';

			if ( ee()->TMPL->fetch_param('login_button_label') !== FALSE AND ee()->TMPL->fetch_param('login_button_label') != '' )
			{
				$login_button_label	= ee()->TMPL->fetch_param('login_button_label');
			}

			// --------------------------------------------
			//	Parse
			// --------------------------------------------
			
			$cond['fbc_login_button'] = $cond['fbc_login_logout_button'] = '<div class="fb-login-button"' . $onlogin . $max_rows . $button_size . $default_audience . $show_faces . $permissions . ' data-auto-logout-link="false">' . $login_button_label . '</div>';
		}

		// --------------------------------------------
		//	Logged in?
		// --------------------------------------------

		else
		{
			// --------------------------------------------
			//	Prepare JS call.
			// --------------------------------------------

			$qs		= ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

			$act	= ee()->functions->fetch_action_id('Fbc', 'facebook_logout');

			$url	= ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . $act . '&params=' . $params_string;

			$onlogout	= ' onlogin="window.location=\'' . $url . '\';"';

			// --------------------------------------------
			//	Cond
			// --------------------------------------------

			$logout_button_label	= '';

			if ( ee()->TMPL->fetch_param('logout_button_label') !== FALSE AND ee()->TMPL->fetch_param('logout_button_label') != '' )
			{
				$logout_button_label	= ee()->TMPL->fetch_param('logout_button_label');
			}

			$cond['fbc_logout_button'] = $cond['fbc_login_logout_button'] = '
<div class="fb-login-button" data-auto-logout-link="true"' . $button_size . $onlogout . '>' . $logout_button_label . '</div>';

			$cond['fbc_logged_in']	= 'y';
			$cond['fbc_logged_out']	= 'n';
		}

		// --------------------------------------------
		//	Parse and return
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		foreach ( $cond as $key => $val )
		{
			$tagdata	= str_replace( LD . $key . RD, $val, $tagdata );
		}

		return $tagdata;
	}

	/*	End login */

	// -------------------------------------------------------------

	/**
	 * Login button
	 *
	 * @access	public
	 * @return	string
	 */

	public function login_button( $label = '', $just_gimme_the_login_button = 'nope' )
	{
		$cond['fbc_logged_in']	= 'n';
		$cond['fbc_logged_out']	= 'y';

		// --------------------------------------------
		//	Prepare action
		// --------------------------------------------

		$act		= ee()->functions->fetch_action_id('Fbc', 'facebook_login');
		$onlogin	= '';

		// --------------------------------------------
		//	Prep params
		// --------------------------------------------

		$params		= array(
			'return_for_passive_register',
			'return_on_failure',
			'return_to_confirm_account_sync',
			'return_to_register',
			'return_when_logged_in'
		);

		$params	= $this->_set_return_params( $params );

		// --------------------------------------------
		//	Convert params into something portable
		// --------------------------------------------

		$params_string	= base64_encode( $this->_implode_explode_params( $params ) );

		// --------------------------------------------
		//	Button label
		// --------------------------------------------

		$button_label	= ( empty( $label ) ) ? '': $label;

		if ( ee()->TMPL->fetch_param('button_label') !== FALSE AND ee()->TMPL->fetch_param('button_label') != '' )
		{
			$button_label	= ee()->TMPL->fetch_param('button_label');
		}

		// --------------------------------------------
		//	Button size
		// --------------------------------------------

		$button_size	= ' data-size="medium"';

		if ( ee()->TMPL->fetch_param('button_size') !== FALSE AND in_array( ee()->TMPL->fetch_param('button_size'), array( 'small', 'medium', 'large', 'xlarge' ) ) === TRUE )
		{
			$button_size	= ' data-size="' . ee()->TMPL->fetch_param('button_size') . '"';
		}

		// --------------------------------------------
		//	Show faces
		// --------------------------------------------

		$show_faces	= ' data-show-faces="false"';

		if ( ee()->TMPL->fetch_param('show_faces') !== FALSE AND in_array( ee()->TMPL->fetch_param('show_faces'), array( 'true', 'false' ) ) === TRUE )
		{
			$show_faces	= ' data-show-faces="' . ee()->TMPL->fetch_param('show_faces') . '"';
		}

		// --------------------------------------------
		//	Default Audience
		// --------------------------------------------

		$default_audience	= ' data-default-audience="everyone"';

		if (ee()->TMPL->fetch_param('default_audience'))
		{
			$default_audience	= ' data-default-audience="' . ee()->TMPL->fetch_param('default_audience') . '"';
		}

		// --------------------------------------------
		//	Max rows
		// --------------------------------------------

		$max_rows	= ' data-max-rows="1"';

		if ( ee()->TMPL->fetch_param('max_rows') !== FALSE AND is_numeric( ee()->TMPL->fetch_param('max_rows') ) === TRUE )
		{
			$max_rows	= ' data-max-rows="' . ee()->TMPL->fetch_param('max_rows') . '"';
		}

		// --------------------------------------------
		//	Permissions
		// --------------------------------------------
		//	http://developers.facebook.com/docs/authentication/permissions/
		// --------------------------------------------

		$permissions	= ' data-scope="public_profile"';

		if ( ee()->TMPL->fetch_param('permissions') !== FALSE )
		{
			$scope	= array_intersect( $this->data->get_possible_permissions(), explode( '|', ee()->TMPL->fetch_param('permissions') ) );

			$permissions	= ' data-scope="' . implode( ',', $scope ) . '"';
		}

		// --------------------------------------------
		//	Prepare JS call.
		// --------------------------------------------

		$qs		= ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

		$url	= ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . $act . '&params=' . $params_string;

		$onlogin	= ' onlogin="window.location=\'' . $url . '\';"';

		// --------------------------------------------
		//	Parse
		// --------------------------------------------

		$cond['fbc_login_button']	= '<div class="fb-login-button"' . $button_size . $onlogin . $default_audience . $show_faces . $max_rows . $permissions . '>' . $button_label . '</div>';

		// --------------------------------------------
		//	Just return the button and get out?
		// --------------------------------------------

		if ( $just_gimme_the_login_button != 'nope' )
		{
			return $cond['fbc_login_button'];
		}

		// --------------------------------------------
		//	Login status?
		// --------------------------------------------

		if ( $this->_passive_login_test() === TRUE )
		{
			$cond['fbc_logged_in']	= 'y';
			$cond['fbc_logged_out']	= 'n';
		}

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		if ( $label != '' OR strpos( ee()->TMPL->tagdata, '{fbc_' ) === FALSE ) return $cond['fbc_login_button'];

		return str_replace( LD . 'fbc_login_button' . RD, $cond['fbc_login_button'], $tagdata );
	}

	/*	End login button */

	// -------------------------------------------------------------

	/**
	 * Login / logout button
	 *
	 * This is deprecated as of FBC 2.0.0
	 *
	 * @access	public
	 * @return	string
	 */

	public function login_logout_button()
	{
		return $this->login();
	}

	/*	End login / logout button */

	// -------------------------------------------------------------

	/**
	 * Login status
	 *
	 * @access	public
	 * @return	string
	 */

	public function login_status()
	{
		// --------------------------------------------
		//	Prep initial vars
		// --------------------------------------------

		$cond['fbc_logged_into_ee']					= ( ee()->session->userdata('member_id') == '0' ) ? 'n': 'y';
		$cond['fbc_logged_into_facebook']			= 'n';
		$cond['fbc_logged_into_facebook_and_ee']	= 'n';
		$cond['fbc_logged_into_ee_and_facebook']	= 'n';

		// --------------------------------------------
		//	Is this user already a registered FB user?
		// --------------------------------------------

		$this->api();
		
		//$info = $this->api->get_user_info();
		//print_r($info);
		
		$force_refresh	= (ee()->session->userdata('member_id') == '0') ? TRUE: FALSE;

		// By sending TRUE, we force a refresh of login status from the API,
		// insuring it is always valid and up to date.
		if ( ( $uid = $this->api->get_user_id($force_refresh)) !== FALSE )
		{
			$cond['fbc_logged_into_facebook']	= 'y';
		}

		// --------------------------------------------
		//	Is this user already a registered FB user in EE?
		// --------------------------------------------

		if ( $this->_facebook_member_is_ee_member() !== FALSE )
		{
			$cond['fbc_logged_into_facebook_and_ee']	= 'y';
			$cond['fbc_logged_into_ee_and_facebook']	= 'y';
		}

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );
		return $tagdata;
	}

	/*	End login status */

	// -------------------------------------------------------------

	/**
	 * Logout button
	 *
	 * @access	public
	 * @return	string
	 */

	public function logout_button()
	{
		// --------------------------------------------
		//	Logged in?
		// --------------------------------------------

		if ( $this->_passive_login_test() === FALSE )
		{
			$cond['fbc_logout_button']	= '';
			$cond['fbc_logged_in']		= 'n';
			$cond['fbc_logged_out']	= 'y';
			$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );
			return str_replace( LD . 'fbc_logout_button' . RD, '', $tagdata );
		}

		// --------------------------------------------
		//	Fetch action
		// --------------------------------------------

		$act		= ee()->functions->fetch_action_id('Fbc', 'facebook_logout');
		$onlogin	= '';

		$params		= array(
			'return_when_logged_out'	=> ee()->uri->uri_string
		);

		foreach ( $params as $key => $val )
		{
			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->TMPL->fetch_param($key) );
			}

			$params[$key]	= $val;
		}

		// --------------------------------------------
		//	Is the FB member an EE member? We'll need to know later in case we shouldn't actually log this person out of EE.
		// --------------------------------------------

		$params['is_ee_member']	= 'n';

		if ( $this->_facebook_member_is_ee_member() === TRUE )
		{
			$params['is_ee_member']	= 'y';
		}

		// --------------------------------------------
		//	Prepare JS call.
		// --------------------------------------------

		$qs		= ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

		$url	= ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . $act . '&return_when_logged_out=' . base64_encode( $params['return_when_logged_out'] ) . '&is_ee_member=' . base64_encode( $params['is_ee_member'] );

		$onlogout	= ' onclick="FB.logout(function(response){window.location=\'' . $url . '\'})"';

		$cond['fbc_logout_button']	= '
<a href="#"' . $onlogout . '>' . '<img src="http://static.ak.fbcdn.net/images/fbconnect/logout-buttons/logout_small.gif" border="0" alt="Facebook Logout" />' . '</a>';

		$cond['fbc_logged_in']	= 'y';
		$cond['fbc_logged_out']	= 'n';

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		return str_replace( LD . 'fbc_logout_button' . RD, $cond['fbc_logout_button'], $tagdata );
	}

	/*	End logout button */

	// -------------------------------------------------------------

	/**
	 * Logout js
	 *
	 * @access	public
	 * @return	string
	 */

	public function logout_js()
	{
		// --------------------------------------------
		//	Logged in?
		// --------------------------------------------

		if ( $this->_passive_login_test() === FALSE )
		{
			$cond['fbc_logout_js']		= '';
			$cond['fbc_logged_in']		= 'n';
			$cond['fbc_logged_out']	= 'y';
			$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );
			return str_replace( LD . 'fbc_logout_js' . RD, '', $tagdata );
		}

		// --------------------------------------------
		//	Fetch action
		// --------------------------------------------

		$act		= ee()->functions->fetch_action_id('Fbc', 'facebook_logout');
		$onlogin	= '';

		$params		= array(
			'return_when_logged_out'	=> ee()->uri->uri_string
		);

		foreach ( $params as $key => $val )
		{
			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->TMPL->fetch_param($key) );
			}

			$params[$key]	= $val;
		}

		// --------------------------------------------
		//	Is the FB member an EE member? We'll need to know later in case we shouldn't actually log this person out of EE.
		// --------------------------------------------

		$params['is_ee_member']	= 'n';

		if ( $this->_facebook_member_is_ee_member() === TRUE )
		{
			$params['is_ee_member']	= 'y';
		}

		// --------------------------------------------
		//	Parse
		// --------------------------------------------

		$cond['fbc_logged_in']	= 'y';
		$cond['fbc_logged_out']	= 'n';

		$qs		= ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

		$act	= ee()->functions->fetch_action_id('Fbc', 'facebook_logout');

		$url	= ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . $act . '&return_when_logged_out=' . base64_encode( $params['return_when_logged_out'] ) . '&is_ee_member=' . base64_encode( $params['is_ee_member'] );

		$cond['fbc_logout_js']	= 'FB.logout(function(response){window.location=\'' . $url . '\'})';

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		return str_replace( LD . 'fbc_logout_js' . RD, $cond['fbc_logout_js'], $tagdata );
	}

	/*	End logout js */

	// -------------------------------------------------------------

	/**
	 * Member data
	 *
	 * This method tries to negotiate a correct screen name for a given user.
	 *
	 * @access	public
	 * @return	string
	 */

	public function member_data()
	{
		// --------------------------------------------
		//	Prep conditionals
		// --------------------------------------------

		$cond	= array(
			'fbc_user_id'					=> '',
			'fbc_screen_name'				=> '',
			'fbc_username'					=> '',
			'fbc_passive'					=> '',
			'fbc_profile_pic'				=> '',
			'fbc_facebook_friends_count'	=> '',
			'fbc_facebook_profile_pic'		=> ''
		);

		// --------------------------------------------
		//	Do we have a member id?
		// --------------------------------------------

		$member_id	= '';

		if ( ee()->TMPL->fetch_param('member_id') !== FALSE AND is_numeric( ee()->TMPL->fetch_param('member_id') ) === TRUE )
		{
			$member_id	= ee()->TMPL->fetch_param('member_id');
		}
		elseif ( ee()->TMPL->fetch_param('member_id') !== FALSE AND ee()->TMPL->fetch_param('member_id') == 'CURRENT_USER' AND ee()->session->userdata('member_id') != 0 )
		{
			$member_id	= ee()->session->userdata('member_id');
		}
		elseif ( ee()->session->userdata('member_id') != 0 )
		{
			$member_id	= ee()->session->userdata('member_id');
		}

		// --------------------------------------------
		//	Do we have a member id and is it meaningful?
		// --------------------------------------------

		if ( ( $member_data	= $this->data->get_member_data_from_member_id( $member_id, 'all_groups' ) ) !== FALSE )
		{
			// --------------------------------------------
			//	Was this person registered passively using Facebook?
			// --------------------------------------------

			if (
				$member_data['facebook_connect_user_id'] != ''
				AND (
					$this->_is_facebook_email( $member_data['email'] ) === TRUE
					OR (
						ee()->TMPL->fetch_param('use_facebook_data') !== FALSE
						AND $this->check_yes( ee()->TMPL->fetch_param('use_facebook_data') ) === TRUE
						)
					)
				)
			{
				$cond['fbc_user_id']		= $member_data['facebook_connect_user_id'];
				$cond['fbc_screen_name']	= '<fb:name uid="' . $member_data['facebook_connect_user_id'] . '" capitalize="true" linked="false" />';
				$cond['fbc_username']		= '<fb:name uid="' . $member_data['facebook_connect_user_id'] . '" capitalize="true" linked="false" />';
				$cond['fbc_passive']		= 'y';
			}
			else
			{
				$cond['fbc_user_id']		= $member_data['facebook_connect_user_id'];
				$cond['fbc_screen_name']	= $member_data['screen_name'];
				$cond['fbc_username']		= $member_data['username'];
				$cond['fbc_passive']		= 'n';
			}
		}

		// --------------------------------------------
		//	Add profile pic
		// --------------------------------------------

		$member_data	= $this->data->get_member_data_from_member_id( $member_id );

		if ( ! empty( $member_data['facebook_connect_user_id'] ) )
		{
			$profile_pic_args	= array();
		
			// --------------------------------------------
			//	Profile pic type
			// --------------------------------------------

			if ( ee()->TMPL->fetch_param( 'profile_pic_type' ) !== FALSE AND in_array( ee()->TMPL->fetch_param( 'profile_pic_type' ), array( 'square', 'small', 'normal', 'large' ) ) === TRUE )
			{
				$profile_pic_args[]	= 'type=' . ee()->TMPL->fetch_param( 'profile_pic_type' );
			}

			// --------------------------------------------
			//	Profile pic width
			// --------------------------------------------

			if ( ee()->TMPL->fetch_param( 'profile_pic_width' ) !== FALSE AND is_numeric( ee()->TMPL->fetch_param( 'profile_pic_width' )  ) === TRUE )
			{
				$profile_pic_args[]	= 'width=' . ee()->TMPL->fetch_param( 'profile_pic_width' );
			}

			// --------------------------------------------
			//	Profile pic height
			// --------------------------------------------

			if ( ee()->TMPL->fetch_param( 'profile_pic_height' ) !== FALSE AND is_numeric( ee()->TMPL->fetch_param( 'profile_pic_height' )  ) === TRUE )
			{
				$profile_pic_args[]	= 'height=' . ee()->TMPL->fetch_param( 'profile_pic_height' );
			}

			// --------------------------------------------
			//	Assemble var
			// --------------------------------------------

			$cond['fbc_facebook_profile_pic'] = $cond['fbc_profile_pic'] = '<img src="//graph.facebook.com/' . $member_data['facebook_connect_user_id'] . '/picture?' . implode('&', $profile_pic_args) . '" />';
		}

		// --------------------------------------------
		//	Add friends count if needed
		// --------------------------------------------

		if ( strpos( ee()->TMPL->tagdata, 'fbc_facebook_friends_count' ) !== FALSE )
		{
			$this->api();

			$cond['fbc_facebook_friends_count']	= $this->api->get_friends_count();
		}

		// --------------------------------------------
		//	Add basic social graph data?
		// --------------------------------------------

		if ( strpos( ee()->TMPL->tagdata, LD . 'fbc_facebook_' ) !== FALSE )
		{
			$out	= array();

			// --------------------------------------------
			//	Set defaults
			// --------------------------------------------

			$default_user_data	= array(
				'id'			=> '',
				'name'			=> '',
				'first_name'	=> '',
				'middle_name'	=> '',
				'last_name'		=> '',
				'link'			=> '',
				'username'		=> '',
				'gender'		=> '',
				'locale'		=> '',
				'email'			=> ''
			);

			// --------------------------------------------
			//	Hit the API
			// --------------------------------------------

			$this->api();
			
			//$this->dd($this->api->get_graph( $member_data['facebook_connect_user_id'] ));

			if ( ! empty( $member_data['facebook_connect_user_id'] ) AND ( $graph = $this->api->get_graph( $member_data['facebook_connect_user_id'] ) ) !== FALSE )
			{
				$out['fbc_facebook_username']	= '';
				$out['fbc_facebook_education']	= '';
				
				foreach ( $graph as $key => $val )
				{
					$out[ 'fbc_facebook_' . $key ]	= $val;
				}
			}
			else
			{
				$out	= array_merge( $out, $default_user_data );
			}

			ee()->TMPL->tagdata	= ee()->TMPL->parse_variables( ee()->TMPL->tagdata, array( $out ));
		}

		// --------------------------------------------
		//	Parse conditionals
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		// --------------------------------------------
		//	Loop and parse
		// --------------------------------------------

		foreach ( $cond as $key => $val )
		{
			if ( strpos( $tagdata, LD.$key.RD ) === FALSE ) continue;

			$tagdata	= str_replace( LD.$key.RD, $val, $tagdata );
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $tagdata;
	}

	/*	End member data */

	// -------------------------------------------------------------

	/**
	 * Passive login test
	 *
	 * @access	private
	 * @return	string
	 */

	public function _passive_login_test()
	{
		// --------------------------------------------
		//	By default we test both EE and FB login status, This can be overridden to only test FB login status.
		// --------------------------------------------

		if ( ee()->TMPL->fetch_param( 'ignore_ee_login' ) === FALSE OR ee()->TMPL->fetch_param( 'ignore_ee_login' ) != 'yes' )
		{
			if ( ee()->session->userdata('member_id') == 0 )
			{
				return FALSE;
			}
		}

		$this->api();

		// By sending TRUE, we force a refresh of login status from the API,
		// insuring it is always valid and up to date.
		if ( ( $uid = $this->api->get_user_id()) == 0 )
		{
			return FALSE;
		}

		if ( ee()->TMPL->fetch_param( 'ignore_ee_login' ) === FALSE OR ee()->TMPL->fetch_param( 'ignore_ee_login' ) != 'yes' )
		{
			// --------------------------------------------
			//	If they are logged in to FB and EE, the accounts have to be linked for us to really say they logged in.
			// --------------------------------------------

			if ( $this->data->get_member_id_from_facebook_user_id( $uid ) != ee()->session->userdata('member_id') )
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	/*	End passive login test */

	// -------------------------------------------------------------

	/**
	 * Prep return
	 *
	 * @access		private
	 * @return		string
	 */

	public function _prep_return( $return = '' )
	{
		// --------------------------------------------
		//	We need to force session ids onto our urls, we have to set the template_type in order to do it.
		// --------------------------------------------

		if ( ee()->config->item('user_session_type') != 'c' )
		{
			ee()->functions->template_type	= 'webpage';
		}

		// --------------------------------------------
		//	Prep return
		// --------------------------------------------

		if ( $return == '' )
		{
			// $return = ee()->functions->fetch_current_uri();	// Commented out by mitchell@solspace.com 2011 06 02. This causes people to be returned to ACT urls when the param provided is empty or points to a site's home page.
		}

		if ( preg_match( "/".LD."\s*path=(.*?)".RD."/", $return, $match ) )
		{
			$return	= ee()->functions->create_url( $match['1'] );
		}
		elseif ( $return == LD . 'site_url' . RD )
		{
			$return	= ee()->config->item( 'site_url' );
		}
		elseif ( stristr( $return, "http://" ) === FALSE && stristr( $return, "https://" ) === FALSE )
		{
			$return	= ee()->functions->create_url( $return );
		}

		return $return;
	}

	// End prep return

	// -------------------------------------------------------------

	/**
	 * Prepare page
	 *
	 * This method is called at the top of an EE template. It parses into the template some of the essential pieces needed for Facebook functionality.
	 *
	 * @access	public
	 * @return	string
	 */

	public function prepare_page()
	{
		// --------------------------------------------
		//	Should we execute?
		// --------------------------------------------

		if ( ee()->TMPL->fetch_param( 'execute' ) !== FALSE AND ee()->TMPL->fetch_param( 'execute' ) != 'yes' )
		{
			return '';
		}

		// --------------------------------------------
		//	Language?
		// --------------------------------------------

		$language	= 'en_US';

		if ( ee()->TMPL->fetch_param( 'language' ) !== FALSE AND ee()->TMPL->fetch_param( 'language' ) != '' )
		{
			$language	= ee()->TMPL->fetch_param( 'language' );
		}

		// --------------------------------------------
		//	Prepare <html> tag
		// --------------------------------------------
		//	Deprecate this by 20150601

		if ( preg_match( '/<html(.*?)>/is', ee()->TMPL->template, $match ) )
		{
			if ( strpos( $match[1], 'http://www.facebook.com' ) === FALSE )
			{
				//ee()->TMPL->template	= str_replace( $match[0], '<html' . $match[1] . ' ' . $this->facebook_xmlns_definition . '>', ee()->TMPL->template );
			}
		}

		// --------------------------------------------
		//	Add FB.init() just above </body>
		// --------------------------------------------

		if (ee()->config->item('fbc_app_id') !== '' &&
			strpos( ee()->TMPL->template, ee()->config->item('fbc_app_id') ) === FALSE )
		{
			ee()->TMPL->template	= preg_replace( '/(<\\' . T_SLASH . 'body>)/is', $this->data->get_facebook_loader_js( $language ) . NL . '$1', ee()->TMPL->template, 1 );

			$this->data->cached['facebook_loader_js']	= TRUE;
		}

		return '';
	}

	/*	End prepare page */

	// -------------------------------------------------------------

	/**
	 * Prompt for permission
	 *
	 * Deprecated in favor of login_button() as of Facebook 2.0 API.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function prompt_for_permission()
	{
		return $this->login_button();
	}

	/*	End prompt for permission */

	// -------------------------------------------------------------

	/**
	 * Set permissions
	 *
	 * Deprecated in favor of login_button() as of Facebook 2.0 API.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function set_permissions()
	{
		return $this->login_button();
	}

	/*	End set permissions */

	// -------------------------------------------------------------

	/**
	 * Get return params
	 *
	 * This method helps us convert return params from a base64 encoded string.
	 *
	 * @access	public
	 * @return	array
	 */

	public function _get_return_params( $params = array(), $expected_params = array() )
	{
		// --------------------------------------------
		//	Set base defaults
		// --------------------------------------------

		$out		= array();

		$default	= '';

		// --------------------------------------------
		//	Capture default return
		// --------------------------------------------

		if ( ! empty( $params['return_default'] ) )
		{
			$default	= $params['return_default'];
		}

		// --------------------------------------------
		//	Loop through everything that we expect to get, and find some way to set that
		// --------------------------------------------

		foreach ( $expected_params as $key )
		{
			// --------------------------------------------
			//	If our param exists...
			// --------------------------------------------

			if ( ! empty( $params[ $key ] ) )
			{
				// --------------------------------------------
				//	If it's marked as a reference to another param...
				// --------------------------------------------

				if ( strpos( $params[ $key ], FBC_URI ) !== FALSE AND ( $d = substr( $params[ $key ], strlen( FBC_URI ) ) ) !== FALSE )
				{
					// --------------------------------------------
					//	If it references our default return...
					// --------------------------------------------

					if ( $d == 'return_default' )
					{
						$out[ $key ]	= $default;
					}

					// --------------------------------------------
					//	Or if it references the value of another param...
					// --------------------------------------------

					elseif ( ! empty( $params[ $d ] ) )
					{
						$out[ $key ]	= $params[ $d ];
					}
				}

				// --------------------------------------------
				//	It was not one of our referenced params
				// --------------------------------------------

				else
				{
					$out[ $key ]	= $params[ $key ];
				}
			}

			// --------------------------------------------
			//	Please set something, anything!
			// --------------------------------------------

			else
			{
				$out[ $key ]	= '';
			}
		}

		// --------------------------------------------
		//	Meowt!
		// --------------------------------------------

		return $out;
	}

	/*	End get return params */

	// -------------------------------------------------------------

	/**
	 * Set return params
	 *
	 * This method helps us capture, prep and store our return params. We have to send the user to Facebook for authentication, we need to know what to do when they return.
	 *
	 * @access	public
	 * @return	array
	 */

	public function _set_return_params( $params = array() )
	{
		$default	= FBC_URI . 'return_default';

		$out		= array(
			'return_default'	=> ee()->uri->uri_string
		);

		foreach ( $params as $key )
		{
			// --------------------------------------------
			//	Is this param set in the template?
			// --------------------------------------------

			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->TMPL->fetch_param($key) );
			}
			else
			{
				$val	= $default;
			}

			// --------------------------------------------
			//	Has this param value already been stored in the params array? If so, reference it.
			// --------------------------------------------

			if ( $val != $default AND ( $search = array_search( $val, $out ) ) !== FALSE )
			{
				$out[$key]	= FBC_URI . $search;
			}
			else
			{
				$out[$key]	= $val;
			}
		}

		return $out;
	}

	/*	End set return params */

	// -------------------------------------------------------------

	/**
	 * Redirect
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function _redirect( $url )
	{
		$url	= $this->_prep_return( $url );

		ee()->functions->redirect( $url );
	}

	/* End redirect */

	// -------------------------------------------------------------

	/**
	 * Register
	 *
	 * Register a new user using the abridged Facebook approach.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function register()
	{
		$this->actions();
		$this->api();

		// --------------------------------------------
		//	Run security tests
		// --------------------------------------------

		if ( $this->actions->_security() === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Already logged in?
		// --------------------------------------------

		if ( ee()->session->userdata('member_id') != 0 )
		{
			$this->error[]	= lang('already_logged_in');
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Do we allow new member registrations?
		// --------------------------------------------

		if ( ee()->config->item('allow_member_registration') == 'n' )
		{
			$this->error[]	= lang('registration_not_enabled');
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Divert when Facebook is the one POSTing registration data
		// --------------------------------------------

		if ( ! empty( $_POST['signed_request'] ) )
		{
			// --------------------------------------------
			//	Log that this came from FB
			// --------------------------------------------

			$this->from_fb	= TRUE;

			// --------------------------------------------
			//	Spin up the API
			// --------------------------------------------

			if ( ( $fb_post = $this->api->convert_signed_request( $_POST['signed_request'] ) ) == FALSE )
			{
				$this->error[]	= lang('facebook_signed_request_failed');
				return $this->show_error($this->error );
			}

			// --------------------------------------------
			//	Prep for later
			// --------------------------------------------

			$params		= array(
				'fields'			=> '',
				'return_on_success'	=> ''
			);

			// --------------------------------------------
			//	Get params for later
			// --------------------------------------------

			foreach ( $params as $key => $val )
			{
				if ( ( $param = $this->data->get_param( $_GET['hash'], $key, 'delete' ) ) !== FALSE )
				{
					$params[$key]	= $param;
				}
				else
				{
					$params[$key]	= $val;
				}
			}

			// --------------------------------------------
			//	Does the field list match?
			// --------------------------------------------

			if ( empty( $fb_post['registration_metadata']['fields'] ) OR $params['fields'] != $fb_post['registration_metadata']['fields'] )
			{
				$this->error[]	= lang('facebook_field_metadata_failed');
				return $this->show_error($this->error );
			}

			// --------------------------------------------
			//	Has a uid been sent over?
			// --------------------------------------------

			$uid	= '';

			if ( ! empty( $fb_post['uid'] ) )
			{
				$uid	= $fb_post['uid'];
			}

			// --------------------------------------------
			//	Force data into POST
			// --------------------------------------------

			$incoming_post	= (array) $fb_post['registration'];

			foreach ( $incoming_post as $field => $val )
			{
				$_POST[$field]	= $val;
			}

			$_POST['name']	= ( empty( $_POST['name'] ) ) ? '': $_POST['name'];

			// --------------------------------------------
			//	Force return
			// --------------------------------------------

			$_POST['return']	= $params['return_on_success'];

			// --------------------------------------------
			//	Fix username
			// --------------------------------------------

			if ( ! empty( $_POST['username'] ) )
			{
				$_POST['username']	= str_replace( ' ', '_', $_POST['username'] );
			}
			else
			{
				$_POST['username']	= str_replace( ' ', '_', $_POST['name'] );	// FB requires that their reg form contain a 'name' field so we know it's there.
			}

			// --------------------------------------------
			//	Fix screen name
			// --------------------------------------------

			if ( empty( $_POST['screen_name'] ) )
			{
				$_POST['screen_name']	= $_POST['name'];
			}

			// --------------------------------------------
			//	Fix email
			// --------------------------------------------

			if ( empty( $_POST['email'] ) )
			{
				$_POST['email']	= md5( time() . $uid ) . '@facebook.com';
			}
		}

		// --------------------------------------------
		//  Get the FB user id if we don't already have it
		// --------------------------------------------

		if ( empty( $uid ) )
		{
			if ( ( $uid = $this->api->get_user_id() ) === FALSE )
			{
				$this->error[]	= lang('facebook_not_logged_in');
				return $this->show_error($this->error );
			}
		}

		// --------------------------------------------
		//	Do we already have a record for this facebook user id?
		// --------------------------------------------

		if ( $this->data->get_member_id_from_facebook_user_id( $uid ) !== FALSE )
		{
			$this->error[]	= lang( 'fb_user_already_exists' );
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Clean the post
		// --------------------------------------------

		$_POST	= ee()->security->xss_clean( $_POST );

		// --------------------------------------------
		//	Handle alternate username / screen name
		// --------------------------------------------

		if ( ee()->db->table_exists('exp_user_params') === TRUE )
		{
			$pref_query = ee()->db->query("SELECT COUNT(*) AS count FROM exp_user_preferences WHERE preference_name = 'email_is_username' AND preference_value = 'y'");

			if (
				empty( $_POST['email'] )
				AND ! empty( $_POST['username'] )
				AND $pref_query->row('count') == 1
			)
			{
				$_POST['email']	= $_POST['username'];
			}
		}

		// --------------------------------------------
		//	Do we have email, username, screen name?
		// --------------------------------------------

		foreach ( array( 'email' => 'required', 'username' => 'required', 'screen_name' => 'optional', 'timezone' => 'optional' ) as $key => $val )
		{
			if ( empty( $_POST[$key] ) AND $val == 'required' )
			{
				$this->error[]	= lang( $key . '_required_for_registration' );
			}
			elseif ( ! empty( $_POST[$key] ) )
			{
				$member_data[$key]	= $_POST[$key];
			}
		}

		// --------------------------------------------
		//	Prep and load custom member fields and check for required's
		// --------------------------------------------

		$custom_member_fields	= $this->data->get_member_fields();

		foreach ( $custom_member_fields as $name => $field_data )
		{
			if ( ! empty( $_POST[ 'm_field_id_' . $field_data['id'] ] ) )
			{
				$member_data[ 'm_field_id_' . $field_data['id'] ]	= $_POST[ 'm_field_id_' . $field_data['id'] ];
			}
			elseif ( ! empty( $_POST[ $name ] ) )
			{
				$member_data[ 'm_field_id_' . $field_data['id'] ]	= $_POST[ $name ];
			}
			elseif ( $field_data['required'] == 'y' )
			{
				$this->error[]	= str_replace( '%field_label%', $field_data['label'], lang( 'blank_required_for_registration' ) );
			}
		}

		// --------------------------------------------
		//	Assign member group
		// --------------------------------------------
		//	We observe activation protocols, but if no activation is required, we make sure that we have a default member group been designation.
		// --------------------------------------------

		if ( ( $member_data['group_id'] = ee()->config->item('fbc_member_group') ) === FALSE )
		{
			$this->error[]	= lang('facebook_member_group_missing');
		}

		// --------------------------------------------
		//	If we are email activating or admin activating, we do some tricks with the group id
		// --------------------------------------------

		if ( ee()->config->item('fbc_account_activation') == 'fbc_email_activation' OR ee()->config->item('fbc_account_activation') == 'fbc_admin_activation' )
		{
			$member_data['group_id']	= 4;
		}

		// --------------------------------------------
		// Require captcha?
		// --------------------------------------------
		// We only check if this is a standard registration through EE. If the reg comes through FB, Fb does not send the captcha value in the POST. We just assumed that FB would not allow an invalid captcha through.
		// --------------------------------------------

		if ( $this->from_fb === FALSE )
		{
			if ( ( $param = $this->data->get_param( $_GET['hash'], 'show_captcha', 'delete' ) ) !== FALSE )
			{
				$show_captcha	= $param;
			}
			else
			{
				$show_captcha	= '';
			}

			if ( $this->check_yes( $show_captcha ) === TRUE AND empty( $_POST['captcha'] ) )
			{
				$this->error[] = lang('captcha_required');
			}
		}

		// --------------------------------------------
		//	Require terms?
		// --------------------------------------------

		if ( ee()->config->item('require_terms_of_service') == 'y' AND empty( $_POST['accept_terms'] ) )
		{
			$this->error[] = lang('mbr_terms_of_service_required');
		}

		// --------------------------------------------
		//	Will there be a password?
		// --------------------------------------------

		if ( ! empty( $_POST['password'] ) )
		{
			if ( empty( $_POST['password_confirm'] ) )
			{
				$this->error[] = lang('passwords_do_not_match');
			}
			else
			{
				$member_data['password']			= $_POST['password'];
				$member_data['password_confirm']	= $_POST['password_confirm'];
			}
		}

		// --------------------------------------------
		//	Errors?
		// --------------------------------------------

		if ( count( $this->error ) > 0 )
		{
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Force screen name?
		// --------------------------------------------

		if ( empty( $member_data['screen_name'] ) )
		{
			$member_data['screen_name']	= $member_data['username'];
		}

		// --------------------------------------------
		//	Validate
		// --------------------------------------------

		$validate = array(
			'val_type'		=> 'new', // new or update
			'fetch_lang'	=> TRUE,
			'require_cpw'	=> FALSE,
			'enable_log'	=> FALSE,
			'username'		=> $member_data['username'],
			'screen_name'	=> stripslashes( $member_data['screen_name'] ),
			'email'			=> $member_data['email']
		);

		if ( ! empty( $member_data['password'] ) )
		{
			$validate['password']			= $member_data['password'];
			$validate['password_confirm']	= $member_data['password_confirm'];
		}

		ee()->load->library('validate', $validate, 'validate');

		ee()->validate->validate_username();
		ee()->validate->validate_screen_name();
		ee()->validate->validate_email();

		if ( ! empty( $member_data['password'] ) )
		{
			ee()->validate->validate_password();
		}

		if ( ee()->db->table_exists('exp_user_params') === TRUE )
		{
			if (
				ee()->config->item('user_email_is_username') != 'n'
				AND ( $key = array_search( lang('username_password_too_long'), ee()->validate->errors ) ) !== FALSE
				)
			{
				if (strlen(ee()->validate->username) <= 50)
				{
					unset(ee()->validate->errors[$key]);
				}
				else
				{
					ee()->validate->errors[$key] = str_replace('32', '50', ee()->validate->errors[$key]);
				}
			}
		}

		if ( count( ee()->validate->errors ) > 0 )
		{
			$this->error	= array_merge( $this->error, ee()->validate->errors );
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Do we require captcha? And are we not in from_fb mode?
		// --------------------------------------------

		if ( ee()->config->item('use_membership_captcha') == 'y' AND $this->from_fb === FALSE )
		{
			$query = ee()->db->query(
				"SELECT COUNT(*) AS count
				FROM exp_captcha
				WHERE word='" . ee()->db->escape_str( $_POST['captcha'] ) . "'
				AND ip_address = '" . ee()->db->escape_str( ee()->input->ip_address() ) . "'
				AND date > UNIX_TIMESTAMP()-7200"
			);

			if ( $query->row('count') == 0 )
			{
				return $this->show_error(lang('captcha_incorrect') );
			}

			ee()->db->query(
				"DELETE FROM exp_captcha
				WHERE (word='" . ee()->db->escape_str($_POST['captcha']) . "'
				AND ip_address = '" . ee()->db->escape_str( ee()->input->ip_address() ) . "')
				OR date < UNIX_TIMESTAMP()-7200"
			);
		}

		// --------------------------------------------
		//	Check form hash
		// --------------------------------------------
		//	If we are registering through Facebook's registration plugin, we have gone through the signed token validation which proves that the request originated from our domain.
		// --------------------------------------------

		if ( $this->from_fb === FALSE )
		{
			if ($this->_check_form_hash() === FALSE)
			{
				return $this->show_error(lang('not_authorized'));
			}
		}

		// --------------------------------------------
		//	Errors?
		// --------------------------------------------

		if ( count( $this->error ) > 0 )
		{
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Attempt to create account
		// --------------------------------------------

		if ( ( $member_data = $this->actions->create_member_account( $uid, $member_data ) ) === FALSE )
		{
			$this->error[]	= lang('could_not_create_account');
			return $this->show_error($this->error );
		}

		// --------------------------------------------
		//	Send admin notification
		// --------------------------------------------

		$this->actions->send_admin_notification_of_registration( $member_data );

		// --------------------------------------------
		//	'fbc_register_end' hook.
		// --------------------------------------------
		//	Additional processing when a member is created through the User Side
		// --------------------------------------------

		if (ee()->extensions->active_hook('fbc_register_end') === TRUE)
		{
			$edata = ee()->extensions->universal_call( 'fbc_register_end', $this, $member_data['member_id'] );
			if (ee()->extensions->end_script === TRUE) return;
		}

		// --------------------------------------------
		//	Prep return
		// --------------------------------------------

		$return	= '';

		if ( ! empty( $_POST['RET'] ) )
		{
			$return	= $_POST['RET'];
		}

		if ( ! empty( $_POST['return'] ) )
		{
			$return	= $_POST['return'];
		}

		$return	= $this->_chars_decode( $return );

		// --------------------------------------------
		//	Is this a pending account?
		// --------------------------------------------

		if ( $member_data['group_id'] == 4 )
		{
			// --------------------------------------------
			//	Send activation email?
			// --------------------------------------------

			if ( ee()->config->item('fbc_account_activation') == 'fbc_email_activation' )
			{
				// --------------------------------------------
				//	Send admin notification
				// --------------------------------------------

				$this->actions->send_user_activation_email( $member_data );

				// --------------------------------------------
				//	Show success message
				// --------------------------------------------

				$data	= array(
					'title'		=> lang('account_created'),
					'heading'	=> lang('account_created'),
					'link'		=> array(
									$return,
									lang('back')
									),
					'content'	=> lang('mbr_membership_instructions_email')
				);

				return ee()->output->show_message( $data, TRUE );
			}

			// --------------------------------------------
			//	Indicate that an admin will activate account?
			// --------------------------------------------

			if ( ee()->config->item('fbc_account_activation') == 'fbc_admin_activation' )
			{
				// --------------------------------------------
				//	Show success message
				// --------------------------------------------

				$data	= array(
					'title'		=> lang('account_created'),
					'heading'	=> lang('account_created'),
					'link'		=> array(
									$return,
									lang('back')
									),
					'content'	=> lang('mbr_admin_will_activate')
				);

				return ee()->output->show_message( $data, TRUE );
			}
		}

		// --------------------------------------------
		//	Just log them in
		// --------------------------------------------

		else
		{
			if ( $this->actions->ee_login( $member_data['member_id'] ) === FALSE )
			{
				return $this->show_error($this->actions->error );
			}
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		$this->_redirect( $return );
		exit();
	}

	/*	End register */

	// -------------------------------------------------------------

	/**
	 * Register form
	 *
	 * Register form for a new user using the abridged Facebook approach.
	 *
	 * @access	public
	 * @return	string
	 */

	public function register_form()
	{
		// --------------------------------------------
		//	Prepare action
		// --------------------------------------------

		$act	= ee()->functions->fetch_action_id('Fbc', 'register');

		$params	= array( 'ACT' => $act );

		// --------------------------------------------
		//	Prepare returns
		// --------------------------------------------

		$returns		= array(
			'return'		=> ee()->uri->uri_string,
			'show_captcha'	=> ''
		);

		foreach ( $returns as $key => $val )
		{
			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->TMPL->fetch_param($key) );
			}

			$params[$key]	= $val;
		}

		// --------------------------------------------
		//	Scraps
		// --------------------------------------------

		$params['RET']	= $params['return'];

		// --------------------------------------------
		// Prep captcha
		// --------------------------------------------

		$cond['captcha'] = ( ee()->config->item('use_membership_captcha') == 'y' ) ? 'TRUE': 'FALSE';

		if ( ee()->TMPL->fetch_param('show_captcha') !== FALSE )
		{
			if ( $this->check_yes( ee()->TMPL->fetch_param('show_captcha') ) === TRUE )
			{
				$cond['captcha']	= TRUE;
			}
			else
			{
				$cond['captcha']	= FALSE;
			}
		}

		// --------------------------------------------
		// Parse
		// --------------------------------------------

		$tagdata	= ee()->TMPL->tagdata;

		$tagdata	= ee()->functions->prep_conditionals( $tagdata, $cond );

		$params['tagdata']	= $tagdata;

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $this->_form( $params );
	}

	/*	End register form */

	// -------------------------------------------------------------

	/**
	 * Registration
	 *
	 * Show the FB registration form. See http://developers.facebook.com/docs/plugins/registration/
	 *
	 * @access	public
	 * @return	string
	 */

	public function registration()
	{
		$this->api();

		// --------------------------------------------
		//	Do we allow new member registrations?
		// --------------------------------------------

		if ( ee()->config->item('allow_member_registration') == 'n' )
		{
			return $this->no_results('fbc');
		}

		// --------------------------------------------
		//	Prep initial vars
		// --------------------------------------------

		$cond['fbc_login_button']					= '';
		$cond['fbc_logged_into_ee']					= ( ee()->session->userdata('member_id') == '0' ) ? 'n': 'y';
		$cond['fbc_logged_into_facebook']			= 'n';
		$cond['fbc_logged_into_facebook_and_ee']	= 'n';
		$cond['fbc_logged_into_ee_and_facebook']	= 'n';
		$cond['fbc_facebook_account_exists']		= 'n';
		$cond['fbc_registration_form']				= 'n';

		// --------------------------------------------
		//	Is this user already a registered FB user?
		// --------------------------------------------

		if ( ( $uid = $this->api->get_user_id() ) !== FALSE )
		{
			$cond['fbc_logged_into_facebook']	= 'y';

			// --------------------------------------------
			//	Do we already have a record for this facebook user id?
			// --------------------------------------------

			if ( $this->data->get_member_id_from_facebook_user_id( $uid ) !== FALSE )
			{
				$cond['fbc_facebook_account_exists']	= 'y';
			}
		}

		// --------------------------------------------
		//	Is this user already a registered FB user in EE?
		// --------------------------------------------

		if ( $this->_facebook_member_is_ee_member() !== FALSE )
		{
			$cond['fbc_logged_into_facebook_and_ee']	= 'y';
			$cond['fbc_logged_into_ee_and_facebook']	= 'y';
		}

		// --------------------------------------------
		//	Prepare params
		// --------------------------------------------

		$params		= array(
			'return_on_success'	=> ee()->uri->uri_string,
			'show_captcha'		=> ee()->config->item('use_membership_captcha')
		);

		foreach ( $params as $key => $val )
		{
			if ( ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '' )
			{
				$val	= $this->_chars_decode( ee()->TMPL->fetch_param($key) );
			}

			$params[$key]	= $val;
		}

		// --------------------------------------------
		//  Prepare the arguments for the FBML string
		// --------------------------------------------

		$fb_arguments		= array();

		$template_params	= array(
			'border_color'	=> '',
			'width'			=> 600
		);

		foreach ( $template_params as $param => $default )
		{
			if ( ee()->TMPL->fetch_param($param) !== FALSE )
			{
				if ( is_numeric( $default ) === TRUE AND is_numeric( ee()->TMPL->fetch_param($param) ) === TRUE )
				{
					$fb_arguments[]	= $param . '="' . ee()->TMPL->fetch_param($param) . '"';
				}
				elseif ( is_string( $default ) === TRUE AND is_string( ee()->TMPL->fetch_param($param) ) === TRUE  )
				{
					$fb_arguments[]	= $param . '="' . ee()->TMPL->fetch_param($param) . '"';
				}
			}
		}

		// --------------------------------------------
		//  Captcha?
		// --------------------------------------------

		$prefs['captcha'] = ( ee()->config->item('use_membership_captcha') == 'y' ) ? 'y': 'n';

		if ( ee()->TMPL->fetch_param('show_captcha') !== FALSE )
		{
			if ( $this->check_yes( ee()->TMPL->fetch_param('show_captcha') ) === TRUE )
			{
				$prefs['captcha']	= 'y';
			}
			else
			{
				$prefs['captcha']	= 'n';
			}
		}

		// --------------------------------------------
		//  Prepare fields
		// --------------------------------------------

		$fields	= $this->data->get_facebook_registration_fields( $prefs );

		// --------------------------------------------
		//  Loop and establish required
		// --------------------------------------------

		$fb_validate	= array();
		$js				= '';

		foreach ( $fields as $field => $data )
		{
			if ( isset( $data['required'] ) AND $data['required'] == 'y' )
			{
				if ( $field == 'accept_terms' )
				{
					$fb_validate[]	= "\n" . 'if ( !form.' . $field . ' ) { errors.' . $field . ' = "' . lang('please_accept_terms') . '" }';
				}
				elseif ( $data['type'] == 'select' )
				{
					$fb_validate[]	= "\n" . 'if ( form.' . $field . ' == "" || form.' . $field . ' == "-" ) { errors.' . $field . ' = "' . lang('please_complete_field') . '" }';
				}
				else
				{
					$fb_validate[]	= "\n" . 'if ( form.' . $field . ' == "" ) { errors.' . $field . ' = "' . lang('please_complete_field') . '" }';
				}

				// Not needed by FB
				unset($fields[$field]['required']);
			}
		}

		if ( ! empty( $fb_validate ) )
		{
			$fb_arguments[]	= 'onvalidate="fb_validate"';

			$js	= "\n" . '<script>function fb_validate(form) {errors = {}; ' . implode( " ", $fb_validate ) . ' return errors;}</script>';
		}

		// --------------------------------------------
		//  Convert to JSON
		// --------------------------------------------

		$params['fields'] = $fields = $this->json_encode( array_values( $fields ) );	// We store this and call it back later to validate against the incoming data from FB.

		// $fields	= str_replace( ",{", ",\n{", $fields );

		// print_r( $fields );

		// --------------------------------------------
		//	Save params
		// --------------------------------------------
		//	We create a tag hash and save that with our cookie so that we can test and not generate one of these DB inserts for every page load per user.
		// --------------------------------------------

		$tag_hash	= md5( serialize( $params ) . ee()->input->ip_address() );

		if ( ( $hash = $this->data->set_params( $tag_hash, $params ) ) === FALSE )
		{
			return $this->no_results('fbc');
		}

		// --------------------------------------------
		//  Load parameters to the tag
		// --------------------------------------------

		$qs		= ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

		$fb_arguments[]	= 'fields=\'' . $fields . '\'' . ' fb_only="true"' . ' redirect-uri="' . ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . ee()->functions->fetch_action_id('Fbc', 'register') . '&hash=' . $hash . '"';

		$cond['fbc_registration_form']	= '<fb:registration ' . implode( ' ', $fb_arguments ) . '></fb:registration>' . $js;

		// --------------------------------------------
		//  Failsafe: if we are logged into don't show the reg form
		// --------------------------------------------

		if ( $cond['fbc_logged_into_ee'] == 'y' )
		{
			$cond['fbc_registration_form']	= '';
		}

		// --------------------------------------------
		//	Grab login button?
		// --------------------------------------------

		if ( strpos( ee()->TMPL->tagdata, LD . 'fbc_login_button' . RD ) !== FALSE )
		{
			$cond['fbc_login_button']	= $this->login_button( '', 'gimme' );
		}

		// --------------------------------------------
		//	Is there tagdata?
		// --------------------------------------------

		if ( ee()->TMPL->tagdata == '' )
		{
			return $cond['fbc_registration_form'];
		}

		// --------------------------------------------
		//	Parse and return
		// --------------------------------------------

		$tagdata	= ee()->functions->prep_conditionals( ee()->TMPL->tagdata, $cond );

		foreach ( $cond as $key => $val )
		{
			$tagdata	= str_replace( LD . $key . RD, $val, $tagdata );
		}

		// --------------------------------------------
		//  Return
		// --------------------------------------------

		return $tagdata;
	}

	/*	End registration */
}
// END CLASS Fbc
<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - Actions
 *
 * Handles all form submissions and action requests used on both user and CP areas of EE.
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/act.fbc.php
 */

require_once 'addon_builder/module_builder.php';

class Fbc_actions extends Addon_builder_fbc
{
	public $api;

	// -------------------------------------------------------

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

	// -------------------------------------------------------

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
			return $this->error[]	= lang('not_authorized');
		}

		return TRUE;
	}

	/*	End check form hash */

	// -------------------------------------------------------

	/**
	 * Create member account
	 *
	 * This method accepts a facebook user id and creates a new EE member account with it.
	 *
	 * @access	public
	 * @return	boolean / numeric
	 */

	public function create_member_account( $uid = '', $member_data = array() )
	{
		// --------------------------------------------
		//	Validate
		// --------------------------------------------

		if ( $uid == '' OR empty( $member_data ) OR empty( $member_data['email'] ) OR empty( $member_data['username'] ) ) return FALSE;

		// --------------------------------------------
		//	'fbc_create_member_account_start' hook.
		// --------------------------------------------

		if ( ee()->extensions->active_hook('fbc_create_member_account_start') === TRUE )
		{
			$edata = ee()->extensions->universal_call( 'fbc_create_member_account_start', $uid,  $member_data );
			if (ee()->extensions->end_script === TRUE) return FALSE;
		}

		// --------------------------------------------
		//	Prepare screen name
		// --------------------------------------------

		if ( empty( $member_data['screen_name'] ) )
		{
			$member_data['screen_name']	= $member_data['username'];
		}

		// --------------------------------------------
		//	Start data
		// --------------------------------------------

		$data	= array(
			'email'						=> $member_data['email'],
			'screen_name'				=> $member_data['screen_name'],
			'username'					=> $member_data['username'],
			'facebook_connect_user_id'	=> $uid,
			'password'					=> ee()->functions->random('encrypt'),
			'unique_id'					=> ee()->functions->random('encrypt'),
			'ip_address'				=> ee()->input->ip_address(),
			'join_date'					=> ee()->localize->now,
			'last_visit'				=> ee()->localize->now
		);

		if ( ! empty( $member_data['password'] ) )
		{
			//$data['password']	= ee()->functions->hash( stripslashes( $member_data['password'] ) );

			$pass_data = ee()->auth->hash_password(stripslashes( $member_data['password']));

			$data['password']    	= $pass_data['password'];
			$data['salt']    		= $pass_data['salt'];
		}

		// --------------------------------------------
		//	Set member group
		// --------------------------------------------

		$data['group_id'] = ( ! empty( $member_data['group_id'] ) ) ? $member_data['group_id']: ee()->config->item('fbc_member_group');

		// --------------------------------------------
		//	We generate an authorization code if the member needs to self-activate
		// --------------------------------------------

		if ( ee()->config->item('fbc_account_activation') == 'fbc_email_activation' )
		{
			$data['authcode'] = ee()->functions->random('alpha', 10);
		}

		// --------------------------------------------
		//	Default timezone
		// --------------------------------------------

		if ( empty( $member_data['timezone'] ) )
		{
			$data['timezone'] = 'UTC';
		}

		// --------------------------------------------
		//	Insert basic member data
		// --------------------------------------------

		ee()->db->query( ee()->db->insert_string('exp_members', $data) );

		$data['member_id'] = ee()->db->insert_id();

		// --------------------------------------------
		//	Prepare custom fields
		// --------------------------------------------

		$cust_fields['member_id'] = $data['member_id'];

		$custom_member_fields	= $this->data->get_member_fields();

		foreach ( $custom_member_fields as $name => $field_data )
		{
			if ( ! empty( $member_data[ 'm_field_id_' . $field_data['id'] ] ) )
			{
				$cust_fields[ 'm_field_id_' . $field_data['id'] ]	= $member_data[ 'm_field_id_' . $field_data['id'] ];
			}
			elseif ( ! empty( $member_data[ $name ] ) )
			{
				$cust_fields[ 'm_field_id_' . $field_data['id'] ]	= $member_data[ $name ];
			}
		}

		// --------------------------------------------
		//	Insert custom fields
		// --------------------------------------------

		ee()->db->query( ee()->db->insert_string('exp_member_data', $cust_fields) );

		// --------------------------------------------
		//	Create a record in the member
		//	homepage table
		// --------------------------------------------

		ee()->db->query( ee()->db->insert_string('exp_member_homepage', array( 'member_id' => $data['member_id'] ) ) );

		// --------------------------------------------
		//	Update global member stats
		// --------------------------------------------

		if (ee()->config->item('req_mbr_activation') == 'none')
		{
			ee()->stats->update_member_stats();
		}

		// --------------------------------------------
		//	'fbc_member_member_register' hook.
		// --------------------------------------------

		if ( ee()->extensions->active_hook('fbc_member_member_register') === TRUE )
		{
			$edata = ee()->extensions->universal_call('fbc_member_member_register', $data);
			if (ee()->extensions->end_script === TRUE) return FALSE;
		}

		// --------------------------------------------
		//	Return data
		// --------------------------------------------

		return $data;
	}

	/*	End create member account */

	// -------------------------------------------------------

	/**
	 * EE login
	 *
	 * This method takes an EE member id and logs that person in.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function ee_login( $member_id = '' )
	{
		// --------------------------------------------
		//	Run security tests
		// --------------------------------------------

		if ( $this->_security() === FALSE )
		{
			return FALSE;
		}

		//--------------------------------------------
		//	2.2.0 Auth lib
		//--------------------------------------------

		ee()->load->library('auth');

		// This should go in the auth lib.
		if ( ! ee()->auth->check_require_ip())
		{
			$this->error[]	= lang('not_authorized');
			return FALSE;
		}


		// --------------------------------------------
		//	'fbc_member_login_start' hook.
		// --------------------------------------------

		if ( ee()->extensions->active_hook('fbc_member_login_start') === TRUE )
		{
			$edata = ee()->extensions->universal_call('fbc_member_login_start');
			if (ee()->extensions->end_script === TRUE) return FALSE;
		}

		// --------------------------------------------
		//	Kill old sessions first
		// --------------------------------------------

		ee()->session->gc_probability = 100;

		ee()->session->delete_old_sessions();

		// --------------------------------------------
		//	Use Facebook's session expiration as our own, or set to one day if there's any trouble.
		// --------------------------------------------

		$this->api();

		$this->api->connect_to_api();

		$expire = ( isset( $this->api->user['expires'] ) === TRUE AND is_numeric( $this->api->user['expires'] ) === TRUE ) ? 86400: $this->api->user['expires'] - time();

		$expire	= 86400;	// Let's do this for a while. Facebook can continually refresh the session it keeps for a user, but we are not going to try to continually update ours. Let's just give the user some breathing room.

		// --------------------------------------------
		//	Get member data
		// --------------------------------------------

		if ( ( $member_data = $this->data->get_member_data_from_member_id( $member_id ) ) === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Is the member account pending?
		// --------------------------------------------

		if ( $member_data['group_id'] == 4 )
		{
			$this->show_error(array(lang('mbr_account_not_active')));
		}

		// --------------------------------------------
		//  Do we allow multiple logins on the same account?
		// --------------------------------------------

		if (ee()->config->item('allow_multi_logins') == 'n')
		{
			$expire = time() - ee()->session->session_length;

			// See if there is a current session

			$result = ee()->db->query("SELECT ip_address, user_agent
								  FROM   exp_sessions
								  WHERE  member_id  = '".$member_data['member_id']."'
								  AND    last_activity > " . ee()->db->escape_str( $expire ) . "");

			// If a session exists, trigger the error message

			if ($result->num_rows() == 1)
			{
				$row	= $result->row_array();

				if ( ee()->session->userdata('ip_address') != $row['ip_address'] OR ee()->session->userdata('user_agent') != $row['user_agent'] )
				{
					$errors[] = lang('multi_login_warning');
				}
			}
		}

		// --------------------------------------------
		//  New auth method in EE 2.2.0
		// --------------------------------------------


		$member	= ee()->db->get_where(
			'members',
			array('member_id' => $member_data['member_id'])
		);

		$session 	= new Auth_result($member->row());

		if (is_callable(array($session, 'remember_me')))
		{
			$session->remember_me(60*60*24*182);
		}

		$session->start_session();

		// Update system stats
		ee()->load->library('stats');

		if ( ! $this->check_no(ee()->config->item('enable_online_user_tracking')))
		{
			ee()->stats->update_stats();
		}


		// --------------------------------------------
		//	Log this
		// --------------------------------------------

		$this->log_to_cp( 'Logged in', $member_data );

		// --------------------------------------------
		//	'fbc_member_login_single' hook.
		// --------------------------------------------

		if ( ee()->extensions->active_hook('fbc_member_login_single') === TRUE )
		{
			$edata = ee()->extensions->universal_call('fbc_member_login_single', $member_data);
			if (ee()->extensions->end_script === TRUE) return FALSE;
		}

		// --------------------------------------------
		//	Return success
		// --------------------------------------------

		return TRUE;
	}

	/*	End EE login */

	// -------------------------------------------------------

	/**
	 * Log to CP
	 *
	 * @access	public
	 * @return	string
	 */

	public function log_to_cp( $msg = '', $member_data = array() )
	{
		return FALSE;

		if ( $msg == '' )
		{
			return FALSE;
		}

		$data = array(
			'id'         => '',
			'member_id'  => ( empty( $member_data['member_id'] ) ) ? '1': $member_data['member_id'],
			'username'   => ( empty( $member_data['username'] ) ) ? 'Solspace Facebook Connect Module': $member_data['username'],
			'ip_address' => ee()->input->ip_address(),
			'act_date'   => ee()->localize->now,
			'action'     => 'Facebook: ' . $msg
		 );

		ee()->db->insert('exp_cp_log', $data);
	}

	/*	End log to CP */

	// -------------------------------------------------------

	/**
	 * Passive registration
	 *
	 * @access	private
	 * @return	boolean
	 */

	public function passive_registration( $uid = '' )
	{
		// --------------------------------------------
		//	Do we allow new member registrations?
		// --------------------------------------------

		if ( ee()->config->item('allow_member_registration') == 'n' )
		{
			$this->error[]	= lang('registration_not_enabled');
			return FALSE;
		}

		//--------------------------------------------
		//	2.2.0 Auth lib
		//--------------------------------------------

		ee()->load->library('auth');

		// This should go in the auth lib.
		if ( ! ee()->auth->check_require_ip())
		{
			$this->error[]	= lang('not_authorized');
			return FALSE;
		}


		// --------------------------------------------
		//	Do we already have a record for this facebook user id?
		// --------------------------------------------

		if ( $this->data->get_member_id_from_facebook_user_id( $uid ) !== FALSE )
		{
			$this->error[]	= lang( 'fb_user_already_exists' );
			return FALSE;
		}

		// --------------------------------------------
		//	Create fake member data
		// --------------------------------------------

		$this->api();
		$default_meber_data	= array();
		$member_data		= array();

		$default_member_data['email']		= $member_data['email'] = md5( time() . $uid ) . '@facebook.com';
		$default_member_data['screen_name']	= $member_data['screen_name']	= 'facebook' . $uid;
		$default_member_data['username']	= $member_data['username']	= 'facebook' . $uid;

		if ( ( $data = $this->api->get_user_info() ) !== FALSE )
		{
			$member_data['email']	= ( empty( $data['email'] ) ) ? $member_data['email']: $data['email'];

			if ( ! empty( $data['name'] ) )
			{
				$member_data['username']	= strtolower( str_replace( ' ', '_', $data['name'] ) );
				$member_data['screen_name']	= $data['name'];
			}
			elseif ( ! empty( $data['first_name'] ) AND ! empty( $data['last_name'] ) )
			{
				$member_data['username']	= strtolower( str_replace( ' ', '_', $data['first_name'] . ' ' . $data['last_name'] ) );
				$member_data['screen_name']	= $data['first_name'] . ' ' . $data['last_name'];
			}
		}

		// --------------------------------------------
		//	Validate
		// --------------------------------------------

		$random_number	= rand(1,999);

		$validate = array(
			'val_type'		=> 'new', // new or update
			'fetch_lang'	=> TRUE,
			'require_cpw'	=> FALSE,
			'enable_log'	=> FALSE,
			'username'		=> $member_data['username'],
			'screen_name'	=> stripslashes( $member_data['screen_name'] ),
			'email'			=> $member_data['email']
		 );

		ee()->load->library('validate', $validate, 'validate');

		// --------------------------------------------
		//	Compensate for email
		// --------------------------------------------

		ee()->validate->validate_email();

		if ( count( ee()->validate->errors ) > 0 )
		{
			$member_data['email']	= $default_member_data['email'];
			ee()->validate->errors	= array();
		}

		// --------------------------------------------
		//	Compensate for screen name
		// --------------------------------------------

		ee()->validate->validate_screen_name();

		if ( count( ee()->validate->errors ) > 0 )
		{
			// --------------------------------------------
			//	Try once more
			// --------------------------------------------

			$member_data['screen_name']	= $member_data['screen_name'] . ' ' . $random_number;
			ee()->validate->screen_name	= $member_data['screen_name'];
			ee()->validate->errors	= array();

			ee()->validate->validate_screen_name();

			if ( count( ee()->validate->errors ) > 0 )
			{
				$member_data['screen_name']	= $default_member_data['screen_name'];
				ee()->validate->errors	= array();
			}
		}

		// --------------------------------------------
		//	Compensate for username
		// --------------------------------------------

		ee()->validate->validate_username();

		if ( count( ee()->validate->errors ) > 0 )
		{
			// --------------------------------------------
			//	Try once more
			// --------------------------------------------

			$member_data['username']	= $member_data['username'] . '_' . $random_number;
			ee()->validate->username	= $member_data['username'];
			ee()->validate->errors	= array();

			ee()->validate->validate_username();

			if ( count( ee()->validate->errors ) > 0 )
			{
				$member_data['username']	= $default_member_data['username'];
				ee()->validate->errors	= array();
			}
		}

		// --------------------------------------------
		//	Attempt to create account
		// --------------------------------------------

		if ( ( $member_id_data = $this->create_member_account( $uid, $member_data ) ) === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Send admin notification
		// --------------------------------------------

		$this->send_admin_notification_of_registration( $member_data );

		// --------------------------------------------
		//	'fbc_passive_register_end' hook.
		// --------------------------------------------
		//	Additional processing when a member is created through the User Side
		// --------------------------------------------

		if ( ee()->extensions->active_hook('fbc_passive_register_end') === TRUE )
		{
			$edata = ee()->extensions->universal_call('fbc_passive_register_end', $this, $member_id_data['member_id']);
			if (ee()->extensions->end_script === TRUE) return;
		}

		// --------------------------------------------
		//	Just log them in
		// --------------------------------------------

		if ( $this->ee_login( $member_id_data['member_id'] ) === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return TRUE;
	}

	/*	End passive registration */

	// -------------------------------------------------------

	/**
	 * Security
	 *
	 * @access	private
	 * @return	boolean
	 */

	public function _security()
	{
		// --------------------------------------------
		//	Is the user banned?
		// --------------------------------------------

		if ( ee()->session->userdata['is_banned'] === TRUE )
		{
			return $this->show_error(lang('not_authorized'));
		}

		// --------------------------------------------
		//	Is the IP address and User Agent required?
		// --------------------------------------------

		if ( ee()->config->item('require_ip_for_posting') == 'y' )
		{
			if ( ( ee()->input->ip_address() == '0.0.0.0' OR ee()->session->userdata['user_agent'] == '' ) AND ee()->session->userdata['group_id'] != 1 )
			{
				return $this->show_error(lang('not_authorized'));
			}
		}

		// --------------------------------------------
		//	Is the nation of the user banned?
		// --------------------------------------------

		ee()->session->nation_ban_check();

		// --------------------------------------------
		//	Blacklist / Whitelist Check
		// --------------------------------------------

		if ( ee()->blacklist->blacklisted == 'y' && ee()->blacklist->whitelisted == 'n' )
		{
			return $this->show_error(lang('not_authorized'));
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return TRUE;
	}

	/*	End security */

	// -------------------------------------------------------

	/**
	 * Send admin notification of registration
	 *
	 * Sends an email to the designated admins that a new member has registered.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function send_admin_notification_of_registration( $member_data = array() )
	{
		if ( ee()->config->item('new_member_notification') == 'y' AND ee()->config->item('mbr_notification_emails') != '' )
		{
			$name = ( $member_data['screen_name'] != '' ) ? $member_data['screen_name'] : $member_data['username'];

			$swap = array(
				'name'					=> $name,
				'site_name'				=> stripslashes( ee()->config->item('site_name') ),
				'control_panel_url'		=> ee()->config->item('cp_url'),
				'username'				=> $member_data['username'],
				'email'					=> $member_data['email']
			 );

			$template	= ee()->functions->fetch_email_template('admin_notify_reg');
			$email_tit	= $this->_var_swap($template['title'], $swap);
			$email_msg	= $this->_var_swap($template['data'], $swap);

			$notify_address = ee()->config->item('mbr_notification_emails');

			ee()->load->helper('string');
			ee()->load->helper('text');

			$notify_address	= reduce_multiples( $notify_address );

			// --------------------------------------------
			//	Send email
			// --------------------------------------------

			ee()->load->library('email');

			ee()->email->initialize();
			ee()->email->wordwrap = true;
			ee()->email->mailtype = 'plain';
			ee()->email->priority = '3';
			ee()->email->from( ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name') );
			ee()->email->to( $notify_address );
			ee()->email->subject( $email_tit );
			ee()->email->message( entities_to_ascii($email_msg) );
			ee()->email->Send();
		}
	}

	/* End send admin notification of registration */

	// -------------------------------------------------------

	/**
	 * Send user activation email
	 *
	 * Sends an email to the registrant to allow them to activate their account.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function send_user_activation_email( $member_data = array() )
	{
		$qs = ( ee()->config->item('force_query_string') == 'y' ) ? '' : '?';

		$action_id  = ee()->functions->fetch_action_id('Fbc', 'activate_member');

		$name = ( ! empty( $member_data['screen_name'] ) ) ? $member_data['screen_name']: $member_data['username'];

		$swap = array(
						'name'				=> $name,
						'activation_url'	=> ee()->functions->fetch_site_index( 0, 0 ) . $qs . 'ACT=' . $action_id . '&id=' . $member_data['authcode'],
						'site_name'			=> stripslashes(ee()->config->item('site_name')),
						'site_url'			=> ee()->config->item('site_url'),
						'username'			=> $member_data['username'],
						'email'				=> $member_data['email']
					 );

		$template = ee()->functions->fetch_email_template('mbr_activation_instructions');
		$email_tit = $this->_var_swap($template['title'], $swap);
		$email_msg = $this->_var_swap($template['data'], $swap);

		// --------------------------------------------
		//	Send email
		// --------------------------------------------

		ee()->load->library('email');
		ee()->load->helper('text');

		ee()->email->initialize();
		ee()->email->wordwrap = true;
		ee()->email->from( ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name') );
		ee()->email->to( $member_data['email'] );
		ee()->email->subject( $email_tit );
		ee()->email->message( entities_to_ascii( $email_msg ) );
		ee()->email->Send();
	}

	/* End send user activation email */

	// -------------------------------------------------------

	/**
	 *	Variable Swapping
	 *
	 *	Available even when $TMPL is not
	 *
	 *	@access		public
	 *	@param		string
	 *	@param		array
	 *	@return		string
	 */

	public function _var_swap($str, $data)
	{
		if ( ! is_array($data))
		{
			return false;
		}

		foreach ($data as $key => $val)
		{
			$str = str_replace('{'.$key.'}', $val, $str);
		}

		return $str;
	}

	/* End _var_swap() */
}
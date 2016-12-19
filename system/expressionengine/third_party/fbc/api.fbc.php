<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - API
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/api.fbc.php
 */

require_once 'addon_builder/module_builder.php';

// define('FACEBOOK_SDK_V4_SRC_DIR', $this->addon_path . 'lib/fb400/src/Facebook/');
// require_once $this->addon_path . 'lib/fb400/autoload.php';

// -------------------------------------
//	Facebook API requires json support
//	This shim is for PHP 5.1.x
// -------------------------------------

if ( ! function_exists('json_decode'))
{
	if ( ! isset($GLOBALS['SOLSPACE_JSON_SVC']))
	{
		if (class_exists('Services_JSON'))
		{
			require_once 'lib/json.php';
		}

		$GLOBALS['SOLSPACE_JSON_SVC'] = new Services_JSON();
	}

	function json_decode($string)
	{
		return $GLOBALS['SOLSPACE_JSON_SVC']->decode($string);
	}

	function json_encode($data)
	{
		return $GLOBALS['SOLSPACE_JSON_SVC']->encode($data);
	}
}

class Fbc_api extends Module_builder_fbc
{
	public $cached 	= array();
	public $user	= null;

	// --------------------------------------------------------------------

	/**
	 * Has app permission
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function has_app_permission( $permission = 'publish_stream' )
	{
		if ( empty( $permission ) ) return FALSE;

		if ( is_array( $permission ) === FALSE AND isset( $this->cached['permissions'][$permission] ) === TRUE )
		{
			return $this->cached['permissions'][$permission];
		}

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if ( ( $uid = $this->get_user_id() ) === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try
		{
			$info	= $this->FB->api( '/me/permissions' );

			if ( is_object( $info ) === TRUE )
			{
				$info	= (array) $info;
			}

			if ( isset( $info['data'][0] ) === TRUE )
			{
				$info	= (array) $info['data'][0];
			}

			if ( is_array( $permission ) === TRUE )
			{
				foreach ( $permission as $val )
				{
					if ( empty( $info[$val] ) )
					{
						$this->cached['permissions'][$val]	= FALSE;
					}
					else
					{
						$this->cached['permissions'][$val]	= TRUE;
					}
				}
			}
			else
			{
				if ( ! empty( $info[ $permission ] ) )	// The returned array has a permission as a key and a 1 or 0 as the value.
				{
					return TRUE;
				}
			}

			return FALSE;
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}
	}

	/*	End has app permission */

	// -------------------------------------------------------------

	/**
	 * Is facebook email
	 *
	 * @access	private
	 * @return	string
	 */

	public function _is_facebook_email( $name = '' )
	{
		if ( $name == '' ) return FALSE;

		if ( preg_match( '/^[a-f0-9]{32}@facebook\.com/si', $name ) ) return TRUE;	// This is testing to see if the email address is an MD5 hash plus @facebook.com. It's the fake email format I use when someone passively registers.

		if ( strpos( $name, 'proxymail.facebook.com' ) !== FALSE ) return TRUE;	// This is an additional FB email format, the proxy email format.

		return FALSE;
	}
	/*	End is facebook email */


	// --------------------------------------------------------------------

	/**
	 * Connect to API
	 *
	 * @access	public
	 * @return	null
	 */

	public function connect_to_api( $create_session = TRUE )
	{
		// --------------------------------------------
		//	Connect
		// --------------------------------------------

		require_once $this->addon_path . 'lib/facebook.php';

		if (! isset($this->FB->api))
		{
			$this->FB = new Facebook(
				array(
				  'appId'  => ee()->config->item('fbc_app_id'),
				  'secret' => ee()->config->item('fbc_secret'),
				  'cookie' => true,
				)
			);
		}

		if ( $create_session === TRUE )
		{
			$this->user = $this->FB->getUser();
		}
	}

	/*	End connect to api */

	// --------------------------------------------------------------------

	/**
	 * Feed
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function feed( $data = array() )
	{
		if ( empty( $data ) OR empty( $data['message'] ) ) return FALSE;

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if ( ( $uid = $this->get_user_id() ) === FALSE )
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Prepare data
		// --------------------------------------------

		$feed	= array( 'access_token' => $this->FB->getAccessToken() );

		foreach ( array( 'message', 'link', 'picture', 'name', 'caption', 'description', 'actions', 'privacy' ) as $val )
		{
			if ( ! empty( $data[$val] ) )
			{
				$feed[$val]	= $data[$val];
			}
			else
			{
				$feed[$val]	= '';
			}
		}

		// --------------------------------------------
		//	Try to send
		// --------------------------------------------

		if ( empty( $feed ) ) return FALSE;

		try
		{
			$result = $this->FB->api( '/me/feed', 'POST', $feed );
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}
	}

	/*	End feed */

	// --------------------------------------------------------------------

	/**
	 * Get user id
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_user_id($recheck = FALSE)
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( func_get_args() );

		if ($recheck !== TRUE && isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = 0;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ( $this->connect_to_api() === FALSE )
		{
			$this->error[]	= lang('could_not_connect_to_facebook');
			return FALSE;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		if ($recheck === TRUE)
		{
			$this->FB->destroySession();
		}

		try
		{
			$fb_user_id	= $this->FB->getUser();

			if ( empty( $fb_user_id ) ) return FALSE;

			$this->cached[$cache_name][$cache_hash]	= $fb_user_id;
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	/*	End get user id */

	// --------------------------------------------------------------------

	/**
	 * Get friends count
	 *
	 * @access	public
	 * @return	string
	 */

	public function get_friends_count( $uid = '' )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( func_get_args() );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = 0;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ( $this->connect_to_api() === FALSE )
		{
			$this->error[]	= lang('could_not_connect_to_facebook');
			return FALSE;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try
		{
			$info	= $this->FB->api( array('method' => 'friends.getAppUsers') );

			if ( is_object( $info ) === TRUE )
			{
				$info	= (array) $info;
			}

			$this->cached[$cache_name][$cache_hash]	= count( $info );
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	/*	End get friends count */

	// --------------------------------------------------------------------

	/**
	 * Get graph
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_graph( $uid = '', $node = 'user' )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( func_get_args() );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ( $this->connect_to_api() === FALSE )
		{
			$this->error[]	= lang('could_not_connect_to_facebook');
			return FALSE;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		$uid	= ( $uid == '' ) ? '/me': '/' . $uid;

		try
		{
			$info	= $this->FB->api( $uid );

			if ( is_object( $info ) === TRUE )
			{
				$info	= (array) $info;
			}

			$this->cached[$cache_name][$cache_hash]	= $info;
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	/*	End get graph */

	// --------------------------------------------------------------------

	/**
	 * Get permissions
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_permissions()
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( func_get_args() );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if (($uid = $this->get_user_id()) === FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try
		{
			$info	= $this->FB->api('/me/permissions');

			if (is_object($info) === TRUE)
			{
				$info	= (array) $info;
			}

			if (isset($info['data']))
			{
				$info	= (array) $info['data'];
				
				$out	= array();
				
				foreach ($info as $val)
				{
					if ($val['status'] != 'granted') continue;
					
					$out[]	= $val['permission'];
				}

				return $this->cached[$cache_name][$cache_hash] = $out;
			}

			return array();
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();
			
			//$this->dd($this->error);

			return array();
		}
	}

	/*	End get permissions */

	// --------------------------------------------------------------------

	/**
	 * Get standard user info
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_standard_user_info()
	{
		// --------------------------------------------
		//  We no longer need to separate this out as its own method.
		// --------------------------------------------

		return $this->get_user_info();
	}

	/*	End get standard user info */

	// --------------------------------------------------------------------

	/**
	 * Get user info
	 *
	 * @access	public
	 * @return	array
	 */

	public function get_user_info( $uid = '' )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( func_get_args() );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ( $this->connect_to_api() === FALSE )
		{
			$this->error[]	= lang('could_not_connect_to_facebook');
			return FALSE;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try
		{
			$info	= $this->FB->api( '/me' );

			if ( is_object( $info ) === TRUE )
			{
				$info	= (array) $info;
			}

			$this->cached[$cache_name][$cache_hash]	= $info;
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	/*	End get user info */

	// --------------------------------------------------------------------

	/**
	 * Convert signed request
	 *
	 * @access	public
	 * @return	array
	 */

	public function convert_signed_request( $data = '' )
	{
		if ( empty( $data ) ) return FALSE;

		// --------------------------------------------
		//	Load API and parse
		// --------------------------------------------

		$this->connect_to_api( FALSE );

		$fb_post	= $this->FB->getSignedRequest( $data );

		if ( is_null( $fb_post ) === TRUE ) return FALSE;

		if ( isset( $fb_post['registration_metadata']->fields ) === TRUE )
		{
			$fb_post['registration_metadata']	= (array) $fb_post['registration_metadata'];
		}

		return $fb_post;
	}

	/*	End convert signed request */


	// --------------------------------------------------------------------

	/**
	 * Stream publish
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function stream_publish( $data = array() )
	{
		if ( empty( $data ) OR empty( $data['message'] ) ) return FALSE;

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if ( ( $uid = $this->get_user_id() ) === FALSE )
		{
			return FALSE;
		}

		if ( ! empty( $data['attachment'] ) )
		{
			$data['attachment']	= json_encode( $data['attachment'] );

			// print_r( $data['attachment'] ); exit();
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try
		{
			if ( empty( $data['action_links'] ) AND empty( $data['attachment'] ) )
			{
				$info	= $this->FB->api_client->stream_publish( $data['message'] );
			}
			else
			{
				$action_links	= ( empty( $data['action_links'] ) ) ? null: $data['action_links'];
				$attachment		= ( empty( $data['attachment'] ) ) ? null: $data['attachment'];

				$info	= $this->FB->api_client->stream_publish( $data['message'], $attachment, $action_links );
			}
		}
		catch (Exception $e)
		{
			$this->error[]	= $e->getMessage();

			return FALSE;
		}
	}

	/*	End stream publish */

	// --------------------------------------------------------------------

	/**
	 * Synchronize facebook email with local email
	 *
	 * @access	public
	 * @return	array
	 */

	public function synchronize_facebook_email_with_local_email( $uid = '' )
	{
		// --------------------------------------------
		//	Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( func_get_args() );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//	uid?
		// --------------------------------------------

		if ( $uid == '' ) return FALSE;

		// --------------------------------------------
		//	Get Facebook data
		// --------------------------------------------

		if ( ( $arr = $this->get_user_info() ) !== FALSE )
		{
			// --------------------------------------------
			//	If this person was previously passively registered, detect that and change their email if we can.
			// --------------------------------------------

			if (
				ee()->session->userdata('email') != ''
				AND ! empty( $arr['email'] )
				AND $this->_is_facebook_email( ee()->session->userdata('email') ) === TRUE
				AND ee()->session->userdata('email') != $arr['email']
			)
			{
				ee()->db->query(
					ee()->db->update_string(
						'exp_members',
						array(
							'email'	=> $arr['email']
						),
						array(
							'facebook_connect_user_id' => $uid
						)
					)
				);
			}
		}

		return $this->cached[$cache_name][$cache_hash] = TRUE;
	}

	/*	End synchronize facebook email with local email */

	// --------------------------------------------------------------------
}
// END CLASS Fbc_api_data

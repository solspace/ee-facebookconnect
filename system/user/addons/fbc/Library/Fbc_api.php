<?php

namespace Solspace\Addons\Fbc\Library;

use Solspace\Addons\Fbc\Model\Preference;

class Fbc_api extends AddonBuilder
{
	/** @var Facebook */
	public $FB;

	/** @var array */
	public $cached = array();
	public $user   = null;

	public function __construct($init_type = null)
	{
		parent::__construct($init_type);

		Preference::updateConfigWithData();
	}

	// --------------------------------------------------------------------

	/**
	 * Has app permission
	 *
	 * @return boolean
	 */
	public function has_app_permission($permission = 'publish_stream')
	{
		if (empty($permission)) {
			return false;
		}

		if (is_array($permission) === false && isset($this->cached['permissions'][$permission]) === true) {
			return $this->cached['permissions'][$permission];
		}

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if (($uid = $this->get_user_id()) === false) {
			return false;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try {
			$info = $this->FB->api('/me/permissions');

			if (is_object($info) === true) {
				$info = (array)$info;
			}

			if (isset($info['data'][0]) === true) {
				$info = (array)$info['data'][0];
			}

			if (is_array($permission) === true) {
				foreach ($permission as $val) {
					if (empty($info[$val])) {
						$this->cached['permissions'][$val] = false;
					} else {
						$this->cached['permissions'][$val] = true;
					}
				}
			} else {
				if (!empty($info[$permission]))    // The returned array has a permission as a key and a 1 or 0 as the value.
				{
					return true;
				}
			}

			return false;
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
		}
	}

	/*	End has app permission */

	// -------------------------------------------------------------

	/**
	 * Is facebook email
	 *
	 * @access    private (Why is this private, if the method access is, in fact, very public?)
	 * @return    string
	 */
	public function _is_facebook_email($name = '')
	{
		if ($name == '') {
			return false;
		}

		if (preg_match('/^[a-f0-9]{32}@facebook\.com/si', $name)) {
			return true;
		}    // This is testing to see if the email address is an MD5 hash plus @facebook.com. It's the fake email format I use when someone passively registers.

		if (strpos($name, 'proxymail.facebook.com') !== false) {
			return true;
		}    // This is an additional FB email format, the proxy email format.

		return false;
	}
	/*	End is facebook email */


	// --------------------------------------------------------------------

	/**
	 * @param bool $create_session
	 */
	public function connect_to_api($create_session = true)
	{
		// --------------------------------------------
		//	Connect
		// --------------------------------------------

		if (!isset($this->FB->api)) {
			$this->FB = new Facebook(
				array(
					'appId'  => $this->getAppId(),
					'secret' => $this->getAppSecret(),
					'cookie' => true,
				)
			);
		}

		if ($create_session === true) {
			$this->user = $this->FB->getUser();
		}
	}

	/*	End connect to api */

	// --------------------------------------------------------------------

	/**
	 * Feed
	 *
	 * @access    public
	 * @return    boolean
	 */

	public function feed($data = array())
	{
		if (empty($data) OR empty($data['message'])) {
			return false;
		}

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if (($uid = $this->get_user_id()) === false) {
			return false;
		}

		// --------------------------------------------
		//	Prepare data
		// --------------------------------------------

		$feed = array('access_token' => $this->FB->getAccessToken());

		foreach (array('message', 'link', 'picture', 'name', 'caption', 'description', 'actions', 'privacy') as $val) {
			if (!empty($data[$val])) {
				$feed[$val] = $data[$val];
			} else {
				$feed[$val] = '';
			}
		}

		// --------------------------------------------
		//	Try to send
		// --------------------------------------------

		if (empty($feed)) {
			return false;
		}

		try {
			$result = $this->FB->api('/me/feed', 'POST', $feed);
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
		}
	}

	/*	End feed */

	// --------------------------------------------------------------------

	/**
	 * Get user id
	 *
	 * @access    public
	 * @return    array
	 */

	public function get_user_id($recheck = false)
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if ($recheck !== true && isset($this->cached[$cache_name][$cache_hash])) {
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = 0;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ($this->connect_to_api() === false) {
			$this->error[] = lang('could_not_connect_to_facebook');

			return false;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		if ($recheck === true) {
			$this->FB->destroySession();
		}

		try {
			$fb_user_id = $this->FB->getUser();

			if (empty($fb_user_id)) {
				return false;
			}

			$this->cached[$cache_name][$cache_hash] = $fb_user_id;
		} catch (Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
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
	 * @access    public
	 * @return    string
	 */

	public function get_friends_count($uid = '')
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash])) {
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = 0;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ($this->connect_to_api() === false) {
			$this->error[] = lang('could_not_connect_to_facebook');

			return false;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try {
			$info = $this->FB->api(array('method' => 'friends.getAppUsers'));

			if (is_object($info) === true) {
				$info = (array)$info;
			}

			$this->cached[$cache_name][$cache_hash] = count($info);
		} catch (Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
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
	 * @access    public
	 * @return    array
	 */

	public function get_graph($uid = '', $node = 'user')
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash])) {
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = false;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ($this->connect_to_api() === false) {
			$this->error[] = lang('could_not_connect_to_facebook');

			return false;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		$uid = ($uid == '') ? '/me' : '/' . $uid;

		try {
			$info = $this->FB->api($uid . '?fields=email,name,gender,locale,first_name,last_name,link');

			if (is_object($info) === true) {
				$info = (array)$info;
			}

			$this->cached[$cache_name][$cache_hash] = $info;
		} catch (Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
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
	 * @access    public
	 * @return    array
	 */

	public function get_permissions()
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash])) {
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if (($uid = $this->get_user_id()) === false) {
			return false;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try {
			$info = $this->FB->api("/$uid/permissions");

			if (is_object($info) === true) {
				$info = (array)$info;
			}

			if (isset($info['data'])) {
				$info = (array)$info['data'];

				$out = array();

				foreach ($info as $val) {
					if ($val['status'] != 'granted') {
						continue;
					}

					$out[] = $val['permission'];
				}

				return $this->cached[$cache_name][$cache_hash] = $out;
			}

			return array();
		} catch (Exception $e) {
			$this->error[] = $e->getMessage();

			//$this->dd($this->error);

			return array();
		}
	}

	/*	End get permissions */

	// --------------------------------------------------------------------

	/**
	 * Get standard user info
	 *
	 * @access    public
	 * @return    array
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
	 * @access    public
	 * @return    array
	 */

	public function get_user_info($uid = '')
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash])) {
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = false;

		// --------------------------------------------
		//	Connect to API
		// --------------------------------------------

		if ($this->connect_to_api() === false) {
			$this->error[] = lang('could_not_connect_to_facebook');

			return false;
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try {
			$info = $this->FB->api('/me');

			if (is_object($info) === true) {
				$info = (array)$info;
			}

			$this->cached[$cache_name][$cache_hash] = $info;
		} catch (Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
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
	 * @access    public
	 * @return    array
	 */

	public function convert_signed_request($data = '')
	{
		if (empty($data)) {
			return false;
		}

		// --------------------------------------------
		//	Load API and parse
		// --------------------------------------------

		$this->connect_to_api(false);

		$fb_post = $this->FB->getSignedRequest($data);

		if (is_null($fb_post) === true) {
			return false;
		}

		if (isset($fb_post['registration_metadata']->fields) === true) {
			$fb_post['registration_metadata'] = (array)$fb_post['registration_metadata'];
		}

		return $fb_post;
	}

	/*	End convert signed request */


	// --------------------------------------------------------------------

	/**
	 * Stream publish
	 *
	 * @access    public
	 * @return    boolean
	 */

	public function stream_publish($data = array())
	{
		if (empty($data) OR empty($data['message'])) {
			return false;
		}

		// --------------------------------------------
		//	Get user id
		// --------------------------------------------

		if (($uid = $this->get_user_id()) === false) {
			return false;
		}

		if (!empty($data['attachment'])) {
			$data['attachment'] = json_encode($data['attachment']);

			// print_r( $data['attachment'] ); exit();
		}

		// --------------------------------------------
		//	Try and get Facebook user id
		// --------------------------------------------

		try {
			if (empty($data['action_links']) AND empty($data['attachment'])) {
				$info = $this->FB->api_client->stream_publish($data['message']);
			} else {
				$action_links = (empty($data['action_links'])) ? null : $data['action_links'];
				$attachment   = (empty($data['attachment'])) ? null : $data['attachment'];

				$info = $this->FB->api_client->stream_publish($data['message'], $attachment, $action_links);
			}
		} catch (Exception $e) {
			$this->error[] = $e->getMessage();

			return false;
		}
	}

	/*	End stream publish */

	// --------------------------------------------------------------------

	/**
	 * Synchronize facebook email with local email
	 *
	 * @access    public
	 * @return    array
	 */

	public function synchronize_facebook_email_with_local_email($uid = '')
	{
		// --------------------------------------------
		//	Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash])) {
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = false;

		// --------------------------------------------
		//	uid?
		// --------------------------------------------

		if ($uid == '') {
			return false;
		}

		// --------------------------------------------
		//	Get Facebook data
		// --------------------------------------------

		if (($arr = $this->get_user_info()) !== false) {
			// --------------------------------------------
			//	If this person was previously passively registered, detect that and change their email if we can.
			// --------------------------------------------

			if (
				ee()->session->userdata('email') != ''
				AND !empty($arr['email'])
				AND $this->_is_facebook_email(ee()->session->userdata('email')) === true
				AND ee()->session->userdata('email') != $arr['email']
			) {
				ee()->db->query(
					ee()->db->update_string(
						'exp_members',
						array(
							'email' => $arr['email'],
						),
						array(
							'facebook_connect_user_id' => $uid,
						)
					)
				);
			}
		}

		return $this->cached[$cache_name][$cache_hash] = true;
	}

	/*	End synchronize facebook email with local email */

	// --------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getAppId()
	{
		return ee()->config->item('fbc_app_id');
	}

	/**
	 * @return string
	 */
	public function getAppSecret()
	{
		return ee()->config->item('fbc_app_secret');
	}
}

// END CLASS Fbc_api_data

<?php

use Solspace\Addons\Fbc\Library\AddonBuilder;

class Fbc_upd extends AddonBuilder
{
	public $hooks          = array();

	/**
	 * Overriding module actions
	 *
	 * @var array
	 */
	public $module_actions = array(
		'account_sync',
		'activate_member',
		'email_sync',
		'facebook_post_authorize_callback',
		'facebook_post_remove_callback',
		'facebook_login',
		'facebook_logout',
		'register',
	);

	/** @var array */
	private $preferences = array(
		'fbc_app_id',
		'fbc_secret',
		'fbc_eligible_member_groups',
		'fbc_member_group',
		'fbc_account_activation',
		'fbc_enable_passive_registration',
		'fbc_confirm_before_syncing_accounts',
	);

	public function __construct()
	{
		parent::__construct('module');

		// --------------------------------------------
		//  Extension Hooks
		// --------------------------------------------

		$this->default_settings = array();

		$default = array(
			'class'    => $this->extension_name,
			'settings' => '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y',
		);

		$this->hooks = array(
			'insert_comment_end' => array_merge(
				$default,
				array(
					'method' => 'insert_comment_end',
					'hook'   => 'insert_comment_end',
				)
			),
			'insert_rating_end'  => array_merge(
				$default,
				array(
					'method' => 'insert_rating_end',
					'hook'   => 'insert_rating_end',
				)
			),
			'status_update'      => array_merge(
				$default,
				array(
					'method' => 'status_update',
					'hook'   => 'friends_status_update_status',
				)
			),
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access    public
	 * @return    bool
	 */

	function install()
	{
		// Already installed, let's not install again.
		if ($this->database_version() !== false) {
			return false;
		}

		// --------------------------------------------
		//  Our Default Install
		// --------------------------------------------

		if ($this->default_module_install() == false) {
			return false;
		}

		// --------------------------------------------
		//	Add prefs
		// --------------------------------------------

		$this->_add_prefs();

		// --------------------------------------------
		//	Additional DB work
		// --------------------------------------------

		$sql = array();

		$sql = array_merge($sql, $this->_sql_alter_comments('install'), $this->_sql_alter_members('install'));

		// --------------------------------------------
		//  Module Install
		// --------------------------------------------

		$sql[] = ee()->db->insert_string(
			'exp_modules',
			array(
				'module_name'    => $this->class_name,
				'module_version' => $this->version,
				'has_cp_backend' => 'y',
			)
		);

		foreach ($sql as $query) {
			ee()->db->query($query);
		}

		return true;
	}



	/**
	 * Add prefs
	 *
	 * @access    public
	 * @return    array
	 */

	function _add_prefs($mode = 'install')
	{
		ee()->load->helper('string');

		// --------------------------------------------
		//	Grab prefs from DB
		// --------------------------------------------

		$sql = "SELECT site_id, site_system_preferences
					FROM exp_sites";

		$query = ee()->db->query($sql);

		if ($query->num_rows() == 0) {
			return false;
		}

		// --------------------------------------------
		//	Deinstall mode?
		// --------------------------------------------

		if ($mode == 'deinstall') {
			foreach ($query->result_array() as $row) {

				$prefs = unserialize(base64_decode($row['site_system_preferences']));


				foreach ($this->preferences as $preference) {
					unset($prefs[$preference]);
				}

				$prefs = base64_encode(serialize($prefs));

				ee()->db->query(
					ee()->db->update_string(
						'exp_sites',
						array(
							'site_system_preferences' => $prefs,
						),
						array(
							'site_id' => $row['site_id'],
						)
					)
				);
			}
		}

		// --------------------------------------------
		//	Install mode?
		// --------------------------------------------

		if ($mode == 'install') {
			foreach ($query->result_array() as $row) {

				$prefs = unserialize(base64_decode($row['site_system_preferences']));

				foreach ($this->preferences as $preference) {
					$prefs[$preference] = '';
				}

				// --------------------------------------------
				//	Set eligible member groups
				// --------------------------------------------

				$prefs['fbc_eligible_member_groups'] = implode(
					"|",
					array_keys($this->model('Data')->get_safe_member_groups($row['site_id']))
				);

				// --------------------------------------------
				//	Set fbc member group
				// --------------------------------------------

				$prefs['fbc_member_group'] = 5;

				// --------------------------------------------
				//	Set member activation
				// --------------------------------------------

				$prefs['fbc_account_activation'] = 'fbc_no_activation';

				// --------------------------------------------
				//	Set FB connect url
				// --------------------------------------------

				$prefs['fbc_connect_url'] = $this->theme_url;

				// --------------------------------------------
				//	Update DB
				// --------------------------------------------

				$prefs = base64_encode(serialize($prefs));

				ee()->db->query(
					ee()->db->update_string(
						'exp_sites',
						array(
							'site_system_preferences' => $prefs,
						),
						array(
							'site_id' => $row['site_id'],
						)
					)
				);
			}
		}
	}


	/**
	 * SQL for exp_comments alters
	 *
	 * @access    public
	 * @return    array
	 */

	function _sql_alter_comments($mode = 'install')
	{
		$sql = array();

		if ($mode == 'install') {
			// --------------------------------------------
			//	Alter email column to accommodate longer Facebook proxied email addresses
			// --------------------------------------------

			$query = ee()->db->query("DESCRIBE exp_comments email");

			if ($query->row('Type') !== 'varchar(100)') {
				$sql[] = "ALTER TABLE exp_comments CHANGE email email varchar(100) NOT NULL DEFAULT ''";
			}
		}

		return $sql;
	}

	/**    End SQL for exp_comments alters */

	// --------------------------------------------------------------------

	/**
	 * SQL for exp_members alters
	 *
	 * @access    public
	 * @return    array
	 */

	function _sql_alter_members($mode = 'install')
	{
		$sql = array();

		// --------------------------------------------
		//	Deinstall mode?
		// --------------------------------------------

		if ($mode == 'deinstall') {
			$sql[] = "ALTER TABLE exp_members DROP facebook_connect_user_id";

			return $sql;
		}

		// --------------------------------------------
		//	Check for columns in members table
		// --------------------------------------------

		if (ee()->db->field_exists('facebook_connect_user_id', 'exp_members') === false) {
			$sql[] = "ALTER TABLE exp_members ADD facebook_connect_user_id bigint(20) unsigned NOT NULL default '0' AFTER member_id";
		}

		// --------------------------------------------
		//	Alter email column to accommodate longer Facebook proxied email addresses
		// --------------------------------------------

		$query = ee()->db->query("DESCRIBE exp_members email");

		if ($query->row('Type') !== 'varchar(100)') {
			$sql[] = "ALTER TABLE exp_members CHANGE email email varchar(100) NOT NULL DEFAULT ''";
		}

		return $sql;
	}

	/**    End SQL for exp_members alters */

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access    public
	 * @return    bool
	 */

	function uninstall()
	{
		// Cannot uninstall what does not exist, right?
		if ($this->database_version() === false) {
			return false;
		}

		// --------------------------------------------
		//	Add prefs
		// --------------------------------------------

		$this->_add_prefs('deinstall');

		// --------------------------------------------
		//	Additional uninstall routines
		// --------------------------------------------

		$sql = array();

		$sql = array_merge($sql, $this->_sql_alter_members('deinstall'));

		foreach ($sql as $query) {
			ee()->db->query($query);
		}

		// --------------------------------------------
		//  Default Module Uninstall
		// --------------------------------------------

		if ($this->default_module_uninstall() == false) {
			return false;
		}

		return true;
	}
	/* END */


	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access    public
	 * @return    bool
	 */

	function update($current = "")
	{
		if ($current == $this->version) {
			return false;
		}

		// --------------------------------------------
		//  Default Module Update
		// --------------------------------------------

		$this->default_module_update();

		// --------------------------------------------
		//	Do DB work
		// --------------------------------------------

		$sqlFilePath = sprintf('%sdb.%s.sql', $this->addon_path, strtolower($this->lower_name));
		if (!file_exists($sqlFilePath)) {
			return false;
		}

		$sql = preg_split(
			"/;;\s*(\n+|$)/",
			file_get_contents($sqlFilePath),
			-1,
			PREG_SPLIT_NO_EMPTY
		);

		if (count($sql) == 0) {
			return false;
		}

		foreach ($sql as $i => $query) {
			$sql[$i] = trim($query);
		}

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}

		// --------------------------------------------
		//	Add FB Connect URL
		// --------------------------------------------

		if (version_compare($this->database_version(), '1.0.1', '<')) {
			$sites = $this->model('Data')->get_sites();

			foreach (array_keys($this->model('Data')->get_sites()) as $site_id) {
				$this->model('Data')->set_preference(array('fbc_connect_url' => $this->theme_url), $site_id);
			}
		}

		// --------------------------------------------
		//	Update for 1.0.5
		// --------------------------------------------

		if (version_compare($this->database_version(), '1.0.5', '<')) {
			$sql = 'ALTER TABLE exp_fbc_params CHANGE `hash` `hash` varchar(32) NOT NULL DEFAULT \'\'';

			ee()->db->query($sql);
		}

		// --------------------------------------------
		//	Update for 2.0.0
		// --------------------------------------------
		//	Param handling changed a bit in 2.0.0 so we want to delete old param values and restart
		// --------------------------------------------

		if (version_compare($this->database_version(), '2.0.0', '<')) {
			$sql = 'TRUNCATE exp_fbc_params';

			ee()->db->query($sql);
		}

		// --------------------------------------------
		//	Update for 2.0.1
		// --------------------------------------------
		//	Delete all FBC tables. We use no extra tables for FBC
		// --------------------------------------------

		if (version_compare($this->database_version(), '2.0.1', '<')) {
			if (ee()->db->table_exists('exp_fbc_member_fields_map') === true) {
				// ee()->db->query( "DROP TABLE exp_fbc_member_fields_map" );
			}
		}

		if (version_compare($this->database_version(), '4.0.0', '<')) {
			$legacyPreferences = $this->model('Data')->getLegacyPreferences();

			foreach ($legacyPreferences as $siteId => $preferences) {
				$mappedPreferences = array(
					'app_id'                          => $preferences['fbc_app_id'],
					'app_secret'                      => $preferences['fbc_secret'],
					'eligible_member_groups'          => $preferences['fbc_eligible_member_groups'],
					'enable_passive_registration'     => $preferences['fbc_passive_registration'],
					'confirm_before_syncing_accounts' => $preferences['fbc_confirm_account_sync'],
					'member_group'                    => $preferences['fbc_member_group'],
				);

				foreach ($mappedPreferences as $key => $value) {
					if ($value) {
						ee()->db->query(
							sprintf(
								'INSERT INTO exp_fbc_preferences(fbc_preference_name, fbc_preference_value) VALUES("%s", "%s")',
								$key,
								$value
							)
						);
					}
				}
			}
		}

		// --------------------------------------------
		//  Version Number Update - LAST!
		// --------------------------------------------

		ee()->db->update(
			'exp_modules',
			array('module_version' => $this->version),
			array('module_name' => $this->class_name)
		);

		return true;
	}

	/* END update() */
}
// END Class

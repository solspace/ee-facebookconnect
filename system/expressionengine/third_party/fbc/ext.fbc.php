<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Facebook Connect - Extension
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		3.0.0
 * @filesource	fbc/ext.fbc.php
 */

require_once 'addon_builder/extension_builder.php';

class Fbc_ext extends Extension_builder_fbc
{
	public $api;
	public $FB;

	public $settings		= array();

	public $name			= '';
	public $version		= '';
	public $description	= '';
	public $settings_exist	= 'n';
	public $docs_url		= '';

	public $required_by 	= array('module');
	// -------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct($settings = array())
	{
		parent::__construct();

		// --------------------------------------------
		//  Settings
		// --------------------------------------------

		$this->settings = $settings;
	}
	/* END Fbc_extension_base() */


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
	 * Insert comment end
	 *
	 * @access	public
	 * @return	array
	 */

	 function insert_comment_end( $data, $comment_moderate, $comment_id )
	 {
		// --------------------------------------------
		//	Have they elected to publish to Facebook?
		// --------------------------------------------

		if (
				(
				! empty( $_POST['fbc_stream_publish'] )
				AND $_POST['fbc_stream_publish'] == 'y'
				)
				OR
				(
				! empty( $_POST['fbc_publish_to_facebook'] )
				AND $_POST['fbc_publish_to_facebook'] == 'y'
				)
			)
		{
			// --------------------------------------------
			//	Negotiate weblog v channel
			// --------------------------------------------

			$weblog	= 'channel';

			// --------------------------------------------
			//	Is an excerpt field indicated for the weblog?
			// --------------------------------------------

			$sql	= "SELECT search_excerpt
			FROM exp_" . $weblog . "s
			WHERE site_id = " . ee()->db->escape_str( ee()->config->item('site_id') ) . "
			AND search_excerpt != 0
			AND " . $weblog . "_id = " . ee()->db->escape_str( $data[ $weblog . '_id' ] ) . "
			LIMIT 1";

			$query	= ee()->db->query( $sql );

			// --------------------------------------------
			//	Get weblog title and excerpt
			// --------------------------------------------

			$sql	= "SELECT wt.title";

			if ( $query->num_rows() > 0 )
			{
				$sql	.= ", wd.field_id_" . $query->row('search_excerpt') . " AS excerpt";
			}

			$sql	.= " FROM exp_" . $weblog . "_titles wt";

			if ( $query->num_rows() > 0 )
			{
				$sql	.= " LEFT JOIN exp_" . $weblog . "_data wd ON wd.entry_id = wt.entry_id";
			}

			$sql	.= " WHERE wt." . $weblog . "_id = " . ee()->db->escape_str( $data[ $weblog . '_id' ] ) . "
				AND wt.entry_id = " . ee()->db->escape_str( $data['entry_id'] ) . "
				LIMIT 1";

			$query	= ee()->db->query( $sql );

			if ( $query->num_rows() == 0 ) return FALSE;

			// --------------------------------------------
			//	Assemble data
			// --------------------------------------------

			$post['message']	= stripslashes( $data['comment'] );
			$post['name']		= $query->row('title');
			// $post['caption']	= $query->row('title');
			$post['link']		= $_SERVER['HTTP_REFERER'];

			$row	= $query->row_array();

			if ( isset( $row['excerpt'] ) !== FALSE )
			{
				$post['description']	= strip_tags( $row['excerpt'] );
			}

			// --------------------------------------------
			//	Assemble image attachment if present
			// --------------------------------------------

			if ( ! empty( $_POST['fbc_image_attachment'] )  )
			{
				$post['picture']	= $_POST['fbc_image_attachment'];
			}

			// --------------------------------------------
			//	Log this action
			// --------------------------------------------

			$this->_log_to_cp( print_r( $post, TRUE ) );

			// --------------------------------------------
			//	Invoke API and send
			// --------------------------------------------

			$this->api();

			$this->api->feed( $post );
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return TRUE;
	 }

	 /*	End insert comment end */

	// -------------------------------------------------------------

	/**
	 * Insert rating end
	 *
	 * @access	public
	 * @return	array
	 */

	 function insert_rating_end( $data, $comment_moderate )
	 {
		// --------------------------------------------
		//	Have they elected to publish to Facebook?
		// --------------------------------------------

		if (
				(
				! empty( $_POST['fbc_stream_publish'] )
				AND $_POST['fbc_stream_publish'] == 'y'
				)
				OR
				(
				! empty( $_POST['fbc_publish_to_facebook'] )
				AND $_POST['fbc_publish_to_facebook'] == 'y'
				)
			)
		{
			// --------------------------------------------
			//	Negotiate weblog v channel
			// --------------------------------------------

			$weblog	= 'channel';

			// --------------------------------------------
			//	Is an excerpt field indicated for the weblog?
			// --------------------------------------------

			$sql	= "SELECT search_excerpt
			FROM exp_" . $weblog . "s
			WHERE site_id = " . ee()->db->escape_str( ee()->config->item('site_id') ) . "
			AND search_excerpt != 0
			AND " . $weblog . "_id = " . ee()->db->escape_str( $data[ $weblog . '_id' ] ) . "
			LIMIT 1";

			$query	= ee()->db->query( $sql );

			// --------------------------------------------
			//	Get weblog title and excerpt
			// --------------------------------------------

			$sql	= "SELECT wt.title";

			if ( $query->num_rows() > 0 )
			{
				$sql	.= ", wd.field_id_" . $query->row('search_excerpt') . " AS excerpt";
			}

			$sql	.= " FROM exp_" . $weblog . "_titles wt";

			if ( $query->num_rows() > 0 )
			{
				$sql	.= " LEFT JOIN exp_" . $weblog . "_data wd ON wd.entry_id = wt.entry_id";
			}

			$sql	.= " WHERE wt." . $weblog . "_id = " . ee()->db->escape_str( $data[ $weblog . '_id' ] ) . "
				AND wt.entry_id = " . ee()->db->escape_str( $data['entry_id'] ) . "
				LIMIT 1";

			$query	= ee()->db->query( $sql );

			if ( $query->num_rows() == 0 ) return FALSE;

			// --------------------------------------------
			//	Handle comment format
			// --------------------------------------------

			$comment_format	= $comment = "I gave this {fbc_rating} out of 5 stars.\n\n{fbc_review}";

			if ( ! empty( $_POST['fbc_rating_comment_format'] ) )
			{
				$comment_format	= $comment = stripslashes( $_POST['fbc_rating_comment_format'] );
			}

			// --------------------------------------------
			//	Add fbc prefix to all keys in $data array and create new array.
			// --------------------------------------------

			$fbc_data		= array();
			$fbc_post_data	= array();

			foreach ( $data as $key => $val )
			{
				$fbc_data[ 'fbc_' . $key ]	= $val;
			}

			foreach ( $_POST as $key => $val )
			{
				$fbc_post_data[ 'fbc_' . $key ]	= $val;
			}

			// --------------------------------------------
			//	Fix for EE's aggressive conditional parsing. Since we want people to be able to use conditionals in the comment format, but since EE will parse them before we get to see them, we use square braces instead.
			// --------------------------------------------

			if ( strpos( $comment_format, '[if' ) !== FALSE )
			{
				$comment_format	= preg_replace( "/\[(.*?)\]/s", LD . "$1" . RD, $comment_format );

				// --------------------------------------------
				//	Load Template Parser and Typography
				// --------------------------------------------

				require_once 'addon_builder/parser.addon_builder.php';

				// --------------------------------------------
				//	Parse
				// --------------------------------------------

				ee()->TMPL = $GLOBALS['TMPL'] = new Addon_builder_parser();

				ee()->TMPL->encode_email = FALSE;

				ee()->TMPL->global_vars	= array_merge(ee()->TMPL->global_vars, $fbc_data, $fbc_post_data);

				$comment = $GLOBALS['TMPL']->process_string_as_template($comment_format);
			}

			foreach ( $fbc_data as $key => $val )
			{
				$comment	= str_replace( LD . $key . RD, $val, $comment );
			}

			// --------------------------------------------
			//	Assemble data
			// --------------------------------------------

			$post['message']	= stripslashes( $comment );
			$post['name']		= $query->row('title');
			//$post['caption']	= $query->row('title');
			$post['link']		= $_SERVER['HTTP_REFERER'];

			$row	= $query->row_array();

			if ( isset( $row['excerpt'] ) !== FALSE )
			{
				$post['description']	= strip_tags( $row['excerpt'] );
			}

			// --------------------------------------------
			//	Assemble image attachment if present
			// --------------------------------------------

			if ( ! empty( $_POST['fbc_image_attachment'] )  )
			{
				$post['picture']	= $_POST['fbc_image_attachment'];
			}

			// --------------------------------------------
			//	Log this action
			// --------------------------------------------

			$this->_log_to_cp( print_r( $post, TRUE ) );

			// --------------------------------------------
			//	Invoke API and send
			// --------------------------------------------

			$this->api();

			$this->api->feed( $post );
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return TRUE;
	 }

	 /*	End insert rating end */

	// -------------------------------------------------------------

	/**
	 * Log to CP
	 *
	 * @access	public
	 * @return	string
	 */

	public function _log_to_cp( $msg = '' )
	{
		if ( $msg == '' )
		{
			return FALSE;
		}

		$data = array(
			'id'         => '',
			'member_id'  => '1',
			'username'   => 'Facebook Connect Module',
			'ip_address' => ee()->input->ip_address(),
			'act_date'   => ee()->localize->now,
			'action'     => 'Facebook:' . $msg
		 );

		ee()->db->insert('exp_cp_log', $data);
	}

	/*	End log to CP */

	// -------------------------------------------------------------

	/**
	 * Status update
	 *
	 * @access	public
	 * @return	array
	 */

	 function status_update( &$ths, $data )
	 {
		$data	= ( ! empty( ee()->extensions->last_call ) AND is_array( ee()->extensions->last_call ) === TRUE ) ? ee()->extensions->last_call: $data;

		// --------------------------------------------
		//	Have they elected to publish to Facebook?
		// --------------------------------------------

		if (
				(
				! empty( $_POST['fbc_stream_publish'] )
				AND $_POST['fbc_stream_publish'] == 'y'
				)
				OR
				(
				! empty( $_POST['fbc_publish_to_facebook'] )
				AND $_POST['fbc_publish_to_facebook'] == 'y'
				)
			)
		{
			// --------------------------------------------
			//	Invoke API
			// --------------------------------------------

			$this->api();

			$this->api->feed( array( 'message' => $data['status'] ) );
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $data;
	 }

	 /*	End status update */

	// -------------------------------------------------------------

	/**
	 * Activate Extension
	 *
	 * A required method that we actually ignore because this extension is installed by its module
	 * and no other place.  If they want the extension enabled, they have to install the module.
	 *
	 * @access	public
	 * @return	null
	 */

	public function activate_extension(){}


	// -------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * A required method that we actually ignore because this extension is installed by its module
	 * and no other place.  If they want the extension disabled, they have to uninstall the module.
	 *
	 * @access	public
	 * @return	null
	 */

	public function disable_extension(){}

	// -------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * A required method that we actually ignore because this extension is updated by its module
	 * and no other place.  We cannot redirect to the module upgrade script because we require a
	 * confirmation dialog, whereas extensions were designed to update automatically as they will try
	 * to call the update script on both the User and CP side.
	 *
	 * @access	public
	 * @return	null
	 */

	public function update_extension(){}

	// -------------------------------------------------------------


	/**
	 * Error Page
	 *
	 * @access	public
	 * @param	string	$error	Error message to display
	 * @return	null
	 */

	public function error_page($error = '')
	{
		$this->cached_vars['error_message'] = $error;

		$this->cached_vars['page_title'] = lang('error');

		// --------------------------------------------
		//  Output
		// --------------------------------------------

		$this->ee_cp_view('error_page.html');
	}
	/* END error_page() */

}
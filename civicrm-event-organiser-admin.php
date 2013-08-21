<?php /*
--------------------------------------------------------------------------------
CiviCRM_WP_Event_Organiser_Admin Class
--------------------------------------------------------------------------------
*/

class CiviCRM_WP_Event_Organiser_Admin {
	
	/** 
	 * properties
	 */
	
	// parent object
	public $plugin;
	
	
	
	/** 
	 * @description: initialises this object
	 * @return object
	 */
	function __construct() {
		
		// is this the back end?
		if ( is_admin() ) {
			
			// multisite?
			if ( is_multisite() ) {
				
				// add menu to Network submenu
				add_action( 'network_admin_menu', array( $this, 'add_admin_menu' ), 30 );
				
			} else {
				
				// add menu to Network submenu
				add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 30 );
				
			}
			
		}
		
		// --<
		return $this;
		
	}
	
	
	
	/**
	 * @description: set references to other objects
	 * @return nothing
	 */
	public function set_references( $parent ) {
		
		// store
		$this->plugin = $parent;
		
	}
	
	
		
	//##########################################################################
	
	
	
	/** 
	 * @description: add an admin page for this plugin
	 * @return nothing
	 */
	public function add_admin_menu() {
		
		// we must be network admin in multisite
		if ( is_multisite() AND !is_super_admin() ) { return false; }
		
		// check user permissions
		if ( !current_user_can( 'manage_options' ) ) { return false; }
		
		// try and update options
		$saved = $this->update_options();

		// multisite?
		if ( is_multisite() ) {
				
			// add the admin page to the Network Settings menu
			$page = add_submenu_page( 
				
				'settings.php', 
				__( 'CiviCRM Event Organiser', 'civicrm-event-organiser' ), 
				__( 'CiviCRM Event Organiser', 'civicrm-event-organiser' ), 
				'manage_options', 
				'civi_eo_admin_page', 
				array( $this, 'admin_form' )
				
			);
			
		} else {
			
			// add the admin page to the Settings menu
			$page = add_options_page( 
				
				'settings.php', 
				__( 'CiviCRM Event Organiser', 'civicrm-event-organiser' ), 
				__( 'CiviCRM Event Organiser', 'civicrm-event-organiser' ), 
				'manage_options', 
				'civi_eo_admin_page', 
				array( $this, 'admin_form' )
				
			);
			
		}
		
		// add styles only on our admin page, see:
		// http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Load_scripts_only_on_plugin_pages
		//add_action( 'admin_print_styles-'.$page, array( $this, 'add_admin_styles' ) );
	
	}
	
	
	
	/**
	 * @description: enqueue any styles and scripts needed by our admin page
	 * @return nothing
	 */
	public function add_admin_styles() {
		
		// add admin css
		wp_enqueue_style(
			
			'civi_eo_admin_style', 
			CIVICRM_WP_EVENT_ORGANISER_URL . 'assets/css/admin.css',
			null,
			CIVICRM_WP_EVENT_ORGANISER_VERSION,
			'all' // media
			
		);
		
	}
	
	
	
	/**
	 * @description: show our admin page
	 * @return nothing
	 */
	public function admin_form() {
		
		// only allow network admins through
		if( ! is_super_admin() ) {
			wp_die( __( 'You do not have permission to access this page.', 'civicrm-event-organiser' ) );
		}
		
		// if we've updated...
		if ( isset( $_GET['updated'] ) ) {
			echo '<div id="message" class="updated"><p>'.__( 'Options saved.', 'civicrm-event-organiser' ).'</p></div>';
		}
		
		// sanitise admin page url
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( is_array( $url_array ) ) { $url = $url_array[0]; }
		
		// get all participant roles
		$roles = $this->plugin->civi->get_participant_roles_select( $event = null );
		
		// get all event types
		$types = $this->plugin->civi->get_event_types_select();
		
		// open admin page
		echo '
		
		<div class="wrap" id="civi_eo_admin_wrapper">
		
		<div class="icon32" id="icon-options-general"><br/></div>
		
		<h2>'.__( 'CiviCRM Event Organiser', 'civicrm-event-organiser' ).'</h2>
		
		<form method="post" action="'.htmlentities($url.'&updated=true').'">
		
		'.wp_nonce_field( 'civi_eo_admin_action', 'civi_eo_nonce', true, false ).'
		'.wp_referer_field( false ).'
		
		';
		
		// open div
		echo '<div id="civi_eo_admin_options">
		
		';
		
		
		
		// show table
		echo '
		<h3>'.__( 'General Settings', 'civicrm-event-organiser' ).'</h3>
		
		<p>'.__( 'The following options configure some CiviCRM and Event Organiser defaults.', 'civicrm-event-organiser' ).'</p>
		
		<table class="form-table">
		
		';
		
		// did we get any roles?
		if ( $roles != '' ) {
		
			echo '
			<tr valign="top">
				<th scope="row"><label for="civi_eo_event_default_role">'.__( 'Default CiviCRM Participant Role for Events', 'civicrm-event-organiser' ).'</label></th>
				<td><select id="civi_eo_event_default_role" name="civi_eo_event_default_role">'.$roles.'</select></td>
			</tr>
			';
			
		}
		
		// did we get any types?
		if ( $types != '' ) {
			
			echo '
			<tr valign="top">
				<th scope="row"><label for="civi_eo_event_default_type">'.__( 'Default CiviCRM Event Type', 'civicrm-event-organiser' ).'</label></th>
				<td><select id="civi_eo_event_default_type" name="civi_eo_event_default_type">'.$types.'</select></td>
			</tr>
			';
			
		}
		
		// close table
		echo '
		</table>';
		
		
		
		// show table
		echo '
		<h3>'.__( 'Event Synchronisation', 'civicrm-event-organiser' ).'</h3>
		
		<table class="form-table">
			
			<tr valign="top">
				<th scope="row"><label for="civi_eo_event_eo_to_civi">'.__( 'Synchronise Event Organiser Events to CiviEvents', 'civicrm-event-organiser' ).'</label></th>
				<td><input id="civi_eo_event_eo_to_civi" name="civi_eo_event_eo_to_civi" value="1" type="checkbox" /></td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="civi_eo_event_civi_to_eo">'.__( 'Synchronise CiviEvents to Event Organiser Events', 'civicrm-event-organiser' ).'</label></th>
				<td><input id="civi_eo_event_civi_to_eo" name="civi_eo_event_civi_to_eo" value="1" type="checkbox" /></td>
			</tr>
			
		</table>';
		
		
		
		// show table
		echo '
		<h3>'.__( 'Event Type Synchronisation', 'civicrm-event-organiser' ).'</h3>
		
		<p>'.__( 'At present, there is no CiviCRM hook that fires when a CiviEvent event type is deleted.', 'civicrm-event-organiser' ).'<br />
		'.__( 'Event types should always be deleted from the Event Category screen.', 'civicrm-event-organiser' ).'</p>
		
		<table class="form-table">
			
			<tr valign="top">
				<th scope="row"><label for="civi_eo_tax_eo_to_civi">'.__( 'Synchronise Event Organiser Categories to CiviCRM Event Types', 'civicrm-event-organiser' ).'</label></th>
				<td><input id="civi_eo_tax_eo_to_civi" name="civi_eo_tax_eo_to_civi" value="1" type="checkbox" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="civi_eo_tax_civi_to_eo">'.__( 'Synchronise CiviCRM Event Types to Event Organiser Categories', 'civicrm-event-organiser' ).'</label></th>
				<td><input id="civi_eo_tax_civi_to_eo" name="civi_eo_tax_civi_to_eo" value="1" type="checkbox" /></td>
			</tr>
			
		</table>';
		
		
		
		// show table
		echo '
		<h3>'.__( 'Venue Synchronisation', 'civicrm-event-organiser' ).'</h3>
		
		<table class="form-table">
			
			<tr valign="top">
				<th scope="row"><label for="civi_eo_eo_to_civi">'.__( 'Synchronise Event Organiser Venues to CiviEvent Locations', 'civicrm-event-organiser' ).'</label></th>
				<td><input id="civi_eo_eo_to_civi" name="civi_eo_eo_to_civi" value="1" type="checkbox" /></td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="civi_eo_civi_to_eo">'.__( 'Synchronise CiviEvent Locations to Event Organiser Venues', 'civicrm-event-organiser' ).'</label></th>
				<td><input id="civi_eo_civi_to_eo" name="civi_eo_civi_to_eo" value="1" type="checkbox" /></td>
			</tr>
			
		</table>';
		
		
		
		// close div
		echo '
		
		</div>';
		
		// show submit button
		echo '
	
		<p class="submit">
			<input type="submit" name="civi_eo_submit" value="'.__( 'Submit', 'civicrm-event-organiser' ).'" class="button-primary" />
		</p>
	
		';
		
		// close form
		echo '
		
		</form>
		
		</div>
		'."\n\n\n\n";
		
		
		
	}
	
	
	
	/**
	 * @description: update options as supplied by our admin form
	 * @return nothing
	 */
	public function update_options() {
		
		// was the form submitted?
		if( isset( $_POST['civi_eo_submit'] ) ) {
			
			// check that we trust the source of the data
			check_admin_referer( 'civi_eo_admin_action', 'civi_eo_nonce' );
			
			// init vars
			$civi_eo_event_default_role = '0';
			$civi_eo_eo_to_civi = '0';
			$civi_eo_civi_to_eo = '0';
			$civi_eo_tax_eo_to_civi = '0';
			$civi_eo_tax_civi_to_eo = '0';
			$civi_eo_event_eo_to_civi = '0';
			$civi_eo_event_civi_to_eo = '0';
			
			// get variables
			extract( $_POST );
			
			// sanitise
			$civi_eo_event_default_role = absint( $civi_eo_event_default_role );
			
			// save option
			$this->option_save( 'civi_eo_event_default_role', $civi_eo_event_default_role );
			
			// sanitise
			$civi_eo_event_default_type = absint( $civi_eo_event_default_type );
			
			// save option
			$this->option_save( 'civi_eo_event_default_type', $civi_eo_event_default_type );
			
			// did we ask to sync events to CiviCRM?
			if ( absint( $civi_eo_event_eo_to_civi ) === 1 ) {
				
				// sync EO to Civi
				$this->sync_events_to_civi();
				
			}
			
			// did we ask to sync events to EO?
			if ( absint( $civi_eo_event_civi_to_eo ) === 1 ) {
				
				// sync Civi to EO
				$this->sync_events_to_eo();
				
			}
			
			// did we ask to sync venues to CiviCRM?
			if ( absint( $civi_eo_eo_to_civi ) === 1 ) {
				
				// sync EO to Civi
				$this->sync_venues_to_locations();
				
			}
			
			// did we ask to sync locations to EO?
			if ( absint( $civi_eo_civi_to_eo ) === 1 ) {
				
				// sync Civi to EO
				$this->sync_locations_to_venues();
				
			}
			
			// did we ask to sync categories to CiviCRM?
			if ( absint( $civi_eo_tax_eo_to_civi ) === 1 ) {
				
				// sync EO to Civi
				$this->sync_categories_to_event_types();
				
			}
			
			// did we ask to sync categories to EO?
			if ( absint( $civi_eo_tax_civi_to_eo ) === 1 ) {
				
				// sync Civi to EO
				$this->sync_event_types_to_categories();
				
			}
			
			// debug
			//$this->show_venues_locations();
			//$this->show_eo_civi_events();
			//$this->show_eo_civi_taxonomies();
			print_r( $this->get_all_event_correspondences() ); die();
			
		}
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * @description: store CiviEvents <-> Event Organiser event data
	 * @param int $post_id the numeric ID of the WP post
	 * @param array $civi_event_ids all CiviEvent IDs for the post
	 * @return nothing
	 */
	public function store_event_correspondences( $post_id, $civi_event_ids ) {
		
		// an EO event needs to know the IDs of all the CiviEvents
		update_post_meta( $post_id, '_civi_eo_civicrm_events', $civi_event_ids );
		
		// init array
		$civi_event_data = array();
		
		// each CiviEvent needs to know the ID of the EO post
		if ( count( $civi_event_ids ) > 0 ) {
			
			// construct array
			foreach( $civi_event_ids AS $civi_event_id ) {
				
				// add post ID, keyed by CiviEvent ID
				$civi_event_data[$civi_event_id] = $post_id;
				
			}
			
		}
		
		// store option
		$this->option_save( 'civi_eo_civi_event_data', $civi_event_data );
		
	}
	
	
	
	/**
	 * @description: get all CiviEvent - Event Organiser event correspondences
	 * @param int $post_id the numeric ID of the WP post
	 * @return array $civi_event_ids all CiviEvent IDs for the post
	 */
	public function get_all_event_correspondences() {
	
		// init return
		$event_data = array();
		
		// add "Civi to EO"
		$event_data['civi_to_eo'] = $this->get_all_civi_to_eo_correspondences();
		
		// add "EO to Civi"
		$event_data['eo_to_civi'] = $this->get_all_eo_to_civi_correspondences();
		
		// --<
		return $event_data;
		
	}
	
	
	
	/**
	 * @description: get Event Organiser event ID for a CiviEvent event ID
	 * @return array $civi_event_data all CiviEvent IDs for the post
	 */
	public function get_civi_to_eo_correspondence( $civi_event_id ) {
	
		// init return
		$eo_event_id = false;
		
		// get all correspondences
		$eo_event_data = $this->get_all_civi_to_eo_correspondences();
		
		// if we get some...
		if ( count( $eo_event_data ) > 0 ) {
		
			// do we have the key
			if ( isset( $eo_event_data[$civi_event_id] ) ) {
			
				// get keyed value
				$eo_event_id = $eo_event_data[$civi_event_id];
				
			}
		
		}
		
		// --<
		return $eo_event_id;
		
	}
	
	
	
	/**
	 * @description: get all Event Organiser events for all CiviEvents
	 * @return array $civi_event_data all CiviEvent IDs for the post
	 */
	public function get_all_civi_to_eo_correspondences() {
	
		// store once
		static $eo_event_data;
		
		// have we done this?
		if ( !isset( $eo_event_data ) ) {
	
			// get option
			$eo_event_data = $this->option_get( 'civi_eo_civi_event_data', array() );
		
		}
		
		// --<
		return $eo_event_data;
		
	}
	
	
	
	/**
	 * @description: get CiviEvent IDs for an Event Organiser event ID
	 * @param int $post_id the numeric ID of the WP post
	 * @return array $civi_event_ids all CiviEvent IDs for the post
	 */
	public function get_eo_to_civi_correspondences( $post_id ) {
		
		// get the meta value
		$civi_event_ids = get_post_meta( $post_id, '_civi_eo_civicrm_events', true );
		
		// if it's not yet set it will be an empty string, so cast as array
		if ( $civi_event_ids === '' ) { $civi_event_ids = array(); }
		
		// --<
		return $civi_event_ids;
		
	}
	
	
	
	/**
	 * @description: get all CiviEvents for all Event Organiser events
	 * @param int $post_id the numeric ID of the WP post
	 * @return array $civi_event_ids all CiviEvent IDs for the post
	 */
	public function get_all_eo_to_civi_correspondences() {
		
		// init civi data
		$civi_event_data = array();
		
		// construct args for all event posts
		$args = array(
			
			'post_type' => 'event',
			'numberposts' => -1,
			
		);
		
		// get all event posts
		$all_events = get_posts( $args );

		// did we get any?
		if ( count( $all_events ) > 0 ) {
			
			// loop
			foreach( $all_events AS $event ) {
				
				// get post meta and add to return array
				$civi_event_data[$event->ID] = $this->get_eo_to_civi_correspondences( $event->ID );
				
			}
			
		}
		
		// --<
		return $civi_event_data;
		
	}
	
	
	
	/**
	 * @description: delete all CiviEvents for an Event Organiser event
	 * @param int $post_id the numeric ID of the WP post
	 * @return nothing
	 */
	public function clear_event_correspondences( $post_id ) {
		
		// delete the meta value
		delete_post_meta( $post_id, '_civi_eo_civicrm_events' );
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * @description: show values
	 * @return nothing
	 */
	public function show_eo_civi_events() {
		
		// construct args for all event posts
		$args = array(
			
			'post_type' => 'event',
			'numberposts' => -1,
			
		);
		
		// get all event posts
		$all_events = get_posts( $args );
		
		// get all EO events
		$all_eo_events = eo_get_events();
		
		// get all Civi Events
		$all_civi_events = $this->plugin->civi->get_all_events();
		
		// init
		$delete = array();
		
		// delete all?
		if ( 1 === 2 ) {
		
			// error check
			if ( $all_civi_events['is_error'] == '0' ) {
			
				// do we have any?
				if ( 
					is_array( $all_civi_events['values'] ) 
					AND 
					count( $all_civi_events['values'] ) > 0 
				) {
			
					// get all event IDs
					$all_civi_event_ids = array_keys( $all_civi_events['values'] );
				
					// delete all CiviEvents!
					$delete = $this->plugin->civi->delete_all_events( $all_civi_event_ids );
			
				}
			
			}
			
		}
		
		print_r( array(
			'all_events' => $all_events,
			'all_eo_events' => $all_eo_events,
			'all_civi_events' => $all_civi_events,
			'delete' => $delete,
		) ); 
		
		die();
		
	}
	
	
	
	/**
	 * @description: sync EO events to CiviEvents
	 * @return nothing
	 */
	public function sync_events_to_civi() {
		
		// construct args for all event posts
		$args = array(
			
			'post_type' => 'event',
			'numberposts' => -1,
			
		);
		
		// get all event posts
		$all_events = get_posts( $args );

		// did we get any?
		if ( count( $all_events ) > 0 ) {
			
			// loop
			foreach( $all_events AS $event ) {
				
				// get dates for this event
				$dates = $this->plugin->eo->get_all_dates( $event->ID );
				
				// update CiviEvent - or create if it doesn't exist
				$civi_event_ids = $this->plugin->civi->update_civi_events( $event, $dates );
				
				// store in EO venue
				$this->store_event_correspondences( $event->ID, $civi_event_ids );
				
			}
			
		}
		
	}
	
	
	
	/**
	 * @description: sync CiviEvents to EO events
	 * @return nothing
	 */
	public function sync_events_to_eo() {
		
		// get all Civi Events
		$all_civi_events = $this->plugin->civi->get_all_events();
		
		// sync Civi to EO
		if ( count( $all_civi_events['values'] ) > 0 ) {
			
			// loop
			foreach( $all_civi_events['values'] AS $civi_event ) {
				
				// update EO venue - or create if it doesn't exist
				$this->plugin->eo->update_event( $civi_event );
				
			}
			
		}
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * @description: show values
	 * @return nothing
	 */
	public function show_eo_civi_taxonomies() {
		
		// get all CiviEvent types
		$civi_types = $this->plugin->civi->get_event_types();
		
		// get all EO event category terms
		$eo_types = $this->plugin->eo->get_event_categories();
		
		///*
		print_r( array(
			'civi_types' => $civi_types,
			'eo_types' => $eo_types,
		) ); die();
		//*/
		
	}
	
	
	
	/**
	 * @description: sync EO event category terms to CiviEvent types
	 * @return nothing
	 */
	public function sync_categories_to_event_types() {
		
		// get all EO event category terms
		$all_terms = $this->plugin->eo->get_event_categories();
		
		// init links
		$links = array();
		
		// did we get any?
		if ( count( $all_terms ) > 0 ) {
			
			// loop
			foreach( $all_terms AS $term ) {
				
				// update CiviEvent term - or create if it doesn't exist
				$civi_event_type_id = $this->plugin->civi->update_event_type( $term );
				
				// add to array keyed by EO term ID
				$links[$term->term_id] = $civi_event_type_id;
				
			}
			
		}
		
		/*
		// did we get any links?
		print_r( array(
			'sync_categories_to_event_types links' => $links,
		) ); //die();
		*/
		
	}
	
	
	
	/**
	 * @description: sync CiviEvent types to EO event category terms
	 * @return nothing
	 */
	public function sync_event_types_to_categories() {
		
		// get all CiviEvent types
		$all_types = $this->plugin->civi->get_event_types();
		
		// kick out if we get nothing back
		if ( $all_types === false ) return;
		
		// init links
		$links = array();
		
		// did we get any?
		if ( $all_types['is_error'] == '0' AND count( $all_types['values'] ) > 0 ) {
			
			// loop
			foreach( $all_types['values'] AS $type ) {
				
				// update CiviEvent term - or create if it doesn't exist
				$eo_term_id = $this->plugin->eo->update_term( $type );
				
				// next on failure - perhaps we should note this?
				if ( $eo_term_id === false ) continue;
				
				// add to array keyed by EO term ID
				$links[$eo_term_id] = $type['id'];
				
			}
			
		}
		
		/*
		// did we get any links?
		print_r( array(
			'sync_event_types_to_categories links' => $links,
		) ); //die();
		*/
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * @description: show values
	 * @return nothing
	 */
	public function show_venues_locations() {
		
		// get all venues
		$all_venues = eo_get_venues();
		
		// get all Civi Event locations
		$all_locations = $this->plugin->civi->get_all_locations();
		
		// delete all Civi Event locations!
		//$this->plugin->civi->delete_all_locations();
		
		print_r( array(
			'all_venues' => $all_venues,
			'all_locations' => $all_locations,
		) ); 
		
		die();
		
	}
	
	
	
	/**
	 * @description: sync venues and locations
	 * @return nothing
	 */
	public function sync_venues_and_locations() {
		
		// sync EO to Civi
		$this->sync_venues_to_locations();
		
		// sync Civi to EO
		$this->sync_locations_to_venues();
		
	}
	
	
	
	/**
	 * @description: sync EO venues to CiviEvent locations
	 * @return nothing
	 */
	public function sync_venues_to_locations() {
		
		// get all venues
		$all_venues = eo_get_venues();
		
		// sync EO to Civi
		if ( count( $all_venues ) > 0 ) {
			
			// loop
			foreach( $all_venues AS $venue ) {
				
				// update Civi location - or create if it doesn't exist
				$location = $this->plugin->civi->update_location( $venue );
				
				// store in EO venue
				$this->plugin->eo_venue->store_civi_location( $venue->term_id, $location );
				
			}
			
		}
		
	}
	
	
	
	/**
	 * @description: sync CiviEvent locations to EO venues
	 * @return nothing
	 */
	public function sync_locations_to_venues() {
		
		// get all Civi Event locations
		$all_locations = $this->plugin->civi->get_all_locations();
		
		// sync Civi to EO
		if ( count( $all_locations['values'] ) > 0 ) {
			
			// loop
			foreach( $all_locations['values'] AS $location ) {
				
				// update EO venue - or create if it doesn't exist
				$this->plugin->eo_venue->update_venue( $location );
				
			}
			
		}
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * @description: get an option
	 * @param string $key the option name
	 * @return mixed $value
	 * @return nothing
	 */
	public function option_get( $key ) {
		
		// if multisite...
		if ( is_multisite() ) {
			
			// get site option
			$value = get_site_option( $key, $value );
			
		} else {
			
			// get option
			$value = get_option( $key, $value );
			
		}
		
		// --<
		return $value;

	}
	
	
	
	/**
	 * @description: save an option
	 * @param string $key the option name
	 * @return mixed $value the value to save
	 * @return nothing
	 */
	public function option_save( $key, $value ) {
		
		// if multisite...
		if ( is_multisite() ) {
			
			// update site option
			update_site_option( $key, $value );
			
		} else {
			
			// update option
			update_option( $key, $value );
			
		}
		
	}
	
	
	
	/**
	 * @description: save an option
	 * @param string $key the option name
	 * @return nothing
	 */
	public function option_delete( $key ) {
		
		// if multisite...
		if ( is_multisite() ) {
			
			// delete site option
			delete_site_option( $key, $value );
			
		} else {
			
			// delete option
			delete_option( $key, $value );
			
		}
		
	}
	
	
	
} // class ends






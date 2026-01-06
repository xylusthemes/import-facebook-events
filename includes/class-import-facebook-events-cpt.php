<?php
/**
 * Class for Register and manage Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class for Custom post type related functionalities.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_Cpt {

	/**
	 * Event Slug
	 *
	 * @var string
	 */
	public $event_slug;

	/**
	 * Event Post Type
	 *
	 * @var string
	 */
	protected $event_posttype;

	/**
	 * Event Taxonomy.
	 *
	 * @var string
	 */
	protected $event_category;

	/**
	 * Event tag.
	 *
	 * @var string
	 */
	protected $event_tag;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->event_posttype = 'facebook_events';
		$this->event_category = 'facebook_category';
		$this->event_tag      = 'facebook_tag';

		$ife_options       = get_option( IFE_OPTIONS );
		$this->event_slug = isset( $ife_options['event_slug'] ) ? $ife_options['event_slug'] : 'facebook-event';
		$deactive_fbevents = isset( $ife_options['deactive_fbevents'] ) ? $ife_options['deactive_fbevents'] : 'no';

		if ( 'no' === $deactive_fbevents ) {
			add_action( 'init', array( $this, 'register_event_post_type' ) );
			add_action( 'init', array( $this, 'register_event_taxonomy' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_event_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'save_event_meta_boxes' ), 10, 2 );

			add_filter( 'manage_facebook_events_posts_columns', array( $this, 'facebook_events_columns' ), 10, 1 );
			add_action( 'manage_posts_custom_column', array( $this, 'facebook_events_columns_data' ), 10, 2 );

			add_filter( 'the_content', array( $this, 'facebook_events_meta_before_content' ) );
			add_shortcode( 'facebook_events', array( $this, 'facebook_events_archive' ) );
		}
	}

	/**
	 * Get Events Post type.
	 *
	 * @since    1.0.0
	 */
	public function get_event_posttype() {
		return $this->event_posttype;
	}

	/**
	 * Get events category taxonomy.
	 *
	 * @since    1.0.0
	 */
	public function get_event_categroy_taxonomy() {
		return $this->event_category;
	}

	/**
	 * Get events tag taxonomy.
	 *
	 * @since    1.0.0
	 */
	public function get_event_tag_taxonomy() {
		return $this->event_tag;
	}

	/**
	 * Register Events Post type
	 *
	 * @since    1.0.0
	 */
	public function register_event_post_type() {
		// Event labels.
		$event_labels   = array(
			'name'                  => _x( 'Facebook Events', 'Post Type General Name', 'import-facebook-events' ),
			'singular_name'         => _x( 'Facebook Event', 'Post Type Singular Name', 'import-facebook-events' ),
			'menu_name'             => __( 'Facebook Events', 'import-facebook-events' ),
			'name_admin_bar'        => __( 'Facebook Event', 'import-facebook-events' ),
			'archives'              => __( 'Event Archives', 'import-facebook-events' ),
			'parent_item_colon'     => __( 'Parent Event:', 'import-facebook-events' ),
			'all_items'             => __( 'Facebook Events', 'import-facebook-events' ),
			'add_new_item'          => __( 'Add New Event', 'import-facebook-events' ),
			'add_new'               => __( 'Add New', 'import-facebook-events' ),
			'new_item'              => __( 'New Event', 'import-facebook-events' ),
			'edit_item'             => __( 'Edit Event', 'import-facebook-events' ),
			'update_item'           => __( 'Update Event', 'import-facebook-events' ),
			'view_item'             => __( 'View Event', 'import-facebook-events' ),
			'search_items'          => __( 'Search Event', 'import-facebook-events' ),
			'not_found'             => __( 'Not found', 'import-facebook-events' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'import-facebook-events' ),
			'featured_image'        => __( 'Featured Image', 'import-facebook-events' ),
			'set_featured_image'    => __( 'Set featured image', 'import-facebook-events' ),
			'remove_featured_image' => __( 'Remove featured image', 'import-facebook-events' ),
			'use_featured_image'    => __( 'Use as featured image', 'import-facebook-events' ),
			'insert_into_item'      => __( 'Insert into Event', 'import-facebook-events' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Event', 'import-facebook-events' ),
			'items_list'            => __( 'Event Items list', 'import-facebook-events' ),
			'items_list_navigation' => __( 'Event Items list navigation', 'import-facebook-events' ),
			'filter_items_list'     => __( 'Filter Event items list', 'import-facebook-events' ),
		);
		$rewrite        = array(
			'slug'       => $this->event_slug,
			'with_front' => false,
			'pages'      => true,
			'feeds'      => true,
			'ep_mask'    => EP_NONE,
		);
		$event_cpt_args = array(
			'label'               => __( 'Events', 'import-facebook-events' ),
			'description'         => __( 'Post type for Events', 'import-facebook-events' ),
			'labels'              => $event_labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'          => array( $this->event_category, $this->event_tag ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
		);
		register_post_type( $this->event_posttype, $event_cpt_args );
	}


	/**
	 * Register Event tag taxonomy
	 *
	 * @since    1.0.0
	 */
	public function register_event_taxonomy() {

		/* Register the Event Category taxonomy. */
		register_taxonomy(
			$this->event_category,
			array( $this->event_posttype ),
			array(
				'labels'            => array(
					'name'           => __( 'Event Categories', 'import-facebook-events' ),
					'singular_name'  => __( 'Event Category', 'import-facebook-events' ),
					'menu_name'      => __( 'Event Categories', 'import-facebook-events' ),
					'name_admin_bar' => __( 'Event Category', 'import-facebook-events' ),
					'search_items'   => __( 'Search Categories', 'import-facebook-events' ),
					'popular_items'  => __( 'Popular Categories', 'import-facebook-events' ),
					'all_items'      => __( 'All Categories', 'import-facebook-events' ),
					'edit_item'      => __( 'Edit Category', 'import-facebook-events' ),
					'view_item'      => __( 'View Category', 'import-facebook-events' ),
					'update_item'    => __( 'Update Category', 'import-facebook-events' ),
					'add_new_item'   => __( 'Add New Category', 'import-facebook-events' ),
					'new_item_name'  => __( 'New Category Name', 'import-facebook-events' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'query_var'         => true,
			)
		);

		/* Register the event Tag taxonomy. */
		register_taxonomy(
			$this->event_tag,
			array( $this->event_posttype ),
			array(
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'show_admin_column' => true,
				'hierarchical'      => false,
				'query_var'         => $this->event_tag,
				/* Labels used when displaying taxonomy and terms. */
				'labels'            => array(
					'name'                       => __( 'Event Tags', 'import-facebook-events' ),
					'singular_name'              => __( 'Event Tag', 'import-facebook-events' ),
					'menu_name'                  => __( 'Event Tags', 'import-facebook-events' ),
					'name_admin_bar'             => __( 'Event Tag', 'import-facebook-events' ),
					'search_items'               => __( 'Search Tags', 'import-facebook-events' ),
					'popular_items'              => __( 'Popular Tags', 'import-facebook-events' ),
					'all_items'                  => __( 'All Tags', 'import-facebook-events' ),
					'edit_item'                  => __( 'Edit Tag', 'import-facebook-events' ),
					'view_item'                  => __( 'View Tag', 'import-facebook-events' ),
					'update_item'                => __( 'Update Tag', 'import-facebook-events' ),
					'add_new_item'               => __( 'Add New Tag', 'import-facebook-events' ),
					'new_item_name'              => __( 'New Tag Name', 'import-facebook-events' ),
					'separate_items_with_commas' => __( 'Separate tags with commas', 'import-facebook-events' ),
					'add_or_remove_items'        => __( 'Add or remove tags', 'import-facebook-events' ),
					'choose_from_most_used'      => __( 'Choose from the most used tags', 'import-facebook-events' ),
					'not_found'                  => __( 'No tags found', 'import-facebook-events' ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
				),
			)
		);

	}


	/**
	 *  Add Meta box for team link meta box.
	 *
	 * @since 1.0.0
	 */
	public function add_event_meta_boxes() {
		add_meta_box(
			'facebook_events_metabox',
			__( 'Events Details', 'import-facebook-events' ),
			array( $this, 'render_event_meta_boxes' ),
			array( $this->event_posttype ),
			'normal',
			'high'
		);
	}

	/**
	 * Event meta box render
	 *
	 * @param object $post Post object.
	 * @return void
	 */
	public function render_event_meta_boxes( $post ) {

		// Use nonce for verification.
		wp_nonce_field( IFE_PLUGIN_DIR, 'ife_event_metabox_nonce' );

		$start_hour     = get_post_meta( $post->ID, 'event_start_hour', true );
		$start_minute   = get_post_meta( $post->ID, 'event_start_minute', true );
		$start_meridian = get_post_meta( $post->ID, 'event_start_meridian', true );
		$end_hour       = get_post_meta( $post->ID, 'event_end_hour', true );
		$end_minute     = get_post_meta( $post->ID, 'event_end_minute', true );
		$end_meridian   = get_post_meta( $post->ID, 'event_end_meridian', true );
		?>
		<table class="ife_form_table">
			<thead>
			<tr>
				<th colspan="2">
					<?php esc_attr_e( 'Time & Date', 'import-facebook-events' ); ?>
					<hr>
				</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php esc_attr_e( 'Start Date & Time', 'import-facebook-events' ); ?>:</td>
				<td>
				<input type="text" name="event_start_date" class="ife_datepicker" id="event_start_date" value="<?php echo esc_attr( get_post_meta( $post->ID, 'event_start_date', true ) ); ?>" /> @
				<?php
				$this->generate_dropdown( 'event_start', 'hour', $start_hour );
				$this->generate_dropdown( 'event_start', 'minute', $start_minute );
				$this->generate_dropdown( 'event_start', 'meridian', $start_meridian );
				?>
				</td>
			</tr>
			<tr>
				<td><?php esc_attr_e( 'End Date & Time', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="event_end_date" class="ife_datepicker" id="event_end_date" value="<?php echo esc_attr( get_post_meta( $post->ID, 'event_end_date', true ) ); ?>" /> @
					<?php
					$this->generate_dropdown( 'event_end', 'hour', $end_hour );
					$this->generate_dropdown( 'event_end', 'minute', $end_minute );
					$this->generate_dropdown( 'event_end', 'meridian', $end_meridian );
					?>
				</td>
			</tr>
			</tbody>
		</table>
		<div style="clear: both;"></div>
		<table class="ife_form_table">
			<thead>
			<tr>
				<th colspan="2">
					<?php esc_attr_e( 'Location Details', 'import-facebook-events' ); ?>
					<hr>
				</th>
			</tr>
			</thead>

			<tbody>
			<tr>
				<td><?php esc_attr_e( 'Venue', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_name" id="venue_name" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_name', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'Address', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_address" id="venue_address" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_address', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'City', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_city" id="venue_city" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_city', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'State', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_state" id="venue_state" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_state', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'Country', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_country" id="venue_country" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_country', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'Zipcode', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_zipcode" id="venue_zipcode" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_zipcode', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'Latitude', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_lat" id="venue_lat" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_lat', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'Longitude', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_lon" id="venue_lon" value="<?php echo esc_attr( get_post_meta( $post->ID, 'venue_lon', true ) ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_attr_e( 'Website', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="venue_url" id="venue_url" value="<?php echo esc_url( get_post_meta( $post->ID, 'venue_url', true ) ); ?>" />
				</td>
			</tr>
			</tbody>
		</table>
		<div style="clear: both;"></div>
		<table class="ife_form_table">
			<thead>
			<tr>
				<th colspan="2">
					<?php esc_attr_e( 'Organizer Details', 'import-facebook-events' ); ?>
					<hr>
				</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php esc_attr_e( 'Organizer Name', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="organizer_name" id="organizer_name" value="<?php echo esc_attr( get_post_meta( $post->ID, 'organizer_name', true ) ); ?>" />
				</td>
			</tr>
			<tr>
				<td><?php esc_attr_e( 'Email', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="email" name="organizer_email" id="organizer_email" value="<?php echo esc_attr( get_post_meta( $post->ID, 'organizer_email', true ) ); ?>" />
				</td>
			</tr>
			<tr>
				<td><?php esc_attr_e( 'Phone', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="organizer_phone" id="organizer_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, 'organizer_phone', true ) ); ?>" />
				</td>
			</tr>
			<tr>
				<td><?php esc_attr_e( 'Website', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="organizer_url" id="organizer_url" value="<?php echo esc_url( get_post_meta( $post->ID, 'organizer_url', true ) ); ?>" />
				</td>
			</tr>
			</tbody>
		</table>

		<div style="clear: both;"></div>
		<table class="ife_form_table">
		<thead>
			<tr>
				<th colspan="2">
					<?php esc_attr_e( 'Event Source Link', 'import-facebook-events' ); ?>
					<hr>
				</th>
			</tr>
		</thead>
			<tbody>
				<tr>
					<td><?php esc_attr_e( 'Source Link', 'import-facebook-events' ); ?>:</td>
				<td>
					<input type="text" name="ife_event_link" id="ife_event_link" value="<?php echo esc_url( get_post_meta( $post->ID, 'ife_event_link', true ) ); ?>" />
				</td>
			</tr>
			</tbody>
		</table>

		<?php
	}

	/**
	 * Generate dropdowns for event time.
	 *
	 * @param string $start_end Start End.
	 * @param string $type Type.
	 * @param string $selected Selected.
	 */
	public function generate_dropdown( $start_end, $type, $selected = '' ) {
		if ( empty( $start_end ) || empty( $type ) ) {
			return;
		}
		$select_name = $start_end . '_' . $type;
		if ( 'hour' === $type ) {
			?>
			<select name="<?php echo esc_attr( $select_name ); ?>">
				<option value="01" <?php selected( $selected, '01' ); ?>>01</option>
				<option value="02" <?php selected( $selected, '02' ); ?>>02</option>
				<option value="03" <?php selected( $selected, '03' ); ?>>03</option>
				<option value="04" <?php selected( $selected, '04' ); ?>>04</option>
				<option value="05" <?php selected( $selected, '05' ); ?>>05</option>
				<option value="06" <?php selected( $selected, '06' ); ?>>06</option>
				<option value="07" <?php selected( $selected, '07' ); ?>>07</option>
				<option value="08" <?php selected( $selected, '08' ); ?>>08</option>
				<option value="09" <?php selected( $selected, '09' ); ?>>09</option>
				<option value="10" <?php selected( $selected, '10' ); ?>>10</option>
				<option value="11" <?php selected( $selected, '11' ); ?>>11</option>
				<option value="12" <?php selected( $selected, '12' ); ?>>12</option>
			</select>
			<?php
		} elseif ( 'minute' === $type ) {
			?>
			<select name="<?php echo esc_attr( $select_name ); ?>">
				<option value="00" <?php selected( $selected, '00' ); ?>>00</option>
				<option value="05" <?php selected( $selected, '05' ); ?>>05</option>
				<option value="10" <?php selected( $selected, '10' ); ?>>10</option>
				<option value="15" <?php selected( $selected, '15' ); ?>>15</option>
				<option value="20" <?php selected( $selected, '20' ); ?>>20</option>
				<option value="25" <?php selected( $selected, '25' ); ?>>25</option>
				<option value="30" <?php selected( $selected, '30' ); ?>>30</option>
				<option value="35" <?php selected( $selected, '35' ); ?>>35</option>
				<option value="40" <?php selected( $selected, '40' ); ?>>40</option>
				<option value="45" <?php selected( $selected, '45' ); ?>>45</option>
				<option value="50" <?php selected( $selected, '50' ); ?>>50</option>
				<option value="55" <?php selected( $selected, '55' ); ?>>55</option>
			</select>
			<?php
		} elseif ( 'meridian' === $type ) {
			?>
			<select name="<?php echo esc_attr( $select_name ); ?>">
				<option value="am" <?php selected( $selected, 'am' ); ?>>am</option>
				<option value="pm" <?php selected( $selected, 'pm' ); ?>>pm</option>
			</select>
			<?php
		}
	}

	/**
	 * Save Testimonial meta box Options
	 *
	 * @param int    $post_id Post id.
	 * @param object $post Post.
	 *
	 * @return void|int
	 */
	public function save_event_meta_boxes( $post_id, $post ) {
		// Verify the nonce before proceeding.
		if ( ! isset( $_POST['ife_event_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ife_event_metabox_nonce'] ) ), IFE_PLUGIN_DIR ) ) { // input var okay;.
			return $post_id;
		}

		// Check user capability to edit post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// can't save if auto save.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// check if team then save it.
		if ( $post->post_type !== $this->event_posttype ) {
			return $post_id;
		}

		$postdata = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST ) ); // input var okay;.

		// Event Date & time Details.
		$event_start_date     = isset( $postdata['event_start_date'] ) ? $postdata['event_start_date'] : '';
		$event_end_date       = isset( $postdata['event_end_date'] ) ? $postdata['event_end_date'] : '';
		$event_start_hour     = isset( $postdata['event_start_hour'] ) ? $postdata['event_start_hour'] : '';
		$event_start_minute   = isset( $postdata['event_start_minute'] ) ? $postdata['event_start_minute'] : '';
		$event_start_meridian = isset( $postdata['event_start_meridian'] ) ? $postdata['event_start_meridian'] : '';
		$event_end_hour       = isset( $postdata['event_end_hour'] ) ? $postdata['event_end_hour'] : '';
		$event_end_minute     = isset( $postdata['event_end_minute'] ) ? $postdata['event_end_minute'] : '';
		$event_end_meridian   = isset( $postdata['event_end_meridian'] ) ? $postdata['event_end_meridian'] : '';

		$start_time = $event_start_date . ' ' . $event_start_hour . ':' . $event_start_minute . ' ' . $event_start_meridian;
		$end_time   = $event_end_date . ' ' . $event_end_hour . ':' . $event_end_minute . ' ' . $event_end_meridian;
		$start_ts   = strtotime( $start_time );
		$end_ts     = strtotime( $end_time );

		// Venue Deatails.
		$venue_name    = isset( $postdata['venue_name'] ) ? $postdata['venue_name'] : '';
		$venue_address = isset( $postdata['venue_address'] ) ? $postdata['venue_address'] : '';
		$venue_city    = isset( $postdata['venue_city'] ) ? $postdata['venue_city'] : '';
		$venue_state   = isset( $postdata['venue_state'] ) ? $postdata['venue_state'] : '';
		$venue_country = isset( $postdata['venue_country'] ) ? $postdata['venue_country'] : '';
		$venue_zipcode = isset( $postdata['venue_zipcode'] ) ? $postdata['venue_zipcode'] : '';
		$venue_lat     = isset( $postdata['venue_lat'] ) ? $postdata['venue_lat'] : '';
		$venue_lon     = isset( $postdata['venue_lon'] ) ? $postdata['venue_lon'] : '';
		$venue_url     = isset( $postdata['venue_url'] ) ? esc_url( $postdata['venue_url'] ) : '';

		// Oraganizer Deatails.
		$organizer_name  = isset( $postdata['organizer_name'] ) ? $postdata['organizer_name'] : '';
		$organizer_email = isset( $postdata['organizer_email'] ) ? $postdata['organizer_email'] : '';
		$organizer_phone = isset( $postdata['organizer_phone'] ) ? $postdata['organizer_phone'] : '';
		$organizer_url   = isset( $postdata['organizer_url'] ) ? esc_url( $postdata['organizer_url'] ) : '';

		// Event Source Link
		$ife_event_link   = isset( $postdata['ife_event_link'] ) ? esc_url( $postdata['ife_event_link'] ) : '';

		// Save Event Data.
		// Date & Time.
		update_post_meta( $post_id, 'event_start_date', $event_start_date );
		update_post_meta( $post_id, 'event_start_hour', $event_start_hour );
		update_post_meta( $post_id, 'event_start_minute', $event_start_minute );
		update_post_meta( $post_id, 'event_start_meridian', $event_start_meridian );
		update_post_meta( $post_id, 'event_end_date', $event_end_date );
		update_post_meta( $post_id, 'event_end_hour', $event_end_hour );
		update_post_meta( $post_id, 'event_end_minute', $event_end_minute );
		update_post_meta( $post_id, 'event_end_meridian', $event_end_meridian );
		update_post_meta( $post_id, 'start_ts', $start_ts );
		update_post_meta( $post_id, 'end_ts', $end_ts );

		// Venue.
		update_post_meta( $post_id, 'venue_name', $venue_name );
		update_post_meta( $post_id, 'venue_address', $venue_address );
		update_post_meta( $post_id, 'venue_city', $venue_city );
		update_post_meta( $post_id, 'venue_state', $venue_state );
		update_post_meta( $post_id, 'venue_country', $venue_country );
		update_post_meta( $post_id, 'venue_zipcode', $venue_zipcode );
		update_post_meta( $post_id, 'venue_lat', $venue_lat );
		update_post_meta( $post_id, 'venue_lon', $venue_lon );
		update_post_meta( $post_id, 'venue_url', $venue_url );

		// Organizer.
		update_post_meta( $post_id, 'organizer_name', $organizer_name );
		update_post_meta( $post_id, 'organizer_email', $organizer_email );
		update_post_meta( $post_id, 'organizer_phone', $organizer_phone );
		update_post_meta( $post_id, 'organizer_url', $organizer_url );
		
		// Event Source Link
		update_post_meta( $post_id, 'ife_event_link', $ife_event_link );
	}

	/**
	 * Add column to event listing in admin
	 *
	 * @param array $cols Columns.
	 */
	public function facebook_events_columns( $cols ) {
		$cols['fbevent_start_date'] = __( 'Event Date', 'import-facebook-events' );
		return $cols;
	}

	/**
	 * Set column data
	 *
	 * @param string $column Column.
	 * @param int    $post_id Post ID.
	 *
	 * @return void
	 */
	public function facebook_events_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case 'fbevent_start_date':
				$start_date = get_post_meta( $post_id, 'event_start_date', true );
				if ( ! empty( $start_date ) ) {
					$start_date = strtotime( $start_date );
					echo esc_attr( gmdate( 'F j, Y', $start_date ) );
				} else {
					echo '-';
				}

				break;
		}
	}

	/**
	 * Render event information above event content.
	 *
	 * @param string $content Content.
	 * @return string $content Altered Content.
	 */
	public function facebook_events_meta_before_content( $content ) {
		if ( is_singular( $this->event_posttype ) ) {
			$event_details = $this->facebook_events_get_event_meta( get_the_ID() );
			$is_before     = apply_filters( 'ife_events_information_before', true );
			if ( $is_before ) {
				$content = $event_details . $content;
			} else {
				$content = $content . $event_details;
			}
		}
		return $content;
	}

	/**
	 * Get meta information for event.
	 *
	 * @param int $event_id Event id.
	 * @return string $event_meta_details Event meta details.
	 */
	public function facebook_events_get_event_meta( $event_id = '' ) {

		ob_start();

			get_ife_template( 'ife-event-meta.php' );

		$event_meta_details = ob_get_contents();
		ob_end_clean();
		return $event_meta_details;
	}

	/**
	 * Render events lisiting.
	 *
	 * @param array $atts Attributes.
	 * @return string $wp_list_events Event list HTML.
	 */
	public function facebook_events_archive( $atts = array() ) {
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// [facebook_events layout="style2" col='2' posts_per_page='12' category="cat1,cat2" past_events="yes" order="desc" orderby="" start_date="" end_date="" ]
		$current_date = current_time( 'timestamp' );
		$ajaxpagi     = isset( $atts['ajaxpagi'] ) ? $atts['ajaxpagi'] : '';
		if ( $ajaxpagi != 'yes' ) {
			$paged        = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
			if ( is_front_page() ) {
				$paged = ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
			}
		}else{
			$paged  = isset( $atts['paged'] ) ? $atts['paged'] : 1;
		}
		$eve_args = array(
			'post_type'   => 'facebook_events',
			'post_status' => 'publish',
			'meta_key'    => 'start_ts', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key --Ignore.
			'paged'       => $paged,
		);

		// post per page.
		if ( isset( $atts['posts_per_page'] ) && ! empty( $atts['posts_per_page'] ) && is_numeric( $atts['posts_per_page'] ) ) {
			$eve_args['posts_per_page'] = $atts['posts_per_page'];
		}

		// Past Events.
		if ( ( isset( $atts['start_date'] ) && ! empty( $atts['start_date'] ) ) || ( isset( $atts['end_date'] ) && ! empty( $atts['end_date'] ) ) ) {
			$start_date_str = '';
			$end_date_str   = '';
			if ( isset( $atts['start_date'] ) && ! empty( $atts['start_date'] ) ) {
				try {
					$start_date_str = strtotime( sanitize_text_field( $atts['start_date'] ) );
				} catch ( Exception $e ) {
					$start_date_str = '';
				}
			}
			if ( isset( $atts['end_date'] ) && ! empty( $atts['end_date'] ) ) {
				try {
					$end_date_str = strtotime( sanitize_text_field( $atts['end_date'] ) );
				} catch ( Exception $e ) {
					$end_date_str = '';
				}
			}

			if ( ! empty( $start_date_str ) && ! empty( $end_date_str ) ) {
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => 'end_ts',
						'compare' => '>=',
						'value'   => $start_date_str,
					),
					array(
						'key'     => 'end_ts',
						'compare' => '<=',
						'value'   => $end_date_str,
					),
				);
			} elseif ( ! empty( $start_date_str ) ) {
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'end_ts',
						'compare' => '>=',
						'value'   => $start_date_str,
					),
				);
			} elseif ( ! empty( $end_date_str ) ) {
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => 'end_ts',
						'compare' => '>=',
						'value'   => strtotime( gmdate( 'Y-m-d' ) ),
					),
					array(
						'key'     => 'end_ts',
						'compare' => '<=',
						'value'   => $end_date_str,
					),
				);
			}
		} else {
			if( isset( $atts['past_events'] ) && $atts['past_events'] === true ){
				$atts['past_events'] = "yes";
			}
			if ( isset( $atts['past_events'] ) && 'yes' === $atts['past_events'] ) {
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'end_ts',
						'compare' => '<=',
						'value'   => $current_date,
					),
				);
			} else {
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'end_ts',
						'compare' => '>=',
						'value'   => $current_date,
					),
				);
			}
		}

		// Category.
		if ( isset( $atts['category'] ) && ! empty( $atts['category'] ) ) {
			$categories = explode( ',', $atts['category'] );
			$tax_field  = 'slug';
			if ( is_numeric( implode( '', $categories ) ) ) {
				$tax_field = 'term_id';
			}
			if ( ! empty( $categories ) ) {
				$eve_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $this->event_category,
						'field'    => $tax_field,
						'terms'    => $categories,
					),
				);
			}
		}

		// Order by.
		if ( isset( $atts['orderby'] ) && ! empty( $atts['orderby'] ) ) {
			if ( 'event_start_date' === $atts['orderby'] || 'event_end_date' === $atts['orderby'] ) {
				if ( 'event_end_date' === $atts['orderby'] ) {
					$eve_args['meta_key'] = 'end_ts'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key --Ignore.
				}
				$eve_args['orderby'] = 'meta_value';
			} else {
				$eve_args['orderby'] = sanitize_text_field( $atts['orderby'] );
			}
		} else {
			$eve_args['orderby'] = 'meta_value';
		}

		// Order.
		if ( isset( $atts['order'] ) && ! empty( $atts['order'] ) ) {
			if ( 'DESC' === strtoupper( $atts['order'] ) || 'ASC' === strtoupper( $atts['order'] ) ) {
				$eve_args['order'] = sanitize_text_field( $atts['order'] );
			}
		} else {
			if( isset( $atts['past_events'] ) && $atts['past_events'] === true ){
				$atts['past_events'] = "yes";
			}
			if ( isset( $atts['past_events'] ) && 'yes' === $atts['past_events'] && 'meta_value' === $eve_args['orderby'] ) {
				$eve_args['order'] = 'DESC';
			} else {
				$eve_args['order'] = 'ASC';
			}
		}

		$col       = 2;
		$css_class = 'col-ife-md-6';
		if ( isset( $atts['col'] ) && ! empty( $atts['col'] ) && is_numeric( $atts['col'] ) ) {
			$col = $atts['col'];
			switch ( $col ) {
				case '1':
					$css_class = 'col-ife-md-12';
					break;

				case '2':
					$css_class = 'col-ife-md-6';
					break;

				case '3':
					$css_class = 'col-ife-md-4';
					break;

				case '4':
					$css_class = 'col-ife-md-3';
					break;

				default:
					$css_class = 'col-ife-md-4';
					break;
			}
		}

		$facebook_events = new WP_Query( $eve_args );

		$wp_list_events = '';
		// Start the Loop.
		if ( is_front_page() ) {
			$curr_paged = $paged;
			global $paged;
			$temp_paged = $paged;
			$paged      = $curr_paged; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		$ife_options  = get_option( IFE_OPTIONS );
		$accent_color = isset( $ife_options['accent_color'] ) ? $ife_options['accent_color'] : '#039ED7';
		$direct_link  = isset( $ife_options['direct_link'] ) ? $ife_options['direct_link'] : 'no';
		$ife_ed_image = isset( $ife_options['ife_event_default_thumbnail'] ) ? $ife_options['ife_event_default_thumbnail'] : '';
		if ( ! ife_is_pro() ) {
			$direct_link = 'no';
		}
		ob_start();
		?>
		<div class="ife_archive row_grid" data-paged="<?php echo esc_attr( $paged ); ?>" data-shortcode='<?php echo wp_json_encode( $atts ); ?>'>
			<?php
			$template_args                 = array();
			$template_args['css_class']    = $css_class;
			$template_args['direct_link']  = $direct_link;
			$template_args['ife_ed_image'] = $ife_ed_image;
			if ( $facebook_events->have_posts() ) :
				while ( $facebook_events->have_posts() ) :
					$facebook_events->the_post();

					if( isset( $atts['layout'] ) && $atts['layout'] == 'style2' && ife_is_pro() ){
						get_ife_template( 'ife-archive-content2.php', $template_args );
					}elseif( isset( $atts['layout'] ) && $atts['layout'] == 'style3' ){
						get_ife_template( 'ife-archive-content3.php', $template_args );
					}elseif( isset( $atts['layout'] ) && $atts['layout'] == 'style4' ){
						get_ife_template( 'ife-archive-content4.php', $template_args );
					}else{
						get_ife_template( 'ife-archive-content.php', $template_args );
					}

				endwhile; // End of the loop.

				if ( isset( $atts['ajaxpagi'] ) && $atts['ajaxpagi'] == 'yes' ) {
					if ( $facebook_events->max_num_pages > 1 ) { ?>
							<div class="col-ife-md-12">
								<nav class="prev-next-posts">
									<div class="prev-posts-link alignright">
										<?php if( $paged < $facebook_events->max_num_pages ) : ?>
											<a href="#" class="ife-next-page" data-page="<?php echo $paged + 1; ?>"><?php esc_attr_e( 'Next Events &raquo;' ); ?></a>
										<?php endif; ?>
									</div>
									<div class="next-posts-link alignleft">
										<?php if( $paged > 1 ) : ?>
											<a href="#" class="ife-prev-page" data-page="<?php echo $paged - 1; ?>"><?php esc_attr_e( '&laquo; Previous Events' ); ?></a>
										<?php endif; ?>
									</div>
								</nav>
							</div>
							<?php
						}
					}else{
						if ( $facebook_events->max_num_pages > 1 ) : // custom pagination
						?>
							<div class="col-ife-md-12">
								<nav class="prev-next-posts">
									<div class="prev-posts-link alignright">
										<?php echo get_next_posts_link( 'Next Events &raquo;', $facebook_events->max_num_pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
									<div class="next-posts-link alignleft">
										<?php echo get_previous_posts_link( '&laquo; Previous Events' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								</nav>
							</div>
						<?php
						endif;
					}
				else :
					echo esc_attr( apply_filters( 'ife_no_events_found_message', __( 'No Events are found.', 'import-facebook-events' ) ) );
			endif;

				?>
		</div>
		<style type="text/css">
			.ife_event .event_date{
				background-color: <?php echo esc_attr( $accent_color ); ?>;
			}
			.ife_event .event_desc .event_title{
				color: <?php echo esc_attr( $accent_color ); ?>;
			}
		</style>
		<?php
		// Allow developers to do something here.
		do_action( 'ife_after_event_list', $facebook_events );
		$wp_list_events = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		if ( is_front_page() ) {
			global $paged;
			$paged = $temp_paged; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		return $wp_list_events;

	}
}

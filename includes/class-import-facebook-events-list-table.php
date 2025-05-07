<?php
/**
 *  List table for scheduled import.
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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class respoinsible for generate list table for scheduled import.
 */
class Import_Facebook_Events_History_List_Table extends WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $status, $page;
			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'import_history',     // singular name of the listed records.
					'plural'   => 'fb_import_histories',   // plural name of the listed records.
					'ajax'     => false,        // does this table support ajax?
				)
			);
	}

	/**
	 * Setup output for default column.
	 *
	 * @since    1.0.0
	 * @param array  $item Items.
	 * @param string $column_name  Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Setup output for title column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	public function column_title( $item ) {

		$ife_url_delete_args = array(
			'page'       => 'facebook_import',
			'tab'        => 'history',
			'ife_action' => 'ife_history_delete',
			'history_id' => absint( $item['ID'] ),
		);
		// Build row actions.
		$actions = array(
			'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this import history? Import history will be permanatly deleted.\')">%2$s</a>', esc_url( wp_nonce_url( add_query_arg( $ife_url_delete_args ), 'ife_delete_history_nonce' ) ), esc_html__( 'Delete', 'import-facebook-events' ) ),
		);

		// Return the title contents.
		return sprintf(
			'<strong>%1$s</strong><span>%3$s</span> %2$s',
			$item['title'],
			$this->row_actions( $actions ),
			__( 'Origin', 'import-facebook-events' ) . ': <b>' . ucfirst( get_post_meta( $item['ID'], 'import_origin', true ) ) . '</b>'
		);
	}

	/**
	 * Setup output for Stats column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	public function column_stats( $item ) {

		$created           = get_post_meta( $item['ID'], 'created', true );
		$updated           = get_post_meta( $item['ID'], 'updated', true );
		$skipped           = get_post_meta( $item['ID'], 'skipped', true );
		$skip_trash        = get_post_meta( $item['ID'], 'skip_trash', true );
		$nothing_to_import = get_post_meta( $item['ID'], 'nothing_to_import', true );

		$success_message = '<span style="color: silver"><strong>';
		if ( $created > 0 ) {
			// translators: %d is numbers of event Created.
			$success_message .= sprintf( esc_attr__( '%d Created', 'import-facebook-events' ), $created ) . '<br>';
		}
		if ( $updated > 0 ) {
			// translators: %d is numbers of event Updated.
			$success_message .= sprintf( esc_attr__( '%d Updated', 'import-facebook-events' ), $updated ) . '<br>';
		}
		if ( $skipped > 0 ) {
			// translators: %d is numbers of event skipped.
			$success_message .= sprintf( esc_attr__( '%d Skipped', 'import-facebook-events' ), $skipped ) . '<br>';
		}
		if ( $skip_trash > 0 ) {
			// translators: %d is numbers of event skipped Trashed .
			$success_message .= sprintf( esc_attr__( '%d Skipped (Already exists in Trash)', 'import-facebook-events' ), $skip_trash ) . '<br>';
		}
		if ( $nothing_to_import ) {
			$success_message .= esc_attr__( 'No events are imported.', 'import-facebook-events' ) . '<br>';
		}
		$success_message .= '</strong></span>';

		// Return the title contents.
		return $success_message;
	}

	/**
	 * Setup output for Action column.
	 *
	 * @param array $item Items.
	 * @return array
	 */
	public function column_action( $item ) {
		$url = add_query_arg(
			array(
				'action'    => 'ife_view_import_history',
				'history'   => $item['ID'],
				'TB_iframe' => 'true',
				'width'     => '800',
				'height'    => '500',
			),
			admin_url( 'admin.php' )
		);

		$imported_data = get_post_meta( $item['ID'], 'imported_data', true );
		if ( ! empty( $imported_data ) ) {
			return sprintf(
				'<a href="%1$s" title="%2$s" class="open-history-details-modal button button-primary thickbox">%3$s</a>',
				$url,
				$item['title'],
				esc_attr__( 'View Imported Events', 'import-facebook-events' )
			);
		} else {
			return '-';
		}
	}

	/**
	 * Return Checkbox Column.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  // Let's simply repurpose the table's singular label ("video").
			/*$2%s*/ $item['ID']                // The value of the checkbox should be the record's id.
		);
	}

	/**
	 * Add Clear History button
	 * 
	 * @param [string] $which
	 * @return void
	 */
	public function extra_tablenav( $which ) {

		if ( 'top' !== $which ) {
			return;
		}	
		$ife_url_all_delete_args = array(
			'page'       => isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : 'facebook_import', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'tab'        => isset( $_REQUEST['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) ) : 'history' , // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'ife_action' => 'ife_all_history_delete',
		);

		$delete_ids  = get_posts( array( 'numberposts' => 1,'fields' => 'ids', 'post_type'   => 'ife_import_history' ) );
		if( !empty( $delete_ids ) ){
			$wp_delete_nonce_url = esc_url( wp_nonce_url( add_query_arg( $ife_url_all_delete_args, admin_url( 'admin.php' ) ),'ife_delete_all_history_nonce' ) );
			$confirmation_message = esc_html__( "Warning!! Are you sure to delete all these import history? Import history will be permanatly deleted.", "import-facebook-events" );
			?>
			<a class="button apply" href="<?php echo esc_url( $wp_delete_nonce_url ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>')">
				<?php esc_html_e( 'Clear Import History', 'import-facebook-events' ); ?>
			</a>
			<?php
		}
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'title'           => esc_attr__( 'Import', 'import-facebook-events' ),
			'import_category' => esc_attr__( 'Import Category', 'import-facebook-events' ),
			'import_date'     => esc_attr__( 'Import Date', 'import-facebook-events' ),
			'stats'           => esc_attr__( 'Import Stats', 'import-facebook-events' ),
			'action'          => esc_attr__( 'Action', 'import-facebook-events' ),
		);
		return $columns;
	}

	/**
	 * Get Bulk Actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		return array(
			'delete' => esc_attr__( 'Delete', 'import-facebook-events' ),
		);

	}

	/**
	 * Prepare Meetup url data.
	 *
	 * @since    1.0.0
	 * @param string $origin Event Origin.
	 */
	public function prepare_items( $origin = '' ) {
		$per_page = 10;
		$columns  = $this->get_columns();
		$hidden   = array( 'ID' );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		if ( ! empty( $origin ) ) {
			$data = $this->get_import_history_data( $origin );
		} else {
			$data = $this->get_import_history_data();
		}

		if ( ! empty( $data ) ) {
			$total_items = ( $data['total_records'] ) ? (int) $data['total_records'] : 0;
			// Set data to items.
			$this->items = ( $data['import_data'] ) ? $data['import_data'] : array();

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,  // WE have to calculate the total number of items.
					'per_page'    => $per_page, // WE have to determine how many items to show on a page.
					'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
				)
			);
		}
	}

	/**
	 * Get Meetup url data.
	 *
	 * @since    1.0.0
	 * @param string $origin Event Origin.
	 */
	public function get_import_history_data( $origin = '' ) {
		global $ife_events;

		$scheduled_import_data = array(
			'total_records' => 0,
			'import_data'   => array(),
		);
		$per_page              = 10;
		$current_page          = $this->get_pagenum();

		$query_args = array(
			'post_type'      => 'ife_import_history',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
		);

		if ( ! empty( $origin ) ) {
			$query_args['meta_key']   = 'import_origin'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
			$query_args['meta_value'] = esc_attr( $origin ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
		}

		$importdata_query                       = new WP_Query( $query_args );
		$scheduled_import_data['total_records'] = ( $importdata_query->found_posts ) ? (int) $importdata_query->found_posts : 0;
		// The Loop.
		if ( $importdata_query->have_posts() ) {
			while ( $importdata_query->have_posts() ) {
				$importdata_query->the_post();

				$import_id     = get_the_ID();
				$import_data   = get_post_meta( $import_id, 'import_data', true );
				$import_origin = get_post_meta( $import_id, 'import_origin', true );
				$import_plugin = isset( $import_data['import_into'] ) ? $import_data['import_into'] : '';

				$term_names   = array();
				$import_terms = isset( $import_data['event_cats'] ) ? $import_data['event_cats'] : array();

				if ( $import_terms && ! empty( $import_terms ) ) {
					foreach ( $import_terms as $term ) {
						$get_term = '';
						if ( ! empty( $import_plugin ) ) {
							$get_term = get_term( $term, $ife_events->$import_plugin->get_taxonomy() );
						}

						if ( ! is_wp_error( $get_term ) && ! empty( $get_term ) ) {
							$term_names[] = $get_term->name;
						}
					}
				}

				$scheduled_import_data['import_data'][] = array(
					'ID'              => $import_id,
					'title'           => get_the_title(),
					'import_category' => implode( ', ', $term_names ),
					'import_date'     => get_the_date( 'F j Y, h:i A' ),
				);
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $scheduled_import_data;
	}
}

/**
 * Class for the shortcode list table.
 */
class IFE_Shortcode_List_Table extends WP_List_Table {

    public function prepare_items() {

        $columns 	= $this->get_columns();
        $hidden 	= $this->get_hidden_columns();
        $sortable 	= $this->get_sortable_columns();
        $data 		= $this->table_data();

        $perPage 		= 25;
        $currentPage 	= $this->get_pagenum();
        $totalItems 	= count( $data );

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice( $data, ( ( $currentPage-1 ) * $perPage ), $perPage );

        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'id'            => __( 'ID', 'import-facebook-events' ),
            'how_to_use'    => __( 'Title', 'import-facebook-events' ),
            'shortcode'     => __( 'Shortcode', 'import-facebook-events' ),
			'action'    	=> __( 'Action', 'import-facebook-events' ),
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        $data = array();

        $data[] = array(
                    'id'            => 1,
                    'how_to_use'    => 'Display All Events',
                    'shortcode'     => '<p class="ife_short_code">[facebook_events]</p>',
                    'action'     	=> '<button class="ife-btn-copy-shortcode button-primary"  data-value="[facebook_events]">Copy</button>',
                    );
		$data[] = array(
					'id'            => 2,
					'how_to_use'    => 'New Grid Layouts <span style="color:green;font-weight: 900;">( PRO )</span>',
					'shortcode'     => '<p class="ife_short_code">[facebook_events layout="style2"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary'  data-value='[facebook_events layout=\"style2\"]'>Copy</button>",
					);
		$data[] = array(
					'id'            => 3,
					'how_to_use'    => 'New Grid Layouts Style 3',
					'shortcode'     => '<p class="ife_short_code">[facebook_events layout="style3"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary'  data-value='[facebook_events layout=\"style3\"]'>Copy</button>",
					);
		$data[] = array(
					'id'            => 4,
					'how_to_use'    => 'New Grid Layouts Style 4',
					'shortcode'     => '<p class="ife_short_code">[facebook_events col="1" layout="style4"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary'  data-value='[facebook_events col=\"1\" layout=\"style4\"]'>Copy</button>",
					);
        $data[] = array(            
                    'id'            => 5,
                    'how_to_use'    => 'Display with column',
					'shortcode'     => '<p class="ife_short_code">[facebook_events col="2"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events col=\"2\"]' >Copy</button>",
                    );
        $data[] = array(
                    'id'            => 6,
                    'how_to_use'    => 'Limit for display events',
					'shortcode'     => '<p class="ife_short_code">[facebook_events posts_per_page="12"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events posts_per_page=\"12\"]' >Copy</button>",
		);
        $data[] = array(
                    'id'            => 7,
                    'how_to_use'    => 'Display Events based on order',
					'shortcode'     => '<p class="ife_short_code">[facebook_events order="asc"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events order=\"asc\"]' >Copy</button>",
                    );
        $data[] = array(
                    'id'            => 8,
                    'how_to_use'    => 'Display events based on category',
					'shortcode'     => '<p class="ife_short_code" >[facebook_events category="cat1"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events category=\"cat1\"]' >Copy</button>",
                    );
        $data[] = array(
                    'id'            => 9,
                    'how_to_use'    => 'Display Past events',
					'shortcode'     => '<p class="ife_short_code">[facebook_events past_events="yes"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events past_events=\"yes\"]' >Copy</button>",
                    );
        $data[] = array(
                    'id'            => 10,
                    'how_to_use'    => 'Display Events based on orderby',
					'shortcode'     => '<p class="ife_short_code">[facebook_events order="asc" orderby="post_title"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events order=\"asc\" orderby=\"post_title\"]' >Copy</button>",
                    );
        $data[] = array(
                    'id'            => 11,
                    'how_to_use'    => 'Full Short-code',
					'shortcode'     => '<p class="ife_short_code">[facebook_events  col="2" posts_per_page="12" category="cat1" past_events="yes" order="desc" orderby="post_title" start_date="YYYY-MM-DD" end_date="YYYY-MM-DD"]</p>',
					'action'     	=> "<button class='ife-btn-copy-shortcode button-primary' data-value='[facebook_events col=\"2\" posts_per_page=\"12\" category=\"cat1\" past_events=\"yes\" order=\"desc\" orderby=\"post_title\" start_date=\"YYYY-MM-DD\" end_date=\"YYYY-MM-DD\"]' >Copy</button>",
                    );       
        return $data;
    }
	
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'how_to_use':
            case 'shortcode':
			case 'action':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
        }
    }
}

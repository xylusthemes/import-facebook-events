<?php
/**
 * Import Facebook Events Live Feed Database Handler.
 *
 * @package Import_Facebook_Events\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IFEPRO_Feed_DB {

	/** @var IFEPRO_Feed_DB */
	private static $instance = null;

	/** @var string DB table name (with prefix) */
	private $table_images;

	/** @var string DB table name for logs (with prefix) */
	private $table_logs;

	/** @var string DB version option key */
	private $db_version_key = 'ifeprofeed_db_version';

	/** @var string Current schema version */
	private $db_version = '1.2';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		$this->table_images = $wpdb->prefix . 'ifeprofeed_images';
		$this->table_logs   = $wpdb->prefix . 'ifeprofeed_logs';
		add_action( 'delete_post', array( $this, 'delete_feed_logs' ) );
	}

	/**
	 * Create or update database tables.
	 */
	public function maybe_create_table() {
		$installed_version = get_option( $this->db_version_key, '0' );
		if ( version_compare( $installed_version, $this->db_version, '>=' ) ) {
			return;
		}

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_images} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id VARCHAR(100) NOT NULL,
			image_url TEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY event_id (event_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		$sql_logs = "CREATE TABLE {$this->table_logs} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			feed_id BIGINT UNSIGNED NOT NULL,
			action_type VARCHAR(50) NOT NULL,
			url TEXT,
			api_cursor TEXT,
			events_count INT DEFAULT 0,
			status VARCHAR(20) DEFAULT 'success',
			error_message TEXT,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY feed_id (feed_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		dbDelta( $sql_logs );

		update_option( $this->db_version_key, $this->db_version );
	}

	// -------------------------------------------------------
	// Image CRUD
	// -------------------------------------------------------

	/**
	 * Get a cached HQ image URL for an event.
	 *
	 * @param string $event_id Facebook event ID.
	 * @return string|false Image URL or false if not cached.
	 */
	public function get_image( $event_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$url = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT image_url FROM {$this->table_images} WHERE event_id = %s LIMIT 1",
				(string) $event_id
			)
		);
		return $url ?: false;
	}

	/**
	 * Save (upsert) a HQ image URL for an event.
	 *
	 * @param string $event_id  Facebook event ID.
	 * @param string $image_url Full image URL.
	 * @return bool True on success.
	 */
	public function save_image( $event_id, $image_url ) {
		global $wpdb;

		// REPLACE INTO = upsert (event_id has UNIQUE key)
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->replace(
			$this->table_images,
			array(
				'event_id'   => (string) $event_id,
				'image_url'  => $image_url,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Delete a single event image.
	 *
	 * @param string $event_id Facebook event ID.
	 */
	public function delete_image( $event_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->table_images,
			array( 'event_id' => (string) $event_id ),
			array( '%s' )
		);
	}

	/**
	 * Delete ALL cached images (hard cache clear).
	 *
	 * @return int Number of rows deleted.
	 */
	public function delete_all_images() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->query( "TRUNCATE TABLE {$this->table_images}" );
	}

	/**
	 * Get total count of cached images.
	 *
	 * @return int
	 */
	public function get_image_count() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_images}" );
	}

	// -------------------------------------------------------
	// Logging
	// -------------------------------------------------------

	/**
	 * Log an API action.
	 */
	public function log_action( $feed_id, $action_type, $url, $cursor = '', $events_count = 0, $status = 'success', $error_message = '' ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			$this->table_logs,
			array(
				'feed_id'       => (int) $feed_id,
				'action_type'   => $action_type,
				'url'           => $url,
				'api_cursor'    => (string) $cursor,
				'events_count'  => (int) $events_count,
				'status'        => $status,
				'error_message' => (string) $error_message,
				'created_at'    => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Delete logs for a deleted feed.
	 */
	public function delete_feed_logs( $post_id ) {
		if ( get_post_type( $post_id ) !== IFEPRO_FEED_CPT ) return;
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->table_logs,
			array( 'feed_id' => $post_id ),
			array( '%d' )
		);
	}

	/**
	 * Get logs (for admin UI).
	 */
	public function get_logs( $limit = 50, $offset = 0 ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$this->table_logs} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}
	
	public function get_logs_count() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_logs}" );
	}

	/**
	 * Schedule the weekly cleanup cron if not already scheduled.
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'ifeprofeed_weekly_image_cleanup' ) ) {
			wp_schedule_event( time(), 'weekly', 'ifeprofeed_weekly_image_cleanup' );
		}
	}

	/**
	 * Unschedule cleanup on deactivation.
	 */
	public static function unschedule_cleanup() {
		$timestamp = wp_next_scheduled( 'ifeprofeed_weekly_image_cleanup' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'ifeprofeed_weekly_image_cleanup' );
		}
	}

	/**
	 * Run the weekly cleanup — delete images older than 3 days, and logs older than 7 days.
	 */
	public function run_weekly_cleanup() {
		global $wpdb;
		$image_cutoff = gmdate( 'Y-m-d H:i:s', time() - ( 3 * DAY_IN_SECONDS ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$this->table_images} WHERE created_at < %s",
				$image_cutoff
			)
		);
		
		$log_cutoff = gmdate( 'Y-m-d H:i:s', time() - ( 7 * DAY_IN_SECONDS ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$this->table_logs} WHERE created_at < %s",
				$log_cutoff
			)
		);
	}

	/**
	 * Get the table name (for debugging / status display).
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_images;
	}
}

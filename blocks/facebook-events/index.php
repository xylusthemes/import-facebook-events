<?php
/**
 * Facebook Events Block Initializer
 *
 * @since   1.6
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Gutenberg Block
 *
 * @return void
 */
function ife_register_gutenberg_block() {
	global $ife_events;
	if ( function_exists( 'register_block_type' ) ) {
		// Register block editor script.
		$js_dir = IFE_PLUGIN_URL . 'assets/js/';
		wp_register_script(
			'ife-facebook-events-block',
			$js_dir . 'gutenberg.blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			IFE_VERSION
		);

		// Register block editor style.
		$css_dir = IFE_PLUGIN_URL . 'assets/css/';
		wp_register_style(
			'ife-facebook-events-block-style',
			$css_dir . 'import-facebook-events.css',
			array(),
			IFE_VERSION
		);

		// Register our block.
		register_block_type( 'ife-block/facebook-events', array(
			'attributes'      => array(
				'col'            => array(
					'type'    => 'number',
					'default' => 3,
				),
				'posts_per_page' => array(
					'type'    => 'number',
					'default' => 12,
				),
				'past_events'    => array(
					'type' => 'string',
				),
				'start_date'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'end_date'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'order'          => array(
					'type'    => 'string',
					'default' => 'ASC',
				),
				'orderby'        => array(
					'type'    => 'string',
					'default' => 'event_start_date',
				),

			),
			'editor_script'   => 'ife-facebook-events-block', // The script name we gave in the wp_register_script() call.
			'editor_style'    => 'ife-facebook-events-block-style', // The script name we gave in the wp_register_style() call.
			'render_callback' => array( $ife_events->cpt, 'facebook_events_archive' ),
		) );
	}
}

add_action( 'init', 'ife_register_gutenberg_block' );

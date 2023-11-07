<?php
/**
 * Class for Facebook User Authorization
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
 * Class for Facebook Account Authorize.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/includes
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_FB_Authorize {

	/**
	 * Facebook API version
	 *
	 * @var string
	 */
	private $api_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->api_version = 'v18.0';
		add_action( 'admin_post_ife_facebook_authorize_action', array( $this, 'ife_facebook_authorize_user' ) );
		add_action( 'admin_post_ife_facebook_authorize_callback', array( $this, 'ife_facebook_authorize_user_callback' ) );
	}

	/**
	 * Authorize facebook user to get access token.
	 *
	 * @return void
	 */
	public function ife_facebook_authorize_user() {
		if ( ! empty( $_POST ) && isset( $_POST['ife_facebook_authorize_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ife_facebook_authorize_nonce'] ) ), 'ife_facebook_authorize_action' ) ) { // input var okay.

			$ife_options       = get_option( IFE_OPTIONS, array() );
			$app_id            = isset( $ife_options['facebook_app_id'] ) ? $ife_options['facebook_app_id'] : '';
			$app_secret        = isset( $ife_options['facebook_app_secret'] ) ? $ife_options['facebook_app_secret'] : '';
			$redirect_url      = admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' );
			$param_url         = rawurlencode( $redirect_url );
			$ife_session_state = md5( uniqid( rand(), true ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_rand
			setcookie( 'ife_session_state', $ife_session_state, '0', '/' );

			if ( ! empty( $app_id ) && ! empty( $app_secret ) ) {

				$dialog_url = 'https://www.facebook.com/' . $this->api_version . '/dialog/oauth?client_id='
						. $app_id . '&redirect_uri=' . $param_url . '&state='
						. $ife_session_state . '&scope=pages_show_list,pages_manage_metadata,pages_read_engagement,pages_read_user_content,page_events';
				header( 'Location: ' . $dialog_url );

			} else {
				die( esc_attr__( 'Please insert Facebook App ID and Secret.', 'import-facebook-events' ) );
			}
		} else {
			die( esc_attr__( 'You have not access to doing this operations.', 'import-facebook-events' ) );
		}
	}

	/**
	 * Authorize facebook user on callback to get access token.
	 *
	 * @return void
	 */
	public function ife_facebook_authorize_user_callback() {
		global $ife_success_msg;
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_COOKIE['ife_session_state'] ) && isset( $_REQUEST['state'] ) && ( sanitize_text_field( wp_unslash( $_REQUEST['state'] ) ) === $_COOKIE['ife_session_state'] ) ) { // input var okay.
				// phpcs:ignore WordPress.Security.NonceVerification
				$code         = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; // input var okay.
				$ife_options  = get_option( IFE_OPTIONS, array() );
				$app_id       = isset( $ife_options['facebook_app_id'] ) ? $ife_options['facebook_app_id'] : '';
				$app_secret   = isset( $ife_options['facebook_app_secret'] ) ? $ife_options['facebook_app_secret'] : '';
				$redirect_url = admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' );
				$param_url    = rawurlencode( $redirect_url );

			if ( ! empty( $app_id ) && ! empty( $app_secret ) ) {

				$token_url = 'https://graph.facebook.com/' . $this->api_version . '/oauth/access_token?'
				. 'client_id=' . $app_id . '&redirect_uri=' . $param_url
				. '&client_secret=' . $app_secret . '&code=' . $code;

				$access_token           = '';
				$ife_user_token_options = array();
				$ife_fb_authorize_user  = array();
				$response               = wp_remote_get( $token_url );
				$body                   = wp_remote_retrieve_body( $response );
				$body_response          = json_decode( $body );
				if ( ! empty( $body ) && isset( $body_response->access_token ) ) {

					$access_token                               = $body_response->access_token;
					$ife_user_token_options['authorize_status'] = 1;
					$ife_user_token_options['access_token']     = sanitize_text_field( $access_token );
					$token_transient_key = 'ife_facebook_access_token';
					delete_transient( $token_transient_key );
					update_option( 'ife_user_token_options', $ife_user_token_options );

					$profile_call = wp_remote_get( 'https://graph.facebook.com/' . $this->api_version . "/me?fields=id,name,picture&access_token=$access_token" );
					$profile      = wp_remote_retrieve_body( $profile_call );
					$profile      = json_decode( $profile );
					if ( isset( $profile->id ) && isset( $profile->name ) ) {
						$ife_fb_authorize_user['ID']   = sanitize_text_field( $profile->id );
						$ife_fb_authorize_user['name'] = sanitize_text_field( $profile->name );
						if ( isset( $profile->picture->data->url ) ) {
							$ife_fb_authorize_user['avtar'] = esc_url_raw( $profile->picture->data->url );
						}
					}
					update_option( 'ife_fb_authorize_user', $ife_fb_authorize_user );

					$args          = array( 'timeout' => 15 );
					$accounts_call = wp_remote_get( 'https://graph.facebook.com/' . $this->api_version . "/me/accounts?access_token=$access_token&limit=100&offset=0", $args );
					$accounts      = wp_remote_retrieve_body( $accounts_call );
					$accounts      = json_decode( $accounts );
					$accounts      = isset( $accounts->data ) ? $accounts->data : array();
					if ( ! empty( $accounts ) ) {
						$pages = array();
						foreach ( $accounts as $account ) {
							$pages[ $account->id ] = array(
								'id'           => $account->id,
								'name'         => $account->name,
								'access_token' => $account->access_token,
							);
						}
						update_option( 'ife_fb_user_pages', $pages );
					}

					$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&authorize=1' );
					wp_safe_redirect( $redirect_url );
					exit();
				} else {
					$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&authorize=0' );
					wp_safe_redirect( $redirect_url );
					exit();
				}
			} else {
				$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&authorize=2' );
				wp_safe_redirect( $redirect_url );
				exit();
			}
		} else {
			die( esc_attr__( 'You have not access to doing this operations.', 'import-facebook-events-pro' ) );
		}
	}
}

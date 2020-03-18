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

	private $api_version = 'v6.0';
	private $api_url = 'https://graph.facebook.com/';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_post_ife_facebook_authorize_action', array( $this, 'ife_facebook_authorize_user' ) );
		add_action( 'admin_post_ife_facebook_authorize_callback', array( $this, 'ife_facebook_authorize_user_callback' ) );
		add_action( 'admin_post_ife_disconnect_user', array( $this, 'disconnect_user_handler' ) );
		add_action( 'admin_post_ife_disconnect_all_users', array( $this, 'disconnect_all_users_handler' ) );
	}

	/**
	 * Authorize facebook user to get access token.
	 *
	 * @return void
	 */
	public function ife_facebook_authorize_user() {
		if ( ! empty( $_GET ) && isset( $_GET['ife_facebook_authorize_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['ife_facebook_authorize_nonce'] ) ), 'ife_facebook_authorize_action' ) ) { // input var okay.

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
						. $ife_session_state . '&scope=groups_access_member_info,user_events,pages_show_list';
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

				$token_url = $this->api_url . $this->api_version . '/oauth/access_token?'
				. 'client_id=' . $app_id . '&redirect_uri=' . $param_url
				. '&client_secret=' . $app_secret . '&code=' . $code;

				$access_token           = '';
				$ife_fb_authorize_user  = array();
				$response               = wp_remote_get( $token_url );
				$body                   = wp_remote_retrieve_body( $response );
				$body_response          = json_decode( $body );
				if ( ! empty( $body ) && isset( $body_response->access_token ) ) {

					$fb_users = get_option( 'ife_fb_users', array() );
					$access_token = $body_response->access_token;
					$profile_call = wp_remote_get( $this->api_url . $this->api_version . "/me?fields=id,name,picture&access_token=$access_token" );
					$profile      = wp_remote_retrieve_body( $profile_call );
					$profile      = json_decode( $profile );
					if ( isset( $profile->id ) && isset( $profile->name ) ) {
						$fb_user = array(
							'ID' => sanitize_text_field( $profile->id ),
							'name' => sanitize_text_field( $profile->name ),
							'authorize_status' => 1,
							'access_token' => sanitize_text_field( $access_token ),
							'avatar' => isset( $profile->picture->data->url ) ? esc_url_raw( $profile->picture->data->url ) : ''
						);
						$fb_users[$profile->id] = $fb_user;
						update_option( 'ife_fb_users', $fb_users );
					}

					$args          = array( 'timeout' => 15 );
					$accounts_call = wp_remote_get( $this->api_url . $this->api_version . "/me/accounts?access_token=$access_token&limit=100&offset=0", $args );
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
						update_option( 'ife_fb_pages_'.$profile->id, $pages );
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
			die( esc_attr__( 'You have not access to doing this operations.', 'import-facebook-events' ) );
		}
	}

	/**
	 * Disconnect all users Action Handler
	 *
	 * @return void
	 */
	public function disconnect_all_users_handler() {
		if ( ! empty( $_GET ) && isset( $_GET['ife_disconnect_all_users_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['ife_disconnect_all_users_nonce'] ) ), 'ife_disconnect_all_users_action' ) ) { // input var okay.
			$fb_users = $this->get_fb_users();
			$all_deleted = true;
			if( !empty($fb_users)){
				foreach($fb_users as $fb_user){
					$deleted = $this->disconnect_user($fb_user['ID']);
					if(!$deleted){
						$all_deleted = false;
					}
				}
			}

			if($all_deleted){
				$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&disconnected=1' );
				wp_safe_redirect( $redirect_url );
				exit();
			}
			$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&disconnected=0' );
			wp_safe_redirect( $redirect_url );
			exit();
		} else {
			die( esc_attr__( 'You have not access to doing this operations.', 'import-facebook-events' ) );
		}
	}

	/**
	 * Disconnect User Action Handler
	 *
	 * @return void
	 */
	public function disconnect_user_handler() {
		if ( ! empty( $_GET ) && isset( $_GET['ife_disconnect_user_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['ife_disconnect_user_nonce'] ) ), 'ife_disconnect_user_action' ) ) { // input var okay.
			$user_id = isset( $_GET['fbuser_id'] ) ? sanitize_text_field( $_GET['fbuser_id'] ) : '0';
			$deleted = $this->disconnect_user($user_id);
			if($deleted){
				$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&disconnected=1' );
				wp_safe_redirect( $redirect_url );
				exit();
			}
			$redirect_url = admin_url( 'admin.php?page=facebook_import&tab=settings&disconnected=0' );
			wp_safe_redirect( $redirect_url );
			exit();
		} else {
			die( esc_attr__( 'You have not access to doing this operations.', 'import-facebook-events' ) );
		}
	}

	/**
	 * Disconnect facebook user from App
	 *
	 * @return boo
	 */
	private function disconnect_user($user_id) {
		$fb_user = $this->get_fbuser_by_id($user_id);
		$access_token = isset( $fb_user['access_token'] ) ? sanitize_text_field( $fb_user['access_token'] ) : '';
		$url = $this->api_url . $this->api_version.'/'.$user_id.'/permissions?access_token='.$access_token;
		$request_args = array(
			'method' => 'DELETE'
		);
		$response      = wp_remote_request( $url, $request_args );
		$body          = wp_remote_retrieve_body( $response );
		$body_response = json_decode( $body );
		if($body_response->success){
			$this->remove_fb_user($user_id);
			return true;
		}
		return false;
	}

	/**
	 * Get connected facebook users
	 *
	 * @return array
	 */
	public function get_fb_users() {
		$fb_users = get_option( 'ife_fb_users', array() );
		return $fb_users;
	}

	/**
	 * Get facebook user by id
	 *
	 * @param string $user_id
	 * @return array
	 */
	public function get_fbuser_by_id( $fbuser_id ){
		$fb_users = $this->get_fb_users();
		$fb_user = isset( $fb_users[$fbuser_id] ) ? $fb_users[$fbuser_id] : array();
		return $fb_user;
	}

	/**
	 * Remove connected facebook user from db.
	 *
	 * @param string $user_id
	 * @return void
	 */
	function remove_fb_user($user_id){
		$fb_users = $this->get_fb_users();
		unset($fb_users[$user_id]);
		delete_option( 'ife_fb_pages_' . $user_id );
		update_option( 'ife_fb_users', $fb_users );
	}
}

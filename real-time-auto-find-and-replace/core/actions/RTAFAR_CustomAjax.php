<?php namespace RealTimeAutoFindReplace\actions;

/**
 * Class: Custom ajax call
 *
 * @package Admin
 * @since 1.0.0
 * @author M.Tuhin <info@codesolz.net>
 */

if ( ! defined( 'CS_RTAFAR_VERSION' ) ) {
	die();
}


class RTAFAR_CustomAjax {

	function __construct() {
		add_action( 'wp_ajax_rtafar_ajax', array( $this, 'rtafar_ajax' ) );
		add_action( 'wp_ajax_nopriv_rtafar_ajax', array( $this, 'rtafar_ajax' ) );
	}


	/**
	 * custom ajax call
	 */
	public function rtafar_ajax() {

		if ( ! isset( $_REQUEST['cs_token'] ) || false === check_ajax_referer( SECURE_AUTH_SALT, 'cs_token', false ) ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid token', 'real-time-auto-find-and-replace' ),
					'text'   => __( 'Sorry! we are unable recognize your auth!', 'real-time-auto-find-and-replace' ),
				)
			);
		}

		if ( ! isset( $_REQUEST['data'] ) && isset( $_POST['method'] ) ) {
			$data = $_POST;
		} else {
			$data = $_REQUEST['data'];
		}

		if ( empty( $method = $data['method'] ) || strpos( $method, '@' ) === false ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid Request', 'real-time-auto-find-and-replace' ),
					'text'   => __( 'Method parameter missing / invalid!', 'real-time-auto-find-and-replace' ),
				)
			);
		}
		$method     = explode( '@', $method );
		$class_path = str_replace( '\\\\', '\\', '\\RealTimeAutoFindReplace\\' . $method[0] );
		if ( ! class_exists( $class_path ) ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid Library', 'real-time-auto-find-and-replace' ),
					'text'   => sprintf( __( 'Library Class "%s" not found! ', 'real-time-auto-find-and-replace' ), $class_path ),
				)
			);
		}

		if ( ! method_exists( $class_path, $method[1] ) ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid Method', 'real-time-auto-find-and-replace' ),
					'text'   => sprintf( __( 'Method "%1$s" not found in Class "%2$s"! ', 'real-time-auto-find-and-replace' ), $method[1], $class_path ),
				)
			);
		}

		echo ( new $class_path() )->{$method[1]}( $data );
		exit;
	}
}

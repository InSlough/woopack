<?php
/*
Plugin Name: woopack
Plugin URI: https://woopack.com
Description: woopack Themes and Plugins! Visit https://woopack.com
Author: woopack
License: Codecanyon Split Licence
Version: 1.7.0
Requires at least: 4.5
Tested up to: 5.9.9
WC requires at least: 3.5.0
WC tested up to: 5.7.9
Author URI: https://woopack.com
Text Domain: woopack
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'csl-settings/load.php' );
if ( wopcb() ) {	return false; }

$GLOBALS['csl'] = isset( $GLOBALS['csl'] ) && version_compare( $GLOBALS['csl'], '1.6.0') == 1 ? $GLOBALS['csl'] : '1.6.0';
update_option( 'wop_key_woopack', 'valid' );
if ( !class_exists( 'wo_for_WooCommerce' ) ) :

	final class wo_for_WooCommerce {

		public static $version = '1.7.0';

		protected static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			if ( is_admin() ) {
				register_activation_hook( __FILE__, array( $this, 'activate' ) );
			}

			if ( $this->check_woocommerce() === false ) {
				return false;
			}

			do_action( 'wop_loading' );

			$this->init_hooks();

			$this->includes();

			do_action( 'wop_loaded' );
		}

		private function init_hooks() {
			add_action( 'init', array( $this, 'textdomain' ), 0 );

			include_once( 'csl-settings/csl-get.php' );
			add_action( 'init', array( $this, 'load_csl' ), 100 );
		}

		private function check_woocommerce() {
			if ( class_exists( 'WooCommerce' ) ) {
				return true;
			}
			else {
				return false;
			}
		}

		public function activate() {
			if ( !class_exists('WooCommerce') ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );

				wp_die( esc_html__( 'This plugin requires WooCommerce. Download it from WooCommerce official website', 'woopack' ) . ' &rarr; https://woocommerce.com' );
				exit;
			}
		}

		public static function load_demo() {
			include_once( 'csl-settings/csl-settings.php' );
		}

		public function load_csl() {
			if ( $this->is_request( 'admin' ) ) {
				include_once( 'csl-settings/csl-settings.php' );
			}
		}

		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		public function includes() {

			$isAdmin = false;
			$page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woopack' ? true: false;

			if ( $page && is_admin() ) {
				$isAdmin = true;
			}

			$options = get_option( '_woopack', array() );

			include_once( 'w-pack/load-modules.php' );

			if ( $this->is_request( 'admin' ) ) {
				include_once( 'library/wop-settings.php' );
			}

		}

		public function textdomain() {
			$this->load_plugin_textdomain();
		}

		public function load_plugin_textdomain() {

			$domain = 'woopack';
			$dir = untrailingslashit( WP_LANG_DIR );
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			return $loaded;

		}

		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		public function plugin_basename() {
			return untrailingslashit( plugin_basename( __FILE__ ) );
		}

		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

		public function version() {
			return self::$version;
		}

	}

	function wop() {
		return wo_for_WooCommerce::instance();
	}

	Wo_for_WooCommerce::instance();

endif;

<?php
/*
Plugin Name: Improved Product Options for WooCommerce
Plugin URI: https://woopack.com
Description: woopack Themes and Plugins! Visit https://woopack.com
Author: woopack
License: Codecanyon Split Licence
Version: 5.3.0
Requires at least: 4.5
Tested up to: 5.9.9
WC requires at least: 3.5.0
WC tested up to: 5.7.9
Author URI: https://woopack.com
Text Domain: improved-variable-product-attributes
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !function_exists( 'wop' ) ) {
	require_once( 'includes/csl-settings/load.php' );

	if ( wopcb() ) {	return false; }
}

$GLOBALS['csl'] = isset( $GLOBALS['csl'] ) && version_compare( $GLOBALS['csl'], '1.6.0') == 1 ? $GLOBALS['csl'] : '1.6.0';

if ( !class_exists( 'wop_Improved_Options' ) ) :

	final class wop_Improved_Options {

		public static $version = '5.3.0';

		protected static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			do_action( 'wcmnivpa_loading' );

			$this->includes();

			if ( !function_exists( 'wop' ) ) {
				$this->single_plugin();
			}

			do_action( 'wcmnivpa_loaded' );
		}

		private function single_plugin() {
			if ( is_admin() ) {
				register_activation_hook( __FILE__, array( $this, 'activate' ) );
			}

			include_once( 'includes/csl-settings/csl-get.php' );
			add_action( 'init', array( $this, 'load_csl' ), 100 );

			// Texdomain only used if out of wop
			add_action( 'init', array( $this, 'textdomain' ), 0 );
		}

		public function activate() {
			if ( !class_exists( 'WooCommerce' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );

				wp_die( esc_html__( 'This plugin requires WooCommerce. Download it from WooCommerce official website', 'woopack' ) . ' &rarr; https://woocommerce.com' );
				exit;
			}
		}

		public function load_csl() {
			if ( $this->is_request( 'admin' ) ) {
				include_once( 'includes/csl-settings/csl-settings.php' );
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

			if ( $this->is_request( 'admin' ) ) {
				include_once( 'includes/ivpa-settings.php' );
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

		}

		public function frontend_includes() {
			include_once( 'includes/ivpa-frontend.php' );
		}

		public function textdomain() {

			$this->load_plugin_textdomain();

		}

		public function load_plugin_textdomain() {

			$domain = 'improved-variable-product-attributes';
			$dir = untrailingslashit( WP_LANG_DIR );
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			if ( $loaded = load_textdomain( $domain, $dir . '/plugins/' . $domain . '-' . $locale . '.mo' ) ) {
				return $loaded;
			}
			else {
				load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
			}

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

		public static function version_check( $version = '3.0.0' ) {
			if ( class_exists( 'WooCommerce' ) ) {
				global $woocommerce;
				if( version_compare( $woocommerce->version, $version, ">=" ) ) {
					return true;
				}
			}
			return false;
		}

		public function version() {
			return self::$version;
		}

		public static function esc_color( $color ) {
			if ( empty( $color ) || is_array( $color ) ) {
				return 'rgba(15,15,15,.125)';
			}

			if ( false === strpos( $color, 'rgba' ) ) {
				return sanitize_hex_color( $color );
			}

			$color = str_replace( ' ', '', $color );
			sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
			return 'rgba('.$red.','.$green.','.$blue.','.$alpha.')';
		}

	}

	function ImprovedOptions() {
		return wop_Improved_Options::instance();
	}

	wop_Improved_Options::instance();

endif;

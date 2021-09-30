<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'wo_for_WooCommerce_Settings' ) ) :

	final class wo_for_WooCommerce_Settings {

		protected static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			do_action( 'wop_settings_loading' );



			$this->includes();

			do_action( 'wop_settings_loaded' );
		}

		public function includes() {
			add_action( 'admin_menu', array( $this, 'load_settings_page' ), 9999999999 );

			$page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woopack' ? true: false;

			if ( $page ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'load_script' ), 0 );
				add_action( 'admin_footer', array( $this, 'add_templates' ), 9999999999 );

				add_filter( 'wop_settings', array( $this, 'get_settings' ), 9999999999 );
			}

			add_action( 'wp_ajax_wop_ajax_factory', array( $this, 'ajax_factory' ), 9999999999 );
		}

		function load_script() {
			wp_enqueue_style( 'wop-style', wop()->plugin_url() .'/library/css/woopack' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, '' );

			wp_register_script( 'wop-script', wop()->plugin_url() . '/library/js/woopack.js', array( 'jquery', 'wp-util' ), '', true, 0 );
			wp_enqueue_script( 'wop-script' );

			wp_localize_script( 'wop-script', 'wop', apply_filters( 'wop_settings', array() ) );
			wp_localize_script( 'wop-script', 'csl', apply_filters( 'wop_csl_settings', array( 'nonce' => wp_create_nonce( 'csl-nonce' ) ) ) );
		}

		function load_settings_page() {
			$page = 'woopack';

			if ( class_exists( 'wop_Whitelabel' ) ) {
				$whitelabel = get_option( '_wop_whitelabel' );

				if ( !empty( $whitelabel['menu'] ) ) {
					$page = $whitelabel['menu'];
				}
			}

			add_submenu_page( 'woocommerce', $page, $page, 'manage_woocommerce', 'woopack', array( $this, 'show_settings' ) );
		}

		function show_settings() {
?>
			<div id="woopack-page" class="<?php echo apply_filters( 'wop_dashboard_class', 'wop-dashboard' ); ?>">

				<div id="woopack"></div>
			</div>
<?php
		}

		function get_settings( $settings ) {
			$options = get_option( '_woopack', array() );

			$settings['ajax'] = esc_url( admin_url( 'admin-ajax.php' ) );
			$settings['key'] = SevenVX()->get_key();

			if ( !empty( $settings['plugins'] ) ) {
				foreach( $settings['plugins'] as $plugin => $data ) {
					$settings['plugins'][$plugin]['state'] = !isset( $options[$data['slug']] ) || $options[$data['slug']] == 'yes' ? 'yes' : 'no';
				}
			}

			return $settings;
		}

		function add_templates() {
		?>
			<script type="text/template" id="tmpl-wop-plugin">
				<# if ( data.plugin.state && data.plugin.state == 'no' ) { #>
					<div id="wop-{{ data.plugin.slug }}" class="wop-plugin disabled">
						<h2>{{ data.plugin.wop }}</h2>
						<span class="wop-button wop-disable" data-plugin="{{ data.plugin.slug }}"><?php esc_html_e( 'Activate', 'woopack' ); ?></span>

						<span class="wop-plugin-label">{{ data.plugin.name }} <span class="wop-plugin-version">{{ data.plugin.version }}</span></span>
					</div>
				<# } else { #>
					<div id="wop-{{ data.plugin.slug }}" class="wop-plugin">
						<h2>{{ data.plugin.wop }}</h2>
						<span class="wop-button-primary wop-configure" data-plugin="{{ data.plugin.slug }}"><?php esc_html_e( 'Dashboard', 'woopack' ); ?></span>
						<span class="wop-plugin-label">{{ data.plugin.name }} <span class="wop-plugin-version">{{ data.plugin.version }}</span><br/><a href="javascript:void(0)" class="wop-disable activate" data-plugin="{{ data.plugin.slug }}"><?php esc_html_e( 'Deactivate module', 'woopack' ); ?></a></span>
					</div>
				<# } #>
			</script>
		<?php
		}

		public function ajax_factory() {

			$opt = array(
				'success' => true
			);

			if ( !isset( $_POST['wop']['type'] ) ) {
				$this->ajax_die($opt);
			}

			if ( apply_filters( 'wop_can_you_save', false ) ) {
				$this->ajax_die($opt);
			}

			switch( $_POST['wop']['type'] ) {

				case 'get_csl' :
					wp_send_json( $this->_get_csl() );
					exit;
				break;

				case 'plugin_switch' :
					wp_send_json( $this->_plugin_switch() );
					exit;
				break;

				default :
					$this->ajax_die($opt);
				break;

			}

		}

		public function _plugin_switch() {
			$plugin = isset( $_POST['wop']['plugin'] ) ? $_POST['wop']['plugin'] : '';
			$state = isset( $_POST['wop']['state'] ) ? $_POST['wop']['state'] : '';

			if ( empty( $plugin ) || empty( $state ) ) {
				return false;
			}

			$options = get_option( '_woopack', array() );

			$options[$plugin] = $state;

			update_option( '_woopack', $options, true );

			return true;
		}

		public function _get_csl() {
			$plugin = isset( $_POST['wop']['plugin'] ) ? $_POST['wop']['plugin'] : '';

			if ( empty( $plugin ) ) {
				return array();
			}

			$plugin = str_replace( '-', '_', sanitize_title( $plugin ) );

			$settings = apply_filters( 'wop_csl_get_' . $plugin, array() );

			if ( !is_array( $settings ) ) {
				return array();
			}

			return $settings;
		}

		public function ajax_die($opt) {
			$opt['success'] = false;
			wp_send_json( $opt );
			exit;
		}

	}

	Wo_for_WooCommerce_Settings::instance();

endif;

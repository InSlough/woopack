<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_Improved_Badges_Settings {

		public static $presets;
		public static $isb_style;
		public static $isb_style_special;
		public static $isb_color;
		public static $isb_position;

		public static $plugin;

		public static function init() {

			self::$plugin = array(
				'name' => 'Improved Badges for WooCommerce',
				'wop' => 'Badges and Counters',
				'slug' => 'product-badges',
				'label' => 'improved_badges',
				'image' => ImprovedBadges()->plugin_url() . '/assets/images/improved-badges-for-woocommerce-elements.png',
				'path' => 'improved-sale-badges/improved-sale-badges',
				'version' => wop_Improved_Badges::$version,
			);

			include_once( 'isb-badges.php' );

			if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'improved_badges' ) {
				add_action( 'csl_plugins_settings', array( 'wop_Improved_Badges_Settings', 'get_settings' ), 50 );
			}

			if ( function_exists( 'wop' ) ) {
				add_filter( 'wop_settings', array( 'wop_Improved_Badges_Settings', 'wop' ), 9999999121 );
				add_filter( 'wop_csl_get_product_badges', array( 'wop_Improved_Badges_Settings', '_get_settings_wop' ) );
			}

			add_filter( 'csl_plugins', array( 'wop_Improved_Badges_Settings', 'add_plugin' ), 0 );

			add_action( 'admin_enqueue_scripts', __CLASS__ . '::isb_scripts', 9 );
			add_action( 'wp_ajax_isb_respond', __CLASS__ . '::isb_respond' );

			add_action( 'woocommerce_product_write_panel_tabs', __CLASS__ . '::isb_add_product_tab' );
			add_action( 'woocommerce_product_data_panels', __CLASS__ . '::isb_product_tab' );

			add_action( 'save_post', __CLASS__ . '::isb_product_save' );

		}

		public static function wop( $settings ) {
			$settings['plugins'][] = self::$plugin;

			return $settings;
		}

		public static function add_plugin( $plugins ) {
			$plugins[self::$plugin['label']] = array(
				'slug' => self::$plugin['label'],
				'name' => self::$plugin['wop']
			);

			return $plugins;
		}

		public static function _get_settings_wop() {
			$settings = self::get_settings( array() );
			return $settings[self::$plugin['label']];
		}

		public static function isb_scripts( $hook ) {

			$init = false;

			if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'improved_badges' ) {
				$init = true;
			}
			if ( isset($_GET['page']) && ($_GET['page'] == 'woopack' ) ) {
				$init = true;
			}

			if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
				$init = true;
			}

			if ( $init === true ) {
				wp_enqueue_style( 'isb-style', ImprovedBadges()->plugin_url() . '/assets/css/admin' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, wop_Improved_Badges::$version );
				wp_enqueue_style( 'isb-css-style', ImprovedBadges()->plugin_url() . '/assets/css/styles' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, wop_Improved_Badges::$version );

				wp_enqueue_script( 'isb-admin', ImprovedBadges()->plugin_url() . '/assets/js/admin.js', array( 'jquery' ), wop_Improved_Badges::$version, true );

				$curr_args = array(
					'ajax' => admin_url( 'admin-ajax.php' ),
				);

				wp_localize_script( 'isb-admin', 'isb', $curr_args );
			}

		}

		public static function _get_setting_preview() {
			return array(
				'name'    => esc_html__( 'Preview', 'woopack' ),
				'type'    => 'hidden',
				'desc'    => esc_html__( 'Current badge preview', 'woopack' ),
				'id'      => 'preview',
				'class'   => 'isb_preview'
			);
		}

		public static function _get_setting_name() {
			return array(
				'name' => esc_html__( 'Preset Name', 'woopack' ),
				'type' => 'text',
				'id' => 'name',
				'desc' => esc_html__( 'Enter preset name', 'woopack' ),
				'default' => '',
			);

		}
		public static function _get_setting_style() {
			return array(
				'name'    => esc_html__( 'Style', 'woopack' ),
				'type'    => 'select',
				'desc'    => esc_html__( 'Select badge style', 'woopack' ),
				'id'      => 'style',
				'default' => 'isb_style_shopkit',
				'options' => self::$isb_style,
			);
		}
		public static function _get_setting_color() {
			return array(
				'name'    => esc_html__( 'Color', 'woopack' ),
				'type'    => 'select',
				'desc'    => esc_html__( 'Select badge color', 'woopack' ),
				'id'      => 'color',
				'default'     => 'isb_sk_material',
				'options' => self::$isb_color,
			);
		}
		public static function _get_setting_position() {
			return array(
				'name'    => esc_html__( 'Position', 'woopack' ),
				'type'    => 'select',
				'desc'    => esc_html__( 'Select badge position', 'woopack' ),
				'id'      => 'position',
				'default'     => 'isb_left',
				'options' => self::$isb_position,
			);
		}
		public static function _get_setting_special() {
			return array(
				'name'    => esc_html__( 'Style', 'woopack' ),
				'type'    => 'select',
				'desc'    => esc_html__( 'Select badge style', 'woopack' ),
				'id'      => 'special',
				'default'     => '',
				'options' => array_merge( array( '' => esc_html__( 'None', 'woopack' ) ), self::$isb_style_special ),
			);

		}
		public static function _get_setting_special_text() {
			return array(
				'name'    => esc_html__( 'Special Text', 'woopack' ),
				'type'    => 'textarea',
				'desc'    => esc_html__( 'Enter badge text', 'woopack' ),
				'id'      => 'special_text',
				'default'     => 'Text',
			);
		}
		public static function _get_setting_image() {
			return array(
				'name'    => esc_html__( 'Image', 'woopack' ),
				'type'    => 'file',
				'desc'    => esc_html__( 'Set image', 'woopack' ),
				'id'      => 'image',
				'default' => '',
			);
		}

		public static function get_settings( $plugins ) {

			$plugins[self::$plugin['label']] = array(
				'slug' => self::$plugin['label'],
				'name' => esc_html( function_exists( 'wop' ) ? self::$plugin['wop'] : self::$plugin['name'] ),
				'desc' => esc_html( function_exists( 'wop' ) ? self::$plugin['name'] . ' v' . self::$plugin['version'] : esc_html__( 'Settings page for', 'woopack' ) . ' ' . self::$plugin['name'] ),
				'link' => esc_url( 'https://woopack.com/store/badges-and-counters/' ),
				'ref' => array(
					'name' => esc_html__( 'Visit woopack.com', 'woopack' ),
					'url' => 'https://woopack.com'
				),
				'doc' => array(
					'name' => esc_html__( 'Get help', 'woopack' ),
					'url' => 'https://help.woopack.com'
				),
				'sections' => array(
					'dashboard' => array(
						'name' => esc_html__( 'Dashboard', 'woopack' ),
						'desc' => esc_html__( 'Dashboard Overview', 'woopack' ),
					),
					'presets' => array(
						'name' => esc_html__( 'Badge Presets', 'woopack' ),
						'desc' => esc_html__( 'Badge Presets Options', 'woopack' ),
					),
					'manager' => array(
						'name' => esc_html__( 'Badge Manager', 'woopack' ),
						'desc' => esc_html__( 'Manager Options', 'woopack' ),
					),
					'timers' => array(
						'name' => esc_html__( 'Timer/Countdowns', 'woopack' ),
						'desc' => esc_html__( 'Timer/Countdowns Options', 'woopack' ),
					),
					'badges' => array(
						'name' => esc_html__( 'Default Badge', 'woopack' ),
						'desc' => esc_html__( 'Default Badges Options', 'woopack' ),
					),
					'installation' => array(
						'name' => esc_html__( 'Installation', 'woopack' ),
						'desc' => esc_html__( 'Installation Options', 'woopack' ),
					),
				),
				'settings' => array(

					'wcmn_dashboard' => array(
						'type' => 'html',
						'id' => 'wcmn_dashboard',
						'desc' => '
						<img src="' . ImprovedBadges()->plugin_url() . '/assets/images/improved-sale-badges-for-woocommerce-shop.png" class="csl-dashboard-image" />
						<h3><span class="dashicons dashicons-store"></span> woopack</h3>
						<p>' . esc_html__( 'Visit woopack.com store, demos and knowledge base.', 'woopack' ) . '</p>
						<p><a href="https://woopack.com" class="wop-button-primary x-color" target="_blank">woopack.com</a></p>

						<br /><hr />

						<h3><span class="dashicons dashicons-admin-tools"></span> ' . esc_html__( 'Help Center', 'woopack' ) . '</h3>
						<p>' . esc_html__( 'Need support? Visit the Help Center.', 'woopack' ) . '</p>
						<p><a href="https://help.woopack.com" class="wop-button-primary red" target="_blank">woopack.com HELP</a></p>

						<br /><hr />

						<h3><span class="dashicons dashicons-update"></span> ' . esc_html__( 'Automatic Updates', 'woopack' ) . '</h3>
						<p>' . esc_html__( 'Get automatic updates, by downloading and installing the Envato Market plugin.', 'woopack' ) . '</p>
						<p><a href="https://envato.com/market-plugin/" class="csl-button" target="_blank">Envato Market Plugin</a></p>

						<br />',
						'section' => 'dashboard',
					),

					'wcmn_utility' => array(
						'name' => esc_html__( 'Plugin Options', 'woopack' ),
						'type' => 'utility',
						'id' => 'wcmn_utility',
						'desc' => esc_html__( 'Quick export/import, backup and restore, or just reset your optons here', 'woopack' ),
						'section' => 'dashboard',
					),

					'default_badge' => array(
						'name' => esc_html__( 'Default badge', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'This option enables/disabled the default badge.', 'woopack' ),
						'id'   => 'default_badge',
						'default' => 'yes',
						'autoload' => false,
						'section' => 'badges',
						'class' => 'csl-refresh-active-tab'
					),

					'wc_settings_isb_preview' => array(
						'name'    => esc_html__( 'Preview', 'woopack' ),
						'type'    => 'hidden',
						'desc'    => esc_html__( 'Current badge preview', 'woopack' ),
						'id'      => 'wc_settings_isb_preview',
						'autoload' => false,
						'section' => 'badges',
						'class'   => 'isb_preview',
						'condition' => 'default_badge:yes',
					),

					'wc_settings_isb_style' => array(
						'name'    => esc_html__( 'Style', 'woopack' ),
						'type'    => 'select',
						'desc'    => esc_html__( 'Select sale badge style', 'woopack' ),
						'id'      => 'wc_settings_isb_style',
						'default' => 'isb_style_shopkit',
						'options' => self::$isb_style,
						'autoload' => false,
						'section' => 'badges',
						'condition' => 'default_badge:yes',
					),
					'wc_settings_isb_color' => array(
						'name'    => esc_html__( 'Color', 'woopack' ),
						'type'    => 'select',
						'desc'    => esc_html__( 'Select sale badge color', 'woopack' ),
						'id'      => 'wc_settings_isb_color',
						'default'     => 'isb_sk_material',
						'options' => self::$isb_color,
						'autoload' => false,
						'section' => 'badges',
						'condition' => 'default_badge:yes',
					),
					'wc_settings_isb_position' => array(
						'name'    => esc_html__( 'Position', 'woopack' ),
						'type'    => 'select',
						'desc'    => esc_html__( 'Select sale badge position', 'woopack' ),
						'id'      => 'wc_settings_isb_position',
						'default'     => 'isb_left',
						'options' => self::$isb_position,
						'autoload' => false,
						'section' => 'badges',
						'condition' => 'default_badge:yes',
					),
					'wc_settings_isb_special' => array(
						'name'    => esc_html__( 'Special', 'woopack' ),
						'type'    => 'select',
						'desc'    => esc_html__( 'Select special badge', 'woopack' ),
						'id'      => 'wc_settings_isb_special',
						'default'     => '',
						'options' => array_merge( array( '' => esc_html__( 'None', 'woopack' ) ), self::$isb_style_special ),
						'autoload' => false,
						'section' => 'badges',
						'condition' => 'default_badge:yes',
					),
					'wc_settings_isb_special_text' => array(
						'name'    => esc_html__( 'Special Text', 'woopack' ),
						'type'    => 'textarea',
						'desc'    => esc_html__( 'Enter special badge text', 'woopack' ),
						'id'      => 'wc_settings_isb_special_text',
						'default'     => 'Text',
						'autoload' => false,
						'section' => 'badges',
						'condition' => 'default_badge:yes',
					),

					'wcmn_isb_presets' => array(
						'name' => esc_html__( 'Presets Manager', 'woopack' ),
						'type' => 'list-select',
						'id'   => 'wcmn_isb_presets',
						'desc' => esc_html__( 'Add badge presets using the Presets Manager', 'woopack' ),
						'autoload' => false,
						'section' => 'presets',
						'title' => esc_html__( 'Preset Name', 'woopack' ),
						'options' => 'list',
						'ajax_options' => 'ajax:wp_options:_wcmn_isb_preset_%NAME%',
						'selects' => array(
							'sale' => esc_html__( 'Sale', 'woopack' ),
							'special' => esc_html__( 'Text', 'woopack' ),
							'image' => esc_html__( 'Image', 'woopack' ),
							'image-sale' => esc_html__( 'Image sale', 'woopack' ),
							'image-special' => esc_html__( 'Image text', 'woopack' ),
						),
						'settings' => array(
							'sale' => array(
								'preview' => self::_get_setting_preview(),
								'name' => self::_get_setting_name(),
								'style' => self::_get_setting_style(),
								'color' => self::_get_setting_color(),
								'position' => self::_get_setting_position(),
							),
							'special' => array(
								'preview' => self::_get_setting_preview(),
								'name' => self::_get_setting_name(),
								'special' => self::_get_setting_special(),
								'special_text' => self::_get_setting_special_text(),
								'color' => self::_get_setting_color(),
								'position' => self::_get_setting_position(),
							),
							'image' => array(
								'preview' => self::_get_setting_preview(),
								'name' => self::_get_setting_name(),
								'image' => self::_get_setting_image(),
								'position' => self::_get_setting_position(),
							),
							'image-sale' => array(
								'preview' => self::_get_setting_preview(),
								'name' => self::_get_setting_name(),
								'image' => self::_get_setting_image(),
								'position' => self::_get_setting_position(),
							),
							'image-special' => array(
								'preview' => self::_get_setting_preview(),
								'name' => self::_get_setting_name(),
								'image' => self::_get_setting_image(),
								'position' => self::_get_setting_position(),
								'special_text' => self::_get_setting_special_text(),
							),
						),
					),

					'wcmn_isb_overrides' => array(
						'name' => esc_html__( 'Badge Overrides', 'woopack' ),
						'type' => 'hidden',
						'id'   => 'wcmn_isb_overrides',
						'desc' => esc_html__( 'Set badge overrides', 'woopack' ),
						'autoload' => false,
						'section' => 'hidden',
					),

					'_wcmn_sale_badge' => array(
						'name' => esc_html__( 'Sale Badge', 'woopack' ),
						'type' => 'multiselect',
						'id'   => '_wcmn_sale_badge',
						'desc' => esc_html__( 'Set sale badge', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
						'options' => 'read:wcmn_isb_presets',
						'class' => 'csl-selectize',
					),

					'_wcmn_outofstock_badge' => array(
						'name' => esc_html__( 'Out of stock Badge', 'woopack' ),
						'type' => 'multiselect',
						'id'   => '_wcmn_outofstock_badge',
						'desc' => esc_html__( 'Set out of stock badge', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
						'options' => 'read:wcmn_isb_presets',
						'class' => 'csl-selectize',
					),

					'_wcmn_expire_in' => array(
						'name' => esc_html__( 'New Badge Period', 'woopack' ),
						'type' => 'number',
						'id'   => '_wcmn_expire_in',
						'desc' => esc_html__( 'Set new product expire in period (days)', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
					),

					'_wcmn_expire_in_preset' => array(
						'name' => esc_html__( 'New Badge', 'woopack' ),
						'type' => 'multiselect',
						'id'   => '_wcmn_expire_in_preset',
						'desc' => esc_html__( 'Set new product badge', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
						'options' => 'read:wcmn_isb_presets',
						'class' => 'csl-selectize',
					),

					'_wcmn_featured_badge' => array(
						'name' => esc_html__( 'Featured Badge', 'woopack' ),
						'type' => 'multiselect',
						'id'   => '_wcmn_featured_badge',
						'desc' => esc_html__( 'Set featured badge', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
						'options' => 'read:wcmn_isb_presets',
						'class' => 'csl-selectize',
					),

					'_wcmn_tags' => array(
						'name' => esc_html__( 'Tag Badges', 'woopack' ),
						'type' => 'list',
						'id'   => '_wcmn_tags',
						'desc' => esc_html__( 'Add tag badges', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
						'title' => esc_html__( 'Name', 'woopack' ),
						'options' => 'list',
						'default' => array(),
						'settings' => array(
							'name' => array(
								'name' => esc_html__( 'Name', 'woopack' ),
								'type' => 'text',
								'id' => 'name',
								'desc' => esc_html__( 'Enter override name', 'woopack' ),
								'default' => '',
							),
							'term' => array(
								'name' => esc_html__( 'Term', 'woopack' ),
								'type' => 'select',
								'id'   => 'term',
								'desc' => esc_html__( 'Set override term', 'woopack' ),
								'options' => 'ajax:taxonomy:product_tag:has_none',
								'default' => '',
								'class' => 'csl-selectize',
							),
							'preset' => array(
								'name' => esc_html__( 'Preset', 'woopack' ),
								'type' => 'multiselect',
								'id'   => 'preset',
								'desc' => esc_html__( 'Set override preset', 'woopack' ),
								'options' => 'read:wcmn_isb_presets',
								'class' => 'csl-selectize',
							),
						),
					),

					'_wcmn_categories' => array(
						'name' => esc_html__( 'Category Badges', 'woopack' ),
						'type' => 'list',
						'id'   => '_wcmn_categories',
						'desc' => esc_html__( 'Add category badges', 'woopack' ),
						'autoload' => false,
						'section' => 'manager',
						'title' => esc_html__( 'Name', 'woopack' ),
						'options' => 'list',
						'default' => array(),
						'settings' => array(
							'name' => array(
								'name' => esc_html__( 'Name', 'woopack' ),
								'type' => 'text',
								'id' => 'name',
								'desc' => esc_html__( 'Enter override name', 'woopack' ),
								'default' => '',
							),
							'term' => array(
								'name' => esc_html__( 'Term', 'woopack' ),
								'type' => 'select',
								'id'   => 'term',
								'desc' => esc_html__( 'Set override term', 'woopack' ),
								'section' => 'manager',
								'options' => 'ajax:taxonomy:product_cat:has_none',
								'default' => '',
								'class' => 'csl-selectize',
							),
							'preset' => array(
								'name' => esc_html__( 'Preset', 'woopack' ),
								'type' => 'multiselect',
								'id'   => 'preset',
								'desc' => esc_html__( 'Set override preset', 'woopack' ),
								'section' => 'manager',
								'options' => 'read:wcmn_isb_presets',
								'class' => 'csl-selectize',
							),
						),
					),

					'wc_settings_isb_template_overrides' => array(
						'name' => esc_html__( 'Use Tempalte Overrides', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'This is the default installation when checked, sale-flash.php template will be replaced with the plugin badge.', 'woopack' ),
						'id'   => 'wc_settings_isb_template_overrides',
						'default' => 'yes',
						'autoload' => true,
						'section' => 'installation'
					),
					'wc_settings_isb_archive_action' => array(
						'name' => esc_html__( 'Shop Init Action', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Use custom initialization action for Shop/Product Archive Pages. Use actions initiated in content-product.php template. Please enter action name in following format action_name:priority', 'woopack' ) . ' ( default: woocommerce_before_shop_loop_item:10 )',
						'id'   => 'wc_settings_isb_archive_action',
						'default' => '',
						'autoload' => true,
						'section' => 'installation'
					),
					'wc_settings_isb_single_action' => array(
						'name' => esc_html__( 'Single Product Init Action', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Use custom initialization action for Single Product Pages. Use actions initiated in content-single-product.php template. Please enter action name in following format action_name:priority', 'woopack' ) . ' ( default: woocommerce_before_single_product_summary:15 )',
						'id'   => 'wc_settings_isb_single_action',
						'default' => '',
						'autoload' => true,
						'section' => 'installation'
					),
					'wc_settings_isb_force_scripts' => array(
						'name' => esc_html__( 'Plugin Scripts', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Check this option to enable plugin scripts in all pages. This option fixes issues in Quick Views.', 'woopack' ),
						'id'   => 'wc_settings_isb_force_scripts',
						'default' => 'no',
						'autoload' => true,
						'section' => 'installation'
					),

					'wc_settings_isb_timer' => array(
						'name' => esc_html__( 'Disable Timers', 'woopack' ),
						'type' => 'multiselect',
						'desc' => esc_html__( 'Select sale timers to disable.', 'woopack' ),
						'id'   => 'wc_settings_isb_timer',
						'options' => array(
							'start' => esc_html__( 'Starting Sale', 'woopack' ),
							'end' => esc_html__( 'Ending Sale', 'woopack' )
						),
						'default' => array(),
						'autoload' => false,
						'section' => 'timers',
						'class' => 'csl-selectize'
					),

					'wc_settings_isb_timer_adjust' => array(
						'name' => esc_html__( 'Adjust Timer', 'woopack' ),
						'type' => 'number',
						'desc' => esc_html__( 'Adjust sale timer countdown clock. Option is set in minutes.', 'woopack' ),
						'id'   => 'wc_settings_isb_timer_adjust',
						'default' => '',
						'autoload' => false,
						'section' => 'timers'
					),

				)
			);

			return SevenVX()->_do_options( $plugins, self::$plugin['label'] );
		}

		public static function _get_badge_by_type( $type, $set ) {
			switch ( $type ) {
				case 'sale' :
					return ImprovedBadges()->plugin_path() . '/includes/styles/' . $set['style'] . '.php';
				break;

				case 'special' :
					return ImprovedBadges()->plugin_path() . '/includes/specials/' . $set['special'] . '.php';
				break;

				case 'image' :
					$set = array_intersect_key( $set, array_flip( array( 'image', 'position' ) ) );
					return ImprovedBadges()->plugin_path() . '/includes/images/isb_image.php';
				break;

				case 'image-sale' :
					$set = array_intersect_key( $set, array_flip( array( 'image', 'position' ) ) );
					return ImprovedBadges()->plugin_path() . '/includes/images/isb_image_sale.php';
				break;

				case 'image-special' :
					$set = array_intersect_key( $set, array_flip( array( 'image', 'position', 'special-text' ) ) );
					return ImprovedBadges()->plugin_path() . '/includes/images/isb_image_special.php';
				break;

				default :
				break;
			}
		}

		public static function call_badge() {

			if ( isset( $_POST['data']['isb_preset'] ) ) {
				$preset = self::get_preset( $_POST['data']['isb_preset'] );
				if ( !empty( $preset ) ) {
					$isb_set = array_merge(
						array( 'type' => 'simple' ),
						$preset[0]
					);
				}
			}

			if ( !isset( $isb_set ) ) {
				$isb_set = array(
					'family' => isset( $_POST['data']['isb_type'] ) && $_POST['data']['isb_type'] !== '' ? $_POST['data']['isb_type'] : null,
					'style' => isset( $_POST['data']['isb_style'] ) && $_POST['data']['isb_style'] !== '' ? $_POST['data']['isb_style'] : '',
					'color' => isset( $_POST['data']['isb_color'] ) && $_POST['data']['isb_color'] !== '' ? $_POST['data']['isb_color'] : '',
					'position' => isset( $_POST['data']['isb_position'] ) && $_POST['data']['isb_position'] !== '' ? $_POST['data']['isb_position'] : SevenVXGet()->get_option( 'wc_settings_isb_position', 'improved_badges', 'isb_left' ),
					'special' => isset( $_POST['data']['isb_special'] ) ? $_POST['data']['isb_special'] : SevenVXGet()->get_option( 'wc_settings_isb_special', 'improved_badges', '' ),
					'special_text' => isset( $_POST['data']['isb_special_text'] ) ? $_POST['data']['isb_special_text'] : SevenVXGet()->get_option( 'wc_settings_isb_special_text', 'improved_badges', '' ),
					'image' => isset( $_POST['data']['isb_image'] ) ? $_POST['data']['isb_image'] : '',
					'type' => 'simple',
				);
			}

			$isb_price['id'] = 1;
			$isb_price['type'] = 'simple';
			$isb_price['regular'] = 32;
			$isb_price['sale'] = 27;
			$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
			$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );
			$isb_price['time'] = '2:04:50';
			$isb_price['time_mode'] = 'end';

			if ( is_array( $isb_set ) ) {
				$isb_class = ( isset( $isb_set['special'] ) && $isb_set['special'] !== '' ? $isb_set['special'] : $isb_set['style'] ) . ' ' . $isb_set['color'] . ' ' . $isb_set['position'];
			}
			else {
				$isb_class = 'isb_style_shopkit isb_sk_material isb_left';
			}

			$isb_curr_set = $isb_set;

			if ( isset( $isb_set['family'] ) && $isb_set['family'] !== '' ) {
				$include = self::_get_badge_by_type( $isb_set['family'], $isb_set );
			}
			else {
				if ( isset( $isb_set['special'] ) && $isb_set['special'] !== '' ) {
					$include = ImprovedBadges()->plugin_path() . '/includes/specials/' . $isb_set['special'] . '.php';
				}
				else {
					$include = ImprovedBadges()->plugin_path() . '/includes/styles/' . $isb_set['style'] . '.php';
				}
			}

			ob_start();

			if ( file_exists( $include ) ) {
				include( $include );
			}

			$html = ob_get_clean();

			die($html);
			exit;

		}

		public static function isb_respond() {
			if ( !isset( $_POST['data'] ) ) {
				die();
				exit;
			}

			self::call_badge();

		}

		public static function isb_add_product_tab() {
			echo ' <li class="isb_tab"><a href="#isb_tab"><span>'. esc_html__('Sale Badges', 'woopack' ) .'</span></a></li>';
		}

		public static function isb_product_tab() {

			global $post, $isb_set;

			$curr_badge = get_post_meta( $post->ID, '_isb_settings' );

			$isb_set['preset'] = ( isset( $_POST['isb_preset'] ) ? $_POST['isb_preset'] : '' );
			$isb_set['style'] = ( isset( $_POST['isb_style'] ) ? $_POST['isb_style'] : '' );
			$isb_set['color'] = ( isset( $_POST['isb_color'] ) ? $_POST['isb_color'] : '');
			$isb_set['position'] = ( isset( $_POST['isb_position'] ) ? $_POST['isb_position'] : '' );
			$isb_set['special'] = ( isset( $_POST['isb_special'] ) ? $_POST['isb_special'] : '' );
			$isb_set['special_text'] = ( isset( $_POST['isb_special_text'] ) ? $_POST['isb_special_text'] : '' );

			$check_settings = array(
				'preset' => $isb_set['preset'],
				'style' => $isb_set['style'],
				'color' => $isb_set['color'],
				'position' => $isb_set['position'],
				'special' => $isb_set['special'],
				'special_text' => $isb_set['special_text']
			);

			if ( is_array( $curr_badge ) && isset( $curr_badge[0] ) ) {
				$curr_badge = $curr_badge[0];
				$isb_set = $curr_badge;
				foreach ( $check_settings as $k => $v ) {
					$curr_badge[$k] = ( isset( $curr_badge[$k] ) && $curr_badge[$k] !== '' ? $curr_badge[$k] : $v );
				}
			}
			else {
				$curr_badge = $check_settings;
			}

			$isb_curr_set = $curr_badge;

			if ( isset( $curr_badge['preset'] ) && $curr_badge['preset'] !== '' ) {
				$preset = self::get_preset( $curr_badge['preset'] );
				if ( !empty( $preset ) ) {
					$isb_curr_set = $preset[0];
				}
			}

		?>
			<div id="isb_tab" class="panel woocommerce_options_panel">

				<div class="options_group grouping basic">
					<span class="wc_settings_isb_title"><?php esc_html_e('Badge Settings', 'woopack' ); ?></span>
					<div id="isb_preview">
					<?php

						$isb_price['id'] = 1;
						$isb_price['type'] = 'simple';
						$isb_price['regular'] = 32;
						$isb_price['sale'] = 27;
						$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
						$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );
						$isb_price['time'] = '2:04:50';
						$isb_price['time_mode'] = 'end';

						if ( is_array($isb_curr_set) ) {
							$isb_class = ( $isb_curr_set['special'] !== '' ? $isb_curr_set['special'] : $isb_curr_set['style'] ) . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
						}
						else {
							$isb_class = 'isb_style_shopkit isb_sk_material isb_left';
						}

						if ( $isb_curr_set['special'] !== '' ) {
							$include = ImprovedBadges()->plugin_path() . '/includes/specials/' . $isb_curr_set['special'] . '.php';
						}
						else {
							$include = ImprovedBadges()->plugin_path() . '/includes/styles/' . $isb_curr_set['style'] . '.php';
						}

						if ( file_exists ( $include ) ) {
							include( $include );
						}

					?>
					</div>
					<p class="form-field isb_preset">
						<label for="wc_settings_isb_preset"><?php esc_html_e('Badge Preset', 'woopack' ); ?></label>
						<?php $presets = SevenVXGet()->get_option( 'wcmn_isb_presets', 'improved_badges', array() ); ?>
						<select id="wc_settings_isb_preset" name="isb_preset_single" class="option select short">
							<option value=""<?php echo ( isset( $isb_set['preset'] ) && $isb_set['preset'] == '' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'None', 'woopack' ); ?></option>
							<?php
								if ( !empty( $presets ) ) {
									foreach ( $presets as $k2 => $v1 ) {
								?>
										<option value="<?php echo esc_attr( $k2 ); ?>"<?php echo ( isset( $isb_set['preset'] ) && $isb_set['preset'] == $k2 ? ' selected="selected"' : '' ); ?>><?php echo esc_html( $v1 ); ?></option>
								<?php
									}
								}
							?>
						</select>
					</p>
					<p class="form-field isb_style isb_no_preset">
						<label for="wc_settings_isb_style"><?php esc_html_e('Badge Style', 'woopack' ); ?></label>
						<select id="wc_settings_isb_style" name="isb_style_single" class="option select short">
							<option value=""<?php echo ( isset($isb_set['style'] ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e('None', 'woopack' ); ?></option>
					<?php
						foreach ( self::$isb_style as $k => $v ) {
							printf('<option value="%1$s"%3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), ( $isb_set['style'] == $k ? ' selected="selected"' : '' ) );
						}
					?>
						</select>
					</p>
					<p class="form-field isb_color isb_no_preset">
						<label for="wc_settings_isb_color"><?php esc_html_e('Badge Color', 'woopack' ); ?></label>
						<select id="wc_settings_isb_color" name="isb_color_single" class="option select short">
							<option value=""<?php echo ( isset($isb_set['color']) ? ' selected="selected"' : '' ); ?>><?php esc_html_e('None', 'woopack' ); ?></option>
					<?php
						foreach ( self::$isb_color as $k => $v ) {
							printf('<option value="%1$s"%3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), ( $isb_set['color'] == $k ? ' selected="selected"' : '' ) );
						}
					?>
						</select>
					</p>
					<p class="form-field isb_position isb_no_preset">
						<label for="wc_settings_isb_position"><?php esc_html_e('Badge Position', 'woopack' ); ?></label>
						<select id="wc_settings_isb_position" name="isb_position_single" class="option select short">
							<option value=""<?php echo ( isset($isb_set['position']) ? ' selected="selected"' : '' ); ?>><?php esc_html_e('None', 'woopack' ); ?></option>
					<?php
						foreach ( self::$isb_position as $k => $v ) {
							printf('<option value="%1$s"%3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), ( $isb_set['position'] == $k ? ' selected="selected"' : '' ) );
						}
					?>
						</select>
					</p>
					<p class="form-field isb_special_badge isb_no_preset">
						<label for="wc_settings_isb_special"><?php esc_html_e('Special Badge', 'woopack' ); ?></label>
						<select id="wc_settings_isb_special" name="isb_style_special" class="option select short">
							<option value=""<?php echo ( isset($isb_set['special']) ? ' selected="selected"' : '' ); ?>><?php esc_html_e('None', 'woopack' ); ?></option>
					<?php
						foreach ( self::$isb_style_special as $k => $v ) {
							printf('<option value="%1$s"%3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), ( isset($isb_set['special']) && $isb_set['special'] == $k ? ' selected="selected"' : '' ) );
						}
					?>
						</select>
					</p>
					<p class="form-field isb_special_text isb_no_preset">
						<label for="wc_settings_isb_special_text"><?php esc_html_e('Special Badge Text', 'woopack' ); ?></label>
						<textarea id="wc_settings_isb_special_text" name="isb_style_special_text" class="option short"><?php echo ( isset( $isb_set['special_text'] ) ? stripslashes( $isb_set['special_text'] ) : '' ); ?></textarea>
					</p>
				</div>

			</div>
		<?php
		}

		public static function isb_product_save( $curr_id ) {
			$curr = array();

			if ( isset( $_POST['product-type'] ) ) {
				$curr = array(
					'preset' => ( isset($_POST['isb_preset_single']) ? $_POST['isb_preset_single'] : '' ),
					'style' => ( isset($_POST['isb_style_single']) ? $_POST['isb_style_single'] : '' ),
					'color' => ( isset($_POST['isb_color_single']) ? $_POST['isb_color_single'] : '' ),
					'position' => ( isset($_POST['isb_position_single']) ? $_POST['isb_position_single'] : '' ),
					'special' => ( isset($_POST['isb_style_special']) ? $_POST['isb_style_special'] : '' ),
					'special_text' => ( isset($_POST['isb_style_special_text']) ? $_POST['isb_style_special_text'] : '' )
				);
				update_post_meta( $curr_id, '_isb_settings', $curr );
			}
		}

		public static function get_preset( $preset ) {

			if ( $preset == '' ) {
				return array();
			}

			$process = get_option( '_wcmn_isb_preset_' . $preset, array() );
			if ( isset( $process['name'] ) ) {
				return array( 0 => $process );
			}
			else {
				return array();
			}

		}

	}

	wop_Improved_Badges_Settings::init();


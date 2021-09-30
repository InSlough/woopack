<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_LiveSearch_Settings {

		public static $plugin;

		public static function init() {

			self::$plugin = array(
				'name' => 'Live Search for WooCommerce',
				'wop' => 'Live Search',
				'slug' => 'live-search-wop',
				'label' => 'live_search_wop',
				'image' => wop_LiveSearch()->plugin_url() . '/assets/images/live-search-woopack.png',
				'path' => 'live-search-wop/live-search-wop',
				'version' => wop_LiveSearch::$version,
			);

			if ( isset( $_GET['page'], $_GET['tab'] ) && ( $_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'live_search_wop' ) {
				add_filter( 'csl_plugins_settings', array( 'wop_LiveSearch_Settings', 'get_settings' ), 50 );
			}

			if ( function_exists( 'wop' ) ) {
				add_filter( 'wop_settings', array( 'wop_LiveSearch_Settings', 'wop' ), 9999999211 );
				add_filter( 'wop_csl_get_live_search_wop', array( 'wop_LiveSearch_Settings', '_get_settings_wop' ) );
			}

			add_filter( 'csl_plugins', array( 'wop_LiveSearch_Settings', 'add_plugin' ), 0 );

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

		public static function get_settings( $plugins ) {

			$plugins[self::$plugin['label']] = array(
				'slug' => self::$plugin['label'],
				'name' => esc_html( function_exists( 'wop' ) ? self::$plugin['wop'] : self::$plugin['name'] ),
				'desc' => esc_html( function_exists( 'wop' ) ? self::$plugin['name'] . ' v' . self::$plugin['version'] : esc_html__( 'Settings page for', 'woopack' ) . ' ' . self::$plugin['name'] ),
				'link' => esc_url( 'https://woopack.com/store/live-search/' ),
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
					'general' => array(
						'name' => esc_html__( 'General', 'woopack' ),
						'desc' => esc_html__( 'General Overview', 'woopack' ),
					),
					'texts' => array(
						'name' => esc_html__( 'Texts', 'woopack' ),
						'desc' => esc_html__( 'Texts Overview', 'woopack' ),
					),
					'search' => array(
						'name' => esc_html__( 'Extended Search', 'woopack' ),
						'desc' => esc_html__( 'Extended Search Overview', 'woopack' ),
					),
				),
				'settings' => array(

					'wcmn_dashboard' => array(
						'type' => 'html',
						'id' => 'wcmn_dashboard',
                        'desc' => '
                            <img src="' . wop_LiveSearch()->plugin_url() . '/assets/images/live-search-for-woocommerce.png" class="csl-dashboard-image" />
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

					'characters' => array(
						'name' => esc_html__( 'Characters to Search', 'woopack' ),
						'type' => 'number',
						'desc' => esc_html__( 'Trigger search when number of characters is reached', 'woopack' ),
						'id'   => 'characters',
						'autoload' => false,
						'default' => '',
						'section' => 'general'
					),

					'separator' => array(
						'name' => esc_html__(  'Category Separator', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Enter category separator', 'woopack' ),
						'id'   => 'separator',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'general'
					),

					'products' => array(
						'name' => esc_html__( 'Products to Display', 'woopack' ),
						'type' => 'number',
						'desc' => esc_html__( 'Enter how many product to display after search', 'woopack' ),
						'id'   => 'products',
						'autoload' => false,
						'default' => '',
						'section' => 'general'
					),

					'placeholder' => array(
						'name' => esc_html__(  'Placeholder', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Enter placeholder text', 'woopack' ),
						'id'   => 'placeholder',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'texts'
					),

					'notfound' => array(
						'name' => esc_html__(  'No Products Found', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Enter no products found message', 'woopack' ),
						'id'   => 'notfound',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'texts'
					),

					'taxonomies' => array(
						'name' => esc_html__( 'Taxonomies', 'woopack' ),
						'type' => 'multiselect',
						'desc' => esc_html__( 'Select taxonomies to perform search', 'woopack' ),
						'section' => 'search',
						'id'   => 'taxonomies',
						'options' => 'ajax:product_taxonomies',
						'default' => '',
						'autoload' => false,
					),

					'interval' => array(
						'name' => esc_html__( 'Taxonomies Cache Interval', 'woopack' ),
						'type' => 'select',
						'desc' => esc_html__( 'Set taxonomies cache interval', 'woopack' ),
						'section' => 'search',
						'id'   => 'interval',
						'default' => 'saved',
						'options' => array(
							'saved' => esc_html__( 'When plugin options are saved', 'woopack' ),
							'hourly' => esc_html__( 'Once hourly', 'woopack' ),
							'twicedaily' => esc_html__( 'Twice daily', 'woopack' ),
							'daily' => esc_html__( 'Once daily', 'woopack' ),
						),
						'autoload' => false,
					),

					'meta_keys' => array(
						'name' => esc_html__( 'Meta Keys', 'woopack' ),
						'type' => 'multiselect',
						'desc' => esc_html__( 'Select meta kets to perform search', 'woopack' ),
						'section' => 'search',
						'id'   => 'meta_keys',
						'options' => apply_filters( 'live_search_wop_meta_keys', array(
							'_sku' => esc_html__( 'SKU', 'woopack' ),
						) ),
						'default' => '',
						'autoload' => false,
					),

				),
			);

			return SevenVX()->_do_options( $plugins, self::$plugin['label'] );
		}

	}

	wop_LiveSearch_Settings::init();

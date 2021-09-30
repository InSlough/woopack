<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_FloatingCart_Settings {

		public static $plugin;

		public static function init() {

			self::$plugin = array(
				'name' => 'Floating Cart for WooCommerce',
				'wop' => 'Floating Cart',
				'slug' => 'floating-cart-wop',
				'label' => 'floating_cart_wop',
				'image' => wop_FloatingCart()->plugin_url() . '/assets/images/floating-cart-for-woocommerce-woopack.png',
				'path' => 'floating-cart-wop/floating-cart-wop',
				'version' => wop_FloatingCart::$version,
			);

			if ( isset( $_GET['page'], $_GET['tab'] ) && ( $_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'floating_cart_wop' ) {
				add_filter( 'csl_plugins_settings', array( 'wop_FloatingCart_Settings', 'get_settings' ), 50 );
			}

			if ( function_exists( 'wop' ) ) {
				add_filter( 'wop_settings', array( 'wop_FloatingCart_Settings', 'wop' ), 9999999221 );
				add_filter( 'wop_csl_get_floating_cart_wop', array( 'wop_FloatingCart_Settings', '_get_settings_wop' ) );
			}

			add_filter( 'csl_plugins', array( 'wop_FloatingCart_Settings', 'add_plugin' ), 0 );
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
				'link' => esc_url( 'https://woopack.com/store/floating-cart/' ),
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
					'cart_contents' => array(
						'name' => esc_html__( 'Cart Contents', 'woopack' ),
						'desc' => esc_html__( 'Cart Contents Overview', 'woopack' ),
					),
					'message' => array(
						'name' => esc_html__( 'Cart Message', 'woopack' ),
						'desc' => esc_html__( 'Cart Message Overview', 'woopack' ),
					),
					'texts' => array(
						'name' => esc_html__( 'Texts', 'woopack' ),
						'desc' => esc_html__( 'Texts Overview', 'woopack' ),
					),
					'install' => array(
						'name' => esc_html__( 'Install', 'woopack' ),
						'desc' => esc_html__( 'Install Overview', 'woopack' ),
					),
				),
				'settings' => array(

					'wcmn_dashboard' => array(
						'type' => 'html',
						'id' => 'wcmn_dashboard',
                        'desc' => '
                            <img src="' . wop_FloatingCart()->plugin_url() . '/assets/images/floating-cart-for-woocommerce.png" class="csl-dashboard-image" />
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

					'position' => array(
						'name' => esc_html__( 'Cart Position', 'woopack' ),
						'type' => 'select',
						'desc' => esc_html__( 'Select Cart position', 'woopack' ),
						'id'   => 'position',
						'autoload' => false,
						'options' => array(
							'inline' => esc_html__( 'Shortcode', 'woopack' ),
							'top-right' => esc_html__( 'Top right', 'woopack' ),
							'top-left' => esc_html__( 'Top left', 'woopack' ),
							'bottom-right' => esc_html__( 'Bottom right', 'woopack' ),
							'bottom-left' => esc_html__( 'Bottom left', 'woopack' ),
						),
						'default' => 'top-right',
						'section' => 'general'
					),

					// 'checkout' => array(
					// 	'name' => esc_html__( 'Show Checkout', 'woopack' ),
					// 	'type' => 'checkbox',
					// 	'desc' => esc_html__( 'Show Checkout', 'woopack' ),
					// 	'id'   => 'checkout',
					// 	'autoload' => false,
					// 	'default' => 'yes',
					// 	'section' => 'general'
					// ),

					'my_account' => array(
						'name' => esc_html__( 'My Account', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Show My Account', 'woopack' ),
						'id'   => 'my_account',
						'autoload' => false,
						'default' => 'yes',
						'section' => 'general'
					),

					'cart_content' => array(
						'name' => esc_html__( 'Show Cart Contents', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Show Cart contents', 'woopack' ),
						'id'   => 'cart_content',
						'autoload' => false,
						'default' => 'yes',
						'section' => 'cart_contents'
					),

					'cart_overlay' => array(
						'name' => esc_html__( 'Use Overlay', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Display dark overlay over website when showing cart contents', 'woopack' ),
						'id'   => 'cart_overlay',
						'autoload' => false,
						'default' => 'yes',
						'section' => 'cart_contents'
					),

					'cart_message' => array(
						'name' => esc_html__( 'Show Cart Message', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Display added to cart message', 'woopack' ),
						'id'   => 'cart_message',
						'autoload' => false,
						'default' => 'yes',
						'section' => 'message'
					),

					'cart_message_delay' => array(
						'name' => esc_html__( 'Cart Message Life', 'woopack' ),
						'type' => 'number',
						'desc' => esc_html__( 'Display added to cart message for how long in miliseconds', 'woopack' ),
						'id'   => 'cart_message_delay',
						'autoload' => false,
						'default' => '',
						'section' => 'message'
					),

					'cart_message_text' => array(
						'name' => esc_html__( 'Cart Message Text', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Enter added to cart message text', 'woopack' ),
						'id'   => 'cart_message_text',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'texts'
					),

					'cart_empty_text' => array(
						'name' => esc_html__( 'Cart Empty Text', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Enter cart empty text', 'woopack' ),
						'id'   => 'cart_empty_text',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'texts'
					),

					'install_type' => array(
						'name' => esc_html__( 'Display Cart', 'woopack' ),
						'type' => 'select',
						'desc' => esc_html__( 'Where to show the Cart', 'woopack' ),
						'id'   => 'install_type',
						'options' => array(
							'everywhere' => esc_html__( 'Everywhere', 'woopack' ),
							'woocommerce' => esc_html__( 'WooCommerce Pages', 'woopack' ),
							'disable' => esc_html__( 'Disabled', 'woopack' ),
						),
						'autoload' => false,
						'default' => 'everywhere',
						'section' => 'install'
					),

					'pages' => array(
						'name' => esc_html__( 'Active Only in Pages', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'To activate plugin only in pages enter page IDs separated by | Sample: &rarr; ', 'woopack' ) . '<code>7|55</code>',
						'id'   => 'pages',
						'autoload' => false,
						'default' => '',
						'section' => 'install'
					),

				)
			);

			return SevenVX()->_do_options( $plugins, self::$plugin['label'] );
		}

	}

	wop_FloatingCart_Settings::init();

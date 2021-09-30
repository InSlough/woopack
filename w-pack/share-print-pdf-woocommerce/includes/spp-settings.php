<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_PDF_Print_Share_Settings {

		public static $plugin;

		public static function init() {

			self::$plugin = array(
				'name' => 'Share, Print and PDF for WooCommerce',
				'wop' => 'Print, PDF and Share',
				'slug' => 'share-print-pdf',
				'label' => 'share_print_pdf',
				'image' => Wcmnspp()->plugin_url() . '/includes/images/share-print-pdf-for-woocommerce-elements.png',
				'path' => 'share-print-pdf-woocommerce/share-woo',
				'version' => wop_PDF_Print_Share::$version,
			);

			if ( isset( $_GET['page'], $_GET['tab'] ) && ( $_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'share_print_pdf' ) {
				add_filter( 'csl_plugins_settings', array( 'wop_PDF_Print_Share_Settings', 'get_settings' ), 50 );
			}

			if ( function_exists( 'wop' ) ) {
				add_filter( 'wop_settings', array( 'wop_PDF_Print_Share_Settings', 'wop' ), 9999999141 );
				add_filter( 'wop_csl_get_share_print_pdf', array( 'wop_PDF_Print_Share_Settings', '_get_settings_wop' ) );
			}

			add_filter( 'csl_plugins', array( 'wop_PDF_Print_Share_Settings', 'add_plugin' ), 0 );
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
				'link' => esc_url( 'https://woopack.com/store/pdf-print-and-share/' ),
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
						'desc' => esc_html__( 'General Options', 'woopack' ),
					),
					'print_pdf_setup' => array(
						'name' => esc_html__( 'Print/PDF Setup', 'woopack' ),
						'desc' => esc_html__( 'Print/PDF Setup Options', 'woopack' ),
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
						<img src="' . Wcmnspp()->plugin_url() . '/includes/images/share-print-pdf-for-woocommerce-shop.png" class="csl-dashboard-image" />
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

					'wc_settings_spp_enable' => array(
						'name' => esc_html__( 'Installation Method', 'woopack' ),
						'type' => 'select',
						'desc' => esc_html__( 'Select method for installing the Share, Print and PDF template in your Shop.', 'woopack' ),
						'id'   => 'wc_settings_spp_enable',
						'autoload' => true,
						'options' => array(
							'override' => esc_html__( 'Override WooCommerce Template', 'woopack' ),
							'action' => esc_html__( 'Init Action', 'woopack' )
						),
						'default' => 'yes',
						'section' => 'installation'
					),

					'wc_settings_spp_action' => array(
						'name' => esc_html__( 'Init Action', 'woopack' ),
						'type' => 'text',
						'desc' => esc_html__( 'Change default plugin initialization action on single product pages. Use actions done in your content-single-product.php file. Please enter action in the following format action_name:priority.', 'woopack' ) . ' ( default: woocommerce_single_product_summary:60 )' . ' (default: :60)',
						'id'   => 'wc_settings_spp_action',
						'autoload' => true,
						'default' => 'woocommerce_single_product_summary:60',
						'section' => 'installation'
					),

					'wc_settings_spp_force_scripts' => array(
						'name' => esc_html__( 'Plugin Scripts', 'woopack' ),
						'type' => 'checkbox',
						'desc' => esc_html__( 'Check this option to enable plugin scripts in all pages. This option fixes issues in Quick Views.', 'woopack' ),
						'id'   => 'wc_settings_spp_force_scripts',
						'autoload' => true,
						'default' => 'no',
						'section' => 'installation'
					),

					'wc_settings_spp_logo' => array(
						'name' => esc_html__( 'Site Logo', 'woopack' ),
						'type' => 'file',
						'desc' => esc_html__( 'Use site logo on print and PDF templates. Enter URL.', 'woopack' ),
						'id'   => 'wc_settings_spp_logo',
						'autoload' => false,
						'default' => '',
						'section' => 'general'
					),

					'wc_settings_spp_title' => array(
						'name' => esc_html__( 'Replace Title', 'woopack' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Replace title with any HTML.', 'woopack' ),
						'id'   => 'wc_settings_spp_title',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'general'
					),

					'wc_settings_spp_style' => array(
						'name' => esc_html__( 'Icons Style', 'woopack' ),
						'type' => 'select',
						'desc' => esc_html__( 'Choose share icons style.', 'woopack' ),
						'id'   => 'wc_settings_spp_style',
						'autoload' => false,
						'options' => array(
							'line-icons' => esc_html__( 'Gray', 'woopack' ),
							'background-colors' => esc_html__( 'Backgrounds', 'woopack' ),
							'border-colors' => esc_html__( 'Borders', 'woopack' ),
							'flat' => esc_html__( 'Flat Colors', 'woopack' )

						),
						'default' => 'line-icons',
						'section' => 'general'
					),

					'wc_settings_spp_shares' => array(
						'name' => esc_html__( 'Hide Icons', 'woopack' ),
						'type' => 'multiselect',
						'desc' => esc_html__( 'Select icons to hide on your webiste.', 'woopack' ),
						'id'   => 'wc_settings_spp_shares',
						'autoload' => false,
						'options' => array(
							'facebook' => esc_html__( 'Facebook', 'woopack' ),
							'twitter' => esc_html__( 'Twitter', 'woopack' ),
							'pin' => esc_html__( 'Pinterest', 'woopack' ),
							'linked' => esc_html__( 'LinkedIn', 'woopack' ),
							'print' => esc_html__( 'Print', 'woopack' ),
							'pdf' => esc_html__( 'PDF', 'woopack' ),
							'email' => esc_html__( 'Email', 'woopack' ),
						),
						'default' => array(),
						'section' => 'general',
						'class' => 'csl-selectize'
					),

					'wc_settings_spp_pagesize' => array(
						'name' => esc_html__( 'Page Size', 'woopack' ),
						'type' => 'select',
						'desc' => esc_html__( 'Select PDF page format.', 'woopack' ),
						'id'   => 'wc_settings_spp_pagesize',
						'autoload' => false,
						'options' => array(
							'letter' => esc_html__( 'Letter', 'woopack' ),
							'legal' => esc_html__( 'Legal', 'woopack' ),
							'a4' => 'A4',
							'a3' => 'A3'
						),
						'default' => 'letter',
						'section' => 'print_pdf_setup'
					),
					'wc_settings_spp_header_after' => array(
						'name' => esc_html__( 'Header After', 'woopack' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Set custom content after header in print and PDF mode.', 'woopack' ),
						'id'   => 'wc_settings_spp_header_after',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'print_pdf_setup'
					),
					'wc_settings_spp_product_before' => array(
						'name' => esc_html__( 'Product Before', 'woopack' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Set custom content before product content in print and PDF mode.', 'woopack' ),
						'id'   => 'wc_settings_spp_product_before',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'print_pdf_setup'
					),
					'wc_settings_spp_product_after' => array(
						'name' => esc_html__( 'Product After', 'woopack' ),
						'type' => 'textarea',
						'desc' => esc_html__( 'Set custom content after product content in print and PDF mode.', 'woopack' ),
						'id'   => 'wc_settings_spp_product_after',
						'autoload' => false,
						'translate' => true,
						'default' => '',
						'section' => 'print_pdf_setup'
					),

				)
			);

			return SevenVX()->_do_options( $plugins, self::$plugin['label'] );
		}

	}

	wop_PDF_Print_Share_Settings::init();

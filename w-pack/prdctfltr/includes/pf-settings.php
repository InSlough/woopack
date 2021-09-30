<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_Product_Filters_Settings {

		public static $settings = null;
		public static $presets = null;

		public static $plugin = array();

		public static function init() {

			self::$plugin = array(
				'name' => esc_html__( 'Product Filter for WooCommerce', 'woopack' ),
				'wop' => esc_html__( 'Product Filters', 'woopack' ),
				'slug' => 'product-filters',
				'label' => 'product_filter',
				'image' => Prdctfltr()->plugin_url() . '/includes/images/product-filter-for-woocommerce-elements.png',
				'path' => 'prdctfltr/prdctfltr',
				'version' => wop_Product_Filters::$version,
			);

			if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'product_filter' ) {
				add_filter( 'csl_plugins_settings', array( 'wop_Product_Filters_Settings', 'get_settings' ), 50 );
				add_action( 'admin_enqueue_scripts', __CLASS__ . '::scripts', 9 );
			}

			if ( isset($_GET['page']) && ($_GET['page'] == 'woopack' )) {
				add_action( 'admin_enqueue_scripts', __CLASS__ . '::scripts', 9 );
			}

			if ( function_exists( 'wop' ) ) {
				add_filter( 'wop_settings', array( 'wop_Product_Filters_Settings', 'wop' ), 9999999101 );
				add_filter( 'wop_csl_get_product_filters', array( 'wop_Product_Filters_Settings', '_get_settings_wop' ) );
			}

			add_filter( 'csl_plugins', array( 'wop_Product_Filters_Settings', 'add_plugin' ), 0 );

			add_action( 'wp_ajax_prdctfltr_analytics_reset', __CLASS__ . '::analytics_reset' );
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

		public static function scripts() {
			wp_register_script( 'google-api', (is_ssl()?'https://':'http://') . 'www.google.com/jsapi', array(), false, true );
			wp_enqueue_script( 'google-api' );

			wp_register_script( 'product-filter', Prdctfltr()->plugin_url() . '/includes/js/csl-admin.js', array( 'jquery' ), Prdctfltr()->version(), true );
			wp_enqueue_script( 'product-filter' );
		}

		public static function ___get_taxonomy_option() {
			return array(
				'name' => esc_html__( 'Select Taxonomy', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select taxonomy for this filter', 'woopack' ),
				'id'   => 'taxonomy',
				'options' => 'ajax:product_taxonomies:has_none',
				'default' => '',
				'class' => 'csl-update-list-title csl-selectize',
			);
		}

		public static function ___get_title_option() {
			return array(
				'name' => esc_html__( 'Title', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Use alternative title', 'woopack' ),
				'id'   => 'name',
				'default' => '',
			);
		}

		public static function ___get_desc_option() {
			return array(
				'name' => esc_html__( 'Description', 'woopack' ),
				'type' => 'textarea',
				'desc' => esc_html__( 'Enter filter description', 'woopack' ),
				'id'   => 'desc',
				'default' => '',
			);
		}

		public static function ___get_include_option() {
			return array(
				'name' => esc_html__( 'Include/Exclude', 'woopack' ),
				'type' => 'include',
				'desc' => esc_html__( 'Select terms to include/exclude', 'woopack' ),
				'id'   => 'include',
				'default' => false,
			);
		}

		public static function ___get_orderby_option() {
			return array(
				'name' => esc_html__( 'Order By', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select term order', 'woopack' ),
				'id'   => 'orderby',
				'default' => '',
				'options' => array(
					'' => esc_html__( 'None (Custom Menu Order)', 'woopack' ),
					'id' => esc_html__( 'ID', 'woopack' ),
					'name' => esc_html__( 'Name', 'woopack' ),
					'number' => esc_html__( 'Number', 'woopack' ),
					'slug' => esc_html__( 'Slug', 'woopack' ),
					'count' => esc_html__( 'Count', 'woopack' )
				),
			);
		}

		public static function ___get_order_option() {
			return array(
				'name' => esc_html__( 'Order', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select ascending/descending', 'woopack' ),
				'id'   => 'order',
				'default' => 'ASC',
				'options' => array(
					'ASC' => esc_html__( 'ASC', 'woopack' ),
					'DESC' => esc_html__( 'DESC', 'woopack' )
				),
			);
		}

		public static function ___get_limit_option() {
			return array(
				'name' => esc_html__( 'Show more', 'woopack' ),
				'type' => 'number',
				'desc' => esc_html__( 'Show more button on term', 'woopack' ),
				'id'   => 'limit',
				'default' => '',
			);
		}

		public static function ___get_hierarchy_option() {
			return array(
				'name' => esc_html__( 'Hierarchy', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Use hierarchy.', 'woopack' ),
				'id'   => 'hierarchy',
				'default' => 'no',
			);
		}

		public static function ___get_hierarchy_mode_option() {
			return array(
				'name' => esc_html__( 'Hierarchy Mode', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select hierarchy mode', 'woopack' ),
				'id'   => 'hierarchy_mode',
				'default' => 'showall',
				'options' => array(
					'showall' => esc_html__( 'Show all terms', 'woopack' ),
					'drill' => esc_html__( 'Show current level terms (Drill filter)', 'woopack' ),
					'drillback' => esc_html__( 'Show current level terms with parent term support (Drill filter)', 'woopack' ),
					'subonly' => esc_html__( 'Show lower level hierarchy terms', 'woopack' ),
					'subonlyback' => esc_html__( 'Show lower level hierarchy terms with parent term support', 'woopack' )
				),
			);
		}

		public static function ___get_hierarchy_expand_option() {
			return array(
				'name' => esc_html__( 'Hierarchy Expand', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Expand hierarchy tree on load', 'woopack' ),
				'id'   => 'hierarchy_expand',
				'default' => 'no',
			);
		}

		public static function ___get_multiselect_option() {
			return array(
				'name' => esc_html__( 'Multiselect', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Use multiselect', 'woopack' ),
				'id'   => 'multiselect',
				'default' => 'no',
			);
		}

		public static function ___get_multiselect_relation_option() {
			return array(
				'name' => esc_html__( 'Multiselect Relation', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select multiselect relation', 'woopack' ),
				'id'   => 'multiselect_relation',
				'default' => 'IN',
				'options' => array(
					'IN' => 'IN',
					'AND' => 'AND',
				),
			);
		}

		public static function ___get_selection_reset_option() {
			return array(
				'name' => esc_html__( 'Selection Reset', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Reset filters on select', 'woopack' ),
				'id'   => 'selection_reset',
				'default' => 'no',
			);
		}

		public static function ___get_adoptive_option() {
			return array(
				'name' => esc_html__( 'Adoptive', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select adoptive filtering', 'woopack' ),
				'id'   => 'adoptive',
				'default' => 'no',
				'options' => array(
					'no' => esc_html__( 'Not active on this filter', 'woopack' ),
					'pf_adptv_default' => esc_html__( 'Terms will be hidden', 'woopack' ),
					'pf_adptv_unclick' => esc_html__( 'Terms will be shown, but unclickable', 'woopack' ),
					'pf_adptv_click' => esc_html__( 'Terms will be shown and clickable', 'woopack' ),
				),
				'condition' => 'a_enable:yes',
			);
		}

		public static function ___get_adoptive_for_range_option() {
			return array(
				'name' => esc_html__( 'Adoptive', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select adoptive filtering', 'woopack' ),
				'id'   => 'adoptive',
				'default' => 'no',
				'options' => array(
					'no' => esc_html__( 'Not active on this filter', 'woopack' ),
					'pf_adptv_default' => esc_html__( 'Terms will be hidden', 'woopack' ),
				),
				'condition' => 'a_enable:yes',
			);
		}

		public static function ___get_term_count_option() {
			return array(
				'name' => esc_html__( 'Term Count', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Show term count', 'woopack' ),
				'id'   => 'term_count',
				'default' => 'no',
			);
		}

		public static function ___get_term_search_option() {
			return array(
				'name' => esc_html__( 'Term Search', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Show term search input', 'woopack' ),
				'id'   => 'term_search',
				'default' => 'no',
			);
		}

		public static function ___get_term_display_option() {
			return array(
				'name' => esc_html__( 'Term Display', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select terms display style', 'woopack' ),
				'id'   => 'term_display',
				'default' => 'none',
				'options' => array(
					'none' => esc_html__( 'Default', 'woopack' ),
					'inline' => esc_html__( 'Inline', 'woopack' ),
					'2_columns' => esc_html__( 'Split into two columns', 'woopack' ),
					'3_columns' => esc_html__( 'Split into three columns', 'woopack' ),
				),
			);
		}

		public static function ___get_none_text() {
			return array(
				'name' => esc_html__( 'None Text', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Change none text', 'woopack' ),
				'id'   => 'none_text',
				'default' => '',
			);
		}

		public static function ___get_hide_elements_option() {
			return array(
				'name' => esc_html__( 'Hide Elements', 'woopack' ),
				'type' => 'multiselect',
				'desc' => esc_html__( 'Select elements to hide', 'woopack' ),
				'id'   => 'hide_elements',
				'default' => '',
				'options' => array(
					'title' => esc_html__( 'Title', 'prdctflr' ),
					'none' => esc_html__( 'None', 'woopack' ),
				),
				'class' => 'csl-selectize',
			);
		}

		public static function ___get_hide_elements_for_range_option() {
			return array(
				'name' => esc_html__( 'Hide Elements', 'woopack' ),
				'type' => 'multiselect',
				'desc' => esc_html__( 'Select elements to hide', 'woopack' ),
				'id'   => 'hide_elements',
				'default' => '',
				'options' => array(
					'title' => esc_html__( 'Title', 'prdctflr' ),
				),
				'class' => 'csl-selectize',
			);
		}

		public static function ___get_range_style_option() {
			return array(
				'name' => esc_html__( 'Style', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Style', 'woopack' ),
				'id'   => 'design',
				'default' => 'thin',
				'options' => array(
					'flat' => esc_html__( 'Flat', 'woopack' ),
					'modern' => esc_html__( 'Modern', 'woopack' ),
					'html5' => esc_html__( 'HTML5', 'woopack' ),
					'white' => esc_html__( 'White', 'woopack' ),
					'thin' => esc_html__( 'Thin', 'woopack' ),
					'knob' => esc_html__( 'Knob', 'woopack' ),
					'metal' => esc_html__( 'Metal', 'woopack' )
				),
			);
		}

		public static function ___get_range_grid_option() {
			return array(
				'name' => esc_html__( 'Grid', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Show grid', 'woopack' ),
				'id'   => 'grid',
				'default' => '',
			);
		}

		public static function ___get_range_start_option() {
			return array(
				'name' => esc_html__( 'Start', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Range start', 'woopack' ),
				'id'   => 'start',
				'default' => '',
			);
		}

		public static function ___get_range_end_option() {
			return array(
				'name' => esc_html__( 'End', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Range end', 'woopack' ),
				'id'   => 'end',
				'default' => '',
			);
		}

		public static function ___get_range_prefix_option() {
			return array(
				'name' => esc_html__( 'Prefix', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Terms prefix', 'woopack' ),
				'id'   => 'prefix',
				'default' => '',
			);
		}

		public static function ___get_range_postfix_option() {
			return array(
				'name' => esc_html__( 'Postfix', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Terms postfix', 'woopack' ),
				'id'   => 'postfix',
				'default' => '',
			);
		}

		public static function ___get_range_step_option() {
			return array(
				'name' => esc_html__( 'Step', 'woopack' ),
				'type' => 'number',
				'desc' => esc_html__( 'Step value', 'woopack' ),
				'id'   => 'step',
				'default' => '',
			);
		}

		public static function ___get_range_grid_num_option() {
			return array(
				'name' => esc_html__( 'Grid density', 'woopack' ),
				'type' => 'number',
				'desc' => esc_html__( 'Grid density value', 'woopack' ),
				'id'   => 'grid_num',
				'default' => '',
			);
		}

		public static function ___get_meta_key_option() {
			return array(
				'name' => esc_html__( 'Meta key', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter meta key', 'woopack' ),
				'id'   => 'meta_key',
				'default' => '',
			);
		}

		public static function ___get_meta_compare_option() {
			return array(
				'name' => esc_html__( 'Meta compare', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select meta compare', 'woopack' ),
				'id'   => 'meta_compare',
				'default' => '=',
				'options' => array(
					'=' => '=',
					'!=' => '!=',
					'>' => '>',
					'<' => '<',
					'>=' => '>=',
					'<=' => '<=',
					'LIKE' => 'LIKE',
					'NOT LIKE' => 'NOT LIKE',
					'IN' => 'IN',
					'NOT IN' => 'NOT IN',
					'EXISTS' => 'EXISTS',
					'NOT EXISTS' => 'NOT EXISTS',
					'BETWEEN' => 'BETWEEN',
					'NOT BETWEEN' => 'NOT BETWEEN',
				),
			);
		}

		public static function ___get_meta_numeric() {
			return array(
				'name' => esc_html__( 'Numeric', 'woopack' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Meta values are numeric', 'woopack' ),
				'id'   => 'meta_numeric',
				'default' => '',
			);
		}

		public static function ___get_meta_type_option() {
			return array(
				'name' => esc_html__( 'Meta type', 'woopack' ),
				'type' => 'select',
				'desc' => esc_html__( 'Select meta type', 'woopack' ),
				'id'   => 'meta_type',
				'default' => 'CHAR',
				'options' => array(
					'NUMERIC' => 'NUMERIC',
					'BINARY' => 'BINARY',
					'CHAR' => 'CHAR',
					'DATE' => 'DATE',
					'DATETIME' => 'DATETIME',
					'DECIMAL' => 'DECIMAL',
					'SIGNED' => 'SIGNED',
					'TIME' => 'TIME',
					'UNSIGNED' => 'UNSIGNED',
				),
			);
		}

		public static function ___get_placeholder_option() {
			return array(
				'name' => esc_html__( 'Placeholder', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Placeholder text', 'woopack' ),
				'id'   => 'placeholder',
				'default' => '',
			);
		}

		public static function ___get_label_option() {
			return array(
				'name' => esc_html__( 'Label', 'woopack' ),
				'type' => 'text',
				'desc' => esc_html__( 'Label text', 'woopack' ),
				'id'   => 'label',
				'default' => '',
			);
		}

		public static function __build_filters() {

			$array = array();

			$array['taxonomy'] = array(
				'taxonomy' => self::___get_taxonomy_option(),
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'include' => self::___get_include_option(),
				'orderby' => self::___get_orderby_option(),
				'order' => self::___get_order_option(),
				'limit' => self::___get_limit_option(),
				'hierarchy' => self::___get_hierarchy_option(),
				'hierarchy_mode' => self::___get_hierarchy_mode_option(),
				'hierarchy_expand' => self::___get_hierarchy_expand_option(),
				'multiselect' => self::___get_multiselect_option(),
				'multiselect_relation' => self::___get_multiselect_relation_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'adoptive' => self::___get_adoptive_option(),
				'term_count' => self::___get_term_count_option(),
				'term_search' => self::___get_term_search_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);


			$array['range'] = array(
				'taxonomy' => self::___get_taxonomy_option(),
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'include' => self::___get_include_option(),
				'orderby' => self::___get_orderby_option(),
				'order' => self::___get_order_option(),
				'design' => self::___get_range_style_option(),
				'start' => self::___get_range_start_option(),
				'end' => self::___get_range_end_option(),
				'prefix' => self::___get_range_prefix_option(),
				'postfix' => self::___get_range_postfix_option(),
				'step' => self::___get_range_step_option(),
				'grid' => self::___get_range_grid_option(),
				'grid_num' => self::___get_range_grid_num_option(),
				'adoptive' => self::___get_adoptive_for_range_option(),
				'hide_elements' => self::___get_hide_elements_for_range_option(),
			);

			$array['meta'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'meta_key' => self::___get_meta_key_option(),
				'meta_compare' => self::___get_meta_compare_option(),
				'meta_type' => self::___get_meta_type_option(),
				'limit' => self::___get_limit_option(),
				'multiselect' => self::___get_multiselect_option(),
				'multiselect_relation' => self::___get_multiselect_relation_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'term_search' => self::___get_term_search_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);


			$array['meta_range'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'meta_key' => self::___get_meta_key_option(),
				'meta_numeric' => self::___get_meta_numeric(),
				'design' => self::___get_range_style_option(),
				'start' => self::___get_range_start_option(),
				'end' => self::___get_range_end_option(),
				'prefix' => self::___get_range_prefix_option(),
				'postfix' => self::___get_range_postfix_option(),
				'step' => self::___get_range_step_option(),
				'grid' => self::___get_range_grid_option(),
				'grid_num' => self::___get_range_grid_num_option(),
				'hide_elements' => self::___get_hide_elements_for_range_option(),
			);

			$array['vendor'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'include' => self::___get_include_option(),
				'limit' => self::___get_limit_option(),
				'multiselect' => self::___get_multiselect_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'term_search' => self::___get_term_search_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);

			$array['orderby'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'include' => self::___get_include_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);

			$array['search'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'placeholder' => self::___get_placeholder_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);

			$array['instock'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'include' => self::___get_include_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);

			$array['price'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);

			$array['price_range'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'design' => self::___get_range_style_option(),
				'grid' => self::___get_range_grid_option(),
				'start' => self::___get_range_start_option(),
				'end' => self::___get_range_end_option(),
				'prefix' => self::___get_range_prefix_option(),
				'postfix' => self::___get_range_postfix_option(),
				'step' => self::___get_range_step_option(),
				'grid_num' => self::___get_range_grid_num_option(),
				'hide_elements' => self::___get_hide_elements_for_range_option(),
			);

			$array['per_page'] = array(
				'name' => self::___get_title_option(),
				'desc' => self::___get_desc_option(),
				'selection_reset' => self::___get_selection_reset_option(),
				'term_display' => self::___get_term_display_option(),
				'none_text' => self::___get_none_text(),
				'hide_elements' => self::___get_hide_elements_option(),
			);

			$array = apply_filters( 'prdctflr_supported_filters', $array );

			return $array;

		}

		public static function get_key() {
			return get_option( 'wop_key_product_filter' ) === false ? 'false' : 'true';
		}

		public static function get_settings( $plugins ) {

			self::$settings['options'] = Prdctfltr()->get_default_options();

			self::$settings['preset'] = Prdctfltr()->___get_preset( 'default' );

			$saved = isset( self::$settings['options']['presets'] ) && is_array ( self::$settings['options']['presets'] ) ? self::$settings['options']['presets'] : array();
			foreach( $saved as $preset ) {
				self::$presets[$preset['slug']] = $preset['name'];
			}

			if ( empty( self::$presets ) ) {
				self::$presets = false;
			}

			$attributes = get_object_taxonomies( 'product' );
			foreach( $attributes as $k ) {
				if ( !in_array( $k, array() ) ) {
					if ( substr( $k, 0, 3 ) == 'pa_' ) {
						$ready_attributes[$k] = wc_attribute_label( $k );
					}
					else {
						$taxonomy = get_taxonomy( $k );
						$ready_attributes[$k] = $taxonomy->label;
					}
				}
			}

			if ( empty( $ready_attributes ) ) {
				$ready_attributes = false;
			}

			include_once( 'class-themes.php' );
			$ajax = wop_Product_Filters_Themes::get_theme();

			$plugins[self::$plugin['label']] = array(
				'slug' => self::$plugin['label'],
				'name' => esc_html( function_exists( 'wop' ) ? self::$plugin['wop'] : self::$plugin['name'] ),
				'desc' => esc_html( function_exists( 'wop' ) ? self::$plugin['name'] . ' v' . self::$plugin['version'] : esc_html__( 'Settings page for', 'woopack' ) . ' ' . self::$plugin['name'] ),
				'link' => esc_url( 'https://woopack.com/store/product-filters/' ),
				'imgs' => esc_url( Prdctfltr()->plugin_url() ),
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
						'name' => esc_html__( 'Filter Presets', 'woopack' ),
						'desc' => esc_html__( 'Filter Presets Options', 'woopack' ),
					),
					'manager' => array(
						'name' => esc_html__( 'Presets Manager', 'woopack' ),
						'desc' => esc_html__( 'Presets Manager Options', 'woopack' ),
					),
					'integration' => array(
						'name' => esc_html__( 'Shop Integration', 'woopack' ),
						'desc' => esc_html__( 'Shop Integration Options', 'woopack' ),
					),
					'ajax' => array(
						'name' => esc_html__( 'AJAX', 'woopack' ),
						'desc' => esc_html__( 'AJAX Options', 'woopack' ),
					),
					'general' => array(
						'name' => esc_html__( 'Advanced', 'woopack' ),
						'desc' => esc_html__( 'Advanced Options', 'woopack' ),
					),
					'analytics' => array(
						'name' => esc_html__( 'Analytics', 'woopack' ),
						'desc' => esc_html__( 'Filtering Analytics', 'woopack' ),
					),
				),
				'extras' => array(
					'product_attributes' => $ready_attributes,
					'more_titles' => array(
						'orderby' => esc_html__( 'Order by', 'woopack' ),
						'per_page' => esc_html__( 'Per page', 'woopack' ),
						'vendor' => esc_html__( 'Vendor', 'woopack' ),
						'search' => esc_html__( 'Search', 'woopack' ),
						'instock' => esc_html__( 'Availability', 'woopack' ),
						'price' => esc_html__( 'Price', 'woopack' ),
						'price_range' => esc_html__( 'Price range', 'woopack' ),
						'meta' => esc_html__( 'Meta filter', 'woopack' ),
					),
					'options' => self::$settings['options'],
					'presets' => array(
						'loaded' => 'default',
						'loaded_settings' => self::$settings['preset'],
						'set' => self::$presets,
					),
					'terms' => array(
						'orderby' => array(
							array(
								'id' => 'menu_order',
								'slug' => 'menu_order',
								'default_name' => 'Default',
							),
							array(
								'id' => 'comment_count',
								'slug' => 'comment_count',
								'default_name' => 'Review Count',
							),
							array(
								'id' => 'popularity',
								'slug' => 'popularity',
								'default_name' => 'Popularity',
							),
							array(
								'id' => 'rating',
								'slug' => 'rating',
								'default_name' => 'Average rating',
							),
							array(
								'id' => 'date',
								'slug' => 'date',
								'default_name' => 'Newness',
							),
							array(
								'id' => 'price',
								'slug' => 'price',
								'default_name' => 'Price: low to high',
							),
							array(
								'id' => 'price-desc',
								'slug' => 'price-desc',
								'default_name' => 'Price: high to low',
							),
							array(
								'id' => 'rand',
								'slug' => 'rand',
								'default_name' => 'Random Products',
							),
							array(
								'id' => 'title',
								'slug' => 'title',
								'default_name' => 'Product Name',
							),
						),
						'instock' => array(
							array(
								'id' => 'out',
								'slug' => 'out',
								'default_name' => 'Out of stock',
							),
							array(
								'id' => 'in',
								'slug' => 'in',
								'default_name' => 'In stock',
							),
							array(
								'id' => 'both',
								'slug' => 'both',
								'default_name' => 'All products',
							),
						),
					),
				),
				'settings' => array(),
			);

			$plugins['product_filter']['settings']['wcmn_dashboard'] = array(
				'type' => 'html',
				'id' => 'wcmn_dashboard',
				'desc' => '
				<img src="' . Prdctfltr()->plugin_url() . '/includes/images/product-filter-for-woocommerce-shop.png" class="csl-dashboard-image" />
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
			);

			$plugins['product_filter']['settings']['wcmn_utility'] = array(
				'name' => esc_html__( 'Plugin Options', 'woopack' ),
				'type' => 'utility',
				'id' => 'wcmn_utility',
				'desc' => esc_html__( 'Quick export/import, backup and restore, or just reset your optons here', 'woopack' ),
				'section' => 'dashboard',
			);

			$plugins['product_filter']['settings'] = array_merge( $plugins['product_filter']['settings'], array(

				'_filter_preset_manager' => array(
					'name' => esc_html__( 'Filter Preset', 'woopack' ),
					'type' => 'select',
					'id' => '_filter_preset_manager',
					'desc' => esc_html__( 'Editing selected filter preset', 'woopack' ),
					'section' => 'presets',
					'options' => 'function:__make_presets',
					'default' => 'default',
					'class' => '',
					'val' => 'default',
				),

				'_filter_preset_options' => array(
					'name' => esc_html__( 'Filter Options', 'woopack' ),
					'type' => 'select',
					'id' => '_filter_preset_options',
					'desc' => esc_html__( 'Select options group for the current preset', 'woopack' ),
					'section' => 'presets',
					'options' => array(
						'filters' => esc_html__( 'Filters' , 'woopack' ),
						'general' => esc_html__( 'General' , 'woopack' ),
						'style' => esc_html__( 'Style' , 'woopack' ),
						'adoptive' => esc_html__( 'Adoptive' , 'woopack' ),
						'responsive' => esc_html__( 'Responsive' , 'woopack' ),
					),
					'default' => 'filters',
					'class' => 'csl-make-group csl-refresh-active-tab',
					'val' => 'filters',
				),

				'g_instant' => array(
					'name' => esc_html__( 'Filter on Click', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to filter on click', 'woopack' ),
					'section' => 'presets',
					'id'   => 'g_instant',
					'default' => 'no',
					'condition' => '_filter_preset_options:general',
				),

				'g_step_selection' => array(
					'name' => esc_html__( 'Stepped Selection', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to use stepped selection', 'woopack' ),
					'section' => 'presets',
					'id'   => 'g_step_selection',
					'default' => 'no',
					'condition' => '_filter_preset_options:general',
				),

				'g_collectors' => array(
					'name' => esc_html__( 'Show Selected Terms In', 'woopack' ),
					'type' => 'multiselect',
					'desc' => esc_html__( 'Select areas where to show the selected terms', 'woopack' ),
					'section' => 'presets',
					'id'   => 'g_collectors',
					'options'   => array(
						'topbar' => esc_html__( 'Top bar', 'woopack' ),
						'collector' => esc_html__( 'Collector', 'woopack' ),
						'intitle' => esc_html__( 'Filter title', 'woopack' ),
						'aftertitle' => esc_html__( 'After filter title', 'woopack' ),
					),
					'default' => array( 'collector' ),
					'condition' => '_filter_preset_options:general',
					'class' => 'csl-selectize',
				),

				'g_collector_style' => array(
					'name' => esc_html__( 'Selected Terms Style', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select selected terms style', 'woopack' ),
					'section' => 'presets',
					'id'   => 'g_collector_style',
					'options'   => array(
						'flat' => esc_html__( 'Flat', 'woopack' ),
						'border' => esc_html__( 'Border', 'woopack' ),
					),
					'default' => 'flat',
					'condition' => '_filter_preset_options:general',
				),

				'g_reorder_selected' => array(
					'name' => esc_html__( 'Reorder Selected', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to bring selected terms to the top', 'woopack' ),
					'section' => 'presets',
					'id'   => 'g_reorder_selected',
					'default' => 'no',
					'condition' => '_filter_preset_options:general',
				),

				'g_form_action' => array(
					'name' => esc_html__( 'Filter Form Action', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter custom filter form action="" parameter', 'woopack' ),
					'section' => 'presets',
					'id'   => 'g_form_action',
					'default' => '',
					'condition' => '_filter_preset_options:general',
				),

				's_style' => array(
					'name' => esc_html__( 'Select Design', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select filter design style', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_style',
					'options'   => array(
						'pf_default' => esc_html__( 'Default', 'woopack' ),
						'pf_arrow' => esc_html__( 'Pop up', 'woopack' ),
						'pf_sidebar' => esc_html__( 'Left fixed sidebar', 'woopack' ),
						'pf_sidebar_right' => esc_html__( 'Right fixed sidebar', 'woopack' ),
						'pf_sidebar_css' => esc_html__( 'Left fixed sidebar with overlay', 'woopack' ),
						'pf_sidebar_css_right' => esc_html__( 'Right fixed sidebar with overlay', 'woopack' ),
						'pf_fullscreen' => esc_html__( 'Full screen filters', 'woopack' ),
						'pf_select' => esc_html__( 'Filters inside select boxes', 'woopack' ),
					),
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_always_visible' => array(
					'name' => esc_html__( 'Always Visible', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Disable slide in/out animation', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_always_visible',
					'default' => 'no',
					'condition' => '_filter_preset_options:style',
				),

				's_hide_elements' => array(
					'name' => esc_html__( 'Hide Elements', 'woopack' ),
					'type' => 'multiselect',
					'desc' => esc_html__( 'Select elements to hide', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_hide_elements',
					'options' => array(
						'hide_icon' => esc_html__( 'Filter icon', 'woopack' ),
						'hide_top_bar' => esc_html__( 'The whole top bar', 'woopack' ),
						'hide_showing' => esc_html__( 'Showing text in top bar', 'woopack' ),
						'hide_sale_button' => esc_html__( 'Sale button', 'woopack' ),
						'hide_instock_button' => esc_html__( 'Instock button', 'woopack' ),
						'hide_reset_button' => esc_html__( 'Reset button', 'woopack' ),
					),
					'default' => '',
					'condition' => '_filter_preset_options:style',
					'class' => 'csl-selectize',
				),

				's_mode' => array(
					'name' => esc_html__( 'Row Display', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select row display mode', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_mode',
					'options'   => array(
						'pf_mod_row' => esc_html__( 'One row', 'woopack' ),
						'pf_mod_multirow' => esc_html__( 'Multiple rows', 'woopack' ),
						'pf_mod_masonry' => esc_html__( 'Masonry Filters', 'woopack' ),
					),
					'default' => 'pf_mod_multirow',
					'condition' => '_filter_preset_options:style',
				),

				's_columns' => array(
					'name' => esc_html__( 'Max Columns', 'woopack' ),
					'type' => 'number',
					'desc' => esc_html__( 'Set max filter columns', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_columns',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_max_height' => array(
					'name' => esc_html__( 'Max Height', 'woopack' ),
					'type' => 'number',
					'desc' => esc_html__( 'Set max filter height', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_max_height',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_js_scroll' => array(
					'name' => esc_html__( 'Scroll Bar Style', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Enable enhanced scroll bars display', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_js_scroll',
					'default' => 'no',
					'condition' => '_filter_preset_options:style',
				),

				's_checkbox_style' => array(
					'name' => esc_html__( 'Checkbox Style', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select term checkbox style', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_checkbox_style',
					'options'   => array(
						'prdctfltr_bold' => esc_html__( 'Hide', 'woopack' ),
						'prdctfltr_round' => esc_html__( 'Round', 'woopack' ),
						'prdctfltr_square' => esc_html__( 'Square', 'woopack' ),
						'prdctfltr_checkbox' => esc_html__( 'Checkbox', 'woopack' ),
						'prdctfltr_system' => esc_html__( 'System Checkboxes', 'woopack' ),
					),
					'default' => 'prdctfltr_round',
					'condition' => '_filter_preset_options:style',
				),

				's_hierarchy_style' => array(
					'name' => esc_html__( 'Hierarchy Style', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select hierarchy style', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_hierarchy_style',
					'options'   => array(
						'prdctfltr_hierarchy_hide' => esc_html__( 'Hide', 'woopack' ),
						'prdctfltr_hierarchy_circle' => esc_html__( 'Circle', 'woopack' ),
						'prdctfltr_hierarchy_filled' => esc_html__( 'Circle Solid', 'woopack' ),
						'prdctfltr_hierarchy_lined' => esc_html__( 'Lined', 'woopack' ),
						'prdctfltr_hierarchy_arrow' => esc_html__( 'Arrows', 'woopack' ),
					),
					'default' => 'prdctfltr_hierarchy_lined',
					'condition' => '_filter_preset_options:style',
				),

				's_filter_icon' => array(
					'name' => esc_html__( 'Filter Icon', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter icon class. Use icon class e.g. prdctfltr-filter or FontAwesome fa fa-shopping-cart or any other', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_filter_icon',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_filter_title' => array(
					'name' => esc_html__( 'Filter Title Text', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter title text', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_filter_title',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_filter_button' => array(
					'name' => esc_html__( 'Filter Button Text', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter filter button text', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_filter_button',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_button_position' => array(
					'name' => esc_html__( 'Button Position', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select button position', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_button_position',
					'options'   => array(
						'bottom' => esc_html__( 'Bottom', 'woopack' ),
						'top' => esc_html__( 'Top', 'woopack' ),
						'both' => esc_html__( 'Both', 'woopack' ),
					),
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_content_align' => array(
					'name' => esc_html__( 'Content Align', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Set content align', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_content_align',
					'options'   => array(
						'left' => esc_html__( 'Left', 'woopack' ),
						'center' => esc_html__( 'Center', 'woopack' ),
						'right' => esc_html__( 'Right', 'woopack' ),
					),
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's_loading_animation' => array(
					'name' => esc_html__( 'Loader Animation', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select loader animation', 'woopack' ),
					'section' => 'presets',
					'id'   => 's_loading_animation',
					'options'   => array(
						'css-spinner-full' => sprintf( esc_html__( 'Overlay #%s', 'woopack' ), '1' ),
						'css-spinner-full-01' => sprintf( esc_html__( 'Overlay #%s', 'woopack' ), '2' ),
						'css-spinner-full-02' => sprintf( esc_html__( 'Overlay #%s', 'woopack' ), '3' ),
						'css-spinner-full-03' => sprintf( esc_html__( 'Overlay #%s', 'woopack' ), '4' ),
						'css-spinner-full-04' => sprintf( esc_html__( 'Overlay #%s', 'woopack' ), '5' ),
						'css-spinner-full-05' => sprintf( esc_html__( 'Overlay #%s', 'woopack' ), '6' ),
						'css-spinner' => sprintf( esc_html__( 'In title #%s', 'woopack' ), '1' ),
						'css-spinner-01' => sprintf( esc_html__( 'In title #%s', 'woopack' ), '2' ),
						'css-spinner-02' => sprintf( esc_html__( 'In title #%s', 'woopack' ), '3' ),
						'css-spinner-03' => sprintf( esc_html__( 'In title #%s', 'woopack' ), '4' ),
						'css-spinner-04' => sprintf( esc_html__( 'In title #%s', 'woopack' ), '5' ),
						'css-spinner-05' => sprintf( esc_html__( 'In title #%s', 'woopack' ), '6' ),
						'none' => esc_html__( 'None', 'woopack' ),
					),
					'default' => 'css-spinner-full',
					'condition' => '_filter_preset_options:style',
				),

				's__tx_sale' => array(
					'name' => esc_html__( 'Sale Text', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter sale button text', 'woopack' ),
					'section' => 'presets',
					'id'   => 's__tx_sale',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's__tx_instock' => array(
					'name' => esc_html__( 'Instock Text', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter instock button text', 'woopack' ),
					'section' => 'presets',
					'id'   => 's__tx_instock',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),

				's__tx_clearall' => array(
					'name' => esc_html__( 'Clear All Text', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter clear all button text', 'woopack' ),
					'section' => 'presets',
					'id'   => 's__tx_clearall',
					'default' => '',
					'condition' => '_filter_preset_options:style',
				),


				'a_enable' => array(
					'name' => esc_html__( 'Enable', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to use adoptive filtering in current preset', 'woopack' ),
					'section' => 'presets',
					'id'   => 'a_enable',
					'default' => 'no',
					'condition' => '_filter_preset_options:adoptive',
				),

				'a_active_on' => array(
					'name' => esc_html__( 'Active On', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select when to activate adoptive filtering', 'woopack' ),
					'section' => 'presets',
					'id'   => 'a_active_on',
					'options'   => array(
						'always' => esc_html__( 'Always active', 'woopack' ),
						'permalink' => esc_html__( 'Active on permalinks and filters', 'woopack' ),
						'filter' => esc_html__( 'Active on filters', 'woopack' ),
					),
					'default' => 'no',
					'condition' => '_filter_preset_options:adoptive',
				),

				'a_depend_on' => array(
					'name' => esc_html__( 'Depend On', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select taxonomy terms can depend on', 'woopack' ),
					'section' => 'presets',
					'id'   => 'a_depend_on',
					'options' => 'ajax:product_taxonomies:has_none',
					'default' => '',
					'condition' => '_filter_preset_options:adoptive',
				),

				'a_term_counts' => array(
					'name' => esc_html__( 'Product Count', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select adoptive product count display', 'woopack' ),
					'section' => 'presets',
					'id'   => 'a_term_counts',
					'options'   => array(
						'default' => esc_html__( 'Filtered count / Total', 'woopack' ),
						'count' => esc_html__( 'Filtered count', 'woopack' ),
						'total' => esc_html__( 'Total', 'woopack' ),
					),
					'default' => 'default',
					'condition' => '_filter_preset_options:adoptive',
				),

				'a_reorder_selected' => array(
					'name' => esc_html__( 'Reorder Terms', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Reorder remaining terms to the top', 'woopack' ),
					'section' => 'presets',
					'id'   => 'a_reorder_selected',
					'default' => 'no',
					'condition' => '_filter_preset_options:adoptive',
				),

				'r_behaviour' => array(
					'name' => esc_html__( 'Responsive Behaviour', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Set filter preset behaviour on defined resolution', 'woopack' ),
					'section' => 'presets',
					'id'   => 'r_behaviour',
					'options'   => array(
						'none' => esc_html__( 'Do not do a thing', 'woopack' ),
						'switch' => esc_html__( 'Switch with filter preset', 'woopack' ),
						'hide' => esc_html__( 'Show on screen resolution smaller than', 'woopack' ),
						'show' => esc_html__( 'Show on screen resolution larger than', 'woopack' ),
					),
					'default' => 'none',
					'condition' => '_filter_preset_options:responsive',
					'class' => 'csl-refresh-active-tab',
				),

				'r_resolution' => array(
					'name' => esc_html__( 'Responsive Resolution', 'woopack' ),
					'type' => 'number',
					'desc' => esc_html__( 'Set screen resolution in pixels that will trigger the responsive behaviour', 'woopack' ),
					'section' => 'presets',
					'id'   => 'r_resolution',
					'default' => '768',
					'condition' => '_filter_preset_options:responsive',
				),

				'r_preset' => array(
					'name' => esc_html__( 'Preset', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Set filter preset', 'woopack' ),
					'section' => 'presets',
					'id'   => 'r_preset',
					'options' => 'function:__make_presets',
					'default' => '',
					'condition' => '_filter_preset_options:responsive&&r_behaviour:switch',
				),

				'filters' => array(
					'name' => esc_html__( 'Filters', 'woopack' ),
					'type' => 'list-select',
					'id'   => 'filters',
					'desc' => esc_html__( 'Add more filters to the current preset', 'woopack' ),
					'section' => 'presets',
					'title' => esc_html__( 'Filter', 'woopack' ),
					'supports' => array( 'customizer' ),
					'options' => 'list',
					'selects' => array(
						'taxonomy' => esc_html__( 'Taxonomy', 'woopack' ),
						'range' => esc_html__( 'Taxonomy Range', 'woopack' ),
						'meta' => esc_html__( 'Meta', 'woopack' ),
						'meta_range' => esc_html__( 'Meta Range', 'woopack' ),
						'vendor' => esc_html__( 'Vendor', 'woopack' ),
						'orderby' => esc_html__( 'Order by', 'woopack' ),
						'search' => esc_html__( 'Search', 'woopack' ),
						'instock' => esc_html__( 'Availability', 'woopack' ),
						'price' => esc_html__( 'Price', 'woopack' ),
						'price_range' => esc_html__( 'Price Range', 'woopack' ),
						'per_page' => esc_html__( 'Per page', 'woopack' ),
					),
					'settings' => self::__build_filters(),
					'condition' => '_filter_preset_options:filters',
					'val' => '',
				)

			) );

			$plugins['product_filter']['settings'] = array_merge( $plugins['product_filter']['settings'], self::__build_overrides() );

			$plugins['product_filter']['settings']['supported_overrides'] = array(
				'name' => esc_html__( 'Select Taxonomies', 'woopack' ),
				'type' => 'multiselect',
				'desc' => esc_html__( 'Select supported taxonomies for Presets Manager (needs a Save and page refresh to take effect!)', 'woopack' ),
				'section' => 'manager',
				'id'   => 'supported_overrides',
				'options' => 'ajax:product_taxonomies',
				'default' => '',
				'class' => '',
			);

			$plugins['product_filter']['settings'] = array_merge( $plugins['product_filter']['settings'], array(

				'variable_images' => array(
					'name' => esc_html__( 'Switch Variable Images', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to switch variable images when filtering attributes', 'woopack' ),
					'section' => 'general',
					'id'   => 'variable_images',
					'default' => 'no',
				),

				'clear_all' => array(
					'name' => esc_html__( 'Clear All', 'woopack' ),
					'type' => 'multiselect',
					'desc' => esc_html__( 'Select taxonomies which Clear All button cannot clear', 'woopack' ),
					'section' => 'general',
					'id'   => 'clear_all',
					'options' => 'ajax:product_taxonomies',
					'default' => '',
				),

				'register_taxonomy' => array(
					'name' => esc_html__( 'Register Taxonomy', 'woopack' ),
					'type' => 'list',
					'id'   => 'register_taxonomy',
					'desc' => esc_html__( 'Register custom product taxonomies (needs a Save and page refresh to take effect!)', 'woopack' ),
					'section' => 'general',
					'title' => esc_html__( 'Name', 'woopack' ),
					'options' => 'list',
					'default' => '',
					'settings' => array(
						'name' => array(
							'name' => esc_html__( 'Plural name', 'woopack' ),
							'type' => 'text',
							'id' => 'name',
							'desc' => esc_html__( 'Enter plural taxonomy name', 'woopack' ),
							'default' => '',
						),
						'single_name' => array(
							'name' => esc_html__( 'Singular name', 'woopack' ),
							'type' => 'text',
							'id' => 'single_name',
							'desc' => esc_html__( 'Enter singular taxonomy name', 'woopack' ),
							'default' => '',
						),
						'hierarchy' => array(
							'name' => esc_html__( 'Use hierarchy', 'woopack' ),
							'type' => 'checkbox',
							'id'   => 'hierarchy',
							'desc' => esc_html__( 'Enable hierarchy for this taxonomy', 'woopack' ),
							'default' => 'no',
						),
					),
				),

				'hide_empty' => array(
					'name' => esc_html__( 'Hide Empty Terms', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Hide empty terms', 'woopack' ),
					'section' => 'general',
					'id'   => 'hide_empty',
					'default' => 'no',
				),

				'enable' => array(
					'name' => esc_html__( 'Use AJAX', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to use AJAX in Shop', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'enable',
					'default' => 'no',
					'class' => 'csl-refresh-active-tab',
				),

				'automatic' => array(
					'name' => esc_html__( 'Automatic AJAX', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to use automatic AJAX installation.', 'woopack' ) . ' <strong>' . ( isset( $ajax['recognized'] ) ? esc_html__( 'Theme supported! AJAX is set for', 'woopack' ) . ' ' . esc_html( $ajax['name'] ) : esc_html__( 'Theme not found in database. Using default settings.', 'woopack' ) ) . '</strong>',
					'section' => 'ajax',
					'id'   => 'automatic',
					'default' => 'yes',
					'class' => 'csl-refresh-active-tab',
					'condition' => 'enable:yes',
				),

				'wrapper' => array(
					'name' => esc_html__( 'Product Wrapper', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter product wrapper jQuery selector.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['wrapper'] ) ? esc_html( $ajax['wrapper'] ) : '' ),
					'section' => 'ajax',
					'id'   => 'wrapper',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'category' => array(
					'name' => esc_html__( 'Product Category', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter product category jQuery selector.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['category'] ) ? esc_html( $ajax['category'] ) : '' ),
					'section' => 'ajax',
					'id'   => 'category',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'product' => array(
					'name' => esc_html__( 'Product', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter product jQuery selector.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['product'] ) ? esc_html( $ajax['product'] ) : '' ),
					'section' => 'ajax',
					'id'   => 'product',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'columns' => array(
					'name' => esc_html__( 'Columns', 'woopack' ),
					'type' => 'number',
					'desc' => esc_html__( 'Fix columns problems', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'columns',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'rows' => array(
					'name' => esc_html__( 'Rows', 'woopack' ),
					'type' => 'number',
					'desc' => esc_html__( 'Fix rows problems', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'rows',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'result_count' => array(
					'name' => esc_html__( 'Result Count', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter result count jQuery selector.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['result_count'] ) ? esc_html( $ajax['result_count'] ) : '' ),
					'section' => 'ajax',
					'id'   => 'result_count',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'order_by' => array(
					'name' => esc_html__( 'Order By', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter order by jQuery selector.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['order_by'] ) ? esc_html( $ajax['order_by'] ) : '' ),
					'section' => 'ajax',
					'id'   => 'order_by',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'pagination' => array(
					'name' => esc_html__( 'Pagination', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter pagination jQuery selector.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['pagination'] ) ? esc_html( $ajax['pagination'] ) : '' ),
					'section' => 'ajax',
					'id'   => 'pagination',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'pagination_function' => array(
					'name' => esc_html__( 'Pagination Function', 'woopack' ),
					'type' => 'text',
					'desc' => esc_html__( 'Enter pagination function.', 'woopack' ) . ' ' . esc_html( 'Currently set to', 'woopack' ) . ': ' . ( isset( $ajax['pagination_function'] ) ? esc_html( $ajax['pagination_function'] ) : 'woocommerce_pagination' ),
					'section' => 'ajax',
					'id'   => 'pagination_function',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'pagination_type' => array(
					'name' => esc_html__( 'Pagination Type', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select pagination type', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'pagination_type',
					'options' => array(
						'default' => esc_html__( 'Default (Theme)', 'woopack' ),
						'prdctfltr-pagination-default' => esc_html__( 'Custom pagination (Product Filter)', 'woopack' ),
						'prdctfltr-pagination-load-more' => esc_html__( 'Load more (Product Filter)', 'woopack' ),
						'prdctfltr-pagination-infinite-load' => esc_html__( 'Infinite load (Product Filter)', 'woopack' ),
					),
					'default' => 'default',
					'condition' => 'enable:yes',
				),

				'failsafe' => array(
					'name' => esc_html__( 'Failsafe', 'woopack' ),
					'type' => 'multiselect',
					'desc' => esc_html__( 'Select which missing element will not trigger AJAX and will reload the page', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'failsafe',
					'options' => array(
						'wrapper' => esc_html__( 'Products wrapper', 'woopack' ),
						'product' => esc_html__( 'Products', 'woopack' ),
						'pagination' => esc_html__( 'Pagination', 'woopack' ),
					),
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'js' => array(
					'name' => esc_html__( 'After AJAX JS', 'woopack' ),
					'type' => 'textarea',
					'desc' => esc_html__( 'Enter JavaScript or jQuery code to execute after AJAX', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'js',
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'animation' => array(
					'name' => esc_html__( 'Product Animation', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select product animation after AJAX', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'animation',
					'options' => array(
						'none' => esc_html__( 'No animation', 'woopack' ),
						'default' => esc_html__( 'Fade in products', 'woopack' ),
						'random' => esc_html__( 'Fade in random products', 'woopack' ),
					),
					'default' => '',
					'condition' => 'enable:yes',
				),

				'scroll_to' => array(
					'name' => esc_html__( 'Scroll To', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select scroll to after AJAX', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'scroll_to',
					'options' => array(
							'none' => esc_html__( 'Disable scroll', 'woopack' ),
							'filter' => esc_html__( 'Filter', 'woopack' ),
							'products' => esc_html__( 'Products', 'woopack' ),
							'top' => esc_html__( 'Page top', 'woopack' ),
					),
					'default' => '',
					'condition' => 'enable:yes',
				),

				'permalinks' => array(
					'name' => esc_html__( 'Browser URL/Permalinks', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Select how to display browser URLs or permalinks on AJAX', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'permalinks',
					'options' => array(
						'no' => esc_html__( 'Use Product Filter redirects', 'woopack' ),
						'query' => esc_html__( 'Only add query parameters', 'woopack' ),
						'yes' => esc_html__( 'Disable URL changes', 'woopack' ),
					),
					'default' => '',
					'condition' => 'enable:yes',
				),

				'dont_load' => array(
					'name' => esc_html__( 'Disable Elements', 'woopack' ),
					'type' => 'multiselect',
					'desc' => esc_html__( 'Select which elements will not be used with AJAX', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'dont_load',
					'options' => array(
						'breadcrumbs' => esc_html__( 'Breadcrumbs', 'woopack' ),
						'title' => esc_html__( 'Shop title', 'woopack' ),
						'desc' => esc_html__( 'Shop description', 'woopack' ),
						'result' => esc_html__( 'Result count', 'woopack' ),
						'orderby' => esc_html__( 'Order By', 'woopack' ),
					),
					'default' => '',
					'condition' => 'enable:yes&&automatic:no',
				),

				'force_product' => array(
					'name' => esc_html__( 'Post Type', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to add the ?post_type=product parameter when filtering', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'force_product',
					'default' => 'no',
					'condition' => 'enable:no',
				),

				'force_action' => array(
					'name' => esc_html__( 'Stay on Permalink ', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to force filtering on same permalink (URL)', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'force_action',
					'default' => 'no',
					'condition' => 'enable:no',
				),

				'force_redirects' => array(
					'name' => esc_html__( 'Permalink Structure', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Set permalinks structure', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'force_redirects',
					'options' => array(
						'no' => esc_html__( 'Use Product Filter redirects', 'woopack' ),
						'yes' => esc_html__( 'Use .htaccess and native WordPress redirects', 'woopack' ),
					),
					'default' => 'no',
					'condition' => 'enable:no',
				),

				'remove_single_redirect' => array(
					'name' => esc_html__( 'Single Product', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to remove redirect when only one product is found', 'woopack' ),
					'section' => 'ajax',
					'id'   => 'remove_single_redirect',
					'default' => 'no',
					'condition' => 'enable:no',
				),

				'actions' => array(
					'name' => esc_html__( 'Integration Hooks', 'woopack' ),
					'type' => 'list',
					'id'   => 'actions',
					'desc' => esc_html__( 'Add filter presets to hooks', 'woopack' ),
					'section' => 'integration',
					'title' => esc_html__( 'Name', 'woopack' ),
					'options' => 'list',
					'default' => '',
					'settings' => array(
						'name' => array(
							'name' => esc_html__( 'Name', 'woopack' ),
							'type' => 'text',
							'id' => 'name',
							'desc' => esc_html__( 'Enter name', 'woopack' ),
							'default' => '',
						),
						'hook' => array(
							'name' => esc_html__( 'Common Hooks', 'woopack' ),
							'type' => 'select',
							'id'   => 'hook',
							'desc' => esc_html__( 'Select a common hook', 'woopack' ),
							'options' => array(
								'' => esc_html__( 'Use custom hook', 'woopack' ),
								'woocommerce_before_main_content' => 'woocommerce_before_main_content',
								'woocommerce_archive_description' => 'woocommerce_archive_description',
								'woocommerce_before_shop_loop' => 'woocommerce_before_shop_loop',
								'woocommerce_after_shop_loop' => 'woocommerce_after_shop_loop',
								'woocommerce_after_main_content' => 'woocommerce_after_main_content',
							),
							'default' => '',
						),
						'action' => array(
							'name' => esc_html__( 'Custom Hook', 'woopack' ),
							'type' => 'text',
							'id'   => 'action',
							'desc' => esc_html__( 'If you use custom hook, rather than common hooks, please enter it here', 'woopack' ),
							'default' => '',
						),
						'priority' => array(
							'name' => esc_html__( 'Priority', 'woopack' ),
							'type' => 'number',
							'id'   => 'priority',
							'desc' => esc_html__( 'Set hook priority', 'woopack' ),
							'default' => '',
						),
						'preset' => array(
							'name' => esc_html__( 'Preset', 'woopack' ),
							'type' => 'select',
							'id'   => 'preset',
							'desc' => esc_html__( 'Set filter preset', 'woopack' ),
							'options' => 'function:__make_presets',
							'default' => '',
							'class' => 'csl-selectize',
						),
						'disable_overrides' => array(
							'name' => esc_html__( 'Presets Manager', 'woopack' ),
							'type' => 'checkbox',
							'id'   => 'disable_overrides',
							'desc' => esc_html__( 'Disable presets manager settings', 'woopack' ),
							'default' => '',
						),
						'id' => array(
							'name' => esc_html__( 'ID', 'woopack' ),
							'type' => 'text',
							'id'   => 'id',
							'desc' => esc_html__( 'Enter filter element ID attribute', 'woopack' ),
							'default' => '',
						),
						'class' => array(
							'name' => esc_html__( 'Class', 'woopack' ),
							'type' => 'text',
							'id'   => 'class',
							'desc' => esc_html__( 'Enter filter element class attribute', 'woopack' ),
							'default' => '',
						),
					),
				),

				'el_result_count' => array(
					'name' => esc_html__( 'Result Count Integration', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Replace WooCommerce result count element with a product filter', 'woopack' ),
					'section' => 'integration',
					'id'   => 'el_result_count',
					'options' => 'function:__make_presets:template',
					'default' => '_do_not',
				),

				'el_orderby' => array(
					'name' => esc_html__( 'Order By Integration', 'woopack' ),
					'type' => 'select',
					'desc' => esc_html__( 'Replace WooCommerce order by element with a product filter', 'woopack' ),
					'section' => 'integration',
					'id'   => 'el_orderby',
					'options' => 'function:__make_presets:template',
					'default' => '_do_not',
				),

				'widget_notice' => array(
					'name' => esc_html__( 'Widget Integration', 'woopack' ),
					'type' => 'html',
					'desc' => '
					<div class="csl-option-header"><h3>' . esc_html__( 'Widget Integration', 'woopack' ) . '</h3></div><div class="csl-option-wrapper"><div class="csl-notice csl-info">' . esc_html__( 'Looking for widget integration options? Product Filter widgets are added to sidebars in the WordPress Widgets screen.', 'woopack' ) . ' <a href="' . admin_url( 'widgets.php' ) . '">' . esc_html__( 'Click here to navigate to WordPress Widgets', 'woopack' ) . '</a><br /><br />' . esc_html__( 'If theme that you are using has limited sidebar options, try plugins such as', 'woopack' ) . ' ' . '<a href="https://wordpress.org/plugins/woosidebars/">WooSidebars</a>, <a href="https://wordpress.org/plugins/custom-sidebars/">Custom Sidebars</a></div></div>',
					'section' => 'integration',
					'id'   => 'widget_notice',
				),

				'analytics' => array(
					'name' => esc_html__( 'Use Analytics', 'woopack' ),
					'type' => 'checkbox',
					'desc' => esc_html__( 'Check this option to use filtering analytics', 'woopack' ),
					'section' => 'analytics',
					'id'   => 'analytics',
					'default' => 'no',
				),

				'analytics_ui' => array(
					'name' => esc_html__( 'Filtering Analytics', 'woopack' ),
					'type' => 'html',
					'desc' => '
					<div class="csl-option-header"><h3>' . esc_html__( 'Filtering Analytics', 'woopack' ) . '</h3></div><div class="csl-option-wrapper">' . self::filtering_ananlytics() . '</div>',
					'section' => 'analytics',
					'id'   => 'analytics_ui',
				),

			) );

			return SevenVX()->_do_options( $plugins, self::$plugin['label'] );
		}

		public static function __build_overrides() {
			if ( empty( self::$settings['options']['general']['supported_overrides'] ) ) {
				return array();
			}

			$array = array();

			foreach( self::$settings['options']['general']['supported_overrides'] as $taxonomy ) {

				if ( taxonomy_exists( $taxonomy ) ) {

					$taxonomy = get_taxonomy( $taxonomy );

					$array['_pf_manager_' . $taxonomy->name] = array(
						'name' => $taxonomy->label . ' ' . esc_html__( 'Presets', 'woopack' ),
						'type' => 'list',
						'id'   => '_pf_manager_' . $taxonomy->name,
						'desc' => esc_html__( 'Add filter presets for', 'woopack' ) . ' ' . $taxonomy->label,
						'section' => 'manager',
						'title' => esc_html__( 'Name', 'woopack' ),
						'options' => 'list',
						'default' => '',
						'settings' => array(
							'name' => array(
								'name' => esc_html__( 'Name', 'woopack' ),
								'type' => 'text',
								'id' => 'name',
								'desc' => esc_html__( 'Enter name', 'woopack' ),
								'default' => '',
							),
							'term' => array(
								'name' => esc_html__( 'Term', 'woopack' ),
								'type' => 'select',
								'id'   => 'term',
								'desc' => esc_html__( 'Choose term, that when selected, will show the set filter preset', 'woopack' ),
								'options' => 'ajax:taxonomy:' . $taxonomy->name . ':has_none:no_lang',
								'default' => '',
								'class' => 'csl-selectize',
							),
							'preset' => array(
								'name' => esc_html__( 'Preset', 'woopack' ),
								'type' => 'select',
								'id'   => 'preset',
								'desc' => esc_html__( 'Set filter preset', 'woopack' ),
								'options' => 'function:__make_presets:has_none',
								'default' => '',
								'class' => 'csl-selectize',
							),
						),
					);
				}
			}

			return $array;

		}

		public static function filtering_ananlytics() {
			ob_start();

			$stats = get_option( '_prdctfltr_analytics', array() );

			if ( empty( $stats ) ) {
			?>
				<div class="csl-notice csl-info">
				<?php
					esc_html_e( 'Filtering analytics are empty!', 'woopack' );
				?>
				</div>
			<?php
			}
			else {
			?>
				<div class="pf-analytics-wrapper">
			<?php
				foreach( $stats as $k => $v ) {
					$show = array();
				?>
					<div class="pf-analytics">
					<?php
						$mode = 'default';
						if ( taxonomy_exists( $k ) ) {
							$mode = 'taxonomy';
							if ( substr( $k, 0, 3 ) == 'pa_' ) {
								$label = wc_attribute_label( $k );
							}
							else {
								if ( $k == 'product_cat' ) {
									$label = esc_html__( 'Categories', 'woopack' );
								}
								else if ( $k == 'product_tag' ) {
									$label = esc_html__( 'Tags', 'woopack' );
								}
								else if ( $k == 'characteristics' ) {
									$label = esc_html__( 'Characteristics', 'woopack' );
								}
								else {
									$curr_term = get_taxonomy( $k );
									$label = $curr_term->name;
								}
							}
						}

						if ( $mode == 'taxonomy' ) {
							if ( !empty( $v ) && is_array( $v ) ) {
								foreach( $v as $vk => $vv ) {
									$term = get_term_by( 'slug', $vk, $k );

									if ( isset( $term->name ) ) {
										$term_name = ucfirst( $term->name ) . ' ( ' . $v[$vk] .' )';
									}
									else {
										$term_name = 'Unknown';
									}

									$show[$term_name] = $v[$vk];
								}
								$title = ucfirst( $label );
							}
						}
						else {
							$title = ucfirst( $k );
						}

					?>
					<div class="pf-analytics-info">
						<strong><?php echo esc_html( $title ); ?></strong>
					</div>
					<div id="<?php echo uniqid( 'pf-analytics-chart-' ); ?>" class="pf-analytics-chart" data-chart="<?php echo esc_attr( json_encode( $show ) ); ?>"></div>
				</div>
			<?php
				}
		?>
			</div>
			<div class="pf-analytics-settings">
				<div class="csl-notice csl-info">
					<?php esc_html_e( 'Click the button to reset filtering analytics.', 'woopack' ); ?><br /><br />
					<span id ="pf-analytics-reset" class="csl-button"><?php esc_html_e( 'Reset analytics', 'woopack' ); ?></span>
				</div>
			</div>
		<?php
			}
			return ob_get_clean();
		}

		public static function analytics_reset() {
			delete_option( '_prdctfltr_analytics' );

			wp_die(1);
			exit;
		}

		public static function stripslashes_deep( $value ) {
			$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
			return $value;
		}

	}

	wop_Product_Filters_Settings::init();


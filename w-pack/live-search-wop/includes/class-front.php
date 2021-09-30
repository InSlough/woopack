<?php

	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_LiveSearch_Frontend {

		protected static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
            }

            return self::$_instance;

		}

		function __construct() {
            $this->init_front();
        }

        function init_front() {
            add_shortcode( 'live_search_wop', array( $this, 'shortcode' ) );

            add_action( 'wp_ajax_nopriv_wpc_live_search', array( $this, 'respond' ) );
            add_action( 'wp_ajax_wpc_live_search', array( $this, 'respond' ) );

            add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
            add_action( 'wp_footer', array( $this, 'check_scripts' ) );

            add_filter( 'posts_search', array( $this, '_extended_search' ), 99999, 2 );

            add_action( 'wop_live_search_taxonomies_cache', array( $this, '_taxonomies_cache' ) );
            add_action( 'csl_ajax_saved_settings_live_search_wop', array( $this, '_taxonomies_cache' ) );

            add_filter( 'wop__add_meta_information_used', array( $this, 'info' ) );
		}

		function info( $val ) {
			return array_merge( $val, array( 'Live Search for WooCommerce' ) );
		}

        function scripts() {
			wp_register_script( 'live-search-wop-js', wop_LiveSearch()->plugin_url() . '/assets/js/scripts.js', array( 'jquery' ), wop_LiveSearch()->version(), true );
            wp_enqueue_script( 'live-search-wop-js' );

            wp_register_style ( 'live-search-wop-css', wop_LiveSearch()->plugin_url() . '/assets/css/styles' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, wop_LiveSearch()->version() );
            wp_enqueue_style( 'live-search-wop-css' );
        }

        function check_scripts() {
            if ( wp_script_is( 'live-search-wop-js', 'enqueued' ) ) {
                $args = array(
                    'ajax' => admin_url( 'admin-ajax.php' ),
                    'characters' => absint( SevenVXGet()->get_option( 'characters', 'live_search_wop', 2 ) ),
                    'localize' => array(
                        'notfound' => esc_html( $this->_get_notfound() ),
                    ),
                    'es' => get_option( '_csl_settings_live_search_wop_cache', array() ),
                );

                wp_localize_script( 'live-search-wop-js', 'ls', $args );
            }

        }

        function shortcode( $atts, $content = null ) {
			$atts = shortcode_atts( array(
                'id'       => '',
                'class'    => '',
                'category' => '',
                'callout'  => 'no',
            ), $atts );

            echo $this->_get_live_search_element( $atts );
        }

        function _get_notfound() {
            $notfound = SevenVXGet()->get_option( 'notfound', 'live_search_wop', '' );

            if ( empty( $notfound ) ) {
                return esc_html__( 'No products found', 'live-search-wop' );
            }

            return $notfound;
        }

        function _get_placeholder() {
            $placeholder = SevenVXGet()->get_option( 'placeholder', 'live_search_wop', '' );

            if ( empty( $placeholder ) ) {
                return esc_html__( 'Enter keywords', 'live-search-wop' );
            }

            return $placeholder;
        }

        function _get_products() {
            $num = absint( SevenVXGet()->get_option( 'products', 'live_search_wop', 10 ) );

            return $num > 0 ? $num : 10;
        }

        function _get_separator() {
            $separator = SevenVXGet()->get_option( 'separator', 'live_search_wop', '' );

            if ( $separator == '' ) {
                $separator = ( is_rtl() ? '<' : '>' );
            }

            return '<span class="wpc--ls-separator">' . esc_html( $separator ) . '</span>';
        }

        function _build_query() {
            $string = '';
            $category = '';

            if ( isset( $_POST['settings'] ) ) {
                $string = esc_html( $_POST['settings'][0] );
                $category = sanitize_title( $_POST['settings'][1] );
            }

            $query = array(
                'wpc__ls'      => true,
                'post_status'  => 'publish',
                's'            => $string,
                'orderby'      => 'relevance',
                'limit'        => $this->_get_products(),
            );

            if ( !empty( $category ) ) {
                $query['category'] = array( $category );
            }

            return apply_filters( 'live_search_wop_query', $query );
        }

        function respond() {
            $products = array();

            $query = wc_get_products( $this->_build_query() );

            if ( $query ) {
                foreach( $query as $product ) {
                    $products[] = array(
                        'id' => absint( $product->get_id() ),
                        'path' => wp_kses_post( $this->_get_trail( $product->get_category_ids() ) ),
                        'title' => wp_kses_post( $this->_get_separator() . '<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_title() ) . '</a>' ),
                        'image' => wp_kses_post( '<a href="' . esc_url( $product->get_permalink() ) . '">' . $product->get_image() . '</a>' ),
                        'price' => strip_tags( $product->get_price_html(), '<del>' ),
                    );
                }
            }

            wp_send_json( $products );
            exit;
        }

        function _get_trail( $ids ) {
            if ( $ids[0] ) {
                $term_id = $ids[0];
            }

            while ( $term_id ) {
                $term = get_term( $term_id, 'product_cat' );

                $parents[] = sprintf( '<a href="%s">%s</a>', esc_url( get_term_link( $term, 'product_cat' ) ), $term->name );

                $term_id = $term->parent;
            }

            array_reverse( $parents );

            return implode( $this->_get_separator(), $parents );
        }

        function _get_live_search_element( $atts ) {
            $id = $this->_get_element_id( $atts['id'] );

            ob_start();

            if ( $atts['callout'] == "yes" ) {
?>
                <div class="wpc--ls-callout<?php esc_attr_e( function_exists( 'SesXIcon' ) ? " is-in-theme" : "" ); ?>" data-callout="<?php esc_attr_e( $id ); ?>"><?php function_exists( 'SesXIcon' ) && SesXIcon()->icon( 'search' ); ?></div>
<?php
            }
?>
            <div id="<?php esc_attr_e( $id ); ?>" class="<?php esc_attr_e( $this->_get_element_class( $atts['class'] ) ); ?>" data-category="<?php esc_attr_e( $this->_get_element_category( $atts['category'] ) ); ?>">
                <input class="wpc--ls-input" type="text" placeholder="<?php esc_attr_e( $this->_get_placeholder() ); ?>"/>
                <button class="wpc--ls-button"></button>
            </div>
<?php
            return ob_get_clean();
        }

        function _get_element_id( $id ) {
            if ( !empty(  $id ) ) {
               return  $id;
            }

            return uniqid( 'wpc--ls-' );
        }

        function _get_element_class( $class ) {
            if ( !empty( $class ) ) {
                return 'wpc--ls-element ' . $class;
            }

            return 'wpc--ls-element';
        }

        function _get_element_category( $category ) {
            $category = sanitize_title( $category );

            if ( term_exists( $category, 'product_cat' ) ) {
                return $category;
            }

            return '';
        }

        function _cache_start( $options ) {
			$job = wp_next_scheduled( 'wop_live_search_taxonomies_cache' );

			if ( ! empty( SevenVXGet()->get_option( 'taxonomies', 'live_search_wop', array() ) ) ) {
				if ( $job ) {
					wp_unschedule_event( $job, 'wop_live_search_taxonomies_cache' );
				}

				wp_schedule_event( time(), SevenVXGet()->get_option( 'interval', 'live_search_wop', 'saved' ), 'wop_live_search_taxonomies_cache' );
			}
			else {
				if ( $job ) {
					wp_unschedule_event( $job, 'wop_live_search_taxonomies_cache' );
				}
			}
		}

        function _taxonomies_cache() {
            $cache = array();
            $taxonomies = SevenVXGet()->get_option( 'taxonomies', 'live_search_wop', array() );

            foreach( $taxonomies as $taxonomy ) {
                $terms = get_terms( $taxonomy, array(
                    'hide_empty' => false,
                    'fields' => 'id=>name',
                ) );

                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $id => $name ) {
                        $cache[] = array(
                            $id,
                            $name,
                            $taxonomy,
                        );
                    }
                }
            }

            if ( ! empty( $cache ) ) {
                update_option( '_csl_settings_live_search_wop_cache', $cache, false );
            }
        }

        function _get_meta_keys_query_product_ids( $terms ) {
            $meta_keys  = SevenVXGet()->get_option( 'meta_keys', 'live_search_wop', array() );

            if ( ! empty( $meta_keys ) && is_array( $meta_keys )  ) {
                foreach( $meta_keys as $meta_key ) {
                    if ( array_key_exists( $meta_key, apply_filters( 'live_search_wop_meta_keys', array(
                        '_sku' => esc_html__( 'SKU', 'woopack' ),
                    ) ) ) ) {
                        $meta_query[] = array(
                            'key'     => esc_attr( $meta_key ),
                            'value'   => $terms,
                        );
                    }
                }

                if ( ! empty( $meta_query ) ) {
                    if (count($meta_keys)>1) {
                        $meta_query['relation'] = 'OR';
                    }

                    $meta_query_ids = (array) get_posts( array(
                        'posts_per_page'  => $this->_get_products(),
                        'post_type'       => 'product',
                        'post_status'     => 'publish',
                        'fields'          => 'ids',
                        'meta_query'      => $meta_query,
                    ) );

                    if ( ! empty( $meta_query_ids ) ) {
                        return $meta_query_ids;
                    }
                }
            }

            return array();
        }

        function _get_taxonomy_query_product_ids() {
            if ( ! empty( $_POST['settings'][2] ) && is_array( $_POST['settings'][2] ) ) {
                $data = $_POST['settings'][2];

                foreach( $data as $item ) {
                    if ( isset( $item['taxonomy'] ) && isset( $item['id'] ) ) {
                        $tax_query[] = array(
                            'taxonomy' => $item['taxonomy'],
                            'field'    => 'id',
                            'terms'    => $item['id'],
                        );
                    }
                }

                if ( ! empty( $tax_query ) ) {
                    if (count($data)>1) {
                        $tax_query['relation'] = 'OR';
                    }

                    $tax_query_ids = (array) get_posts( array(
                        'posts_per_page'  => $this->_get_products(),
                        'post_type'       => 'product',
                        'post_status'     => 'publish',
                        'fields'          => 'ids',
                        'tax_query'       => $tax_query,
                    ) );

                    if ( ! empty( $tax_query_ids ) ) {
                        return $tax_query_ids;
                    }
                }
            }

            return array();
        }

        function _extended_search( $search, $query ) {
            if( empty( $search ) ) {
                return $search;
            }

            if ( !isset( $query->query_vars['wpc__ls'] ) ) {
                return $search;
            }

            $tax_product_ids = $this->_get_taxonomy_query_product_ids();
            $meta_product_ids = $this->_get_meta_keys_query_product_ids( $query->query_vars['search_terms'] );

            $product_ids = array_unique( array_merge( $tax_product_ids, $meta_product_ids ) );

            if ( count( $product_ids ) > 0 ) {
                global $wpdb;
                return str_replace( 'AND (((', "AND ((({$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")) OR (", $search);
            }

            return $search;
        }

    }

    add_action( 'init', array( 'wop_LiveSearch_Frontend', 'instance' ) );

	if ( !function_exists( 'wop__add_meta_information' ) ) {
		function wop__add_meta_information_action() {
			echo '<meta name="generator" content="woopack.com - ' . esc_attr( implode( ' - ', apply_filters( 'wop__add_meta_information_used', array() ) ) ) . '"/>';
		}
		function wop__add_meta_information() {
			add_action( 'wp_head', 'wop__add_meta_information_action', 99 );
		}
		wop__add_meta_information();
	}

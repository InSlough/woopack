<?php

	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_FloatingCart_Frontend {

        protected static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
            }

            return self::$_instance;

		}

		function __construct() {
            add_shortcode( 'floating_cart_wop', array( $this, 'shortcode' ) );

            add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
            add_action( 'wp_footer', array( $this, 'footer_scripts' ) );

            add_action( 'woocommerce_add_to_cart_fragments', array( $this, 'add_ajax_fragment' ) );

            add_action( 'wp_footer', array( $this, 'add_floating_cart' ), 0 );

			add_filter( 'wop__add_meta_information_used', array( $this, 'info' ) );
		}

		function info( $val ) {
			return array_merge( $val, array( 'Floating Cart for WooCommerce' ) );
		}

        function scripts() {
			wp_register_script( 'floating-cart-wop-js', wop_FloatingCart()->plugin_url() . '/assets/js/scripts.js', array( 'jquery' ), wop_FloatingCart()->version(), true );
            wp_enqueue_script( 'floating-cart-wop-js' );

            wp_register_style ( 'floating-cart-wop-css', wop_FloatingCart()->plugin_url() . '/assets/css/styles' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, wop_FloatingCart()->version() );
            wp_enqueue_style( 'floating-cart-wop-css' );
        }

        function footer_scripts() {
            if ( wp_script_is( 'floating-cart-wop-js', 'enqueued' ) ) {
                $vars = array(
                    'ajax' => admin_url( 'admin-ajax.php' ),
                    'settings' => array(
                        SevenVXGet()->get_option( 'cart_content', 'floating_cart_wop', 'yes' ),
                        SevenVXGet()->get_option( 'cart_message', 'floating_cart_wop', 'yes' ),
                        SevenVXGet()->get_option( 'cart_message_delay', 'floating_cart_wop', 2500 ),
                    ),
                    'localize' => array(
                        esc_html( SevenVXGet()->get_option( 'cart_empty_text', 'floating_cart_wop', '' ) == '' ? esc_html__( 'Added to cart', 'woopack' ) : SevenVXGet()->get_option( 'cart_empty_text', 'floating_cart_wop', '' ) ),
                    ),
                );

                wp_localize_script( 'floating-cart-wop-js', 'fcart', $vars );
            }
        }

        function dequeue_scripts() {
            wp_dequeue_script( 'floating-cart-wop-js' );
        }

        function add_ajax_fragment( $fragments ) {
            $fragments['.floating-cart-total'] = '<span class="floating-cart-total">' . intval( $this->_get_cart_count() ) . '</span>';

            if ( SevenVXGet()->get_option( 'cart_content', 'floating_cart_wop', 'yes' ) == 'yes' ) {
                $fragments['.floating-cart-content'] = $this->get_cart_contents_ajax();
            }

            return $fragments;
        }

        function add_floating_cart() {
            $position = SevenVXGet()->get_option( 'position', 'floating_cart_wop', 'top-right' );

            switch ( SevenVXGet()->get_option( 'install_type', 'floating_cart_wop', 'everywhere' ) ) {
                case 'everywhere':
                    $this->go_cart( $position );

                    return;
                break;

                case 'woocommerce':
                    if ( is_woocommerce() ) {
                        $this->go_cart( $position );

                        return;
                    }
                break;

                default :
                break;
            }

            $pages = SevenVXGet()->get_option( 'pages', 'floating_cart_wop' );

            if ( !empty( $pages ) ) {
                $pages = explode( '|', $pages );

                if ( in_array( get_the_ID(), $pages ) ) {
                    $this->go_cart( $position );

                    return;
                }
            }

            if ( $position !== 'inline' ) {
                $this->dequeue_scripts();
            }
        }

        function go_cart( $position ) {
?>
            <div class="floating-cart floating-cart-<?php esc_attr_e( $position ); ?><?php esc_attr_e( function_exists( 'SesXIcon' ) ? " is-in-theme" : "" ); ?>">
<?php
                if ( SevenVXGet()->get_option( 'cart_message', 'floating_cart_wop', 'yes' ) == 'yes' ) {
                    $this->get_add_to_cart_message();
                }

                $this->get_cart();

                if ( SevenVXGet()->get_option( 'checkout', 'floating_cart_wop', 'yes' ) == 'yes' ) {
                    $this->get_checkout();
                }

                if ( SevenVXGet()->get_option( 'cart_content', 'floating_cart_wop', 'yes' ) == 'yes' ) {
                    $this->get_cart_contents();
                }

                if ( SevenVXGet()->get_option( 'my_account', 'floating_cart_wop', 'yes' ) == 'yes' ) {
                    $this->get_my_account();
                }
                ?>
              </div>
              <?php
                          if ( SevenVXGet()->get_option( 'cart_overlay', 'floating_cart_wop', 'yes' ) == 'yes' ) {
              ?>
                              <div class="floating-cart-overlay"></div>
              <?php
                          }
         }

        function get_cart_content_items() {

            if ( WC()->cart->is_empty() ) {
                $this->get_cart_content_empty();
            }
            else {
                $this->get_cart_links();
?>
                <ul class="floating-cart-items">
<?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                            $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
?>
                            <li class="floating-cart-item">
<?php
                                echo apply_filters(
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a href="%s" class="remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        esc_attr__( 'Remove this item', 'woocommerce' ),
                                        esc_attr( $product_id ),
                                        esc_attr( $cart_item_key ),
                                        esc_attr( $_product->get_sku() )
                                    ),
                                    $cart_item_key
                                );
?>
                                <div class="floating-cart-item-summary">
<?php
                                if ( empty( $product_permalink ) ) {
                                    echo wp_kses_post( $thumbnail . $product_name );
                                }
                                else {
?>
                                    <a href="<?php echo esc_url( $product_permalink ); ?>">
                                        <?php echo wp_kses_post( $thumbnail . $product_name ); ?>
                                    </a>
<?php
                                }

                                    $this->get_item_data( $cart_item );
?>
                                </div>
<?php
                                echo wp_kses_post( '<span class="floating-cart-item-total">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>' );
?>
                            </li>
<?php
                        }
                    }
?>
                </ul>
<?php
                $this->get_total();
            }
        }

        function get_item_data( $cart_item ) {
?>
            <div class="floating-cart-item-data">
<?php
                echo wp_strip_all_tags( wc_get_formatted_cart_item_data( $cart_item ), true );
?>
            </div>
<?php
        }

        function get_cart_contents() {
?>
            <div class="floating-cart-content">
<?php
                $this->get_cart_content_items();
?>
            </div>
<?php
        }

        function get_cart_contents_ajax() {

            ob_start();
?>
            <div class="floating-cart-content">
<?php
                $this->get_cart_content_items();
?>
            </div>
<?php
            return ob_get_clean();
        }

        function get_cart_content_empty() {
?>
            <div class="floating-cart-items"><?php echo esc_html( SevenVXGet()->get_option( 'cart_message_text', 'floating_cart_wop', '' ) == '' ? esc_html__( 'Your Cart is empty', 'woopack' ) : SevenVXGet()->get_option( 'cart_message_text', 'floating_cart_wop', '' ) ); ?></div>
<?php
            do_action( 'floating_cart_empty_wop' );
        }

        function get_cart_links() {
?>
            <div class="floating-cart-links">
                <a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'woopack'); ?></a>
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'To checkout &rarr;', 'woopack'); ?></a>
            </div>
<?php
        }

        function get_total() {
?>
            <div class="floating-cart-payment">
<?php
                esc_html_e( 'Total:', 'woopack');
?>
                <span>
<?php
                    echo wp_strip_all_tags( wc_price( WC()->cart->get_cart_contents_total() + WC()->cart->get_shipping_total() + WC()->cart->get_taxes_total( false, false ) ), true );
?>
                </span>
            </div>
<?php
        }

        function get_add_to_cart_message() {
?>
            <div class="floating-cart-message">

            </div>
<?php
        }

        function get_checkout() {
?>
            <div class="floating-cart-checkout">
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php function_exists( 'SesXIcon' ) && SesXIcon()->icon( 'checkout' ); ?></a>
            </div>
<?php
        }

        function get_my_account() {
?>
            <div class="floating-cart-my-account">
                <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#' ); ?>"><?php function_exists( 'SesXIcon' ) && SesXIcon()->icon( 'my-account' ); ?></a>
            </div>
<?php
        }

        function get_cart() {

?>
            <div class="floating-cart-cart">
                <a id="floating-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php function_exists( 'SesXIcon' ) && SesXIcon()->icon( 'cart' ); ?><?php $this->get_cart_count(); ?></a>
            </div>
<?php
        }

        function get_cart_count() {
            if ( function_exists( 'WC' ) && intval( WC()->cart->get_cart_contents_count() ) > 0 ) {
?>
                <span class="floating-cart-total"><?php echo intval( WC()->cart->get_cart_contents_count() ); ?></span>
<?php
            }
            else {
?>
                <span class="floating-cart-total">0</span>
<?php
            }
        }

        function _get_cart_count() {
            if ( function_exists( 'WC' ) && intval( WC()->cart->get_cart_contents_count() ) > 0 ) {
                return WC()->cart->get_cart_contents_count();
            }

            return 0;
        }

        function shortcode( $atts, $content = null ) {
            $atts = shortcode_atts( array(
                'id' => '',
                'class' => '',
                'position' => 'inline'
            ), $atts );

            return $this->_get_floating_cart_element( $atts );
        }

        function _get_floating_cart_element( $atts ) {
            ob_start();
?>
            <div id="<?php echo esc_attr( $this->_get_element_id( $atts['id'] ) ); ?>" class="<?php echo esc_attr( $this->_get_element_class( $atts['class'] ) ); ?>">
                <?php $this->go_cart( 'inline' ); ?>
            </div>
<?php
            return ob_get_clean();
        }


        function _get_element_id( $id ) {
            if ( !empty(  $id ) ) {
               return  $id;
            }

            return uniqid( 'wpc--fc-' );
        }

        function _get_element_class( $class ) {
            if ( !empty( $class ) ) {
                return 'wpc--ls-fc ' . $class;
            }

            return 'wpc--ls-fc';
        }

    }

    add_action( 'init', array( 'wop_FloatingCart_Frontend', 'instance' ) );

	if ( !function_exists( 'wop__add_meta_information' ) ) {
		function wop__add_meta_information_action() {
			echo '<meta name="generator" content="woopack.com - ' . esc_attr( implode( ' - ', apply_filters( 'wop__add_meta_information_used', array() ) ) ) . '"/>';
		}
		function wop__add_meta_information() {
			add_action( 'wp_head', 'wop__add_meta_information_action', 99 );
		}
		wop__add_meta_information();
	}

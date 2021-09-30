<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class wop_PriceCommander_Settings {

		protected static $_instance = null;

		public static $time = 0;
		public static $startTime = 0;
		public static $plugin;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {

			self::$plugin = array(
				'name' => 'Price Commander for WooCommerce',
				'wop' => 'Price Commander',
				'slug' => 'price-commander-wop',
				'label' => 'price_commander_wop',
				'image' => wop_PriceCommander()->plugin_url() . '/includes/images/price-commander-woopack.png',
				'path' => 'price-commander-wop/price-commander-wop',
				'version' => wop_PriceCommander::$version,
			);

			$this->includes();
		}

		function includes() {

			if ( isset( $_GET['page'], $_GET['tab'] ) && ( $_GET['page'] == 'wc-settings' ) && $_GET['tab'] == 'price_commander_wop' ) {
				add_filter( 'csl_plugins_settings', array( 'wop_PriceCommander_Settings', 'get_settings' ), 50 );
			}
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_footer', array( $this, 'add_templates' ), 9999999999 );

			add_action( 'wp_ajax_pc_ajax_factory', array( $this, 'pc_ajax_factory' ), 9999999999 );

			if ( function_exists( 'wop' ) ) {
				add_filter( 'wop_settings', array( $this, 'wop' ), 9999999191 );
				add_filter( 'wop_csl_get_price_commander_wop', array( 'wop_PriceCommander_Settings', '_get_settings_wop' ) );
			}

			add_filter( 'csl_plugins', array( $this, 'add_plugin' ), 0 );

		}

		public function wop( $settings ) {
			$settings['plugins'][] = self::$plugin;

			return $settings;
		}

		public function add_plugin( $plugins ) {
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

		public function scripts( $hook ) {

			if ( in_array( $hook, array( 'woocommerce_page_wc-settings' ) ) ) {
				$init = true;
			}

			if ( $hook == 'woocommerce_page_wc-settings' && isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] == 'wc-settings' && $_GET['tab'] == 'price_commander_wop' ) {
				$init = true;
			}

			if ( isset( $_GET['page']) && $_GET['page'] == 'woopack' ) {
				$init = true;
			}

			if ( !isset( $init ) ) {
				return false;
			}

			wp_register_script( 'price-commander-js', wop_PriceCommander()->plugin_url() . '/includes/js/admin.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-util' ), wop_PriceCommander()->version(), true );
			wp_enqueue_script( 'price-commander-js' );

			wp_localize_script( 'price-commander-js', 'pc', array(
				'ajax' => esc_url( admin_url( 'admin-ajax.php' ) ),
				'wc' => array(
					get_woocommerce_currency_symbol(),
					get_option( 'woocommerce_currency_pos', '' ),
					get_option( 'woocommerce_price_thousand_sep', '' ),
					get_option( 'woocommerce_price_decimal_sep', '' ),
					get_option( 'woocommerce_price_num_decimals', '' ),
				),
			) );

			wp_enqueue_style( 'price-commander-css', wop_PriceCommander()->plugin_url() . '/includes/css/admin' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, '' );

		}

		public static function get_settings( $plugins ) {

			$plugins[self::$plugin['label']] = array(
				'slug' => self::$plugin['label'],
				'name' => esc_html( function_exists( 'wop' ) ? self::$plugin['wop'] : self::$plugin['name'] ),
				'desc' => esc_html( function_exists( 'wop' ) ? self::$plugin['name'] . ' v' . self::$plugin['version'] : esc_html__( 'Settings page for', 'woopack' ) . ' ' . self::$plugin['name'] ),
				'link' => esc_url( 'https://woopack.com/store/price-commander/' ),
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
					'commander' => array(
						'name' => esc_html__( 'Price Commander', 'woopack' ),
						'desc' => esc_html__( 'Price Commander Overview', 'woopack' ),
					),
				),
				'settings' => array(

					'wcmn_dashboard' => array(
						'type' => 'html',
						'id' => 'wcmn_dashboard',
                        'desc' => '
                            <img src="' . wop_PriceCommander()->plugin_url() . '/includes/images/price-commander-for-woocommerce-shop.png" class="csl-dashboard-image" />
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

					'price_commander' => array(
						'name' => esc_html__( 'Price Commander', 'woopack' ),
						'type' => 'html',
						'desc' => '',
						'section' => 'commander',
						'id'   => 'price_commander',
					),

				)
			);

			return SevenVX()->_do_options( $plugins, self::$plugin['label'] );
		}

		function get_available_variations( $product ) {
			$available_variations = array();

			foreach ( $product->get_children() as $child_id ) {
				$available_variations[] = $child_id;
			}

			return $available_variations;
		}

		function _get_product( $id ) {
			$product = wc_get_product( $id );
			$image = wp_get_attachment_image_src( $product->get_image_id() );

			$variations = false;
			if ( ( $type = $product->get_type() ) == 'variable' ) {
				$variations = $this->get_available_variations( $product );
			}

			return array(
				'title' => $product->get_name(),
				'type' => $type,
				'image' => $image[0],
				'_price' => $product->get_price(),
				'_regular_price' => $product->get_regular_price(),
				'_sale_price' => $product->get_sale_price(),
				'_sale_from' => $product->get_date_on_sale_from(),
				'_sale_to' => $product->get_date_on_sale_to(),
				'variations' => $variations,
			);
		}

		function _change_price() {
			$product = wc_get_product( absint( $_POST['pc']['id'] ) );

			if ( isset( $_POST['pc']['price_type'] ) && $_POST['pc']['price_type'] == '_regular_price' ) {
				$product->set_regular_price( floatval( $_POST['pc']['price'] ) );

				$product->save();

				return array( 'success' => true );
			}

			if ( isset( $_POST['pc']['price_type'] ) && $_POST['pc']['price_type'] == '_sale_price' ) {
				$product->set_sale_price( floatval( $_POST['pc']['price'] ) );

				$product->save();

				return array( 'success' => true );
			}

			return array( 'success' => false );
		}

		function _get_orderby() {
			switch( $_POST['pc']['query']['orderby'] ) {
				case 'title' :
					return 'title';
				break;

				case 'menu_order' :
					return 'menu_order title';
				break;

				default:
					return null;
				break;
			}
		}

		function _get_order() {
			switch( $_POST['pc']['query']['orderby'] ) {
				case 'title' :
					return 'asc';
				break;

				case 'menu_order' :
					return 'asc';
				break;

				default:
					return null;
				break;
			}
		}

		function _get_products() {

			$args = array(
				'post_type' 		=> 'product',
				'product_type'		=> array( 'simple', 'external', 'variable' ),
				'fields'        	=> 'ids',
				'posts_per_page'	=> 12,
				'paged'				=> 1,
				'orderby'			=> 'title',
				'order'				=> 'asc',
			);

			if ( isset( $_POST['pc']['query'] ) ) {
				if ( isset( $_POST['pc']['query']['paged'] ) ) {
					$args['paged'] = absint( $_POST['pc']['query']['paged'] );
				}

				if ( isset( $_POST['pc']['query']['posts_per_page'] ) ) {
					$args['posts_per_page'] = absint( $_POST['pc']['query']['posts_per_page'] );
				}

				if ( isset( $_POST['pc']['query']['s'] ) ) {
					$args['s'] = esc_attr( $_POST['pc']['query']['s'] );
				}

				if ( isset( $_POST['pc']['query']['orderby'] ) ) {
					$args['orderby'] = esc_attr( $this->_get_orderby() );
					$args['order'] = esc_attr( $this->_get_order() );
				}
			}

			$query = new WP_Query( $args );

			$products = array();

			if ( $query->have_posts() ) {

				$products = array();

				if ( !empty( $query->posts ) ) {
					foreach( $query->posts as $k0 => $id ) {
						$products[$id] = $this->_get_product( $id );

						$products[$id]['order'] = $k0*1000;

						if ( $products[$id]['variations'] ) {
							foreach( $products[$id]['variations'] as $k1 => $variation ) {
								$products[$variation] = $this->_get_product( $variation );

								$products[$variation]['order'] = $k0*1000+($k1+1);
								$products[$variation]['parent'] = $id;
							}
						}
					}

					$pagination = array(
						'paged' => $args['paged'],
						'posts_per_page' => $query->get( 'posts_per_page' ),
						'total' => $query->found_posts,
					);

					return array( $products, $pagination );
				}
			}

			return array();

		}

		function add_templates() {
?>
			<script type="text/template" id="tmpl-pc-commander">
				<div id="pc-command-header">
					<?php esc_html_e( 'Command panel', 'woopack' ); ?>
				</div>
				<div id="pc-command-panel">
					<div id="pc-execute">
						<input type="checkbox" class="pc-checkbox" name="pc-set" id="pc-set" />
						<label for="pc-set"></label>

						<input type="checkbox" class="pc-checkbox" name="pc-add" id="pc-add" />
						<label for="pc-add"></label>

						<input type="checkbox" class="pc-checkbox" name="pc-substract" id="pc-substract" />
						<label for="pc-substract"></label>

						<input type="checkbox" class="pc-checkbox" name="pc-multiply" id="pc-multiply" />
						<label for="pc-multiply"></label>

						<input type="checkbox" class="pc-checkbox" name="pc-divide" id="pc-divide" />
						<label for="pc-divide"></label>

						<input type="checkbox" class="pc-checkbox" name="pc-per-cent-up" id="pc-per-cent-up" />
						<label for="pc-per-cent-up"></label>

						<input type="checkbox" class="pc-checkbox" name="pc-per-cent-down" id="pc-per-cent-down" />
						<label for="pc-per-cent-down"></label>

						<input type="number" class="pc-text" name="pc-operand" id="pc-operand" min="0" />

						<span id="pc-execute-command" class="csl-button-primary"><?php esc_html_e( 'Set new prices', 'woopack' ); ?></span>
						<span id="pc-clear-selection" class="csl-button"><?php esc_html_e( 'Clear selection', 'woopack' ); ?></span>
						<span id="pc-reset-operands" class="csl-button"><?php esc_html_e( 'Reset operands', 'woopack' ); ?></span>
					</div>

					<div id="pc-query">
						<input type="text" id="pc-search" name="pc-search" placeholder="<?php esc_html_e( 'Enter keywords', 'woopack' ); ?>" />

						<select id="pc-orderby" name="pc-orderby">
							<option value="title" selected="selected"><?php esc_html_e( 'Title', 'woopack' ); ?></option>
							<option value="latest"><?php esc_html_e( 'Latest', 'woopack' ); ?></option>
							<option value="menu_order"><?php esc_html_e( 'Menu order', 'woopack' ); ?></option>
						</select>

						<select id="pc-per-page" name="pc-per-page">
							<option value="3">3</option>
							<option value="6">6</option>
							<option value="12" selected="selected">12</option>
							<option value="24">24</option>
							<option value="48">48</option>
							<option value="96">96</option>
							<option value="192">192</option>
							<option value="384">384</option>
							<option value="99999">99999</option>
						</select>

						<?php esc_html_e( 'Page', 'woopack' ); ?>

						<div id="pc-pagination">
						</div>
					</div>
				</div>

				<div id="pc-commander">
					<div id="pc-header" class="pc-flex">
						<div><?php esc_html_e( 'Product', 'woopack' ) ; ?></div>
						<div><?php esc_html_e( 'Regular price', 'woopack' ); ?> <a href="javascript:void(0)" class="pc-select-column-regular"></a></div>
						<div><?php esc_html_e( 'Sale price', 'woopack' ); ?> <a href="javascript:void(0)" class="pc-select-column-sale"></a></div>
						<div class="pc-schedule-title"><?php esc_html_e( 'Schedule sale', 'woopack' ); ?> <a href="javascript:void(0)" class="pc-select-column-schedule"></a><a href="javascript:void(0)" class="pc-show-column-variations"></a></div>
					</div>
					<div id="pc-products">
					</div>
				</div>
			</script>
<?php
?>
			<script type="text/template" id="tmpl-pc-product">
			<# if ( data.type == 'variable' ) { #>
				<div class="pc-product pc-product-{{ data.type }} pc-flex{{ data._regular_price[1] }}{{ data._sale_price[1] }}" data-id="{{ data.id }}">
					<div class="pc-product-meta"><img class="pc-product-image" src="{{{ data.image }}}" /> {{{ data.title }}}</div>
					<div class="pc-expand-variations{{ data.parent[1] }}"></div>
				</div>
			<# } else { #>
				<div class="pc-product pc-product-{{ data.type }} pc-flex{{ data._regular_price[1] }}{{ data._sale_price[1] }}" data-id="{{ data.id }}"<# if ( data.parent[0] ) { #> data-parent="{{ data.parent[0] }}"<# } #>>
					<div class="pc-product-meta"><img class="pc-product-image" src="{{{ data.image }}}" /> {{{ data.title }}}</div>
					<div class="pc-price{{ data._regular_price[1] }}" data-type="_regular_price">{{{ data._regular_price[0] }}}</div>
					<div class="pc-price{{ data._sale_price[1] }}" data-type="_sale_price">{{{ data._sale_price[0] }}}</div>
					<div class="pc-schedule{{ data._sale_dates[0] }}"></div>
				</div>
			<# } #>

			</script>
<?php
?>
			<script type="text/template" id="tmpl-pc-schedule">
				<div id="pc-schedule" data-id="{{ data.id }}">
					<div class="pc-dates" data-date="{{ data._sale_from }}"><?php esc_html_e( 'Start sale', 'woopack' ) ; ?> <input id="pc-schedule-from" type="text" value="{{ data._sale_from }}" /></div>
					<div class="pc-dates" data-date="{{ data._sale_to }}"><?php esc_html_e( 'End sale', 'woopack' ) ; ?> <input id="pc-schedule-to" type="text" value="{{ data._sale_to }}" /></div>
					<div class="pc-schedule-operations">
						<span id="pc-make-schedule" class="csl-button-primary"><?php esc_html_e( 'Set', 'woopack' ); ?></span>
						<span id="pc-schedule-cancel" class="csl-button-primary red"><?php esc_html_e( 'Cancel', 'woopack' ); ?></span>
						<span id="pc-schedule-exit" class="csl-button"><?php esc_html_e( 'Exit', 'woopack' ); ?></span>
					</div>
				</div>
			</script>
<?php
		}

		function ajax_die($opt) {
			$opt['success'] = false;
			wp_send_json( $opt );
			exit;
		}

		function pc_ajax_factory() {
			$opt = array(
				'success' => true
			);

			if ( !isset( $_POST['pc']['type'] ) ) {
				$this->ajax_die($opt);
			}

			switch( $_POST['pc']['type'] ) {

				case 'change_price' :
					if ( apply_filters( 'csl_can_you_save', false ) ) {
						wp_send_json( $opt );
						exit;
					}

					wp_send_json( $this->_change_price() );
					exit;
				break;

				case 'get_products' :
					wp_send_json( $this->_get_products() );
					exit;
				break;

				case 'execute' :
				case 'schedule_sale' :
					if ( apply_filters( 'csl_can_you_save', false ) ) {
						wp_send_json( $opt );
						exit;
					}

					$this->initTimer();

					wp_send_json( $this->_get_execution() );
					exit;
				break;

				default :
					$this->ajax_die($opt);
					exit;
				break;

			}
		}

		function __get_execution_function_array() {
			return array(
				'pc-set', 'pc-add', 'pc-substract', 'pc-multiply', 'pc-divide', 'pc-per-cent-up', 'pc-per-cent-down',
			);
		}

		function _get_execution_function() {
			return isset( $_POST['pc']['operands'][0] ) && in_array( $_POST['pc']['operands'][0], $this->__get_execution_function_array() ) ? $_POST['pc']['operands'][0] : false;
		}

		function _get_execution_operand() {
			return isset( $_POST['pc']['operands'][1] ) ? floatval( $_POST['pc']['operands'][1] ) : false;
		}

		function _do_execution_cycle( $transient ) {
			$skip = true;
			$timeout = isset( $_POST['pc']['timeout'] ) ? intval( $_POST['pc']['timeout'] ) : 0;

			foreach( $transient[0] as $id => $v ) {

				if ( $skip && $timeout > 0 ) {
					if ( $id !== $timeout ) {
						continue;
					}

					if ( $id == $timeout ) {
						$skip = false;
					}
				}

				$this->setTimer( $id );

				if ( isset( $v['_regular_price'] ) && $v['_regular_price'] == true ) {
					$this->_do_execute_price( $id, '_regular_price', array( $transient[1], $transient[2] ) );
				}

				if ( isset( $v['_sale_price'] ) && $v['_sale_price'] == true ) {
					$this->_do_execute_price( $id, '_sale_price', array( $transient[1], $transient[2] ) );
				}

				if ( isset( $v['_sale_dates'] ) && $v['_sale_dates'] == true ) {
					$this->_do_execute_date( $id, '_sale_dates', array( $transient[1], $transient[2] ) );
				}

			}

			delete_transient( '__pc_do_product_execution' );

			wp_send_json( array(
				'success' => true
			) );
			exit;

		}

		function _do_execute_date( $id, $key, $operands ) {
			$product = wc_get_product( absint( $id ) );

			if ( $key == '_sale_dates' ) {
				$product->set_date_on_sale_from( $this->_fix_date( $operands[0] ) );
				$product->set_date_on_sale_to( $this->_fix_date( $operands[1] ) );

				$product->save();
			}
		}

		function _do_execute_price( $id, $key, $operands ) {
			$product = wc_get_product( absint( $id ) );

			if ( $key == '_regular_price' ) {
				$product->set_regular_price( $this->_fix_price( $product->get_regular_price(), $operands ) );

				$product->save();
			}

			if ( $key == '_sale_price' ) {
				$salePrice = $product->get_sale_price();

				if ( $salePrice == '' ) {
					$salePrice = $product->get_regular_price();
				}

				$product->set_sale_price( $this->_fix_price( $salePrice, $operands ) );

				$product->save();
			}
		}

		function _get_execution() {
			$transient = get_transient( '__pc_do_product_execution' );

			if ( $transient === false ) {

				if ( isset( $_POST['pc']['execute'] ) ) {
					$transient = array(
						is_array( $_POST['pc']['execute'] ) && !empty( $_POST['pc']['execute'] ) ? $_POST['pc']['execute'] : false,
						$this->_get_execution_function(),
						$this->_get_execution_operand(),
					);
				}

				if ( isset( $_POST['pc']['schedule'] ) ) {
					$transient = array(
						!empty( $_POST['pc']['schedule'][0] ) && is_array( $_POST['pc']['schedule'][0] ) ? $_POST['pc']['schedule'][0] : false,
						$this->_get_schedule_from(),
						$this->_get_schedule_to(),
					);
				}


				set_transient( '__pc_do_product_execution', $transient );
			}

			$this->_do_execution_cycle( $transient );
		}

		function _get_schedule_from() {
			return !empty( $_POST['pc']['schedule'][1] ) ? $_POST['pc']['schedule'][1] : null;
		}

		function _get_schedule_to() {
			return !empty( $_POST['pc']['schedule'][2] ) ? $_POST['pc']['schedule'][2] : null;
		}

		function _fix_date( $date ) {
			return $date;
		}

		function _fix_price( $price, $operands ) {

			switch( $operands[0] ) {

				case 'pc-set' :
					$op = $operands[1];
				break;

				case 'pc-add' :
					$op = $price+$operands[1];
				break;

				case 'pc-substract' :
					$op = $price-$operands[1];
				break;

				case 'pc-multiply' :
					$op = $price*$operands[1];
				break;

				case 'pc-divide' :
					$op = $price/$operands[1];
				break;

				case 'pc-per-cent-up' :
					$op = ($operands[1]/100+1)*$price;
				break;

				case 'pc-per-cent-down' :
					$op = (1-$operands[1]/100)*$price;
				break;

				default :
				break;

			}

			if ( $op == 0 ) {
				return '';
			}

			if ( $op < 0 ) {
				return $price;
			}

			return $op;

		}

		function setTimer( $id ) {
			wop_PriceCommander_Settings::$time = wop_PriceCommander_Settings::$time + microtime( true ) - wop_PriceCommander_Settings::$startTime;

			if ( wop_PriceCommander_Settings::$time > 5 ) {
				$opt['timeout'] = $id;
				$opt['success'] = false;

				wp_send_json( $opt );
				exit;

			}
		}

		function initTimer() {
			wop_PriceCommander_Settings::$startTime = microtime( true );
		}

	}

	wop_PriceCommander_Settings::instance();

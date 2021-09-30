<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $GLOBALS['csl'] ) && version_compare( $GLOBALS['csl'], '1.6.0' ) == 0 ) :

if ( !class_exists( 'SevenVXSettings' ) ) {

	class SevenVXSettings {

		public static $version = '1.6.0';

		protected static $_instance = null;

		public static $plugin = null;
		public static $slug = null;

		public static $lang;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			do_action( 'csl_loading' );

			$this->init_hooks();

			do_action( 'csl_loaded' );
		}

		private function init_hooks() {

			$plugins = apply_filters( 'csl_plugins', array() );

			$page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wc-settings' ? true : false;
			$slug = isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $plugins ) ? $_REQUEST['tab']: '';

			if ( !empty( $plugins ) && $page ) {
				if ( !function_exists( 'wop' ) ) {
					foreach( $plugins as $p ) {
						add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 50 );
					}
				}

				if ( $slug ) {
					add_action( 'woocommerce_settings_tabs_' . $slug, array( $this, 'display_tab' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ), 10 );
					add_action( 'admin_footer', array( $this, 'add_vars' ) );
					add_filter( 'csl_settings_templates', array( $this, 'default_templates') );
				}

				self::$slug = $slug;
			}

			$page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woopack' ? true: false;

			if ( $page ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ), 10 );
				add_action( 'admin_footer', array( $this, 'add_vars' ) );
				add_filter( 'csl_settings_templates', array( $this, 'default_templates') );
			}

			add_action( 'wp_ajax_csl_ajax_factory', array( $this, 'ajax_factory' ) );
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

		public function admin_js() {

			$plugins = apply_filters( 'csl_plugins', array() );

			$slug = isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $plugins ) ? $_REQUEST['tab']: '';
			$page = isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woopack' ? true: false;

			if ( $slug || $page ) {
				wp_enqueue_style( 'csl-style', $this->plugin_url() .'/css/csl-style' . ( is_rtl() ? '-rtl' : '' ) . '.css', false, self::$version );

				wp_register_script( 'csl-settings', $this->plugin_url() . '/js/csl-core.js', array( 'jquery', 'wp-util', 'jquery-ui-core', 'jquery-ui-sortable' ), self::$version, true );
				wp_enqueue_script( 'csl-settings' );

				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );

				if ( function_exists( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
				}
			}

		}

		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function add_tab( $tabs ) {

			$plugins = apply_filters( 'csl_plugins', array() );

			if ( !empty( $plugins ) ) {
				foreach( $plugins as $p ) {
					$tabs[$p['slug']] = $p['name'];
				}
			}

			return $tabs;

		}

		public function display_tab() {

		?>
			<div id="csl-settings" class="csl-<?php echo esc_attr( current_filter() ); ?> <?php echo apply_filters( 'wop_dashboard_class', 'wop-dashboard' ); ?>"></div>
		<?php

		}

		public function add_templates() {

			?>
			<script type="text/template" id="tmpl-csl-license">
				<div class="csl-license">
					<h2><?php esc_html_e( 'Plugin not registered!', 'woopack' ); ?></h2>
					<p>
						{{ data.name }} <?php esc_html_e( 'license is not registered. Please register! Click Register now button to start.', 'woopack' ); ?><br />
						<?php esc_html_e( 'Need help? Visit this link', 'woopack' ); ?> &rarr; <a href="https://help.woopack.com/how-to-register" target="_blank"><?php esc_html_e( 'How to register?', 'woopack' ); ?></a>
					</p>
					<span id="csl-register" class="csl-button"><?php esc_html_e( 'Register now', 'woopack' ); ?></span>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-license-details">
				<div class="csl-license details">
					<span class="csl-preformatted-row"><?php esc_html_e( 'Connecting to server', 'woopack' ); ?>...</span>
					<hr />
					<span id="csl-ok-license-details" class="csl-button csl-button-primary"><?php esc_html_e( 'OK', 'woopack' ); ?></span>
					<a href="https://help.woopack.com/my-support-tickets/" class="csl-button" target="_blank"><?php esc_html_e( 'Remove license', 'woopack' ); ?></a>
					<a href="https://help.woopack.com/my-support-tickets/" class="csl-button" target="_blank"><?php esc_html_e( 'License details', 'woopack' ); ?></a>
					<a href="https://woopack.com/store/" class="csl-button" target="_blank"><?php esc_html_e( 'Extend support', 'woopack' ); ?></a>
									</div>
			</script>

			<script type="text/template" id="tmpl-csl-license-input">
				<input id="csl-register-key" name="csl-register-key" placeholder="Paste woopack.com registration key" />
				<span id="csl-register-confirm" class="csl-button csl-button-primary"><?php esc_html_e( 'Confirm key', 'woopack' ); ?></span>
				<a href="https://help.woopack.com/my-support-tickets/" id="csl-register-get-key" class="csl-button" target="_blank"><?php esc_html_e( 'Get key', 'woopack' ); ?></span>
			</script>

			<script type="text/template" id="tmpl-csl-main-wrapper">
				<div id="csl-main-wrapper" data-slug="{{ data.slug }}"<?php echo self::get_language(); ?>>

					<div id="csl-main">
						<ul id="csl-settings-menu"></ul>
						<div id="csl-settings-main"></div>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-button">
				<a href="{{ data.url }}" class="csl-button{{ data.class }}">
					{{ data.name }}
				</a>
			</script>

			<script type="text/template" id="tmpl-csl-li-menu">
				<li data-id="{{ data.id }}">
					{{ data.name }}
				</li>
			</script>

			<script type="text/template" id="tmpl-csl-settings">
				<div id="csl-settings-main-{{ data.id }}" data-id="{{ data.id }}">
					<div id="csl-settings-header">
						<div class="csl-desc">
							<span id="save" class="csl-button-primary"><?php esc_html_e( 'Save', 'woopack' ); ?></span>
						</div>
					</div>
					<div id="csl-settings-wrapper">
						{{{ data.settings }}}
					</div>
					<div id="csl-settings-footer">
						<div class="csl-desc">
							<span id="save-alt" class="csl-button-primary"><?php esc_html_e( 'Save', 'woopack' ); ?></span>
						</div>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-option">
				<div id="{{ data.id }}-option" class="{{ data.class }}<# if ( data.column ) { #>{{ 'csl-column csl-column-'+data.column }}<# } #>">
					<div class="csl-option-header">
						<h3>
							{{ data.name }}
						</h3>
					</div>
					<div class="csl-option-wrapper">
						{{{ data.option }}}
						<p class="csl-desc">
						</p>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-option-html">
				<div id="{{ data.id }}-option" class="{{ data.class }}<# if ( data.column ) { #>{{ 'csl-column csl-column-'+data.column }}<# } #>">
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-option-utility">
				<span id="csl-export" class="csl-button"><?php esc_html_e( 'Export', 'woopack' ); ?></span><span id="csl-import" class="csl-button"><?php esc_html_e( 'Import', 'woopack' ); ?></span><span id="csl-backup" class="csl-button"><?php esc_html_e( 'Backup', 'woopack' ); ?></span><span id="csl-restore" class="csl-button"><?php esc_html_e( 'Restore', 'woopack' ); ?></span><span id="csl-reset" class="csl-button"><?php esc_html_e( 'Reset', 'woopack' ); ?></span>
			</script>

			<script type="text/template" id="tmpl-csl-option-text">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="text"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
			</script>

			<script type="text/template" id="tmpl-csl-option-file">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="text"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> /> <span class="csl-button csl-file-add"><?php esc_html_e( 'Add +', 'woopack' ); ?></span>
			</script>

			<script type="text/template" id="tmpl-csl-option-textarea">
				<textarea id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}"><# if ( data.val ) { #>{{ data.val }}<# } else { #><# } #></textarea>
			</script>

			<script type="text/template" id="tmpl-csl-option-multiselect">
				<select id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" class="csl-multiple" multiple>
					{{{ data.options }}}
				</select>
			</script>

			<script type="text/template" id="tmpl-csl-option-values-multiselect">
				<option value="{{ data.val }}"<# if ( data.sel ) { #> selected="selected"<# } else { #><# } #>>{{ data.name }}</option>
			</script>

			<script type="text/template" id="tmpl-csl-option-select">
				<select id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}">
					{{{ data.options }}}
				</select>
			</script>

			<script type="text/template" id="tmpl-csl-option-values-select">
				<option value="{{ data.val }}"<# if ( data.sel ) { #> selected="selected"<# } else { #><# } #>>{{ data.name }}</option>
			</script>

			<script type="text/template" id="tmpl-csl-option-checkbox">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="checkbox" <# if ( data.val == "yes" ) { #> checked="checked"<# } else { #><# } #>/> <label for="{{ data.eid }}"></label>
			</script>

			<script type="text/template" id="tmpl-csl-option-number">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="number"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
			</script>

			<script type="text/template" id="tmpl-csl-option-hidden">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="hidden"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
			</script>

			<script type="text/template" id="tmpl-csl-option-include">
				<span class="csl-button csl-include"><?php esc_html_e( 'Configure', 'woopack' ); ?></span>
			</script>

			<script type="text/template" id="tmpl-csl-option-list">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="hidden"<# if ( data.val ) { #> value="{{ data.val }}"<# } else { #><# } #> />
				<div id="{{ data.id }}-list" class="csl-option-list">
					{{{ data.options }}}
				</div>
				<span class="csl-button-primary csl-option-list-add" data-id="{{ data.id }}"><?php esc_html_e( 'Add Item +', 'woopack' ); ?></span>
			</script>

			<script type="text/template" id="tmpl-csl-option-list-item">
				<div class="csl-option-list-item">
					<span class="csl-option-list-item-icon csl-list-expand-button" data-type="{{ data.type }}"></span>
					<span class="csl-option-list-item-title">{{ data.title }}</span>
					<span class="csl-option-list-item-icon csl-list-remove-button" data-id="{{ data.id }}"></span>
					<span class="csl-option-list-item-icon csl-list-move-button"></span>
					<# if ( data.customizer ) { #><span class="csl-option-list-item-icon csl-list-customizer-button"></span><# } #>
					<div class="csl-option-list-item-container">
						{{{ data.options }}}
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-option-list-select">
				<input id="{{ data.eid }}" data-option="{{ data.id }}" class="csl-change{{ data.class }}" name="{{ data.name }}" type="hidden"<# if ( data.val ) { #> value="{{ data.val }}"<# } #> />
				<div id="{{ data.id }}-list" class="csl-option-list">
					{{{ data.options }}}
				</div>
				{{{ data.selects }}} <span class="csl-button-primary csl-option-list-select-add" data-id="{{ data.id }}"><?php esc_html_e( 'Add Item +', 'woopack' ); ?></span>
			</script>

			<script type="text/template" id="tmpl-csl-include-customizer">
				<div id="csl-include-customizer" data-id="{{ data.id }}">
					<div class="csl-include-customizer-wrapper">
						<div class="csl-include-customizer-header">
							<span id="csl-include-customizer-exit"></span>
							<h2><?php esc_html_e( 'Include/Exclude Manager', 'woopack' ); ?></h2>
							<span id="csl-exclude-toggle" class="<# if ( data.selected == 'OUT' ) { #>csl-button-primary<# } else { #>csl-button<# } #>"><?php esc_html_e( 'Exclude', 'woopack' ); ?></span>
							<span id="csl-include-toggle" class="<# if ( data.selected == 'IN' ) { #>csl-button-primary<# } else { #>csl-button<# } #>"><?php esc_html_e( 'Include', 'woopack' ); ?></span>
						</div>
						<div id="csl-include-customizer-terms" data-taxonomy="{{ data.taxonomy }}"></div>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-customizer">
				<div id="csl-customizer" data-id="{{ data.id }}">
					<div class="csl-customizer-wrapper">
						<div class="csl-customizer-header">
							<span id="csl-customizer-exit"></span>
							<h2><?php esc_html_e( 'Terms Manager', 'woopack' ); ?></h2>
							<# if ( data.type !== 'orderby' && data.type !== 'vendor' && data.type !== 'instock' && data.type !== 'range' ) { #>

								<# if ( data.type == 'meta' || data.type == 'meta_range' || data.type == 'ivpa_custom' || data.type == 'price' || data.type == 'per_page' ) { #>
									<span id="csl-customizer-add" class="csl-button-primary">Add Term +</span>
								<# }
								else { #>
									<span id="csl-customizer-custom-order" class="<# if ( data.order == 'true' ) { #>csl-button-primary<# } else { #>csl-button<# } #>"><?php esc_html_e( 'Custom Order', 'woopack' ); ?></span>
								<# } #>

							<# } #>
						</div>
						<# if ( data.type !== 'range' && data.type !== 'meta_range' ) { #>
							<div class="csl-customizer-style">
								<div id="csl-special-options">
									<span class="csl-special-option">
										<label><?php esc_html_e( 'Type', 'woopack' ); ?></label>
										<select class="csl-terms-style-change" data-option="type">
											<option value=""<# if ( data.style == '' ) { #> selected="selected"<# } #>><?php esc_html_e( 'None', 'woopack' ); ?></option>
											<option value="text"<# if ( data.style == 'text' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Plain Text', 'woopack' ); ?></option>
											<option value="color"<# if ( data.style == 'color' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Color', 'woopack' ); ?></option>
											<option value="image"<# if ( data.style == 'image' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Thumbnail', 'woopack' ); ?></option>
											<option value="selectbox"<# if ( data.style == 'selectbox' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Select Box', 'woopack' ); ?></option>
											<# if ( data.type.substr(0,5) !== 'ivpa_' ) { #>
												<option value="system"<# if ( data.style == 'system' ) { #> selected="selected"<# } #>><?php esc_html_e( 'System Select', 'woopack' ); ?></option>
												<option value="selectize"<# if ( data.style == 'selectize' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Live Select', 'woopack' ); ?></option>
											<# } #>
											<option value="html"<# if ( data.style == 'html' ) { #> selected="selected"<# } #>>HTML</option>
											<# if ( data.type == 'ivpa_custom' ) { #>
												<option value="input"<# if ( data.style == 'input' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Input Field', 'woopack' ); ?></option>
												<option value="checkbox"<# if ( data.style == 'checkbox' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Checkbox', 'woopack' ); ?></option>
												<option value="textarea"<# if ( data.style == 'textarea' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Textarea', 'woopack' ); ?></option>
												<option value="system"<# if ( data.style == 'system' ) { #> selected="selected"<# } #>><?php esc_html_e( 'System Select', 'woopack' ); ?></option>
											<# } #>
										</select>
									</span>
									{{{ data.controls }}}
								</div>
							</div>
						<# } #>
						<div id="csl-customizer-terms" class="csl-terms-list" data-taxonomy="{{ data.taxonomy }}">
							{{{ data.terms }}}
						</div>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-customizer-style-text">
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Style', 'woopack' ); ?></label>
					<select class="csl-terms-style-change" data-option="style">
						<option value="border"<# if ( data.border == 'round' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Border', 'woopack' ); ?></option>
						<option value="background"<# if ( data.style == 'background' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Background', 'woopack' ); ?></option>
						<option value="round"<# if ( data.style == 'round' ) { #> selected="selected"<# } #>><?php esc_html_e( 'Round', 'woopack' ); ?></option>
					</select>
				</span>
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Normal', 'woopack' ); ?></label>
					<input type="text" class="csl-terms-color csl-terms-style-change" data-option="normal"<# if ( data.normal ) { #> value="{{ data.normal }}"<# } #> />
				</span>
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Active', 'woopack' ); ?></label>
					<input type="text" class="csl-terms-color csl-terms-style-change" data-option="active"<# if ( data.active ) { #> value="{{ data.active }}"<# } #> />
				</span>
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Disabled', 'woopack' ); ?></label>
					<input type="text" class="csl-terms-color csl-terms-style-change" data-option="disabled"<# if ( data.disabled ) { #> value="{{ data.disabled }}"<# } #> />
				</span>
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Out of stock', 'woopack' ); ?></label>
					<input type="text" class="csl-terms-color csl-terms-style-change" data-option="outofstock"<# if ( data.outofstock ) { #> value="{{ data.outofstock }}"<# } #> />
				</span>
			</script>

			<script type="text/template" id="tmpl-csl-customizer-style-swatch">
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Show labels', 'woopack' ); ?></label>
					<select class="csl-terms-style-change" data-option="label">
						<option value="no"<# if ( data.label == 'no' ) { #> selected="selected"<# } #>>No</option>
						<option value="side"<# if ( data.label == 'side' ) { #> selected="selected"<# } #>>Aside</option>
					</select>
				</span>
				<span class="csl-special-option">
					<label><?php esc_html_e( 'Design', 'woopack' ); ?></label>
					<select class="csl-terms-style-change" data-option="swatchDesign">
						<option value="square"<# if ( data.swatchDesign == 'square' ) { #> selected="selected"<# } #>>Square</option>
						<option value="round"<# if ( data.swatchDesign == 'round' ) { #> selected="selected"<# } #>>Round</option>
					</select>
				</span>
				<span class="csl-special-option csl-special-option-sm">
					<label><?php esc_html_e( 'Swatch size', 'woopack' ); ?></label>
					<input type="text" class="csl-terms-style-change" data-option="size"<# if ( data.size ) { #> value="{{ data.size }}"<# } #> />
				</span>
			</script>

			<script type="text/template" id="tmpl-csl-customizer-term">
				<div class="csl-terms-list-item" data-id="{{ data.id }}" data-slug="{{ data.slug }}">
					<div class="csl-term-badge">
						<span class="csl-term-item-title">{{ data.title }}</span>
						<# if ( data.type == 'meta' || data.type == 'meta_range' || data.type == 'price' || data.type == 'per_page' ) { #>
							<span class="csl-term-item-icon csl-term-remove-button" data-id="{{ data.id }}"></span>
						<# } #>
						<# if ( data.type == 'meta' || data.type == 'meta_range' || data.type == 'price' || data.type == 'per_page' || data.type =='orderby' || data.type =='vendor' || data.type =='instock' || data.order == 'true' ) { #>
							<span class="csl-term-item-icon csl-term-move-button"></span>
						<# } #>
					</div>
					<div class="csl-term-options-holder">
						<div class="csl-term-option">
							<label><?php esc_html_e( 'Name', 'woopack' ); ?></label>
							<input type="text" class="csl-terms-change" name="name" />
						</div>

						<# if ( data.type == 'meta' || data.type == 'meta_range' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Meta value', 'woopack' ); ?></label>
								<input type="text" class="csl-terms-change" name="data" />
							</div>
						<# } #>

						<# if ( data.style !== 'text' && data.style !== 'selectbox' && data.style !== 'system' && data.style !== 'selectize' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Value', 'woopack' ); ?></label>
								<# if ( data.style !== 'html' ) { #>
									<input type="text" class="csl-terms-change <# if ( data.style ) { #>csl-terms-{{ data.style }}<# } #>" name="value" />
								<# }
								else { #>
									<textarea class="csl-terms-change <# if ( data.style ) { #>csl-terms-{{ data.style }}<# } #>" name="value"></textarea>
								<# } #>
								<# if ( data.style == 'image' ) { #>
									<span class="csl-button csl-terms-image-add">Add Image +</span>
								<# } #>
							</div>
						<# } #>

 						<# if ( data.type == 'price' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Min', 'woopack' ); ?></label>
								<input type="number" class="csl-terms-change" name="min" />
								<label><?php esc_html_e( 'Max', 'woopack' ); ?></label>
								<input type="number" class="csl-terms-change" name="max" />
							</div>
						<# } #>

						<# if ( data.type == 'per_page' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Count', 'woopack' ); ?></label>
								<input type="number" class="csl-terms-change" name="count" />
							</div>
						<# } #>

						<# if ( data.type !== 'range' && data.type !== 'meta_range' && data.style !== 'system' && data.style !== 'selectize' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Tooltip', 'woopack' ); ?></label>
								<textarea class="csl-terms-change" name="tooltip"></textarea>
							</div>
						<# } #>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-csl-customizer-term-ivpa">
				<div class="csl-terms-list-item" data-id="{{ data.id }}" data-slug="{{ data.slug }}">
					<div class="csl-term-badge">
						<span class="csl-term-item-title">{{ data.title }}</span>
						<# if ( data.type == 'ivpa_custom' ) { #>
							<span class="csl-term-item-icon csl-term-remove-button" data-id="{{ data.id }}"></span>
						<# } #>
						<# if ( data.type == 'ivpa_custom' || data.order == 'true' ) { #>
							<span class="csl-term-item-icon csl-term-move-button"></span>
						<# } #>
					</div>
					<div class="csl-term-options-holder">
						<div class="csl-term-option">
							<label><?php esc_html_e( 'Name', 'woopack' ); ?></label>
							<input type="text" class="csl-terms-change" name="name" />
						</div>

						<# if ( data.style !== 'text' && data.style !== 'selectbox' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Value', 'woopack' ); ?></label>
								<# if ( data.style !== 'html' ) { #>
									<input type="text" class="csl-terms-change <# if ( data.style ) { #>csl-terms-{{ data.style }}<# } #>" name="value" />
								<# }
								else { #>
									<textarea class="csl-terms-change <# if ( data.style ) { #>csl-terms-{{ data.style }}<# } #>" name="value"></textarea>
								<# } #>
								<# if ( data.style == 'image' ) { #>
									<span class="csl-button csl-terms-image-add"><?php esc_html_e( 'Add Image +', 'woopack' ); ?></span>
								<# } #>
							</div>
						<# } #>

						<# if ( data.type == 'ivpa_custom' ) { #>
							<div class="csl-term-option">
								<label><?php esc_html_e( 'Price', 'woopack' ); ?></label>
								<input type="text" class="csl-terms-change" name="price" />
							</div>
						<# } #>

						<div class="csl-term-option">
							<label><?php esc_html_e( 'Tooltip', 'woopack' ); ?></label>
							<textarea class="csl-terms-change" name="tooltip"></textarea>
						</div>

					</div>
				</div>
			</script>
		<?php

		}

		public function add_vars() {

			if ( wp_script_is( 'csl-settings', 'enqueued' ) ) {

				$this->add_templates();

				$vars = apply_filters( 'csl_plugins_settings', array() );

				$slug = isset( $_REQUEST['tab'] ) && array_key_exists( $_REQUEST['tab'], $vars ) ? $_REQUEST['tab']: '';
				if ( isset( $vars[$slug] ) ) {
					$vars[$slug]['nonce'] = wp_create_nonce( 'csl-nonce' );
					$vars[$slug]['ajax'] = esc_url( admin_url( 'admin-ajax.php' ) );

					wp_localize_script( 'csl-settings', 'csl', $vars[$slug] );
				}

			}

		}

		public function ajax_die($opt) {
			$opt['success'] = false;
			wp_send_json( $opt );
			exit;
		}


		public function _terms_get_options( $terms, &$ready, &$level, $mode ) {
			foreach ( $terms as $term ) {
				if ( $mode == 'select' ) {
					$ready[$term->term_id] = ( $level > 0 ? str_repeat( '-', $level ) . ' ' : '' ) . $term->name;
				}
				else {
					$ready[] = array(
						'id' => $term->term_id,
						'name' => ( $level > 0 ? str_repeat( '-', $level ) . ' ' : '' ) . $term->name,
						'slug' => $term->slug,
					);
				}
				if ( !empty( $term->children ) ) {
					$level++;
					SevenVX()->_terms_get_options( $term->children, $ready, $level, $mode );
					$level--;
				}
			}
		}

		public function _terms_sort_hierarchicaly( Array &$cats, Array &$into, $parentId = 0 ) {
			foreach ( $cats as $i => $cat ) {
				if ( $cat->parent == $parentId ) {
					$into[$cat->term_id] = $cat;
					unset($cats[$i]);
				}
			}
			foreach ( $into as $topCat ) {
				$topCat->children = array();
				SevenVX()->_terms_sort_hierarchicaly( $cats, $topCat->children, $topCat->term_id );
			}
		}

		public function __remove_languages() {
			if ( class_exists( 'SitePress') ) {
				do_action( 'wpml_switch_language', apply_filters( 'wpml_default_language', NULL ) );
			}
		}

		public function _terms_get( $set, $mode ) {
			$taxonomy = $set[2];
			$ready = array();

			if ( taxonomy_exists( $taxonomy ) ) {

				if ( isset( $set[4] ) && $set[4] == 'no_lang' && $this->language() ) {
					$this->__remove_languages();
				}

				$args = array(
					'hide_empty' => 0,
					'hierarchical' => ( is_taxonomy_hierarchical( $taxonomy ) ? 1 : 0 )
				);

				$terms = get_terms( $taxonomy, $args );

				if ( is_taxonomy_hierarchical( $taxonomy ) ) {
					$terms_sorted = array();
					SevenVX()->_terms_sort_hierarchicaly( $terms, $terms_sorted );
					$terms = $terms_sorted;
				}

				if ( !empty( $terms ) && !is_wp_error( $terms ) ){
					$var =0;
					SevenVX()->_terms_get_options( $terms, $ready, $var, $mode );
				}

			}

			return $ready;
		}

		public function _terms_decode( $str ) {
			$str = preg_replace( "/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode( $str ) );
			return html_entity_decode( $str, null, 'UTF-8' );
		}

		public function _types_get() {
			$types = wc_get_product_types();
			$ready = array();
			if ( !empty( $types ) ) {
				foreach( $types as $k => $v ) {
					$ready[$k] = $v;
				}
			}
			return $ready;
		}

		public function _taxonomies_get() {
			$taxonomies = get_object_taxonomies( 'product' );
			$ready = array();
			if ( !empty( $taxonomies ) ) {
				foreach( $taxonomies as $k ) {
					if ( substr($k, 0, 3) == 'pa_' ) {
						$ready[$k] =  wc_attribute_label( $k );
					}
					else if ( taxonomy_exists( $k ) ) {
						$taxonomy = get_taxonomy( $k );
						if ( $taxonomy->public ) {
							$ready[$k] = $taxonomy->label;
						}
					}
				}
			}
			return $ready;
		}

		public function _attributes_get_alt() {
			$attributes = get_object_taxonomies( 'product' );
			$ready = array();
			if ( !empty( $attributes ) ) {
				foreach( $attributes as $k ) {
					if ( substr($k, 0, 3) == 'pa_' ) {
						$ready[$k] =  wc_attribute_label( $k );
					}
				}
			}
			return $ready;
		}

		public function _attributes_get() {
			$attributes = wc_get_attribute_taxonomies();
			$ready = array();

			if ( !empty( $attributes ) ) {
				foreach( $attributes as $attribute ) {
					$ready['pa_' . $attribute->attribute_name] = $attribute->attribute_label;
				}
			}

			return $ready;
		}

		public function array_overlay( $a, $b ) {
			foreach( $b as $k => $v ) {
				$a[$k] = $v;
			}
			return $a;
		}

		public function ajax_factory() {
			check_ajax_referer( 'csl-nonce', 'nonce' );

			$opt = array(
				'success' => true
			);

			if ( !current_user_can( 'manage_woocommerce' ) ) {
				SevenVX()->ajax_die($opt);
			}

			if ( !isset( $_POST['csl']['type'] ) ) {
				SevenVX()->ajax_die($opt);
			}

			if ( apply_filters( 'csl_can_you_save', false ) ) {
				$this->ajax_die($opt);
			}


			switch( $_POST['csl']['type'] ) {

				case 'get_control_options' :

					$set = explode( ':', $_POST['csl']['settings'] );

					switch( $set[1] ) {

						case 'image_sizes' :
							$image_array = array();
							$image_sizes = get_intermediate_image_sizes();
							foreach ( $image_sizes as $image_size ) {
								$image_array[$image_size] = $image_size;
							}
							wp_send_json( $image_array );
							exit;
						break;
						case 'wp_options' :
							wp_send_json( get_option( substr( $_POST['csl']['settings'], 16 ) ) );
							exit;
						break;
						case 'users' :
							$return = array();
							$users = get_users( array( 'fields' => array( 'id', 'display_name' ) ) );

							foreach ( $users as $user ) {
								$return[$user->id] = $user->display_name;
							}

							wp_send_json( $return );
							exit;
						break;
						case 'product_attributes' :
							wp_send_json( SevenVX()->_attributes_get_alt() );
							exit;
						break;
						case 'product_taxonomies' :
							wp_send_json( SevenVX()->_taxonomies_get() );
							exit;
						break;
						case 'product_types' :
							wp_send_json( SevenVX()->_types_get() );
							exit;
						break;
						case 'taxonomy' :
							wp_send_json( SevenVX()->_terms_get( $set, 'select' ) );
							exit;
						break;
						case 'terms' :
							wp_send_json( SevenVX()->_terms_get( $set, 'terms' ) );
							exit;
						break;
						default :
							SevenVX()->ajax_die($opt);
						break;

					}

				break;

				case 'save' :

					$slc = isset( $_POST['csl']['delete'] ) && is_array( $_POST['csl']['delete'] ) ? $_POST['csl']['delete'] : array();

					if ( !empty( $slc ) ) {
						foreach( $slc as $k => $v ) {
							delete_option( $v );
						}
					}

					$sld = isset( $_POST['csl']['solids'] ) && is_array( $_POST['csl']['solids'] ) ? $_POST['csl']['solids'] : array();

					if ( !empty( $sld ) ) {
						foreach( $sld as $k => $v ) {
							$val = isset( $v['val'] ) && !empty( $v['val'] ) ? $v['val'] : false;
							if ( !is_array( $val ) ) {
								$val = array();
							}
							$std = get_option( $k, array() );
							if ( !is_array( $std ) ) {
								$std = array();
							}

							if ( empty( $val ) ) {
								update_option( $k, '', false );
							}
							else {

								update_option( $k, $val, false );
							}
						}
					}

					$stg = isset( $_POST['csl']['settings'] ) && is_array( $_POST['csl']['settings'] ) ? $_POST['csl']['settings'] : array();

					foreach( $stg as $k => $v ) {
						if ( isset( $v['autoload'] ) ) {
							if ( $v['autoload'] == 'true' ) {
								$opt['auto'][$k] = $v['val'];
							}
							else if ( $v['autoload'] == 'false' ) {
								$opt['std'][$k] = isset( $v['val'] ) ? $v['val'] : false;
							}
						}
					}

					$opt = apply_filters( 'csl_ajax_save_settings', $opt );

					if ( isset( $opt['std'] ) && !empty( $opt['std'] ) && is_array( $opt['std'] ) ) {
						update_option( 'csl_settings_' . $_POST['csl']['plugin'], array_merge( get_option( 'csl_settings_' . $_POST['csl']['plugin'], array() ), $opt['std'] ), false );
					}

					$less = isset( $_POST['csl']['less'] ) && is_array( $_POST['csl']['less'] ) ? $_POST['csl']['less'] : array();

					if ( !empty( $less['length'] ) && $less['length'] > 0 ) {
						$option = isset( $less['option'] ) ? $less['option'] : false;
						if ( $option !== false ) {
							unset( $less['option'] );
							if ( isset( $less['solids'] ) ) {
								$solids = $less['solids'];
								if ( isset( $solids['name'] ) ) {
									$presets = $opt['std'][$solids['name']];
									foreach( $presets as $b => $j ) {
										$preset = apply_filters( 'csl_before_solid' . $solids['solid'], get_option( $solids['solid'] . sanitize_title( $b ), array() ) );
										if ( isset( $preset['name'] ) ) {
											foreach( $solids['options'] as $n ) {
												if ( isset( $preset[$n] ) ) {
													switch ( $n ) {
														case 'name' :
															$less[$n . 's'] .= $less[$n . 's'] == '' ? sanitize_title( $b ) : ',' . sanitize_title( $b );
														break;
														default :
															$less[$n . 's'] .= $less[$n . 's'] == '' ? $preset[$n] : ',' . $preset[$n];
														break;
													}

												}
											}
										}
									}
								}
								$less['url'] = '~"' . $less['solids']['url'] . '"';
								unset( $less['solids'] );
							}

							$compiled = self::compile_less( $solids, $less );
						}
					}

					if ( isset( $compiled ) ) {
						$opt['auto'][$option] = $compiled;
					}

					if ( isset( $opt['auto'] ) && !empty( $opt['auto'] ) && is_array( $opt['auto'] ) ) {
						$opt['auto'] = array_merge( get_option( 'csl_autoload', array() ), $opt['auto'] );
					}

					$opt = apply_filters( 'csl_ajax_save_settings_auto', $opt );

					if ( !empty( $opt['auto'] ) ) {
						update_option( 'csl_autoload', $opt['auto'], true );
					}

					do_action( 'csl_ajax_saved_settings_' . $_POST['csl']['plugin'], $opt );

					wp_send_json( array( 'success' => true ) );
					exit;

				break;

				case 'export' :
					$stg = isset( $_POST['csl']['settings'] ) && is_array( $_POST['csl']['settings'] ) ? $_POST['csl']['settings'] : array();

					if ( isset( $stg['auto'] ) && !empty( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
						$backup_auto = get_option( 'csl_autoload', array() );

						foreach( $stg['auto'] as $k ) {
							if ( isset( $backup_auto[$k] ) ) {
								$exp['auto'][$k] = $backup_auto[$k];
							}
						}
					}

					if ( isset( $stg['std'] ) && !empty( $stg['std'] ) && is_array( $stg['std'] ) ) {
						$exp['std'] = get_option( 'csl_settings_' . $_POST['csl']['plugin'], array() );
					}

					if ( isset( $stg['solids'] ) && !empty( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
						foreach( $stg['solids'] as $k ) {
							$exp['solids'][$k] = get_option( $k );
						}
					}

					wp_send_json( $this->get_for_options( $exp ) );
					exit;
				break;

				case 'import' :
					$stg = isset( $_POST['csl']['settings'] ) ? $_POST['csl']['settings'] : '';

					if ( $stg !== '' ) {

						$opt = $this->get_for_options( json_decode( stripslashes( $stg ), true ) );

						$opt = apply_filters( 'csl_ajax_save_settings', $opt );

						if ( isset( $opt['auto'] ) && !empty( $opt['auto'] ) && is_array( $opt['auto'] ) ) {
							$opt['auto'] = array_merge( get_option( 'csl_autoload', array() ), $opt['auto'] );
							update_option( 'csl_autoload', $opt['auto'], true );
						}

						if ( isset( $opt['std'] ) && !empty( $opt['std'] ) && is_array( $opt['std'] ) ) {
							update_option( 'csl_settings_' . $_POST['csl']['plugin'], $opt['std'], false );
						}

						if ( isset( $opt['solids'] ) && !empty( $opt['solids'] ) && is_array( $opt['solids'] ) ) {
							foreach( $opt['solids'] as $key => $solid ) {
								update_option( $key, $solid, false );
							}
						}

						wp_send_json( array( 'success' => true ) );
						exit;
					}
					wp_send_json( array( 'success' => false ) );
					exit;
				break;

				case 'backup' :
					$bkp = array();
					$stg = isset( $_POST['csl']['settings'] ) && is_array( $_POST['csl']['settings'] ) ? $_POST['csl']['settings'] : array();

					if ( isset( $stg['auto'] ) && !empty( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
						$backup_auto = get_option( 'csl_autoload', array() );
						foreach( $stg['auto'] as $k ) {
							if ( isset( $backup_auto[$k] ) ) {
								$bkp['auto'][$k] = $backup_auto[$k];
							}
						}
					}

					if ( isset( $stg['std'] ) && !empty( $stg['std'] ) && is_array( $stg['std'] ) ) {
						$bkp['std'] = get_option( 'csl_settings_' . $_POST['csl']['plugin'], array() );
					}

					if ( isset( $stg['solids'] ) && !empty( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
						foreach( $stg['solids'] as $k ) {
							$bkp['solids'][$k] = get_option( $k );
						}
					}

					$bkp['time'] = time();

					update_option( '_csl_settings_backup_' . $_POST['csl']['plugin'], $bkp );

					wp_send_json( array( 'success' => true ) );
					exit;
				break;

				case 'restore' :
					$bkp = get_option( '_csl_settings_backup_' . $_POST['csl']['plugin'] );

					if ( isset( $bkp['auto'] ) && !empty( $bkp['auto'] ) && is_array( $bkp['auto'] ) ) {
						$bkp['auto'] = array_merge( get_option( 'csl_autoload', array() ), $bkp['auto'] );
						update_option( 'csl_autoload', $bkp['auto'], true );
					}

					if ( isset( $bkp['std'] ) && !empty( $bkp['std'] ) && is_array( $bkp['std'] ) ) {
						update_option( 'csl_settings_' . $_POST['csl']['plugin'], $bkp['std'], false );
					}

					if ( isset( $bkp['solids'] ) && !empty( $bkp['solids'] ) && is_array( $bkp['solids'] ) ) {
						foreach( $bkp['solids'] as $key => $solid ) {
							update_option( $key, $solid, false );
						}
					}

					wp_send_json( array( 'success' => true ) );
					exit;
				break;

				case 'reset' :
					$stg = isset( $_POST['csl']['settings'] ) && is_array( $_POST['csl']['settings'] ) ? $_POST['csl']['settings'] : array();

					if ( isset( $stg['auto'] ) && !empty( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
						$opt = get_option( 'csl_autoload', array() );

						foreach( $opt as $k => $v ) {
							if ( in_array( $k, $stg['auto'] ) ) {
								unset( $opt[$k] );
							}
						}

						update_option( 'csl_autoload', $opt, true );
					}

					delete_option( 'csl_settings_' . $_POST['csl']['plugin'] );
					if ( $_POST['csl']['plugin'] == 'product_filter' ) {
						if ( get_option( 'wc_settings_prdctfltr_version', false ) !== false ) {
							delete_option( 'wc_settings_prdctfltr_version' );
						}
					}

					if ( !empty( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
						foreach( $stg['solids'] as $key ) {
							delete_option( $key );
						}
					}

					wp_send_json( array( 'success' => true ) );
					exit;
				break;

				case 'register' :
					SevenVX()->_check_register();
				break;

				case 'license_details' :
					SevenVX()->_check_license();
				break;

				case 'filter' :
					SevenVX()->_filter_get();
				break;

				default :
					SevenVX()->ajax_die($opt);
				break;

			}

		}

		public function get_real_key( $plugin = 'woopack' ) {
			if ( class_exists( 'wo_for_WooCommerce' ) ) {
				$plugin = 'woopack';
			}

			$code = get_option( 'wop_key_' . $plugin );

			return $code === false ? 'false' : $code;
		}

		public function get_key( $plugin = 'woopack' ) {
			if ( class_exists( 'wo_for_WooCommerce' ) ) {
				$plugin = 'woopack';
			}

			return get_option( 'wop_key_' . $plugin ) === false ? 'false' : 'true';
		}

		public function __curl_url( $url ) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url );

			$cr = curl_exec($ch);

			curl_close($ch);

			return $cr;
		}

		public function _get_license_url() {
			return preg_replace( '/^www\./', '', preg_replace( '#^http(s)?://#', '', get_bloginfo('url') ) );
		}

		public function _check_license() {
			$plugin = isset( $_POST['csl']['plugin'] ) ? $_POST['csl']['plugin'] : false;
			$key = SevenVX()->get_real_key( $plugin );

			$confirm = 'https://help.woopack.com/my-support-tickets/?wop=view&code=' . $key . '&plugin=' . $plugin . '&website=' . $this->_get_license_url();
			$response = json_decode( $this->__curl_url( $confirm ) );

			if ( isset( $response->success ) && $response->success === true && isset( $response->entry ) ) {
				wp_send_json( array( 'success' => true, 'license' => $response->entry ) );
				exit;
			}
			else if ( isset( $response->success ) && $response->success === false && isset( $response->remove ) && $response->remove === true ) {
				delete_option( 'wop_key_' . $plugin );
			}

			wp_send_json( array( 'success' => false ) );
			exit;
		}

		public function _check_register() {
			$key = isset( $_POST['csl']['key'] ) ? $_POST['csl']['key'] : false;
			$plugin = isset( $_POST['csl']['plugin'] ) ? $_POST['csl']['plugin'] : false;

			if ( $key === false || $plugin === false ) {
				SevenVX()->ajax_die( array() );
			}

			$confirm = 'https://help.woopack.com/my-support-tickets/?wop=register&code=' . $key . '&plugin=' . $plugin . '&website=' . $this->_get_license_url();
			$response = json_decode( $this->__curl_url( $confirm ) );

			if ( isset( $response->success ) && $response->success === true ) {
				update_option( 'wop_key_' . $plugin, $key, false );

				wp_send_json( array( 'success' => true ) );
				exit;
			}

			wp_send_json( array( 'success' => false ) );
			exit;
		}

		public function _filter_get() {

			$key = isset( $_POST['csl']['settings'] ) ? $_POST['csl']['settings'] : false;

			if ( $key === false ) {
				SevenVX()->ajax_die( array() );
			}

			wp_send_json( Prdctfltr()->___get_preset( $key ) );
			exit;

		}

		public function get_for_options( $stg ) {
			$opt = array(
				'auto' => array(),
				'std' => array(),
			);

			if ( isset( $stg['auto'] ) && is_array( $stg['auto'] ) ) {
				$opt['auto'] = $stg['auto'];
			}

			if ( isset( $stg['std'] ) && is_array( $stg['std'] ) ) {
				$opt['std'] = $stg['std'];
			}

			if ( isset( $stg['solids'] ) && is_array( $stg['solids'] ) ) {
				$opt['solids'] = $stg['solids'];
			}

			return $opt;
		}


		public static function compile_less( $solids, $less_variables ) {

			$access_type = get_filesystem_method();

			if( $access_type === 'direct' ) {
				$creds = request_filesystem_credentials( site_url() . '/wp-content/', '', false, false, array() );

				if ( !WP_Filesystem( $creds ) ) {
					return false;
				}

				require_once( 'less/lessc.inc.php' );

				$src = $solids['url'] . '/assets/less/' . $solids['file'] . '.less';

				$src_scheme = wp_parse_url( $src, PHP_URL_SCHEME );

				$wp_content_url_scheme = wp_parse_url( WP_CONTENT_URL, PHP_URL_SCHEME );

				if ( $src_scheme != $wp_content_url_scheme ) {

					$src = set_url_scheme( $src, $wp_content_url_scheme );

				}

				$file = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, esc_url( $src ) );

				$less = new lessc;

				foreach( $less_variables as $k => $v ) {
					if ( is_array( $v ) ) {
						$less_variables[$k] = implode( ',', $v );
					}
				}

				$less->setFormatter( 'compressed' );
				$less->setPreserveComments( 'false' );
				$less->setVariables( $less_variables );

				$compile = $less->cachedCompile( $file );

				$upload = wp_upload_dir();

				$id = uniqid();

				$check = get_option( 'csl_autoload', array() );

				if ( $check ){
					$cached = isset( $check[$solids['option']] ) ? $check[$solids['option']] : false;
				}

				if ( $cached === false ) {
					$cached_transient = '';
				}
				else {
					if ( isset( $cached['id'] ) ) {
						$cached_transient = $cached['id'];
						if ( $cached['last_known'] !== '' ) {
							$delete = untrailingslashit( $upload['basedir'] ) . '/' . sanitize_file_name( $solids['file'] . '-' . $cached['last_known'] . '.css' );
							if ( is_writable( $delete ) ) {
								unlink( $delete );
							}
						}
					}
					else {
						$cached_transient = '';
					}
				}

				global $wp_filesystem;
				if ( $wp_filesystem->put_contents( untrailingslashit( $upload['basedir'] ) . '/' . sanitize_file_name( $solids['file'] . '-' . $id . '.css' ), self::optimize_less( $compile['compiled'] ), FS_CHMOD_FILE ) ) {
					return array(
						'id' => $id,
						'url' => untrailingslashit( $upload['baseurl'] ) . '/' . sanitize_file_name( $solids['file'] . '-' . $id . '.css' ),
						'last_known' => $cached_transient,
					);
				}
			}


		}

		public static function optimize_less( $file_contents ) {
			$file_contents = preg_replace( '/([\w-]+)\s*:\s*unset;?/', '', $file_contents );
			$file_contents = preg_replace( '/([\w-]+)\s*:\s*unset?/', '', $file_contents );

			return $file_contents;
		}

		public static function get_language() {
			if ( !empty( self::language() ) ) {
				return ' data-language="' . esc_attr( self::language() ) . '"';
			}
			return false;
		}

		public static function language() {
			if ( self::$lang ) {
				return self::$lang;
			}

			self::$lang = '';

			if ( class_exists( 'SitePress' ) ) {
				$default = apply_filters( 'wpml_default_language', NULL );
				$language = apply_filters( 'wpml_current_language', NULL );
				if ( $default !== $language ) {
					$doit = $language;
				}
			}

			if ( function_exists( 'qtranxf_getLanguageDefault' ) ) {
				$default = qtranxf_getLanguageDefault();
				$language = qtranxf_getLanguage();
				if ( $default !== $language ) {
					$doit = $language;
				}
			}

			if ( function_exists( 'pll_default_language' ) ) {
				$default = pll_default_language();
				$language = pll_current_language();
				if ( $default !== $language ) {
					$doit = $language;
				}
			}

			if ( isset( $doit ) ) {
				self::$lang = $doit;
			}

			return self::$lang;
		}

		public static function stripslashes_deep( $value ) {
			$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
			return $value;
		}

		public static function sanitize_color( $color ) {
			if ( empty( $color ) || is_array( $color ) ) {
				return 'rgba(0,0,0,0)';
			}

			if ( false === strpos( $color, 'rgba' ) ) {
				return sanitize_hex_color( $color );
			}

			$color = str_replace( ' ', '', $color );
			sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
			return 'rgba('.$red.','.$green.','.$blue.','.$alpha.')';
		}

		public function _do_options( $plugins, $slug ) {

			$backup = get_option( '_csl_settings_backup_' . $slug, '' );

			if ( $backup !== '' && isset( $backup['time'] ) ) {
				$plugins[$slug]['backup'] = date( get_option( 'time_format', '' ) . ', '. get_option( 'date_format', 'd/m/Y' ), $backup['time'] );
			}

			foreach ( $plugins[$slug]['settings'] as $k => $v ) {
				if ( empty( $v ) || isset( $v['section'] ) && $v['section'] == 'dashboard' ) {
					continue;
				}

				$get = isset( $v['translate'] ) && !empty( $this->language() ) ? $v['id'] . '_' . $this->language() : $v['id'];

				$set = isset( $v['default'] ) ?  $v['default'] : '';

				if ( isset( $v['autoload'] ) && $v['autoload'] === true ) {
					$set = SevenVXGet()->get_option_autoload( $get, $set );
				}
				else {
					$set = SevenVXGet()->get_option( $get, $slug, $set );
				}

				if ( $set === false ) {
					$set = isset( $v['default'] ) ?  $v['default'] : '';
				}

				$plugins[$slug]['settings'][$k]['val'] = $this->stripslashes_deep( $set );
			}

			$plugins[$slug]['key'] = $this->get_key( $slug );

			return apply_filters( 'wop_' . $slug . '_settings', $plugins );
		}

	}

	function SevenVX() {
		return SevenVXSettings::instance();
	}

	SevenVXSettings::instance();

}

endif;

(function($){

	"use strict";

	if (!Element.prototype.matches) {
		Element.prototype.matches = Element.prototype.msMatchesSelector ||
		Element.prototype.webkitMatchesSelector;
	}

	if ( u(wop)===false ) {
		return false;
	}

	var xContainer = document.getElementById( 'woopack' );

	if ( xContainer===null ) {
		return false;
	}

	start_wop();

	function start_wop() {
		create_plugins_menu();
	}

	function create_plugins_menu() {
		var html = '';

		for( var i = 0; i<wop.plugins.length; i++ ) {
			html += get_plugin_card(i);
		}

		xContainer.innerHTML = html;

		do_checks_after();
	}

	function get_plugin_card(i) {
		var template = wp.template( 'wop-plugin' );

		var tmplData = {
			plugin: wop.plugins[i],
		};

		var html = template( tmplData );

		return html;
	}

	$(document).on( 'click', function(e) {

		if (e.target && e.target.matches('.wop-back') ) {
			back_to_dashboard(e);
		}

		if (e.target && e.target.matches('.wop-configure')) {
			create_plugin_enviroment(e);
		}

		if (e.target && e.target.matches('.wop-disable') ) {
			disable_plugin(e);
		}

	} );

	function back_to_dashboard(e) {
		$('#csl-settings').remove();
		$('#woopack-nav').removeClass('wop-plugin-screen');
		$('#woopack').show();
	}

	function disable_plugin(e) {

		if ( ajaxOn == 'active' ) {
			return false;
		}

		ajaxOn = 'active';

		var settings = {
			'type' : 'plugin_switch',
			'plugin' : e.target.dataset.plugin,
			'state' : document.getElementById( 'wop-'+e.target.dataset.plugin ).classList.contains('disabled') ? 'yes' : 'no',
		};

		$.when( wop_ajax(settings) ).done( function(g) {
			$.each( wop.plugins, function(i,o) {
				if ( o.slug == e.target.dataset.plugin ) {
					o.state = settings.state;
				}
			} );

			create_plugins_menu();
		} );

	}

	function do_checks_after() {
		_check_warranties_and_returns();
	}

	function _check_warranties_and_returns() {
		if ( $('#wop-warranties-and-returns.disabled').length>0 ) {
			$('#menu-posts-wcwar_warranty_req:visible').hide();
		}
		else {
			$('#menu-posts-wcwar_warranty_req:hidden').show();
		}
	}

	function create_plugin_enviroment(e) {

		if ( ajaxOn == 'active' ) {
			return false;
		}

		ajaxOn = 'active';

		var settings = {
			'type' : 'get_csl',
			'plugin' : e.target.dataset.plugin,
		};

		$.when( wop_ajax(settings) ).done( function(response) {
			$.each( response, function(i,o) {
				csl[i] = o;
			} );

			$('#woopack').hide().before('<div id="csl-settings"></div>');
			$('#woopack-nav').addClass('wop-plugin-screen');

			csl.initOptions();

		} );

	}

	var ajaxOn = 'notactive';

	function wop_ajax( settings ) {

		var data = {
			action: 'wop_ajax_factory',
			wop: settings
		};

		return $.ajax( {
			type: 'POST',
			url: wop.ajax,
			data: data,
			success: function(response) {
				ajaxOn = 'notactive';
			},
			error: function() {
				alert( 'AJAX Error!' );
				ajaxOn = 'notactive';
			}
		} );

	}

	function u(e) {
		return typeof e == 'undefined' ? false : e;
	}

})(jQuery);

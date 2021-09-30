(function($){
    "use strict";

    if ( u(fcart) === false ) {
        return;
    }

    fcart.options = {};

    add_events();

    function add_events() {
		$(document).on( 'click', function(e) {

			if (fcart.settings[0] == 'yes' ) {
                if (e.target && e.target.id == 'floating-cart') {
                    toggle_cart_contents(e);

                    return false;
                }

                if (e.target && e.target.matches('.floating-cart-overlay')) {
                    _hide_cart_contents(e.target.previousElementSibling);
                }
            }

        } );

        if (fcart.settings[1] == 'yes') {
            $(document.body).on( 'removed_from_cart', function(f) {
                _refresh();
            } );

            $(document.body).on( 'added_to_cart', function(f) {
                _refresh();
            } );
        }
    }

    function _refresh() {
        $('.floating-cart-message').html('<span>'+fcart.localize[0]+'</span>');

        setTimeout( function() {
            $('.floating-cart-message').empty();
        }, parseInt(fcart.settings[2], 10)>0?parseInt(fcart.settings[2]):2500 );

        setTimeout( function() {
            _check_width(false);
        }, 50 );
    }

    function toggle_cart_contents(e) {
        if (e.target.parentNode.parentNode && e.target.parentNode.parentNode.matches('.floating-cart')) {
            _hide_cart_contents(e.target.parentNode.parentNode);
        }
        else {
            _show_cart_contents(e.target.parentNode.parentNode);
        }
    }

    function _show_cart_contents(f) {
        fcart.options.open = true;
        f.classList.add('floating-cart-active');

        _check_width(f);
    }

    function _hide_cart_contents(f) {
        fcart.options.open = false;
        f.classList.remove('floating-cart-active');
    }

    function _check_width(f) {
        var contents, contentsWidth, bodyWidth;

        contents = f!==false ? f.getElementsByClassName( 'floating-cart-content' ) : document.body.getElementsByClassName( 'floating-cart-content' );

        contentsWidth = contents[0].getBoundingClientRect().width;
        bodyWidth = document.body.getBoundingClientRect().width-30;

        if (bodyWidth<=contentsWidth) {
            contents[0].setAttribute("style", "max-width: "+bodyWidth+"px;");
        } else {
            contents[0].setAttribute("style", "max-width: "+420+"px;");
        }
    }

	window.addEventListener( 'resize', debounceFunc(function(e) {
		_check_width(false);
	} ) );

	function debounceFunc(func) {
		var timer;

		return function(event){
			if (timer) clearTimeout(timer);
			timer = setTimeout(func,300,event);
		};
	}

    function u(e) {
		return typeof e == 'undefined' ? false : e;
	}

})(jQuery);

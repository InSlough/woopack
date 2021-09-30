<?php

    if ( $isAdmin || !isset( $options['product-filters'] ) || $options['product-filters'] == 'yes' ) {
        include_once( 'prdctfltr/prdctfltr.php' );
    }

    if ( $isAdmin || !isset( $options['product-options'] ) || $options['product-options'] == 'yes' ) {
        include_once( 'improved-variable-product-attributes/improved-variable-product-attributes.php' );
    }

    if ( $isAdmin || !isset( $options['product-badges'] ) || $options['product-badges'] == 'yes' ) {
        include_once( 'improved-sale-badges/improved-sale-badges.php' );
    }

    if ( $isAdmin || !isset( $options['share-print-pdf'] ) || $options['share-print-pdf'] == 'yes' ) {
        include_once( 'share-print-pdf-woocommerce/share-woo.php' );
    }

    if ( $isAdmin || !isset( $options['price-commander-wop'] ) || $options['price-commander-wop'] == 'yes' ) {
        include_once( 'price-commander-wop/price-commander-wop.php' );
    }

    if ( $isAdmin || !isset( $options['live-search-wop'] ) || $options['live-search-wop'] == 'yes' ) {
        include_once( 'live-search-wop/live-search-wop.php' );
    }

    if ( $isAdmin || !isset( $options['floating-cart-wop'] ) || $options['floating-cart-wop'] == 'yes' ) {
        include_once( 'floating-cart-wop/floating-cart-wop.php' );
    }


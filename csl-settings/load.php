<?php

if ( !function_exists( 'wopcb' ) ) {
	function wopcb() {
		if ( isset( $_REQUEST['ct_builder'] ) && $_REQUEST['ct_builder'] ) {
			return true;
		}

		if ( isset( $_REQUEST['et_tb'] ) && $_REQUEST['et_tb'] ) {
			return true;
        }

        if ( apply_filters( 'csl__disable_load', false ) ) {
            return true;
        }

		return false;
	}
}

<div class="isb_sale_badge <?php echo esc_attr( $isb_class ); ?>" data-id="<?php echo esc_attr( $isb_price['id'] ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="60" height="60" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 60 60" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
			<path class="<?php echo esc_attr( $isb_curr_set['color'] ); ?>" d="M52.6435 12.2024l0.3528 3.3739c0.0914,0.8743 0.5041,1.5919 1.2139,2.1105l2.7264 1.9918c1.0638,0.7772 1.4901,2.0913 1.0849,3.3449l-1.0457 3.2361c-0.2697,0.8346 -0.1837,1.6557 0.253,2.4163l1.6917 2.9459c0.6555,1.1418 0.5114,2.5144 -0.3669,3.4952l-2.2603 2.5242c-0.5855,0.6539 -0.84,1.4402 -0.7488,2.3132l0.3534 3.3809c0.1372,1.312 -0.554,2.511 -1.7581,3.05l-3.0693 1.3742c-0.8039,0.3599 -1.358,0.9775 -1.6288,1.8156l-1.0397 3.2174c-0.407,1.2595 -1.5311,2.0776 -2.8547,2.0776l-3.3414 0c-0.8841,0 -1.6451,0.34 -2.2349,0.9985l-2.238 2.4995c-0.8852,0.9885 -2.2496,1.279 -3.4607,0.7368l-3.0466 -1.3638c-0.8082,-0.3618 -1.6432,-0.3618 -2.4514,0l-3.0465 1.3638c-1.2111,0.5422 -2.5756,0.2517 -3.4608,-0.7369l-2.238 -2.4994c-0.5898,-0.6585 -1.3508,-0.9985 -2.2349,-0.9985l-3.3414 0c-1.3236,0 -2.4477,-0.8181 -2.8547,-2.0776l-1.0397 -3.2174c-0.2708,-0.8381 -0.8249,-1.4557 -1.6288,-1.8156l-3.0695 -1.3742c-1.204,-0.539 -1.8951,-1.738 -1.7579,-3.05l0.3534 -3.3809c0.0912,-0.873 -0.1633,-1.6593 -0.7488,-2.3132l-2.2604 -2.5242c-0.8782,-0.9808 -1.0223,-2.3534 -0.3668,-3.4951l1.6917 -2.946c0.4367,-0.7606 0.5227,-1.5817 0.253,-2.4163l-1.0457 -3.2361c-0.4051,-1.2536 0.0212,-2.5677 1.085,-3.3449l2.7263 -1.9918c0.7098,-0.5185 1.1226,-1.2362 1.214,-2.1103l0.3527 -3.3741c0.1374,-1.3142 1.0646,-2.3457 2.3567,-2.6218l3.2765 -0.7003c0.863,-0.1844 1.5351,-0.6746 1.9746,-1.4399l1.6771 -2.9208c0.6601,-1.1496 1.9324,-1.717 3.2287,-1.4399l3.2631 0.6974c0.8656,0.185 1.682,0.0108 2.3967,-0.5113l2.7004 -1.9728c1.0719,-0.783 2.4676,-0.783 3.5394,0l2.7004 1.9728c0.7147,0.5222 1.5311,0.6963 2.3967,0.5113l3.2631 -0.6974c1.2963,-0.2771 2.5687,0.2903 3.2287,1.4399l1.6771 2.9208c0.4395,0.7653 1.1116,1.2555 1.9746,1.4399l3.2765 0.7003c1.2921,0.2761 2.2193,1.3077 2.3567,2.6218z"/>
		</g>
	</svg>
	<div class="isb_sale_percentage">
		<span class="isb_percentage">
			<?php echo esc_html( $isb_price['percentage'] ); ?>
		</span>
		<span class="isb_percentage_text">
			<?php esc_html_e('%', 'woopack' ); ?>
		</span>
	</div>
	<div class="isb_sale_text">
		<?php
			if ( $isb_price['type'] == 'simple' || is_singular( 'product' ) && $isb_price['id'] != 0 ) {
				esc_html_e('Save', 'woopack' );
			}
			else {
				esc_html_e('Up to', 'woopack' );
			}
		?>
	</div>
<?php
	if ( isset($isb_price['time']) ) {
?>
	<div class="isb_scheduled_sale isb_scheduled_<?php echo esc_attr( $isb_price['time_mode'] ); ?> <?php echo esc_attr( $isb_curr_set['color'] ); ?>">
		<span class="isb_scheduled_text">
			<?php
				if ( $isb_price['time_mode'] == 'start' ) {
					esc_html_e('Starts in', 'woopack' );
				}
				else {
					esc_html_e('Ends in', 'woopack' );
				}
			?>
		</span>
		<span class="isb_scheduled_time isb_scheduled_compact">
			<?php echo esc_html( $isb_price['time'] ); ?>
		</span>
	</div>
<?php
	}
?>
</div>

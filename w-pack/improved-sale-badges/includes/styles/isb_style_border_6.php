<div class="isb_sale_badge isb_group_responsive isb_group_border <?php echo esc_attr( $isb_class ); ?>" data-id="<?php echo esc_attr( $isb_price['id'] ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="200" height="200" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="-5 -5 210 210" xmlns:xlink="http://www.w3.org/1999/xlink">
		<path class="<?php echo esc_attr( $isb_curr_set['color'] ); ?> isb_stroke" d="M14 66l52 -52c19,-19 49,-19 68,0l52 52c19,19 19,49 0,68l-52 52c-19,19 -49,19 -68,0l-52 -52c-19,-19 -19,-49 0,-68z"/>
	</svg>
	<div class="isb_sale_percentage isb_color">
		<?php echo esc_html( $isb_price['percentage'] ); ?>
	</div>
<?php
	if ( isset( $isb_price['time'] ) ) {
?>
	<div class="isb_scheduled_sale isb_scheduled_<?php echo esc_attr( $isb_price['time_mode'] ); ?> <?php echo esc_attr( $isb_curr_set['color'] ); ?> isb_color">
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

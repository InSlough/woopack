<div class="isb_sale_badge <?php echo esc_attr( $isb_class ); ?>" data-id="<?php echo esc_attr( $isb_price['id'] ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="66" height="66" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 66 66" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
		<?php
			if ( $isb_curr_set['position'] == 'isb_right' ) {
		?>
			<polygon class="<?php echo esc_attr( $isb_curr_set['color'] ); ?>" points="13.4694,0 -0,61.2856 66,47.8163 66,0 "/>
		<?php
			}
			else {
		?>
			<polygon class="<?php echo esc_attr( $isb_curr_set['color'] ); ?>" points="52.5306,0 66,61.2856 -0,47.8163 -0,0 "/>
		<?php
			}
		?>
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
	<div class="isb_money_saved">
		<span class="isb_saved_text">
			<?php
				if ( $isb_price['type'] == 'simple' || is_singular( 'product' ) && $isb_price['id'] != 0 ) {
					esc_html_e('Save', 'woopack' );
				}
				else {
					esc_html_e('Up to', 'woopack' );
				}
			?>
		</span>
		<span class="isb_saved">
			<?php echo strip_tags( wc_price( $isb_price['difference'] ) ); ?>
		</span>
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

<div class="isb_sale_badge <?php echo esc_attr( $isb_class ); ?>" data-id="<?php echo esc_attr( $isb_price['id'] ); ?>">
	<div class="isb_sale_percentage">
		<span class="isb_percentage">
			<?php echo esc_html( $isb_price['percentage'] ); ?>
		</span>
		<span class="isb_percentage_text">
			<?php esc_html_e('%', 'woopack' ); ?>
		</span>
	</div>
	<div class="isb_sale_text"><?php esc_html_e('OFF', 'woopack' ); ?></div>
<?php
	if ( isset( $isb_price['time'] ) ) {
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

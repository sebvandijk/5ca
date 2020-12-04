<?php
/**
 * Bulk Smush upgrade meta box.
 *
 * @since 3.7.2
 * @package WP_Smush
 */

$total_images = $this->get_total_images_to_smush();

if ( 0 === $total_images ) {
	$message = esc_html__( 'Get Smush Pro and bulk optimize every image you’ve ever added to your site with one click and fix your Google PageSpeed with the best image optimizer WordPress has ever known.', 'wp-smushit' );

	$pro_upgrade_url = add_query_arg(
		array(
			'utm_source'   => 'smush',
			'utm_medium'   => 'plugin',
			'utm_campaign' => 'smush_bulksmush_upsell_notice',
		),
		$this->upgrade_url
	);
} else {
	$message = sprintf(
		/* translators: 1. opening 'strong' tag, 2. closing 'strong' tag, 3. count of images to smush. */
		esc_html__( 'Bulk smush %1$sall your %3$d images%2$s in one click and get a %1$s30%% Welcome Discount%2$s just for Smush Free users!', 'wp-smushit' ),
		'<strong>',
		'</strong>',
		esc_html( $total_images )
	);

	$pro_upgrade_url = add_query_arg(
		array(
			'coupon'       => 'SMUSH30OFF',
			'checkout'     => 0,
			'utm_source'   => 'smush',
			'utm_medium'   => 'plugin',
			'utm_campaign' => 'smush_bulksmush_limit_reached_tryproforfree',
		),
		$this->upgrade_url
	);
}
?>
<div id="wp-smush-bulk-smush-upgrade-text-container">
	<p><?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" class="sui-button sui-button-purple" target="_blank">
		<?php esc_html_e( 'Try pro absolutely free', 'wp-smushit' ); ?>
	</a>
</div>

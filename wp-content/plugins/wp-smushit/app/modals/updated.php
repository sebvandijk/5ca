<?php
/**
 * Show Updated Features modal.
 *
 * @package WP_Smush
 *
 * @since 3.7.0
 *
 * @var string $yearly_url   URL for the yearly plan CTA button.
 * @var string $monthly_url  URL for the monthly plan CTA button.
 * @var string $main_cta_url URL for the modal's CTA button.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="smush-updated-dialog"
		class="sui-modal-content smush-updated-dialog"
		aria-modal="true"
		aria-labelledby="smush-title-updated-dialog"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-sides--60 sui-spacing-top--60 sui-spacing-bottom--30">
				<button class="sui-button-icon sui-button-float--right" data-modal-close="" onclick="WP_Smush.onboarding.hideUpgradeModal()">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
				</button>

				<h3 class="sui-box-title sui-lg" id="smush-title-updated-dialog" style="white-space: normal">
					<?php esc_html_e( 'Introducing our new Smush-Only Plans! 🎉', 'wp-smushit' ); ?>
				</h3>

				<p class="sui-description">
					<?php esc_html_e( "We've heard your requests to get Smush Pro by itself and now you can. To celebrate, we're throwing in a special 30% intro discount! Grab it while it lasts.", 'wp-smushit' ); ?>
				</p>
			</div>

			<div class="sui-box-body sui-content-center sui-spacing-sides--50 sui-spacing-top--0">

				<div id="wp-smush-yearly-plan-highlight" class="sui-col-sm-6">
					<div class="wp-smush-icon-container">
						<span class="sui-icon-smush sui-lg" aria-hidden="true"></span>
					</div>
					<div class="wp-smush-plan-highlight-text-container">
						<h4><?php esc_html_e( '2 Months Free + 30% OFF', 'wp-smushit' ); ?></h4>
						<small><?php esc_html_e( 'Limited offer, get a yearly plan with a discount!', 'wp-smushit' ); ?></small>
					</div>
				</div>

				<div class="sui-border-frame">
					<div id="wp-smush-plan-comparison" class="sui-row">
						<div class="sui-col-sm-6">
							<div class="wp-smush-plan-wrapper">
								<div class="wp-smush-plan-content">
									<h4><?php esc_html_e( 'Yearly', 'wp-smushit' ); ?></h4>
									<p class="wp-smush-plan-description"><?php esc_html_e( 'Smush Pro yearly plan for all your image optimization needs', 'wp-smushit' ); ?></p>
									<del>$72</del>
									<span class="wp-smush-plan-price">$42</span>
									<span class="wp-smush-plan-period"><?php esc_html_e( '/year', 'wp-smushit' ); ?></span>
									<p class="wp-smush-price-description"><?php esc_html_e( '*Just $3.50 per month.', 'wp-smushit' ); ?><br/><span style="color: #8D00B1;"><?php esc_html_e( 'Total of 5 months free!', 'wp-smushit' ); ?></span></p>
								</div>
								<div class="wp-smush-plan-cta-container">
									<a href="<?php echo esc_url( $yearly_url ); ?>" target="_blank" class="sui-button sui-button-purple"><?php esc_html_e( 'Try Free For 7 Days', 'wp-smushit' ); ?></a>
								</div>
							</div>
						</div>
						<div class="sui-col-sm-6">
							<div class="wp-smush-plan-wrapper">
								<div class="wp-smush-plan-content">
									<h4><?php esc_html_e( 'Monthly', 'wp-smushit' ); ?></h4>
									<p class="wp-smush-plan-description"><?php esc_html_e( 'Smush Pro monthly plan for all your image optimization needs', 'wp-smushit' ); ?></p>
									<span class="wp-smush-plan-price">$6</span>
									<span class="wp-smush-plan-period"><?php esc_html_e( '/month', 'wp-smushit' ); ?></span>
								</div>
								<div class="wp-smush-plan-cta-container">
									<a href="<?php echo esc_url( $monthly_url ); ?>" target="_blank" class="sui-button sui-button-purple"><?php esc_html_e( 'Try Free For 7 Days', 'wp-smushit' ); ?></a>
								</div>
							</div>
						</div>
					</div>

					<div id="wp-smush-pro-benefits-title">
						<span class="sui-icon-smush sui-md" aria-hidden="true"></span>
						<span><?php esc_html_e( 'Smush Pro benefits', 'wp-smushit' ); ?></span>
					</div>

					<div class="sui-row smush-pro-features">
						<div class="sui-col-md-6">
							<ul>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'Unlimited bulk Smush', 'wp-smushit' ); ?>
								</div></li>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( '10 GB Smush CDN', 'wp-smushit' ); ?>
								</div></li>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'Super Smush lossy compression', 'wp-smushit' ); ?>
								</div></li>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'NextGen Gallery integration', 'wp-smushit' ); ?>
								</div></li>
							</ul>
						</div>

						<div class="sui-col-md-6">
							<ul>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'Smush original images', 'wp-smushit' ); ?>
								</div></li>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'Automated resizing', 'wp-smushit' ); ?>
								</div></li>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'Auto convert PNG’s to JPEG’s', 'wp-smushit' ); ?>
								</div></li>
								<li class="smush-pro-feature-row"><div class="smush-pro-feature-title">
									<?php esc_html_e( 'WebP Conversion', 'wp-smushit' ); ?>
								</div></li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div class="sui-box-footer sui-content-center sui-flatten">
				<a href="<?php echo esc_url( $main_cta_url ); ?>" target="_blank" class="sui-button sui-button-ghost" onclick="WP_Smush.onboarding.hideUpgradeModal()">
					<i class="sui-icon-open-new-window" aria-hidden="true"></i>
					<?php esc_html_e( 'Check all plans', 'wp-smushit' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>

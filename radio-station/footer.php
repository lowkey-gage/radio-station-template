<?php
/**
 * Site footer.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
</main>

<footer class="site-footer">
	<div class="wrap-wide footer-grid">
		<div>
			<p class="footer-wordmark"><?php echo esc_html(radio_station_mod('name')); ?></p>
			<p class="footer-freq">
				<?php echo esc_html(radio_station_mod('frequency')); ?>
				<span aria-hidden="true"> · </span>
				<?php echo esc_html(radio_station_mod('city')); ?>
			</p>
			<p class="footer-tagline"><?php echo esc_html(radio_station_mod('tagline')); ?></p>
		</div>

		<nav aria-label="<?php esc_attr_e('Footer', 'radio-station'); ?>">
			<?php radio_station_primary_nav(); ?>
		</nav>

		<p class="footer-copy">
			<?php
			echo esc_html(
				sprintf(
					'© %1$s %2$s %3$s',
					wp_date('Y'),
					radio_station_mod('name'),
					radio_station_mod('frequency')
				)
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

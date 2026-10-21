<?php
/**
 * Where to listen.
 *
 * Template Name: Listen
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $stream  = radio_station_mod('stream_url');
    $apps    = array(
        'tunein_url'  => __('TuneIn', 'radio-station'),
        'spotify_url' => __('Spotify', 'radio-station'),
        'apple_url'   => __('Apple Podcasts', 'radio-station'),
    );
    $phone   = radio_station_mod('request_line');
    ?>
	<article <?php post_class('listen-page'); ?>>
		<header class="listen-hero wrap">
			<p class="eyebrow"><?php echo esc_html(radio_station_mod('city')); ?></p>
			<h1><?php esc_html_e('Where to listen', 'radio-station'); ?></h1>
			<p class="listen-frequency"><?php echo esc_html(radio_station_mod('frequency')); ?></p>
			<p class="lede"><?php echo esc_html(radio_station_mod('tagline')); ?></p>
			<?php if (get_the_content() !== '') : ?>
				<div class="prose intro-copy"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>

		<div class="wrap listen-methods">
			<section class="listen-card">
				<h2><?php esc_html_e('On the dial', 'radio-station'); ?></h2>
				<p class="tune-stat"><?php echo esc_html(radio_station_mod('frequency')); ?></p>
				<p><?php echo esc_html(sprintf(__('Over the air in %s.', 'radio-station'), radio_station_mod('city'))); ?></p>
			</section>

			<section class="listen-card">
				<h2><?php esc_html_e('In the browser', 'radio-station'); ?></h2>
				<?php if ($stream) : ?>
					<audio class="listen-audio" controls preload="none" src="<?php echo esc_url($stream); ?>">
						<a href="<?php echo esc_url($stream); ?>"><?php esc_html_e('Open the stream', 'radio-station'); ?></a>
					</audio>
					<p><a href="<?php echo esc_url($stream); ?>"><?php esc_html_e('Open the stream', 'radio-station'); ?></a></p>
				<?php else : ?>
					<p><?php esc_html_e('A browser player appears here once a stream address is saved.', 'radio-station'); ?></p>
					<?php if (current_user_can('customize')) : ?>
						<p class="admin-hint">
							<a href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=radio_station_listen')); ?>">
								<?php esc_html_e('Add a stream URL', 'radio-station'); ?>
							</a>
						</p>
					<?php endif; ?>
				<?php endif; ?>
			</section>

			<?php
			$app_links = array();
			foreach ($apps as $key => $label) {
				$url = radio_station_mod($key);
				if ($url !== '') {
					$app_links[$label] = $url;
				}
			}
			if ($app_links) :
				?>
				<section class="listen-card">
					<h2><?php esc_html_e('Apps', 'radio-station'); ?></h2>
					<ul class="aside-links">
						<?php foreach ($app_links as $label => $url) : ?>
							<li><?php echo radio_station_external_link($url, $label); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ($phone !== '') : ?>
				<section class="listen-card">
					<h2><?php esc_html_e('Request line', 'radio-station'); ?></h2>
					<p><a href="<?php echo esc_url('tel:' . preg_replace('/[^\d+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p>
				</section>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();

<?php
/**
 * Site header.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$live = radio_station_on_air_shows();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#14120f">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'radio-station'); ?></a>

<header class="site-header">
	<div class="wrap-wide site-header-inner">
		<a class="wordmark" href="<?php echo esc_url(home_url('/')); ?>">
			<?php
			$logo_id = (int) get_theme_mod('custom_logo');
			if ($logo_id) {
				echo wp_get_attachment_image($logo_id, 'thumbnail', false, array('class' => 'custom-logo'));
			} else {
				?>
				<svg class="signal-icon" viewBox="0 0 32 32" width="28" height="28" aria-hidden="true" focusable="false">
					<circle cx="16" cy="16" r="3" fill="currentColor"></circle>
					<path d="M10 16a6 6 0 0 1 12 0" fill="none" stroke="currentColor" stroke-width="1.7"></path>
					<path d="M6.5 16a9.5 9.5 0 0 1 19 0" fill="none" stroke="currentColor" stroke-width="1.7"></path>
					<path d="M3 16a13 13 0 0 1 26 0" fill="none" stroke="currentColor" stroke-width="1.7"></path>
				</svg>
				<?php
			}
			?>
			<span class="wordmark-name"><?php echo esc_html(radio_station_mod('name')); ?></span>
			<span class="wordmark-freq"><?php echo esc_html(radio_station_mod('frequency')); ?></span>
		</a>

		<nav class="site-nav" aria-label="<?php esc_attr_e('Primary', 'radio-station'); ?>">
			<?php radio_station_primary_nav(); ?>
		</nav>

		<div class="header-tools">
			<?php get_search_form(); ?>

			<?php if ($live) : ?>
				<a class="on-air-pill" href="<?php echo esc_url(get_permalink($live[0])); ?>">
					<span class="live-dot" aria-hidden="true"></span>
					<span class="on-air-label"><?php esc_html_e('On air', 'radio-station'); ?></span>
					<span class="on-air-title"><?php echo esc_html(get_the_title($live[0])); ?></span>
				</a>
			<?php endif; ?>

			<a class="button button-small" href="<?php echo esc_url(radio_station_page_url('listen')); ?>">
				<?php esc_html_e('Listen', 'radio-station'); ?>
			</a>
		</div>
	</div>
</header>

<main id="main" class="site-main">

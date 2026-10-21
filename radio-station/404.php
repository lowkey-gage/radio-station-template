<?php
/**
 * Not found.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="page-intro wrap">
	<p class="eyebrow"><?php echo esc_html(radio_station_mod('frequency')); ?></p>
	<h1><?php esc_html_e('Page not found', 'radio-station'); ?></h1>
	<p class="lede"><?php esc_html_e('Nothing is scheduled at this address.', 'radio-station'); ?></p>
	<div class="hero-actions">
		<a class="button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to the station', 'radio-station'); ?></a>
		<a class="button button-ghost" href="<?php echo esc_url(get_post_type_archive_link('shows')); ?>"><?php esc_html_e('Browse shows', 'radio-station'); ?></a>
	</div>
</header>

<?php
get_footer();

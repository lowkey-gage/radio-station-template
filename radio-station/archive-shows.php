<?php
/**
 * Show archive.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="page-intro wrap">
	<p class="eyebrow"><?php esc_html_e('Program guide', 'radio-station'); ?></p>
	<h1><?php post_type_archive_title(); ?></h1>
	<p class="lede"><?php esc_html_e('Every show on the station, with its hosts, genre, and the next time it airs.', 'radio-station'); ?></p>
</header>

<?php get_template_part('template-parts/show', 'filters'); ?>
<?php get_template_part('template-parts/show', 'loop'); ?>

<?php
get_footer();

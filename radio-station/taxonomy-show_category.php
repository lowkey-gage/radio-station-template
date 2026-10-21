<?php
/**
 * Show category archive.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="page-intro wrap">
	<p class="eyebrow"><?php esc_html_e('Show category', 'radio-station'); ?></p>
	<h1><?php single_term_title(); ?></h1>
	<?php the_archive_description('<div class="lede">', '</div>'); ?>
</header>

<?php get_template_part('template-parts/show', 'filters'); ?>
<?php get_template_part('template-parts/show', 'loop'); ?>

<?php
get_footer();

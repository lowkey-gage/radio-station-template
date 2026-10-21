<?php
/**
 * DJ archive.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="page-intro wrap">
	<p class="eyebrow"><?php esc_html_e('The voices', 'radio-station'); ?></p>
	<h1><?php post_type_archive_title(); ?></h1>
	<p class="lede"><?php esc_html_e('On-air names, the people behind them, and the shows they host.', 'radio-station'); ?></p>
</header>

<div class="wrap section-end">
	<?php if (have_posts()) : ?>
		<div class="dj-grid">
			<?php
			while (have_posts()) :
				the_post();
				get_template_part('template-parts/dj', 'card');
			endwhile;
			?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'mid_size'           => 1,
				'prev_text'          => __('Previous', 'radio-station'),
				'next_text'          => __('Next', 'radio-station'),
				'screen_reader_text' => __('DJs navigation', 'radio-station'),
			)
		);
		?>
	<?php else : ?>
		<?php
		get_template_part(
			'template-parts/content',
			'none',
			array(
				'message' => __('No DJ profiles yet.', 'radio-station'),
			)
		);
		?>
	<?php endif; ?>
</div>

<?php
get_footer();

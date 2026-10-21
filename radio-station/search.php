<?php
/**
 * Search results across shows, DJs, and pages.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="page-intro wrap">
	<p class="eyebrow"><?php esc_html_e('Search', 'radio-station'); ?></p>
	<h1>
		<?php
		printf(
			/* translators: %s: search query */
			esc_html__('Results for “%s”', 'radio-station'),
			esc_html(get_search_query())
		);
		?>
	</h1>
</header>

<div class="wrap section-end">
	<?php if (have_posts()) : ?>
		<div class="result-list">
			<?php
			while (have_posts()) :
				the_post();
				$type = get_post_type_object(get_post_type());
				?>
				<article <?php post_class('result'); ?>>
					<?php if ($type) : ?>
						<p class="result-type"><?php echo esc_html($type->labels->singular_name); ?></p>
					<?php endif; ?>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="lede"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'mid_size'           => 1,
				'prev_text'          => __('Previous', 'radio-station'),
				'next_text'          => __('Next', 'radio-station'),
				'screen_reader_text' => __('Search navigation', 'radio-station'),
			)
		);
		?>
	<?php else : ?>
		<?php
		get_template_part(
			'template-parts/content',
			'none',
			array(
				'message' => __('Nothing matched that search.', 'radio-station'),
			)
		);
		?>
	<?php endif; ?>
</div>

<?php
get_footer();

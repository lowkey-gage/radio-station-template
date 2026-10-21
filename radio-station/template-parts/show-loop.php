<?php
/**
 * Show archive loop.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap section-end">
	<?php if (have_posts()) : ?>
		<div class="show-grid">
			<?php
			while (have_posts()) :
				the_post();
				get_template_part('template-parts/show', 'card');
			endwhile;
			?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'mid_size'           => 1,
				'prev_text'          => __('Previous', 'radio-station'),
				'next_text'          => __('Next', 'radio-station'),
				'screen_reader_text' => __('Shows navigation', 'radio-station'),
			)
		);
		?>
	<?php else : ?>
		<?php
		get_template_part(
			'template-parts/content',
			'none',
			array(
				'message' => __('No shows match that filter.', 'radio-station'),
			)
		);
		?>
	<?php endif; ?>
</div>

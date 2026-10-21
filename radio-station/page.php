<?php
/**
 * Default page.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    ?>
	<article <?php post_class('wrap sheet page-sheet'); ?>>
		<h1><?php the_title(); ?></h1>
		<div class="prose">
			<?php the_content(); ?>
			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="page-links">' . esc_html__('Pages:', 'radio-station'),
					'after'  => '</nav>',
				)
			);
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();

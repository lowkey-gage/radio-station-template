<?php
/**
 * Single post fallback.
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
		</div>
	</article>
	<?php
endwhile;

get_footer();

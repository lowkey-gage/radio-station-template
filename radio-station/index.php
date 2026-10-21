<?php
/**
 * Fallback index.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="page-intro wrap">
	<h1><?php esc_html_e('News', 'radio-station'); ?></h1>
</header>

<div class="wrap section-end">
	<?php if (have_posts()) : ?>
		<div class="result-list">
			<?php
			while (have_posts()) :
				the_post();
				?>
				<article <?php post_class('result'); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="lede"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<?php get_template_part('template-parts/content', 'none'); ?>
	<?php endif; ?>
</div>

<?php
get_footer();

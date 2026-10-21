<?php
/**
 * Single DJ.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $dj_id = get_the_ID();
    $meta  = radio_station_dj_meta($dj_id);
    $links = array(
        'instagram' => __('Instagram', 'radio-station'),
        'facebook'  => __('Facebook', 'radio-station'),
        'website'   => __('Website', 'radio-station'),
    );
    $shows = radio_station_shows_for_dj($dj_id);
    ?>
	<article <?php post_class('dj-single'); ?>>
		<header class="dj-hero wrap-wide">
			<div class="dj-hero-media">
				<?php if (has_post_thumbnail()) : ?>
					<?php the_post_thumbnail('radio-portrait', array('loading' => 'eager')); ?>
				<?php else : ?>
					<?php radio_station_media_fallback(get_the_title(), 22); ?>
				<?php endif; ?>
			</div>
			<div class="dj-hero-copy">
				<p class="eyebrow"><?php esc_html_e('DJ', 'radio-station'); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php if ($meta['real_name'] !== '') : ?>
					<p class="dj-real"><?php echo esc_html($meta['real_name']); ?></p>
				<?php endif; ?>
				<?php if ($meta['favorite_genre'] !== '') : ?>
					<p class="dj-favorite"><?php echo esc_html(sprintf(__('Favorite genre: %s', 'radio-station'), $meta['favorite_genre'])); ?></p>
				<?php endif; ?>
				<?php
				$socials = array();
				foreach ($links as $key => $label) {
					if ($meta[$key] !== '') {
						$socials[] = radio_station_external_link($meta[$key], $label);
					}
				}
				if ($socials) :
					?>
					<ul class="social-row">
						<?php foreach ($socials as $social) : ?>
							<li><?php echo $social; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</header>

		<?php if (get_the_content() !== '') : ?>
			<div class="wrap sheet prose dj-bio">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<section class="section">
			<div class="wrap section-heading">
				<div>
					<p class="eyebrow"><?php esc_html_e('On the schedule', 'radio-station'); ?></p>
					<h2><?php esc_html_e('Shows hosted', 'radio-station'); ?></h2>
				</div>
			</div>
			<div class="wrap">
				<?php if ($shows) : ?>
					<div class="show-grid">
						<?php
						global $post;
						foreach ($shows as $show_post) {
							$post = $show_post;
							setup_postdata($post);
							get_template_part('template-parts/show', 'card');
						}
						wp_reset_postdata();
						?>
					</div>
				<?php else : ?>
					<p class="lede"><?php esc_html_e('This DJ is not hosting a show yet.', 'radio-station'); ?></p>
				<?php endif; ?>
			</div>
		</section>
	</article>
	<?php
endwhile;

get_footer();

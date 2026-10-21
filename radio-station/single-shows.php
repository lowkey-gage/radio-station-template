<?php
/**
 * Single show.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $show_id    = get_the_ID();
    $record     = radio_station_get_show_record($show_id);
    $days       = radio_station_days();
    $hosts      = radio_station_get_show_djs($show_id);
    $categories = radio_station_show_categories($show_id);
    $active     = radio_station_active_slot($show_id);
    ?>
	<article <?php post_class('show-single'); ?>>
		<header class="show-hero wrap-wide">
			<div class="show-hero-media">
				<?php if (has_post_thumbnail()) : ?>
					<?php the_post_thumbnail('radio-hero', array('loading' => 'eager')); ?>
				<?php else : ?>
					<?php radio_station_media_fallback(get_the_title(), radio_station_genre_hue($record['genre'])); ?>
				<?php endif; ?>
			</div>
			<div class="show-hero-copy">
				<?php if ($record['genre'] !== '') : ?>
					<p class="eyebrow"><?php echo esc_html(radio_station_genre_label($record['genre'])); ?></p>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ($active) : ?>
					<p class="show-live-line"><span class="live-dot" aria-hidden="true"></span> <?php echo esc_html(sprintf(__('On air until %s', 'radio-station'), radio_station_format_clock($active['slot']['end']))); ?></p>
				<?php elseif (!radio_station_is_active($show_id)) : ?>
					<p class="show-inactive-line"><?php esc_html_e('This show is inactive.', 'radio-station'); ?></p>
				<?php endif; ?>
				<?php if ($hosts) : ?>
					<ul class="host-row">
						<?php foreach ($hosts as $dj) : ?>
							<li>
								<a class="host-chip" href="<?php echo esc_url(get_permalink($dj)); ?>">
									<?php
									if (has_post_thumbnail($dj)) {
										echo get_the_post_thumbnail($dj, 'thumbnail', array('alt' => ''));
									} else {
										echo '<span class="host-initial">' . esc_html(radio_station_initial(get_the_title($dj))) . '</span>';
									}
									?>
									<span><?php echo esc_html(get_the_title($dj)); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</header>

		<div class="wrap-wide detail-layout">
			<div class="sheet prose">
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
			<aside class="detail-aside">
				<h2><?php esc_html_e('This week', 'radio-station'); ?></h2>
				<?php
				$slots = array();
				foreach ($days as $key => $label) {
					$slot = isset($record['schedule'][$key]) ? $record['schedule'][$key] : null;
					if (radio_station_slot_is_scheduled($slot)) {
						$slots[] = '<li><span>' . esc_html($label) . '</span> <span>' . esc_html(radio_station_format_range($slot)) . '</span></li>';
					}
				}
				if ($slots) {
					echo '<ul class="aside-schedule">' . implode('', $slots) . '</ul>';
				} else {
					echo '<p>' . esc_html__('No broadcast times yet.', 'radio-station') . '</p>';
				}
				?>

				<?php if ($record['genre'] !== '') : ?>
					<h2><?php esc_html_e('Genre', 'radio-station'); ?></h2>
					<p>
						<a href="<?php echo esc_url(add_query_arg('genre', $record['genre'], get_post_type_archive_link('shows'))); ?>">
							<?php echo esc_html(radio_station_genre_label($record['genre'])); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ($categories) : ?>
					<h2><?php esc_html_e('Categories', 'radio-station'); ?></h2>
					<ul class="aside-links">
						<?php foreach ($categories as $category) : ?>
							<li><a href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</aside>
		</div>
	</article>
	<?php
endwhile;

get_footer();

<?php
/**
 * Show card. Expects the loop.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$show_id = get_the_ID();
$record  = radio_station_get_show_record($show_id);
$active  = radio_station_active_slot($show_id);
$hosts   = radio_station_host_links($show_id);
$excerpt = wp_trim_words(get_the_excerpt(), 18);
?>
<article <?php post_class('show-card'); ?>>
	<a class="show-card-media" href="<?php the_permalink(); ?>">
		<?php if (has_post_thumbnail()) : ?>
			<?php the_post_thumbnail('radio-card', array('loading' => 'lazy')); ?>
		<?php else : ?>
			<?php radio_station_media_fallback(get_the_title(), radio_station_genre_hue($record['genre'])); ?>
		<?php endif; ?>
		<?php if (!radio_station_is_active($show_id)) : ?>
			<span class="show-badge show-badge-inactive"><?php esc_html_e('Inactive', 'radio-station'); ?></span>
		<?php elseif ($active) : ?>
			<span class="show-badge show-badge-live"><?php esc_html_e('On air', 'radio-station'); ?></span>
		<?php endif; ?>
	</a>
	<div class="show-card-body">
		<?php if ($record['genre'] !== '') : ?>
			<p class="eyebrow">
				<a href="<?php echo esc_url(add_query_arg('genre', $record['genre'], get_post_type_archive_link('shows'))); ?>">
					<?php echo esc_html(radio_station_genre_label($record['genre'])); ?>
				</a>
			</p>
		<?php endif; ?>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ($excerpt !== '') : ?>
			<p class="show-card-excerpt"><?php echo esc_html($excerpt); ?></p>
		<?php endif; ?>
		<?php if ($hosts !== '') : ?>
			<p class="show-card-hosts">
				<?php esc_html_e('With', 'radio-station'); ?>
				<?php echo $hosts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in radio_station_host_links(). ?>
			</p>
		<?php endif; ?>
		<?php if ($active) : ?>
			<p class="show-card-when"><?php echo esc_html(sprintf(__('On air until %s', 'radio-station'), radio_station_format_clock($active['slot']['end']))); ?></p>
		<?php else : ?>
			<?php $when = radio_station_next_airing_label($show_id); ?>
			<?php if ($when !== '') : ?>
				<p class="show-card-when"><?php echo esc_html($when); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</article>

<?php
/**
 * DJ card. Expects the loop.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$dj_id = get_the_ID();
$meta  = radio_station_dj_meta($dj_id);
$count = count(radio_station_shows_for_dj($dj_id));
?>
<article <?php post_class('dj-card'); ?>>
	<a href="<?php the_permalink(); ?>">
		<span class="dj-card-media">
			<?php if (has_post_thumbnail()) : ?>
				<?php the_post_thumbnail('radio-portrait', array('loading' => 'lazy')); ?>
			<?php else : ?>
				<?php radio_station_media_fallback(get_the_title(), 18 + (abs((int) crc32(get_the_title())) % 36)); ?>
			<?php endif; ?>
		</span>
		<div class="dj-card-body">
			<h3 class="dj-card-name"><?php the_title(); ?></h3>
			<?php if ($meta['real_name'] !== '') : ?>
				<span class="dj-card-real"><?php echo esc_html($meta['real_name']); ?></span>
			<?php endif; ?>
			<span class="dj-card-meta">
				<?php
				$bits = array();
				if ($meta['favorite_genre'] !== '') {
					$bits[] = $meta['favorite_genre'];
				}
				$bits[] = sprintf(_n('%d show', '%d shows', $count, 'radio-station'), $count);
				echo esc_html(implode(' · ', $bits));
				?>
			</span>
		</div>
	</a>
</article>

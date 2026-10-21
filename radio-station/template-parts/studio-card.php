<?php
/**
 * Now-playing card for the home hero.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$live = radio_station_on_air_shows();
$next = radio_station_next_show();
$show = null;
$mode = '';

if ($live) {
    $show = $live[0];
    $mode = 'live';
} elseif ($next) {
    $show = $next['post'];
    $mode = 'next';
}
?>
<aside class="studio-card" aria-label="<?php esc_attr_e('Studio', 'radio-station'); ?>">
	<?php if (!$show) : ?>
		<p class="eyebrow"><?php esc_html_e('Studio', 'radio-station'); ?></p>
		<h2 class="studio-title"><?php esc_html_e('The schedule is clear.', 'radio-station'); ?></h2>
		<p><?php esc_html_e('Add a show and give it broadcast times to put something on the air.', 'radio-station'); ?></p>
	<?php else : ?>
		<?php $slot = ($mode === 'live') ? radio_station_active_slot($show->ID) : null; ?>
		<p class="eyebrow">
			<?php if ($mode === 'live') : ?>
				<span class="live-dot" aria-hidden="true"></span>
				<?php esc_html_e('On air', 'radio-station'); ?>
			<?php else : ?>
				<?php esc_html_e('Next up', 'radio-station'); ?>
			<?php endif; ?>
		</p>
		<h2 class="studio-title">
			<a href="<?php echo esc_url(get_permalink($show)); ?>"><?php echo esc_html(get_the_title($show)); ?></a>
		</h2>
		<?php $hosts = radio_station_host_list($show->ID); ?>
		<?php if ($hosts !== '') : ?>
			<p class="studio-hosts"><?php echo esc_html(sprintf(__('With %s', 'radio-station'), $hosts)); ?></p>
		<?php endif; ?>
		<?php if ($mode === 'live' && $slot) : ?>
			<p class="studio-time"><?php echo esc_html(sprintf(__('Until %s', 'radio-station'), radio_station_format_clock($slot['slot']['end']))); ?></p>
		<?php elseif ($mode === 'next') : ?>
			<p class="studio-time">
				<?php
				$days  = radio_station_days();
				$label = ($next['day'] === radio_station_now()['day']) ? __('Today', 'radio-station') : $days[$next['day']];
				echo esc_html($label . ', ' . radio_station_format_range($next['slot']));
				?>
			</p>
		<?php endif; ?>
		<?php if (count($live) > 1) : ?>
			<p class="studio-extra">
				<a href="<?php echo esc_url(radio_station_page_url('schedule')); ?>">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of additional live shows */
							_n('%d more show is on the air', '%d more shows are on the air', count($live) - 1, 'radio-station'),
							count($live) - 1
						)
					);
					?>
				</a>
			</p>
		<?php endif; ?>
		<a class="button" href="<?php echo esc_url(radio_station_page_url('listen')); ?>"><?php esc_html_e('Listen live', 'radio-station'); ?></a>
	<?php endif; ?>
</aside>

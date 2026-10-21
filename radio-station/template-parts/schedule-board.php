<?php
/**
 * Seven-day broadcast board.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$board    = radio_station_week_board();
$days     = radio_station_days();
$today    = radio_station_now()['day'];
$now      = radio_station_now()['time'];
$only_day = (isset($args['day']) && $args['day'] !== 'week' && isset($days[$args['day']])) ? $args['day'] : '';
?>
<div class="wrap-wide">
	<?php if ($only_day === '') : ?>
		<p class="schedule-hint"><?php esc_html_e('Swipe sideways for the rest of the week.', 'radio-station'); ?></p>
	<?php endif; ?>
	<div class="schedule-board<?php echo $only_day !== '' ? ' is-single-day' : ''; ?>" tabindex="0" aria-label="<?php esc_attr_e('Weekly schedule', 'radio-station'); ?>">
		<?php foreach ($days as $day => $label) : ?>
			<?php if ($only_day !== '' && $day !== $only_day) { continue; } ?>
			<section class="schedule-day<?php echo $day === $today ? ' is-today' : ''; ?>">
				<h2>
					<?php echo esc_html($label); ?>
					<?php if ($day === $today) : ?>
						<span class="today-flag"><?php esc_html_e('Today', 'radio-station'); ?></span>
					<?php endif; ?>
				</h2>
				<?php
				$continuations = radio_station_continuations($day);
				$entries       = $board[$day];
				if (!$continuations && !$entries) :
					?>
					<p class="schedule-empty"><?php esc_html_e('Nothing scheduled', 'radio-station'); ?></p>
				<?php else : ?>
					<ul class="schedule-list">
						<?php foreach ($continuations as $item) : ?>
							<?php $is_live = ($day === $today && $now < $item['slot']['end']); ?>
							<li>
								<a class="schedule-slot is-continuation<?php echo $is_live ? ' is-live' : ''; ?>" href="<?php echo esc_url(get_permalink($item['post'])); ?>">
									<?php if ($is_live) : ?>
										<span class="slot-live"><?php esc_html_e('On air', 'radio-station'); ?></span>
									<?php endif; ?>
									<time><?php echo esc_html(sprintf(__('Until %s', 'radio-station'), radio_station_format_clock($item['slot']['end']))); ?></time>
									<strong><?php echo esc_html(get_the_title($item['post'])); ?></strong>
									<span><?php echo esc_html(sprintf(__('Continued from %s', 'radio-station'), $days[radio_station_previous_day($day)])); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
						<?php foreach ($entries as $entry) : ?>
							<?php
							$active = radio_station_active_slot($entry['post']->ID);
							$is_live = $active && $active['day'] === $day;
							$hosts   = radio_station_host_list($entry['post']->ID);
							?>
							<li>
								<a class="schedule-slot<?php echo $is_live ? ' is-live' : ''; ?>" href="<?php echo esc_url(get_permalink($entry['post'])); ?>">
									<?php if ($is_live) : ?>
										<span class="slot-live"><?php esc_html_e('On air', 'radio-station'); ?></span>
									<?php endif; ?>
									<time><?php echo esc_html(radio_station_format_range($entry['slot'])); ?></time>
									<strong><?php echo esc_html(get_the_title($entry['post'])); ?></strong>
									<?php if ($hosts !== '') : ?>
										<span><?php echo esc_html($hosts); ?></span>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
</div>

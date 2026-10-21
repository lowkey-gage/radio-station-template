<?php
/**
 * Weekly schedule.
 *
 * Template Name: Weekly Schedule
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
	<header class="page-intro wrap">
		<p class="eyebrow"><?php echo esc_html(radio_station_mod('name')); ?></p>
		<h1><?php the_title(); ?></h1>
		<p class="lede">
			<?php
			$timezone = wp_timezone_string();
			if ($timezone === '+00:00' || $timezone === 'UTC') {
				$timezone = 'UTC';
			} elseif (preg_match('/^[+-]/', $timezone)) {
				$timezone = 'UTC' . $timezone;
			} else {
				$timezone = str_replace('_', ' ', $timezone);
			}
			echo esc_html(
				sprintf(
					/* translators: 1: city, 2: timezone name */
					__('Broadcast times for %1$s, shown in %2$s.', 'radio-station'),
					radio_station_mod('city'),
					$timezone
				)
			);
			?>
		</p>
	</header>
	<?php
	if (has_block('radio-station/show-schedule')) {
		the_content();
	} else {
		get_template_part('template-parts/schedule', 'board');
	}
	?>
	<?php
endwhile;

get_footer();

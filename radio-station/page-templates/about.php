<?php
/**
 * Station information and contact details.
 *
 * Template Name: About and Contact
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $email   = radio_station_mod('contact_email');
    $address = radio_station_mod('studio_address');
    $phone   = radio_station_mod('request_line');
    ?>
	<article <?php post_class('about-page'); ?>>
		<header class="page-intro wrap">
			<p class="eyebrow"><?php echo esc_html(radio_station_mod('city')); ?></p>
			<h1><?php the_title(); ?></h1>
			<p class="lede"><?php echo esc_html(radio_station_mod('about')); ?></p>
		</header>

		<div class="wrap about-grid">
			<section class="listen-card">
				<h2><?php esc_html_e('The station', 'radio-station'); ?></h2>
				<p class="tune-stat"><?php echo esc_html(radio_station_mod('frequency')); ?></p>
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: station name, 2: city */
							__('%1$s broadcasts from %2$s.', 'radio-station'),
							radio_station_mod('name'),
							radio_station_mod('city')
						)
					);
					?>
				</p>
				<?php if (get_the_content() !== '') : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endif; ?>
			</section>

			<section class="listen-card">
				<h2><?php esc_html_e('Contact', 'radio-station'); ?></h2>
				<?php if ($address !== '') : ?>
					<p><?php echo esc_html($address); ?></p>
				<?php endif; ?>
				<?php if ($email !== '') : ?>
					<p><a href="<?php echo esc_url('mailto:' . antispambot($email)); ?>"><?php echo esc_html(antispambot($email)); ?></a></p>
				<?php endif; ?>
				<?php if ($phone !== '') : ?>
					<p>
						<?php esc_html_e('Request line', 'radio-station'); ?>
						<a href="<?php echo esc_url('tel:' . preg_replace('/[^\d+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
					</p>
				<?php endif; ?>
				<?php if ($address === '' && $email === '' && $phone === '') : ?>
					<p><?php esc_html_e('Contact details can be added in the Customizer.', 'radio-station'); ?></p>
				<?php endif; ?>
			</section>
		</div>
	</article>
	<?php
endwhile;

get_footer();

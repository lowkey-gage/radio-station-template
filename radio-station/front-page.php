<?php
/**
 * Station home.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$show_query = new WP_Query(
    array(
        'post_type'      => 'shows',
        'posts_per_page' => 6,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'   => '_show_status',
                'value' => 'active',
            ),
            array(
                'key'     => '_show_status',
                'compare' => 'NOT EXISTS',
            ),
        ),
    )
);

$dj_query = new WP_Query(
    array(
        'post_type'      => 'djs',
        'posts_per_page' => 8,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    )
);
?>

<section class="hero">
	<div class="wrap-wide hero-grid">
		<div class="hero-copy">
			<p class="hero-kicker"><?php echo esc_html(radio_station_mod('city')); ?></p>
			<h1 class="hero-title"><?php echo esc_html(radio_station_mod('name')); ?></h1>
			<p class="hero-frequency"><?php echo esc_html(radio_station_mod('frequency')); ?></p>
			<p class="hero-tagline"><?php echo esc_html(radio_station_mod('tagline')); ?></p>
			<div class="hero-actions">
				<a class="button" href="<?php echo esc_url(radio_station_page_url('listen')); ?>"><?php esc_html_e('Listen live', 'radio-station'); ?></a>
				<a class="button button-ghost" href="<?php echo esc_url(radio_station_page_url('schedule')); ?>"><?php esc_html_e('See the week', 'radio-station'); ?></a>
			</div>
		</div>
		<?php get_template_part('template-parts/studio', 'card'); ?>
	</div>
</section>

<section class="section" id="programs">
	<div class="wrap section-heading">
		<div>
			<p class="eyebrow"><?php esc_html_e('Discover', 'radio-station'); ?></p>
			<h2><?php esc_html_e('Shows', 'radio-station'); ?></h2>
		</div>
		<a class="section-link" href="<?php echo esc_url(get_post_type_archive_link('shows')); ?>"><?php esc_html_e('All shows', 'radio-station'); ?></a>
	</div>

	<?php get_template_part('template-parts/genre', 'nav'); ?>

	<div class="wrap">
		<?php if ($show_query->have_posts()) : ?>
			<div class="show-grid">
				<?php
				while ($show_query->have_posts()) :
					$show_query->the_post();
					get_template_part('template-parts/show', 'card');
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/content',
				'none',
				array(
					'message' => __('No shows are on the schedule yet.', 'radio-station'),
				)
			);
			?>
		<?php endif; ?>
	</div>
</section>

<section class="section section-djs" id="djs">
	<div class="wrap section-heading">
		<div>
			<p class="eyebrow"><?php esc_html_e('The voices', 'radio-station'); ?></p>
			<h2><?php esc_html_e('DJs', 'radio-station'); ?></h2>
		</div>
		<a class="section-link" href="<?php echo esc_url(get_post_type_archive_link('djs')); ?>"><?php esc_html_e('All DJs', 'radio-station'); ?></a>
	</div>
	<div class="wrap">
		<?php if ($dj_query->have_posts()) : ?>
			<div class="dj-grid">
				<?php
				while ($dj_query->have_posts()) :
					$dj_query->the_post();
					get_template_part('template-parts/dj', 'card');
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/content',
				'none',
				array(
					'message' => __('No DJ profiles yet.', 'radio-station'),
				)
			);
			?>
		<?php endif; ?>
	</div>
</section>

<section class="section" id="week">
	<div class="wrap section-heading">
		<div>
			<p class="eyebrow"><?php esc_html_e('Broadcast schedule', 'radio-station'); ?></p>
			<h2><?php esc_html_e('This week', 'radio-station'); ?></h2>
		</div>
		<a class="section-link" href="<?php echo esc_url(radio_station_page_url('schedule')); ?>"><?php esc_html_e('Full schedule', 'radio-station'); ?></a>
	</div>
	<?php get_template_part('template-parts/schedule', 'board'); ?>
</section>

<section class="section tune-in" id="tune-in">
	<div class="wrap tune-grid">
		<div>
			<p class="eyebrow"><?php esc_html_e('Where to listen', 'radio-station'); ?></p>
			<h2><?php esc_html_e('Tune in', 'radio-station'); ?></h2>
			<p class="lede"><?php echo esc_html(radio_station_mod('about')); ?></p>
			<a class="button" href="<?php echo esc_url(radio_station_page_url('listen')); ?>"><?php esc_html_e('How to listen', 'radio-station'); ?></a>
		</div>
		<ul class="tune-cards">
			<li>
				<p class="eyebrow"><?php esc_html_e('On the dial', 'radio-station'); ?></p>
				<p class="tune-stat"><?php echo esc_html(radio_station_mod('frequency')); ?></p>
				<p><?php echo esc_html(radio_station_mod('city')); ?></p>
			</li>
			<li>
				<p class="eyebrow"><?php esc_html_e('In the browser', 'radio-station'); ?></p>
				<?php if (radio_station_mod('stream_url')) : ?>
					<p><?php esc_html_e('The live stream is open.', 'radio-station'); ?></p>
				<?php else : ?>
					<p><?php esc_html_e('The dial is the sure way in. A stream can be added when you have a mount.', 'radio-station'); ?></p>
				<?php endif; ?>
				<a class="section-link" href="<?php echo esc_url(radio_station_page_url('listen')); ?>"><?php esc_html_e('Listen page', 'radio-station'); ?></a>
			</li>
		</ul>
	</div>
</section>

<?php
get_footer();

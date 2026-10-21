<?php
/**
 * Category and genre filters for show listings.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_genre = radio_station_current_genre();
$base          = radio_station_shows_filter_base();
$archive       = get_post_type_archive_link('shows');
$terms         = get_terms(
    array(
        'taxonomy'   => 'show_category',
        'hide_empty' => true,
    )
);
$current_term = is_tax('show_category') ? get_queried_object_id() : 0;
?>
<div class="filters wrap">
	<?php if (!is_wp_error($terms) && $terms) : ?>
		<nav class="filter-row" aria-label="<?php esc_attr_e('Show categories', 'radio-station'); ?>">
			<a class="<?php echo $current_term ? '' : 'is-active'; ?>" href="<?php echo esc_url($current_genre ? add_query_arg('genre', $current_genre, $archive) : $archive); ?>">
				<?php esc_html_e('All shows', 'radio-station'); ?>
			</a>
			<?php foreach ($terms as $term) : ?>
				<?php
				$term_link = get_term_link($term);
				if (is_wp_error($term_link)) {
					continue;
				}
				if ($current_genre) {
					$term_link = add_query_arg('genre', $current_genre, $term_link);
				}
				?>
				<a class="<?php echo ((int) $term->term_id === (int) $current_term) ? 'is-active' : ''; ?>" href="<?php echo esc_url($term_link); ?>">
					<?php echo esc_html($term->name); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<nav class="filter-row" aria-label="<?php esc_attr_e('Genres', 'radio-station'); ?>">
		<a class="<?php echo $current_genre === '' ? 'is-active' : ''; ?>" href="<?php echo esc_url($base); ?>">
			<?php esc_html_e('All genres', 'radio-station'); ?>
		</a>
		<?php foreach (radio_station_genres() as $slug => $label) : ?>
			<a class="<?php echo $current_genre === $slug ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('genre', $slug, $base)); ?>">
				<?php echo esc_html($label); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</div>

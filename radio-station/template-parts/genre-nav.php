<?php
/**
 * Genre links into the show archive.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$archive = get_post_type_archive_link('shows');
?>
<div class="wrap">
	<nav class="genre-row" aria-label="<?php esc_attr_e('Genres', 'radio-station'); ?>">
		<?php foreach (radio_station_genres() as $slug => $label) : ?>
			<a href="<?php echo esc_url(add_query_arg('genre', $slug, $archive)); ?>"><?php echo esc_html($label); ?></a>
		<?php endforeach; ?>
	</nav>
</div>

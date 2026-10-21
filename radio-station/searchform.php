<?php
/**
 * Header search.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<form class="site-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
	<label class="screen-reader-text" for="header-search"><?php esc_html_e('Search shows and DJs', 'radio-station'); ?></label>
	<input id="header-search" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search', 'radio-station'); ?>">
	<button type="submit"><?php esc_html_e('Search', 'radio-station'); ?></button>
</form>

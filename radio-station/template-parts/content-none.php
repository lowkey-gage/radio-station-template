<?php
/**
 * Empty state.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

$message = (isset($args['message']) && $args['message'] !== '')
    ? $args['message']
    : __('Nothing here yet.', 'radio-station');
?>
<div class="empty-state">
	<p><?php echo esc_html($message); ?></p>
	<?php if (current_user_can('edit_posts') && (is_post_type_archive('shows') || is_front_page())) : ?>
		<p class="admin-hint"><a href="<?php echo esc_url(admin_url('post-new.php?post_type=shows')); ?>"><?php esc_html_e('Add a show', 'radio-station'); ?></a></p>
	<?php endif; ?>
</div>

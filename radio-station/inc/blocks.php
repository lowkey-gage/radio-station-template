<?php
/**
 * Editor blocks for the schedule, DJ profiles, and show listings.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the three station blocks and their editor script.
 */
function radio_station_register_blocks() {
    wp_register_script(
        'radio-station-blocks',
        get_template_directory_uri() . '/assets/js/blocks.js',
        array(
            'wp-blocks',
            'wp-element',
            'wp-block-editor',
            'wp-components',
            'wp-server-side-render',
            'wp-i18n',
            'wp-data',
        ),
        wp_get_theme()->get('Version'),
        true
    );

    $days = array(
        array(
            'value' => 'week',
            'label' => __('Full week', 'radio-station'),
        ),
    );

    foreach (radio_station_days() as $key => $label) {
        $days[] = array(
            'value' => $key,
            'label' => $label,
        );
    }

    $djs = array(
        array(
            'value' => 0,
            'label' => __('Select a DJ', 'radio-station'),
        ),
    );

    if (post_type_exists('djs')) {
        foreach (radio_station_get_dj_posts() as $dj) {
            $djs[] = array(
                'value' => (int) $dj->ID,
                'label' => get_the_title($dj),
            );
        }
    }

    $categories = array(
        array(
            'value' => '',
            'label' => __('All categories', 'radio-station'),
        ),
    );

    if (taxonomy_exists('show_category')) {
        $terms = get_terms(
            array(
                'taxonomy'   => 'show_category',
                'hide_empty' => false,
            )
        );

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[] = array(
                    'value' => $term->slug,
                    'label' => $term->name,
                );
            }
        }
    }

    wp_localize_script(
        'radio-station-blocks',
        'radioStationBlockData',
        array(
            'days'       => $days,
            'djs'        => $djs,
            'categories' => $categories,
        )
    );

    $common = array(
        'api_version'   => 3,
        'editor_script' => 'radio-station-blocks',
        'category'      => 'radio-station',
    );

    register_block_type(
        'radio-station/show-schedule',
        array_merge(
            $common,
            array(
                'title'           => __('Show Schedule', 'radio-station'),
                'description'     => __('Recurring shows grouped by weekday.', 'radio-station'),
                'attributes'      => array(
                    'day' => array(
                        'type'    => 'string',
                        'default' => 'week',
                    ),
                ),
                'render_callback' => 'radio_station_render_schedule_block',
            )
        )
    );

    register_block_type(
        'radio-station/dj-info',
        array_merge(
            $common,
            array(
                'title'           => __('DJ Information', 'radio-station'),
                'description'     => __('A DJ photo, name, short biography, and profile link.', 'radio-station'),
                'attributes'      => array(
                    'djId' => array(
                        'type'    => 'number',
                        'default' => 0,
                    ),
                    'mode' => array(
                        'type'    => 'string',
                        'default' => 'selected',
                    ),
                ),
                'uses_context'    => array('postId', 'postType'),
                'render_callback' => 'radio_station_render_dj_info_block',
            )
        )
    );

    register_block_type(
        'radio-station/show-listings',
        array_merge(
            $common,
            array(
                'title'           => __('Show Listings', 'radio-station'),
                'description'     => __('Linked show cards, optionally filtered by category.', 'radio-station'),
                'attributes'      => array(
                    'category'   => array(
                        'type'    => 'string',
                        'default' => '',
                    ),
                    'useProfile' => array(
                        'type'    => 'boolean',
                        'default' => false,
                    ),
                ),
                'uses_context'    => array('postId', 'postType'),
                'render_callback' => 'radio_station_render_show_listings_block',
            )
        )
    );
}
add_action('init', 'radio_station_register_blocks');

/**
 * Give the three blocks their own inserter group.
 *
 * @param array $categories Block categories.
 * @return array
 */
function radio_station_block_category($categories) {
    $categories[] = array(
        'slug'  => 'radio-station',
        'title' => __('Radio Station', 'radio-station'),
        'icon'  => 'playlist-audio',
    );

    return $categories;
}
add_filter('block_categories_all', 'radio_station_block_category');

/**
 * Post the block is rendering against.
 *
 * @param WP_Block $block Block instance.
 * @return int
 */
function radio_station_block_post_id($block) {
    if ($block instanceof WP_Block && !empty($block->context['postId'])) {
        return (int) $block->context['postId'];
    }

    if (is_singular()) {
        return (int) get_queried_object_id();
    }

    $post = get_post();

    return $post ? (int) $post->ID : 0;
}

/**
 * Render the Show Schedule block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function radio_station_render_schedule_block($attributes) {
    $day = isset($attributes['day']) ? sanitize_key($attributes['day']) : 'week';

    if ($day !== 'week' && !array_key_exists($day, radio_station_days())) {
        $day = 'week';
    }

    ob_start();
    echo '<div class="radio-block radio-block-schedule">';
    get_template_part(
        'template-parts/schedule',
        'board',
        array(
            'day' => $day,
        )
    );
    echo '</div>';

    return ob_get_clean();
}

/**
 * Render the DJ Information block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner content.
 * @param WP_Block $block      Block instance.
 * @return string
 */
function radio_station_render_dj_info_block($attributes, $content, $block) {
    unset($content);

    $mode  = isset($attributes['mode']) ? $attributes['mode'] : 'selected';
    $dj_id = isset($attributes['djId']) ? (int) $attributes['djId'] : 0;
    $djs   = array();

    if ($mode === 'assigned') {
        $post_id = radio_station_block_post_id($block);
        $post    = $post_id ? get_post($post_id) : null;

        if (!$post || $post->post_type !== 'shows') {
            return radio_station_block_notice(__('Assigned DJs appear when this block is on a show page.', 'radio-station'));
        }

        $djs = radio_station_get_show_djs($post->ID);

        if (!$djs) {
            return radio_station_block_notice(__('This show has no assigned DJs yet.', 'radio-station'));
        }
    } else {
        $dj = $dj_id ? get_post($dj_id) : null;

        if (!$dj || $dj->post_type !== 'djs' || $dj->post_status !== 'publish') {
            return radio_station_block_notice(__('Select a DJ in the block settings.', 'radio-station'));
        }

        $djs = array($dj);
    }

    ob_start();
    echo '<div class="radio-block dj-block-grid">';

    foreach ($djs as $dj) {
        radio_station_render_dj_block_card($dj);
    }

    echo '</div>';

    return ob_get_clean();
}

/**
 * One DJ card for the DJ Information block.
 *
 * @param WP_Post $dj DJ post.
 */
function radio_station_render_dj_block_card($dj) {
    $permalink = get_permalink($dj);
    $bio       = trim(wp_strip_all_tags($dj->post_content));
    $alt       = (string) get_post_meta(get_post_thumbnail_id($dj), '_wp_attachment_image_alt', true);

    if ($alt === '') {
        $alt = get_the_title($dj);
    }

    echo '<article class="dj-block">';
    echo '<a class="dj-block-media" href="' . esc_url($permalink) . '">';

    if (has_post_thumbnail($dj)) {
        echo get_the_post_thumbnail(
            $dj,
            'radio-portrait',
            array(
                'alt'     => $alt,
                'loading' => 'lazy',
            )
        );
    } else {
        radio_station_media_fallback(get_the_title($dj), 22);
    }

    echo '</a>';
    echo '<h3><a href="' . esc_url($permalink) . '">' . esc_html(get_the_title($dj)) . '</a></h3>';

    if ($bio !== '') {
        echo '<p>' . esc_html(wp_trim_words($bio, 28)) . '</p>';
    } else {
        echo '<p>' . esc_html__('No biography yet.', 'radio-station') . '</p>';
    }

    echo '<a class="section-link" href="' . esc_url($permalink) . '">' . esc_html__('Full profile', 'radio-station') . '</a>';
    echo '</article>';
}

/**
 * Render the Show Listings block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner content.
 * @param WP_Block $block      Block instance.
 * @return string
 */
function radio_station_render_show_listings_block($attributes, $content, $block) {
    unset($content);

    if (!post_type_exists('shows')) {
        return radio_station_block_notice(__('Shows are not available.', 'radio-station'));
    }

    $use_profile = !empty($attributes['useProfile']);
    $shows       = array();

    if ($use_profile) {
        $post_id = radio_station_block_post_id($block);
        $post    = $post_id ? get_post($post_id) : null;

        if (!$post || $post->post_type !== 'djs') {
            return radio_station_block_notice(__('Related shows appear when this block is on a DJ profile.', 'radio-station'));
        }

        $shows = radio_station_shows_for_dj($post->ID);
    } else {
        $query_args = array(
            'post_type'      => 'shows',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        );
        $category = isset($attributes['category']) ? sanitize_title($attributes['category']) : '';

        if ($category !== '') {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'show_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ),
            );
        }

        $shows = get_posts($query_args);
    }

    if (!$shows) {
        return radio_station_block_notice(__('No shows match this block yet.', 'radio-station'));
    }

    ob_start();
    echo '<div class="radio-block show-grid">';

    global $post;

    foreach ($shows as $show_post) {
        $post = $show_post;
        setup_postdata($post);
        get_template_part('template-parts/show', 'card');
    }

    wp_reset_postdata();
    echo '</div>';

    return ob_get_clean();
}

/**
 * Empty or instructional block result.
 *
 * @param string $message Message.
 * @return string
 */
function radio_station_block_notice($message) {
    return '<p class="radio-block empty-state">' . esc_html($message) . '</p>';
}

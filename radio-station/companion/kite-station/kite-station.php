<?php
/**
 * Plugin Name: KITE Station
 * Description: Shows, DJs, show categories, and weekly schedule fields for the radio station theme. Content stays available if the theme is changed.
 * Version: 1.2.0
 * Author: Gage W. Reed
 * License: GPLv2 or later
 * Update URI: https://gage-radio.mystagingwebsite.com/
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('KITE_STATION_FILE')) {
    define('KITE_STATION_FILE', __FILE__);
}

/*
|--------------------------------------------------------------------------
| 1. REGISTER CUSTOM POST TYPES
|--------------------------------------------------------------------------
*/

function radio_station_register_post_types() {

    // Register Shows
    register_post_type('shows', array(
        'labels' => array(
            'name' => 'Shows',
            'singular_name' => 'Show',
            'add_new_item' => 'Add New Show',
            'edit_item' => 'Edit Show',
            'all_items' => 'All Shows'
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-video-alt3',
        'supports' => array(
            'title',
            'editor',
            'thumbnail'
        ),
        'rewrite' => array(
            'slug' => 'shows'
        )
    ));

    // Register DJs
    register_post_type('djs', array(
        'labels' => array(
            'name' => 'DJs',
            'singular_name' => 'DJ',
            'add_new_item' => 'Add New DJ',
            'edit_item' => 'Edit DJ',
            'all_items' => 'All DJs'
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-format-audio',
        'supports' => array(
            'title',
            'editor',
            'thumbnail'
        ),
        'rewrite' => array(
            'slug' => 'djs'
        )
    ));
}

add_action('init', 'radio_station_register_post_types');


/*
|--------------------------------------------------------------------------
| REGISTER SHOW CATEGORIES
|--------------------------------------------------------------------------
*/

function radio_station_register_show_categories() {

    $labels = array(
        'name' => 'Show Categories',
        'singular_name' => 'Show Category',
        'search_items' => 'Search Show Categories',
        'all_items' => 'All Show Categories',
        'edit_item' => 'Edit Show Category',
        'update_item' => 'Update Show Category',
        'add_new_item' => 'Add New Show Category',
        'new_item_name' => 'New Show Category Name',
        'menu_name' => 'Show Categories'
    );

    register_taxonomy('show_category', array('shows'), array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => array(
            'slug' => 'show-category'
        )
    ));
}

add_action('init', 'radio_station_register_show_categories');


/*
|--------------------------------------------------------------------------
| 2. REGISTER SHOW META BOX
|--------------------------------------------------------------------------
*/

function radio_station_add_show_meta_box() {

    add_meta_box(
        'radio_station_show_details',
        'Show Details',
        'radio_station_render_show_meta_box',
        'shows',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'radio_station_add_show_meta_box');


/*
|--------------------------------------------------------------------------
| 3. DISPLAY SHOW CUSTOM FIELDS
|--------------------------------------------------------------------------
*/

function radio_station_render_show_meta_box($post) {

    wp_nonce_field(
        'radio_station_save_show',
        'radio_station_show_nonce'
    );

    $selected_djs = get_post_meta(
        $post->ID,
        '_show_djs',
        true
    );

    $selected_djs = is_array($selected_djs)
        ? array_map('absint', $selected_djs)
        : array();

    $schedule = get_post_meta(
        $post->ID,
        '_show_schedule',
        true
    );

    $schedule = is_array($schedule)
        ? $schedule
        : array();

    $genre = get_post_meta(
        $post->ID,
        '_show_genre',
        true
    );

    $status = get_post_meta(
        $post->ID,
        '_show_status',
        true
    );

    $status = $status ?: 'active';

    $djs = get_posts(array(
        'post_type' => 'djs',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ));

    $days = array(
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday'
    );

    ?>

    <h3>Assigned DJs</h3>

    <p>Select the DJs who host this show.</p>

    <?php if (empty($djs)) : ?>

        <p>
            No DJs found. Publish a DJ first.
        </p>

    <?php else : ?>

        <?php foreach ($djs as $dj) : ?>

            <p>
                <label>
                    <input
                        type="checkbox"
                        name="show_djs[]"
                        value="<?php echo esc_attr($dj->ID); ?>"
                        <?php
                        checked(
                            in_array(
                                (int) $dj->ID,
                                $selected_djs,
                                true
                            )
                        );
                        ?>
                    >

                    <?php echo esc_html($dj->post_title); ?>

                </label>
            </p>

        <?php endforeach; ?>

    <?php endif; ?>

    <hr>

    <h3>Weekly Broadcast Schedule</h3>

    <p>
        Select the days this show airs and enter
        the start and end times for each day.
    </p>

    <table class="widefat striped">

        <thead>
            <tr>
                <th>Day</th>
                <th>Broadcast</th>
                <th>Start Time</th>
                <th>End Time</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($days as $key => $label) :

                $day_schedule = isset($schedule[$key])
                    && is_array($schedule[$key])
                    ? $schedule[$key]
                    : array();

                $enabled = !empty(
                    $day_schedule['enabled']
                );

                $start = isset($day_schedule['start'])
                    ? $day_schedule['start']
                    : '';

                $end = isset($day_schedule['end'])
                    ? $day_schedule['end']
                    : '';

            ?>

                <tr>

                    <td>
                        <strong>
                            <?php echo esc_html($label); ?>
                        </strong>
                    </td>

                    <td>
                        <input
                            type="checkbox"
                            name="show_schedule[<?php echo esc_attr($key); ?>][enabled]"
                            value="1"
                            <?php checked($enabled); ?>
                        >
                    </td>

                    <td>
                        <input
                            type="time"
                            name="show_schedule[<?php echo esc_attr($key); ?>][start]"
                            value="<?php echo esc_attr($start); ?>"
                        >
                    </td>

                    <td>
                        <input
                            type="time"
                            name="show_schedule[<?php echo esc_attr($key); ?>][end]"
                            value="<?php echo esc_attr($end); ?>"
                        >
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

    <hr>

    <h3>Show Information</h3>

    <p>
        <label for="show_genre">
            <strong>Show Genre</strong>
        </label>
        <br>

        <select
            name="show_genre"
            id="show_genre"
        >

            <?php

            $genres = array(
                'pop' => 'Pop',
                'rock' => 'Rock',
                'hip-hop' => 'Hip-Hop',
                'country' => 'Country',
                'electronic' => 'Electronic',
                'talk' => 'Talk Radio',
                'mixed' => 'Mixed'
            );

            foreach ($genres as $value => $label) {

                echo '<option value="' .
                    esc_attr($value) . '" ' .
                    selected(
                        $genre,
                        $value,
                        false
                    ) .
                    '>' .
                    esc_html($label) .
                    '</option>';
            }

            ?>

        </select>
    </p>

    <p>
        <label for="show_status">
            <strong>Show Status</strong>
        </label>
        <br>

        <select
            name="show_status"
            id="show_status"
        >

            <option
                value="active"
                <?php selected($status, 'active'); ?>
            >
                Active
            </option>

            <option
                value="inactive"
                <?php selected($status, 'inactive'); ?>
            >
                Inactive
            </option>

        </select>
    </p>

    <?php
}


/*
|--------------------------------------------------------------------------
| 4. SAVE SHOW CUSTOM FIELDS
|--------------------------------------------------------------------------
*/

function radio_station_save_show_details($post_id) {

    if (
        !isset($_POST['radio_station_show_nonce']) ||
        !is_string($_POST['radio_station_show_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_POST['radio_station_show_nonce'])
            ),
            'radio_station_save_show'
        )
    ) {
        return;
    }

    if (
        defined('DOING_AUTOSAVE') &&
        DOING_AUTOSAVE
    ) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    /*
     * Save Assigned DJs
     */

    $submitted_djs = isset($_POST['show_djs'])
        && is_array($_POST['show_djs'])
        ? wp_unslash($_POST['show_djs'])
        : array();

    $djs = array_map(
        'absint',
        $submitted_djs
    );

    $djs = array_values(
        array_unique(
            array_filter(
                $djs,
                function($dj_id) {

                    return
                        get_post_type($dj_id) === 'djs'
                        &&
                        get_post_status($dj_id) === 'publish';
                }
            )
        )
    );

    update_post_meta(
        $post_id,
        '_show_djs',
        $djs
    );

    /*
     * Save Weekly Schedule
     */

    $days = array(
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    );

    $submitted_schedule = isset($_POST['show_schedule'])
        && is_array($_POST['show_schedule'])
        ? wp_unslash($_POST['show_schedule'])
        : array();

    $schedule = array();

    $time_pattern = '/^([01]\d|2[0-3]):[0-5]\d$/';

    foreach ($days as $day) {

        $day_data = isset($submitted_schedule[$day])
            && is_array($submitted_schedule[$day])
            ? $submitted_schedule[$day]
            : array();

        $enabled = !empty($day_data['enabled']);

        $start = isset($day_data['start'])
            && is_string($day_data['start'])
            ? sanitize_text_field($day_data['start'])
            : '';

        $end = isset($day_data['end'])
            && is_string($day_data['end'])
            ? sanitize_text_field($day_data['end'])
            : '';

        if (
            $start !== '' &&
            !preg_match($time_pattern, $start)
        ) {
            $start = '';
        }

        if (
            $end !== '' &&
            !preg_match($time_pattern, $end)
        ) {
            $end = '';
        }

        $schedule[$day] = array(
            'enabled' => $enabled ? 1 : 0,
            'start' => $start,
            'end' => $end
        );
    }

    update_post_meta(
        $post_id,
        '_show_schedule',
        $schedule
    );

    /*
     * Save Genre
     */

    $allowed_genres = array(
        'pop',
        'rock',
        'hip-hop',
        'country',
        'electronic',
        'talk',
        'mixed'
    );

    $genre = isset($_POST['show_genre'])
        && is_string($_POST['show_genre'])
        ? sanitize_key(
            wp_unslash($_POST['show_genre'])
        )
        : 'mixed';

    if (!in_array($genre, $allowed_genres, true)) {
        $genre = 'mixed';
    }

    update_post_meta(
        $post_id,
        '_show_genre',
        $genre
    );

    /*
     * Save Status
     */

    $status = isset($_POST['show_status'])
        && is_string($_POST['show_status'])
        ? sanitize_key(
            wp_unslash($_POST['show_status'])
        )
        : 'active';

    if (
        !in_array(
            $status,
            array('active', 'inactive'),
            true
        )
    ) {
        $status = 'active';
    }

    update_post_meta(
        $post_id,
        '_show_status',
        $status
    );
}

add_action(
    'save_post_shows',
    'radio_station_save_show_details'
);


/*
|--------------------------------------------------------------------------
| 5. REGISTER DJ META BOX
|--------------------------------------------------------------------------
*/

function radio_station_add_dj_meta_box() {

    add_meta_box(
        'radio_station_dj_details',
        'DJ Details',
        'radio_station_render_dj_meta_box',
        'djs',
        'normal',
        'high'
    );
}

add_action(
    'add_meta_boxes',
    'radio_station_add_dj_meta_box'
);


/*
|--------------------------------------------------------------------------
| 6. DISPLAY DJ CUSTOM FIELDS
|--------------------------------------------------------------------------
*/

function radio_station_render_dj_meta_box($post) {

    wp_nonce_field(
        'radio_station_save_dj',
        'radio_station_dj_nonce'
    );

    $fields = array(
        'real_name' => array(
            'Real Name',
            'text'
        ),
        'favorite_genre' => array(
            'Favorite Genre',
            'text'
        ),
        'instagram' => array(
            'Instagram URL',
            'url'
        ),
        'facebook' => array(
            'Facebook URL',
            'url'
        ),
        'website' => array(
            'Website URL',
            'url'
        )
    );

    foreach ($fields as $key => $field) {

        $value = get_post_meta(
            $post->ID,
            '_dj_' . $key,
            true
        );

        ?>

        <p>
            <label for="dj_<?php echo esc_attr($key); ?>">

                <strong>
                    <?php echo esc_html($field[0]); ?>
                </strong>

            </label>

            <br>

            <input
                type="<?php echo esc_attr($field[1]); ?>"
                id="dj_<?php echo esc_attr($key); ?>"
                name="dj_<?php echo esc_attr($key); ?>"
                value="<?php echo esc_attr($value); ?>"
                style="width:100%; max-width:450px;"
            >
        </p>

        <?php
    }
}


/*
|--------------------------------------------------------------------------
| 7. SAVE DJ CUSTOM FIELDS
|--------------------------------------------------------------------------
*/

function radio_station_save_dj_details($post_id) {

    if (
        !isset($_POST['radio_station_dj_nonce']) ||
        !is_string($_POST['radio_station_dj_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_POST['radio_station_dj_nonce'])
            ),
            'radio_station_save_dj'
        )
    ) {
        return;
    }

    if (
        defined('DOING_AUTOSAVE') &&
        DOING_AUTOSAVE
    ) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = array(
        'real_name',
        'favorite_genre'
    );

    $url_fields = array(
        'instagram',
        'facebook',
        'website'
    );

    /*
     * Save Text Fields
     */

    foreach ($text_fields as $field) {

        $key = 'dj_' . $field;

        if (
            isset($_POST[$key]) &&
            is_string($_POST[$key])
        ) {

            update_post_meta(
                $post_id,
                '_dj_' . $field,
                sanitize_text_field(
                    wp_unslash($_POST[$key])
                )
            );
        }
    }

    /*
     * Save URL Fields
     */

    foreach ($url_fields as $field) {

        $key = 'dj_' . $field;

        if (
            isset($_POST[$key]) &&
            is_string($_POST[$key])
        ) {

            update_post_meta(
                $post_id,
                '_dj_' . $field,
                esc_url_raw(
                    wp_unslash($_POST[$key])
                )
            );
        }
    }
}

add_action(
    'save_post_djs',
    'radio_station_save_dj_details'
);


/*
|--------------------------------------------------------------------------
| 8. DISPLAY SHOW DETAILS ON FRONTEND
|--------------------------------------------------------------------------
*/

function radio_station_display_show_details($content) {

    if (
        !is_singular('shows') ||
        !in_the_loop() ||
        !is_main_query()
    ) {
        return $content;
    }

    $show_id = get_the_ID();

    $djs = get_post_meta(
        $show_id,
        '_show_djs',
        true
    );

    $schedule = get_post_meta(
        $show_id,
        '_show_schedule',
        true
    );

    $genre = get_post_meta(
        $show_id,
        '_show_genre',
        true
    );

    $status = get_post_meta(
        $show_id,
        '_show_status',
        true
    );

    $djs = is_array($djs) ? $djs : array();
    $schedule = is_array($schedule) ? $schedule : array();

    $days = array(
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday'
    );

    $output = '<div class="radio-station-show-details">';

    /*
     * Display DJs
     */

    if (!empty($djs)) {

        $dj_links = array();

        foreach ($djs as $dj_id) {

            $dj_id = absint($dj_id);

            if (
                get_post_type($dj_id) !== 'djs' ||
                get_post_status($dj_id) !== 'publish'
            ) {
                continue;
            }

            $dj_links[] =
                '<a href="' .
                esc_url(get_permalink($dj_id)) .
                '">' .
                esc_html(get_the_title($dj_id)) .
                '</a>';
        }

        if (!empty($dj_links)) {

            $output .= '<p><strong>Hosted by:</strong> ';

            $output .= implode(', ', $dj_links);

            $output .= '</p>';
        }
    }

    /*
     * Display Weekly Schedule
     */

    $schedule_output = '';

    foreach ($days as $key => $label) {

        if (
            empty($schedule[$key]['enabled']) ||
            empty($schedule[$key]['start']) ||
            empty($schedule[$key]['end'])
        ) {
            continue;
        }

        $start = $schedule[$key]['start'];
        $end = $schedule[$key]['end'];

        $start_formatted = date(
            'g:i A',
            strtotime($start)
        );

        $end_formatted = date(
            'g:i A',
            strtotime($end)
        );

        $schedule_output .= '<li>';

        $schedule_output .=
            esc_html($label) .
            ': ' .
            esc_html($start_formatted) .
            ' - ' .
            esc_html($end_formatted);

        $schedule_output .= '</li>';
    }

    if ($schedule_output !== '') {

        $output .= '<h3>Broadcast Schedule</h3>';

        $output .= '<ul>';

        $output .= $schedule_output;

        $output .= '</ul>';
    }

    /*
     * Display Genre
     */

    if (!empty($genre)) {

        $output .= '<p><strong>Genre:</strong> ';

        $output .= esc_html(
            ucwords(str_replace('-', ' ', $genre))
        );

        $output .= '</p>';
    }

    /*
     * Display Status
     */

    if ($status === 'inactive') {

        $output .= '<p><em>';

        $output .= 'This show is currently inactive.';

        $output .= '</em></p>';
    }

    $output .= '</div>';

    return $output . $content;
}

add_filter(
    'the_content',
    'radio_station_display_show_details'
);

function radio_station_display_dj_shows($content) {

    // Only display on individual DJ pages
    if (
        !is_singular('djs') ||
        !in_the_loop() ||
        !is_main_query()
    ) {
        return $content;
    }

    $dj_id = get_the_ID();

    // Retrieve published shows
    $shows = get_posts(array(
        'post_type' => 'shows',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    $related_shows = array();

    foreach ($shows as $show) {

        // Get DJs assigned to this show
        $assigned_djs = get_post_meta(
            $show->ID,
            '_show_djs',
            true
        );

        if (!is_array($assigned_djs)) {
            continue;
        }

        // Check whether this DJ hosts the show
        if (in_array($dj_id, array_map('absint', $assigned_djs), true)) {
            $related_shows[] = $show;
        }
    }

    // Nothing to display
    if (empty($related_shows)) {
        return $content;
    }

    $output = '<div class="radio-station-dj-shows">';

    $output .= '<h2>Shows Hosted By This DJ</h2>';

    $output .= '<ul>';

    foreach ($related_shows as $show) {

        $show_url = get_permalink($show->ID);
        $show_title = get_the_title($show->ID);

        $output .= '<li>';

        $output .= '<a href="' . esc_url($show_url) . '">';

        $output .= esc_html($show_title);

        $output .= '</a>';

        $output .= '</li>';
    }

    $output .= '</ul>';

    $output .= '</div>';

    return $content . $output;
}

add_filter(
    'the_content',
    'radio_station_display_dj_shows'
);

function radio_station_display_show_categories($content) {

    if (
        !is_singular('shows') ||
        !in_the_loop() ||
        !is_main_query()
    ) {
        return $content;
    }

    $categories = get_the_terms(
        get_the_ID(),
        'show_category'
    );

    if (empty($categories) || is_wp_error($categories)) {
        return $content;
    }

    $category_names = array();

    foreach ($categories as $category) {
        $category_names[] = esc_html($category->name);
    }

    $output = '<div class="radio-station-show-categories">';

    $output .= '<p><strong>Categories:</strong> ';

    $output .= implode(', ', $category_names);

    $output .= '</p>';

    $output .= '</div>';

    return $output . $content;
}

add_filter(
    'the_content',
    'radio_station_display_show_categories'
);
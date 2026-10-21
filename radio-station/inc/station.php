<?php
/**
 * Station data, schedule math, and small template helpers.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer defaults for the fictional station.
 *
 * @return array<string, string>
 */
function radio_station_defaults() {
    return array(
        'name'         => 'KITE',
        'frequency'    => '91.7 FM',
        'tagline'      => 'The signal you stay up for.',
        'city'         => 'Bay Harbor',
        'about'        => 'KITE is an independent station in Bay Harbor. New music, long-form talk, and overnight shows that do not fit anywhere else.',
        'stream_url'   => '',
        'tunein_url'   => '',
        'spotify_url'  => '',
        'apple_url'    => '',
        'request_line'   => '',
        'contact_email'  => 'studio@kite.fm',
        'studio_address' => '100 Harbor Street, Bay Harbor',
    );
}

/**
 * Read a station setting.
 *
 * @param string $key Setting key without the theme-mod prefix.
 * @return string
 */
function radio_station_mod($key) {
    $defaults = radio_station_defaults();
    $default  = isset($defaults[$key]) ? $defaults[$key] : '';

    return (string) get_theme_mod('radio_station_' . $key, $default);
}

/**
 * Show genres stored by the plugin.
 *
 * @return array<string, string>
 */
function radio_station_genres() {
    return array(
        'pop'        => __('Pop', 'radio-station'),
        'rock'       => __('Rock', 'radio-station'),
        'hip-hop'    => __('Hip-Hop', 'radio-station'),
        'country'    => __('Country', 'radio-station'),
        'electronic' => __('Electronic', 'radio-station'),
        'talk'       => __('Talk Radio', 'radio-station'),
        'mixed'      => __('Mixed', 'radio-station'),
    );
}

/**
 * Human label for a genre slug.
 *
 * @param string $slug Genre slug.
 * @return string
 */
function radio_station_genre_label($slug) {
    $genres = radio_station_genres();

    if (isset($genres[$slug])) {
        return $genres[$slug];
    }

    if ($slug === '') {
        return '';
    }

    return ucwords(str_replace('-', ' ', $slug));
}

/**
 * Hue used to tint a show that has no artwork.
 *
 * @param string $genre Genre slug.
 * @return int
 */
function radio_station_genre_hue($genre) {
    $hues = array(
        'pop'        => 330,
        'rock'       => 16,
        'hip-hop'    => 272,
        'country'    => 38,
        'electronic' => 168,
        'talk'       => 206,
        'mixed'      => 24,
    );

    return isset($hues[$genre]) ? $hues[$genre] : 24;
}

/**
 * Weekday keys match the plugin schedule meta.
 *
 * @return array<string, string>
 */
function radio_station_days() {
    return array(
        'monday'    => __('Monday', 'radio-station'),
        'tuesday'   => __('Tuesday', 'radio-station'),
        'wednesday' => __('Wednesday', 'radio-station'),
        'thursday'  => __('Thursday', 'radio-station'),
        'friday'    => __('Friday', 'radio-station'),
        'saturday'  => __('Saturday', 'radio-station'),
        'sunday'    => __('Sunday', 'radio-station'),
    );
}

/**
 * Station-local day and clock time.
 *
 * @return array{day: string, time: string}
 */
function radio_station_now() {
    $now = current_datetime();

    return array(
        'day'  => strtolower($now->format('l')),
        'time' => $now->format('H:i'),
    );
}

/**
 * Convert an HH:MM clock time to minutes from midnight.
 *
 * @param string $time Clock time.
 * @return int
 */
function radio_station_minutes($time) {
    $parts = explode(':', (string) $time);

    return ((int) $parts[0]) * 60 + (int) (isset($parts[1]) ? $parts[1] : 0);
}

/**
 * Format a stored clock time for display.
 *
 * @param string $time HH:MM value.
 * @return string
 */
function radio_station_format_clock($time) {
    $dt = DateTimeImmutable::createFromFormat('!H:i', (string) $time);

    if (!$dt) {
        $dt = DateTimeImmutable::createFromFormat('!H:i:s', (string) $time);
    }

    if (!$dt) {
        return (string) $time;
    }

    return $dt->format('g:i A');
}

/**
 * Format a start and end pair.
 *
 * @param array $slot Schedule slot.
 * @return string
 */
function radio_station_format_range($slot) {
    return radio_station_format_clock($slot['start']) . ' – ' . radio_station_format_clock($slot['end']);
}

/**
 * A slot is usable when it is enabled and has two different times.
 *
 * @param mixed $slot Schedule slot.
 * @return bool
 */
function radio_station_slot_is_scheduled($slot) {
    if (!is_array($slot) || empty($slot['enabled']) || empty($slot['start']) || empty($slot['end'])) {
        return false;
    }

    return $slot['start'] !== $slot['end'];
}

/**
 * Whether a same-day slot is on the air at a clock time.
 *
 * Overnight slots (end earlier than start) stay on the air past midnight.
 *
 * @param mixed  $slot Schedule slot.
 * @param string $time Current HH:MM.
 * @return bool
 */
function radio_station_slot_is_on_air($slot, $time) {
    if (!radio_station_slot_is_scheduled($slot)) {
        return false;
    }

    $start = $slot['start'];
    $end   = $slot['end'];

    if ($start < $end) {
        return $time >= $start && $time < $end;
    }

    return $time >= $start || $time < $end;
}

/**
 * Weekday before the given key.
 *
 * @param string $day Weekday key.
 * @return string
 */
function radio_station_previous_day($day) {
    $days  = array_keys(radio_station_days());
    $index = array_search($day, $days, true);

    if ($index === false) {
        return 'sunday';
    }

    return $days[($index + 6) % 7];
}

/**
 * Hydrated show meta, cached for the request.
 *
 * @param int $post_id Show ID.
 * @return array
 */
function radio_station_get_show_record($post_id) {
    static $cache = array();

    $post_id = (int) $post_id;

    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }

    $schedule = get_post_meta($post_id, '_show_schedule', true);
    $dj_ids   = get_post_meta($post_id, '_show_djs', true);
    $status   = get_post_meta($post_id, '_show_status', true);
    $genre    = (string) get_post_meta($post_id, '_show_genre', true);

    if ($status !== 'active' && $status !== 'inactive') {
        $status = 'active';
    }

    $cache[$post_id] = array(
        'id'       => $post_id,
        'schedule' => is_array($schedule) ? $schedule : array(),
        'dj_ids'   => is_array($dj_ids) ? array_values(array_unique(array_filter(array_map('absint', $dj_ids)))) : array(),
        'genre'    => $genre,
        'status'   => $status,
    );

    return $cache[$post_id];
}

/**
 * Published shows.
 *
 * @param bool $active_only Limit to shows marked active.
 * @return WP_Post[]
 */
function radio_station_get_show_posts($active_only = false) {
    static $posts = null;

    if ($posts === null) {
        $posts = get_posts(
            array(
                'post_type'      => 'shows',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            )
        );
    }

    if (!$active_only) {
        return $posts;
    }

    return array_values(
        array_filter(
            $posts,
            function ($post) {
                return radio_station_is_active($post->ID);
            }
        )
    );
}

/**
 * Published DJs.
 *
 * @return WP_Post[]
 */
function radio_station_get_dj_posts() {
    static $posts = null;

    if ($posts === null) {
        $posts = get_posts(
            array(
                'post_type'      => 'djs',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            )
        );
    }

    return $posts;
}

/**
 * Whether a show is currently on the schedule.
 *
 * @param int $post_id Show ID.
 * @return bool
 */
function radio_station_is_active($post_id) {
    $record = radio_station_get_show_record($post_id);

    return $record['status'] !== 'inactive';
}

/**
 * Published DJs assigned to a show.
 *
 * @param int $post_id Show ID.
 * @return WP_Post[]
 */
function radio_station_get_show_djs($post_id) {
    $record = radio_station_get_show_record($post_id);
    $djs    = array();

    foreach ($record['dj_ids'] as $dj_id) {
        $dj = get_post($dj_id);

        if ($dj && $dj->post_type === 'djs' && $dj->post_status === 'publish') {
            $djs[] = $dj;
        }
    }

    return $djs;
}

/**
 * Shows that list this DJ as a host.
 *
 * @param int $dj_id DJ ID.
 * @return WP_Post[]
 */
function radio_station_shows_for_dj($dj_id) {
    $dj_id   = (int) $dj_id;
    $matches = array();

    foreach (radio_station_get_show_posts() as $show) {
        $record = radio_station_get_show_record($show->ID);

        if (in_array($dj_id, $record['dj_ids'], true)) {
            $matches[] = $show;
        }
    }

    return $matches;
}

/**
 * Profile fields saved by the plugin.
 *
 * @param int $post_id DJ ID.
 * @return array<string, string>
 */
function radio_station_dj_meta($post_id) {
    return array(
        'real_name'      => (string) get_post_meta($post_id, '_dj_real_name', true),
        'favorite_genre' => (string) get_post_meta($post_id, '_dj_favorite_genre', true),
        'instagram'      => (string) get_post_meta($post_id, '_dj_instagram', true),
        'facebook'       => (string) get_post_meta($post_id, '_dj_facebook', true),
        'website'        => (string) get_post_meta($post_id, '_dj_website', true),
    );
}

/**
 * Show categories, or an empty list.
 *
 * @param int $post_id Show ID.
 * @return WP_Term[]
 */
function radio_station_show_categories($post_id) {
    $terms = get_the_terms($post_id, 'show_category');

    if (empty($terms) || is_wp_error($terms)) {
        return array();
    }

    return $terms;
}

/**
 * The slot that makes a show on the air right now, including an overnight spill from yesterday.
 *
 * @param int $post_id Show ID.
 * @return array{day: string, slot: array}|null
 */
function radio_station_active_slot($post_id) {
    if (!radio_station_is_active($post_id)) {
        return null;
    }

    $now         = radio_station_now();
    $record      = radio_station_get_show_record($post_id);
    $today_slot  = isset($record['schedule'][$now['day']]) ? $record['schedule'][$now['day']] : null;

    if (radio_station_slot_is_on_air($today_slot, $now['time'])) {
        return array(
            'day'  => $now['day'],
            'slot' => $today_slot,
        );
    }

    $yesterday      = radio_station_previous_day($now['day']);
    $yesterday_slot = isset($record['schedule'][$yesterday]) ? $record['schedule'][$yesterday] : null;

    if (
        radio_station_slot_is_scheduled($yesterday_slot)
        && $yesterday_slot['end'] < $yesterday_slot['start']
        && $now['time'] < $yesterday_slot['end']
    ) {
        return array(
            'day'  => $yesterday,
            'slot' => $yesterday_slot,
        );
    }

    return null;
}

/**
 * Whether a show is broadcasting now.
 *
 * @param int $post_id Show ID.
 * @return bool
 */
function radio_station_show_is_on_air($post_id) {
    return radio_station_active_slot($post_id) !== null;
}

/**
 * Active shows currently on the air.
 *
 * @return WP_Post[]
 */
function radio_station_on_air_shows() {
    $live = array();

    foreach (radio_station_get_show_posts(true) as $post) {
        if (radio_station_active_slot($post->ID)) {
            $live[] = $post;
        }
    }

    return $live;
}

/**
 * Next future start for one show.
 *
 * @param int $post_id Show ID.
 * @return array{day: string, slot: array, score: int}|null
 */
function radio_station_next_airing($post_id) {
    if (!radio_station_is_active($post_id)) {
        return null;
    }

    $record      = radio_station_get_show_record($post_id);
    $now         = radio_station_now();
    $days        = array_keys(radio_station_days());
    $today_index = array_search($now['day'], $days, true);

    if ($today_index === false) {
        return null;
    }

    $now_minutes = radio_station_minutes($now['time']);
    $best        = null;

    for ($offset = 0; $offset < 7; $offset++) {
        $day  = $days[($today_index + $offset) % 7];
        $slot = isset($record['schedule'][$day]) ? $record['schedule'][$day] : null;

        if (!radio_station_slot_is_scheduled($slot)) {
            continue;
        }

        $start_minutes = radio_station_minutes($slot['start']);

        if ($offset === 0 && $start_minutes <= $now_minutes) {
            continue;
        }

        $score = ($offset * 1440) + $start_minutes;

        if ($best === null || $score < $best['score']) {
            $best = array(
                'day'   => $day,
                'slot'  => $slot,
                'score' => $score,
            );
        }
    }

    return $best;
}

/**
 * The soonest show that is not already on the air.
 *
 * @return array{day: string, slot: array, score: int, post: WP_Post}|null
 */
function radio_station_next_show() {
    $best = null;

    foreach (radio_station_get_show_posts(true) as $post) {
        if (radio_station_active_slot($post->ID)) {
            continue;
        }

        $airing = radio_station_next_airing($post->ID);

        if (!$airing) {
            continue;
        }

        if ($best === null || $airing['score'] < $best['score']) {
            $best         = $airing;
            $best['post'] = $post;
        }
    }

    return $best;
}

/**
 * Listener-facing label for a show's next start.
 *
 * @param int $post_id Show ID.
 * @return string
 */
function radio_station_next_airing_label($post_id) {
    $airing = radio_station_next_airing($post_id);

    if (!$airing) {
        return '';
    }

    $now   = radio_station_now();
    $days  = radio_station_days();
    $label = ($airing['day'] === $now['day']) ? __('Today', 'radio-station') : $days[$airing['day']];

    return sprintf(
        '%1$s, %2$s',
        $label,
        radio_station_format_range($airing['slot'])
    );
}

/**
 * Week board of active shows, sorted by start time.
 *
 * @return array<string, array<int, array{post: WP_Post, slot: array}>>
 */
function radio_station_week_board() {
    $board = array();

    foreach (radio_station_days() as $day => $label) {
        $board[$day] = array();
    }

    foreach (radio_station_get_show_posts(true) as $post) {
        $record = radio_station_get_show_record($post->ID);

        foreach ($board as $day => $entries) {
            unset($entries);
            $slot = isset($record['schedule'][$day]) ? $record['schedule'][$day] : null;

            if (!radio_station_slot_is_scheduled($slot)) {
                continue;
            }

            $board[$day][] = array(
                'post' => $post,
                'slot' => $slot,
            );
        }
    }

    foreach ($board as $day => $entries) {
        usort(
            $entries,
            function ($a, $b) {
                return strcmp($a['slot']['start'], $b['slot']['start']);
            }
        );
        $board[$day] = $entries;
    }

    return $board;
}

/**
 * Overnight shows that continue into this weekday.
 *
 * @param string $day Weekday key.
 * @return array<int, array{post: WP_Post, slot: array}>
 */
function radio_station_continuations($day) {
    $previous = radio_station_previous_day($day);
    $items    = array();

    foreach (radio_station_get_show_posts(true) as $post) {
        $record = radio_station_get_show_record($post->ID);
        $slot   = isset($record['schedule'][$previous]) ? $record['schedule'][$previous] : null;

        if (!radio_station_slot_is_scheduled($slot) || $slot['end'] >= $slot['start']) {
            continue;
        }

        $items[] = array(
            'post' => $post,
            'slot' => $slot,
        );
    }

    return $items;
}

/**
 * Blank weekly schedule in the plugin's shape.
 *
 * @return array<string, array{enabled: int, start: string, end: string}>
 */
function radio_station_blank_schedule() {
    $schedule = array();

    foreach (array_keys(radio_station_days()) as $day) {
        $schedule[$day] = array(
            'enabled' => 0,
            'start'   => '',
            'end'     => '',
        );
    }

    return $schedule;
}

/**
 * Genre selected on a show archive, if it is one the plugin allows.
 *
 * @return string
 */
function radio_station_current_genre() {
    $genre = isset($_GET['genre']) ? sanitize_key(wp_unslash($_GET['genre'])) : '';

    if (!array_key_exists($genre, radio_station_genres())) {
        return '';
    }

    return $genre;
}

/**
 * Base URL for show filters. Stays on a category archive when one is open.
 *
 * @return string
 */
function radio_station_shows_filter_base() {
    if (is_tax('show_category')) {
        $link = get_term_link(get_queried_object());

        if (!is_wp_error($link)) {
            return $link;
        }
    }

    $archive = get_post_type_archive_link('shows');

    return $archive ? $archive : home_url('/');
}

/**
 * Permalink for the Schedule or Listen page.
 *
 * @param string $key schedule|listen.
 * @return string
 */
function radio_station_page_url($key) {
    $templates = array(
        'schedule' => 'page-templates/schedule.php',
        'listen'   => 'page-templates/listen.php',
        'about'    => 'page-templates/about.php',
    );

    if (!isset($templates[$key])) {
        return home_url('/');
    }

    $stored = get_option('radio_station_pages');

    if (is_array($stored) && !empty($stored[$key])) {
        $url = get_permalink((int) $stored[$key]);

        if ($url) {
            return $url;
        }
    }

    $pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => $templates[$key],
            'no_found_rows'  => true,
        )
    );

    if ($pages) {
        return get_permalink($pages[0]);
    }

    return home_url('/');
}

/**
 * Whether a primary nav item matches the current view.
 *
 * @param string $slug shows|djs|schedule|listen.
 * @return bool
 */
function radio_station_nav_is_current($slug) {
    if ($slug === 'shows') {
        return is_post_type_archive('shows') || is_singular('shows') || is_tax('show_category');
    }

    if ($slug === 'djs') {
        return is_post_type_archive('djs') || is_singular('djs');
    }

    if ($slug === 'schedule') {
        return is_page_template('page-templates/schedule.php');
    }

    if ($slug === 'listen') {
        return is_page_template('page-templates/listen.php');
    }

    if ($slug === 'about') {
        return is_page_template('page-templates/about.php');
    }

    return false;
}

/**
 * Compare two local URLs loosely enough for menu highlighting.
 *
 * @param string $first  First URL.
 * @param string $second Second URL.
 * @return bool
 */
function radio_station_urls_match($first, $second) {
    return untrailingslashit((string) $first) === untrailingslashit((string) $second);
}

/**
 * Mark Station menu items current on show and DJ screens.
 *
 * @param WP_Post[] $items Menu items.
 * @return WP_Post[]
 */
function radio_station_mark_menu_items($items) {
    $targets = array(
        'shows'    => get_post_type_archive_link('shows'),
        'djs'      => get_post_type_archive_link('djs'),
        'schedule' => radio_station_page_url('schedule'),
        'about'    => radio_station_page_url('about'),
        'listen'   => radio_station_page_url('listen'),
    );

    foreach ($items as $item) {
        foreach ($targets as $slug => $url) {
            if ($url && radio_station_urls_match($item->url, $url) && radio_station_nav_is_current($slug)) {
                $item->current = true;
                $item->classes[] = 'current-menu-item';
            }
        }
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'radio_station_mark_menu_items');

/**
 * Print the primary navigation.
 */
function radio_station_primary_nav() {
    wp_nav_menu(
        array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'site-nav-list',
            'fallback_cb'    => 'radio_station_nav_fallback',
            'depth'          => 1,
            'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
        )
    );
}

/**
 * Navigation used before a menu is assigned.
 */
function radio_station_nav_fallback() {
    $items = array(
        'shows'    => array(get_post_type_archive_link('shows'), __('Shows', 'radio-station')),
        'djs'      => array(get_post_type_archive_link('djs'), __('DJs', 'radio-station')),
        'schedule' => array(radio_station_page_url('schedule'), __('Schedule', 'radio-station')),
        'about'    => array(radio_station_page_url('about'), __('About', 'radio-station')),
        'listen'   => array(radio_station_page_url('listen'), __('Listen', 'radio-station')),
    );

    echo '<ul class="site-nav-list">';

    foreach ($items as $slug => $item) {
        if (empty($item[0])) {
            continue;
        }

        echo '<li>';
        echo '<a href="' . esc_url($item[0]) . '"';

        if (radio_station_nav_is_current($slug)) {
            echo ' aria-current="page"';
        }

        echo '>' . esc_html($item[1]) . '</a>';
        echo '</li>';
    }

    echo '</ul>';
}

/**
 * Host names as plain text.
 *
 * @param int $post_id Show ID.
 * @return string
 */
function radio_station_host_list($post_id) {
    $names = array();

    foreach (radio_station_get_show_djs($post_id) as $dj) {
        $names[] = get_the_title($dj);
    }

    return implode(', ', $names);
}

/**
 * Linked host names. Escaped inside the helper.
 *
 * @param int $post_id Show ID.
 * @return string
 */
function radio_station_host_links($post_id) {
    $links = array();

    foreach (radio_station_get_show_djs($post_id) as $dj) {
        $links[] = '<a href="' . esc_url(get_permalink($dj)) . '">' . esc_html(get_the_title($dj)) . '</a>';
    }

    return implode(', ', $links);
}

/**
 * External profile link.
 *
 * @param string $url   Destination.
 * @param string $label Link text.
 * @return string
 */
function radio_station_external_link($url, $label) {
    if ($url === '') {
        return '';
    }

    return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
        . esc_html($label)
        . '<span class="screen-reader-text"> ('
        . esc_html__('opens in a new tab', 'radio-station')
        . ')</span></a>';
}

/**
 * First letter of a title, for artwork fallbacks.
 *
 * @param string $title Title.
 * @return string
 */
function radio_station_initial($title) {
    $title = trim(wp_strip_all_tags((string) $title));

    if ($title === '') {
        return '?';
    }

    if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
        return mb_strtoupper(mb_substr($title, 0, 1));
    }

    return strtoupper(substr($title, 0, 1));
}

/**
 * Print the letter tile used when a show or DJ has no image.
 *
 * @param string $title Title.
 * @param int    $hue   Hue from 0 to 360.
 */
function radio_station_media_fallback($title, $hue) {
    echo '<span class="media-fallback" style="--hue: ' . esc_attr((string) (int) $hue) . '">';
    echo esc_html(radio_station_initial($title));
    echo '</span>';
}

/**
 * Use the station name in the browser title.
 *
 * @param array $parts Title parts.
 * @return array
 */
function radio_station_document_title($parts) {
    $station = trim(radio_station_mod('name') . ' ' . radio_station_mod('frequency'));

    if (is_front_page()) {
        $parts['title']   = $station;
        $parts['tagline'] = radio_station_mod('tagline');
        unset($parts['site']);
        return $parts;
    }

    $parts['site'] = $station;

    return $parts;
}
add_filter('document_title_parts', 'radio_station_document_title');

/**
 * Body classes for live state and the two station pages.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function radio_station_body_class($classes) {
    if (radio_station_on_air_shows()) {
        $classes[] = 'has-live-show';
    }

    if (is_page_template('page-templates/schedule.php')) {
        $classes[] = 'page-schedule';
    }

    if (is_page_template('page-templates/listen.php')) {
        $classes[] = 'page-listen';
    }

    return $classes;
}
add_filter('body_class', 'radio_station_body_class');

/**
 * Archive ordering and the genre filter.
 *
 * @param WP_Query $query Main query.
 */
function radio_station_filter_archives($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_post_type_archive('shows') || $query->is_tax('show_category')) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', 12);

        $genre = radio_station_current_genre();

        if ($genre !== '') {
            $meta_query   = (array) $query->get('meta_query');
            $meta_query[] = array(
                'key'   => '_show_genre',
                'value' => $genre,
            );
            $query->set('meta_query', $meta_query);
        }
    }

    if ($query->is_post_type_archive('djs')) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', 12);
    }

    if ($query->is_search()) {
        $query->set('post_type', array('shows', 'djs', 'page', 'post'));
    }
}
add_action('pre_get_posts', 'radio_station_filter_archives');

/**
 * Include a DJ's shows when the search matches that DJ's name.
 *
 * Assigned hosts are stored as meta, so a title search would otherwise miss them.
 *
 * @param string   $search Search SQL.
 * @param WP_Query $query  Query.
 * @return string
 */
function radio_station_extend_search($search, $query) {
    if ($search === '' || is_admin() || !$query->is_main_query() || !$query->is_search()) {
        return $search;
    }

    global $wpdb;

    $term = $query->get('s');

    if (!is_string($term) || $term === '') {
        return $search;
    }

    $like   = '%' . $wpdb->esc_like($term) . '%';
    $dj_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'djs' AND post_status = 'publish' AND post_title LIKE %s",
            $like
        )
    );
    $real_name_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_dj_real_name' AND meta_value LIKE %s",
            $like
        )
    );
    $dj_ids = array_values(array_unique(array_map('absint', array_merge($dj_ids, $real_name_ids))));
    $show_ids = array();

    foreach ($dj_ids as $dj_id) {
        foreach (radio_station_shows_for_dj($dj_id) as $show) {
            $show_ids[] = (int) $show->ID;
        }
    }

    $show_ids = array_values(array_unique($show_ids));

    if (!$show_ids) {
        return $search;
    }

    $id_sql = implode(',', $show_ids);
    $inner  = preg_replace('/^\s*AND\s*/i', '', $search);

    return " AND ( {$inner} OR {$wpdb->posts}.ID IN ({$id_sql}) ) ";
}
add_filter('posts_search', 'radio_station_extend_search', 10, 2);

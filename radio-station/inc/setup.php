<?php
/**
 * First-run pages, menu, and starter station library.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create the Schedule and Listen pages, flush rewrites, and seed an empty library once.
 */
function radio_station_bootstrap() {
    if (get_option('radio_station_theme_ready')) {
        return;
    }

    if (!post_type_exists('shows') || !post_type_exists('djs')) {
        return;
    }

    radio_station_ensure_pages();
    flush_rewrite_rules(false);
    radio_station_ensure_menu();
    radio_station_maybe_brand_site();
    radio_station_maybe_seed();
    update_option('radio_station_theme_ready', 1);
}
add_action('init', 'radio_station_bootstrap', 99);

/**
 * Rebuild pretty permalinks when Shows, DJs, or categories are missing from them.
 *
 * A flush that ran before those content types were registered leaves /djs/ as a 404
 * and can send /shows/ to the homepage.
 */
function radio_station_maybe_flush_rewrites() {
    if (!post_type_exists('shows') || !post_type_exists('djs') || !taxonomy_exists('show_category')) {
        return;
    }

    $rules = get_option('rewrite_rules');

    if (!is_array($rules)) {
        $rules = array();
    }

    $has_shows    = false;
    $has_djs      = false;
    $has_category = false;

    foreach ($rules as $query) {
        if (!is_string($query)) {
            continue;
        }

        if (preg_match('/post_type=shows(&|$)/', $query)) {
            $has_shows = true;
        }

        if (preg_match('/post_type=djs(&|$)/', $query)) {
            $has_djs = true;
        }

        if (strpos($query, 'show_category=') !== false) {
            $has_category = true;
        }
    }

    if ($has_shows && $has_djs && $has_category) {
        return;
    }

    flush_rewrite_rules(false);
}
add_action('init', 'radio_station_maybe_flush_rewrites', 99);

/**
 * Run setup again the next time the theme is activated.
 */
function radio_station_reset_bootstrap() {
    delete_option('radio_station_theme_ready');
}
add_action('after_switch_theme', 'radio_station_reset_bootstrap');

/**
 * Publish the two station pages and remember their IDs.
 */
function radio_station_ensure_pages() {
    $definitions = array(
        'schedule' => array(
            'title'    => __('Schedule', 'radio-station'),
            'template' => 'page-templates/schedule.php',
        ),
        'listen'   => array(
            'title'    => __('Listen', 'radio-station'),
            'template' => 'page-templates/listen.php',
        ),
        'about'    => array(
            'title'    => __('About', 'radio-station'),
            'template' => 'page-templates/about.php',
        ),
    );

    $stored = get_option('radio_station_pages');

    if (!is_array($stored)) {
        $stored = array();
    }

    foreach ($definitions as $slug => $page) {
        $page_id = 0;

        if (!empty($stored[$slug])) {
            $existing = get_post((int) $stored[$slug]);

            if ($existing && $existing->post_type === 'page') {
                $page_id = (int) $existing->ID;
            }
        }

        if (!$page_id) {
            $by_path = get_page_by_path($slug);

            if ($by_path) {
                $page_id = (int) $by_path->ID;
            }
        }

        if (!$page_id) {
            $inserted = wp_insert_post(
                array(
                    'post_title'   => $page['title'],
                    'post_name'    => $slug,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => '',
                )
            );

            if (is_wp_error($inserted) || !$inserted) {
                continue;
            }

            $page_id = (int) $inserted;
        }

        update_post_meta($page_id, '_wp_page_template', $page['template']);
        $stored[$slug] = $page_id;
    }

    update_option('radio_station_pages', $stored);
}

/**
 * Create the Station menu when the primary slot is empty.
 */
function radio_station_ensure_menu() {
    $locations = get_theme_mod('nav_menu_locations');

    if (!is_array($locations)) {
        $locations = array();
    }

    $menu = wp_get_nav_menu_object('Station');

    if ($menu) {
        $menu_id = (int) $menu->term_id;
    } else {
        $created = wp_create_nav_menu('Station');

        if (is_wp_error($created)) {
            return;
        }

        $menu_id = (int) $created;
    }

    $items = wp_get_nav_menu_items($menu_id);

    if (empty($items)) {
        $links = array(
            __('Shows', 'radio-station')    => get_post_type_archive_link('shows'),
            __('DJs', 'radio-station')      => get_post_type_archive_link('djs'),
            __('Schedule', 'radio-station') => radio_station_page_url('schedule'),
            __('About', 'radio-station')    => radio_station_page_url('about'),
            __('Listen', 'radio-station')   => radio_station_page_url('listen'),
        );

        $position = 1;

        foreach ($links as $title => $url) {
            if (!$url) {
                continue;
            }

            wp_update_nav_menu_item(
                $menu_id,
                0,
                array(
                    'menu-item-title'    => $title,
                    'menu-item-url'      => $url,
                    'menu-item-status'   => 'publish',
                    'menu-item-type'     => 'custom',
                    'menu-item-position' => $position,
                )
            );

            $position++;
        }
    }

    if (empty($locations['primary'])) {
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}

/**
 * Replace a fresh WordPress site title with the station name.
 */
function radio_station_maybe_brand_site() {
    $name          = (string) get_option('blogname');
    $generic_names = array('', 'WordPress', 'My WordPress', 'Site Title');

    if (in_array($name, $generic_names, true)) {
        update_option('blogname', 'KITE 91.7');
    }

    $description = (string) get_option('blogdescription');

    if ($description === '' || $description === 'Just another WordPress site') {
        update_option('blogdescription', radio_station_defaults()['tagline']);
    }
}

/**
 * Publish a small station library when Shows and DJs are both empty.
 */
function radio_station_maybe_seed() {
    $state = get_option('radio_station_seeded');

    if ($state === 'yes' || $state === 'skipped') {
        return;
    }

    $show_ids = get_posts(
        array(
            'post_type'      => 'shows',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        )
    );
    $dj_ids = get_posts(
        array(
            'post_type'      => 'djs',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        )
    );

    if ($show_ids || $dj_ids) {
        update_option('radio_station_seeded', 'skipped');
        return;
    }

    $nova = radio_station_seed_dj(
        'Nova Ellison',
        'Nova builds the overnight from records that feel like weather. She came up on college radio and still cues the first song by hand.',
        'Maya Chen',
        'Electronic'
    );
    $june = radio_station_seed_dj(
        'June Park',
        'June hosts the conversations Bay Harbor has in the morning and pretends it did not. Interviews, call-ins, and the occasional long silence.',
        'Priya Raman',
        'Talk'
    );
    $miles = radio_station_seed_dj(
        'Miles Hart',
        'Miles plays guitars with dirt still on them: rock, country, and the songs that sit between.',
        'Samuel Hart',
        'Rock'
    );
    $lena = radio_station_seed_dj(
        'Lena Ortiz',
        'Lena calls the local games and stays on after the final whistle to talk through what just happened.',
        'Elena Ortiz',
        'Sports'
    );

    if (!$nova || !$june || !$miles || !$lena) {
        return;
    }

    $music     = radio_station_ensure_term('Music', 'show_category');
    $talk      = radio_station_ensure_term('Talk', 'show_category');
    $news      = radio_station_ensure_term('News', 'show_category');
    $sports    = radio_station_ensure_term('Sports', 'show_category');
    $overnight = radio_station_ensure_term('Overnight', 'show_category');
    $today     = radio_station_now()['day'];
    $live_slot = radio_station_seed_live_slot();

    $afterglow = radio_station_blank_schedule();
    $afterglow[$live_slot['day']] = array(
        'enabled' => 1,
        'start'   => $live_slot['start'],
        'end'     => $live_slot['end'],
    );

    if ($today !== 'friday') {
        $afterglow['friday'] = array(
            'enabled' => 1,
            'start'   => '22:00',
            'end'     => '23:30',
        );
    }

    if ($today !== 'tuesday') {
        $afterglow['tuesday'] = array(
            'enabled' => 1,
            'start'   => '21:00',
            'end'     => '23:30',
        );
    }

    radio_station_seed_show(
        'Afterglow',
        'A slow electronic set for the hours when the harbor lights thin out. No countdowns and no shout-outs, just a continuous stretch of music.',
        array($nova),
        'electronic',
        $afterglow,
        array_filter(array($music, $overnight))
    );

    $open = radio_station_blank_schedule();

    foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday') as $day) {
        if ($day === $today) {
            continue;
        }

        $open[$day] = array(
            'enabled' => 1,
            'start'   => '07:00',
            'end'     => '09:00',
        );
    }

    radio_station_seed_show(
        'Open Channel',
        'Morning talk for Bay Harbor. Local voices, one guest, and enough time to finish a thought.',
        array($june),
        'talk',
        $open,
        array_filter(array($talk))
    );

    $copper = radio_station_blank_schedule();

    if ($today !== 'saturday') {
        $copper['saturday'] = array(
            'enabled' => 1,
            'start'   => '20:00',
            'end'     => '22:00',
        );
    }

    radio_station_seed_show(
        'Copper Hour',
        'Saturday night with Miles Hart: rock and country, new and worn out. The request line is open.',
        array($miles),
        'rock',
        $copper,
        array_filter(array($music))
    );

    $split = radio_station_blank_schedule();

    if ($today !== 'thursday') {
        $split['thursday'] = array(
            'enabled' => 1,
            'start'   => '21:00',
            'end'     => '23:00',
        );
    }

    radio_station_seed_show(
        'Split Signal',
        'Nova and Miles trade hip-hop and the records around it. Two libraries, one chair, and nothing prepared in advance.',
        array($nova, $miles),
        'hip-hop',
        $split,
        array_filter(array($music))
    );

    $dock = radio_station_blank_schedule();

    if ($today !== 'wednesday') {
        $dock['wednesday'] = array(
            'enabled' => 1,
            'start'   => '18:00',
            'end'     => '20:00',
        );
    }

    radio_station_seed_show(
        'The Long Dock',
        'Midweek, June and Nova stay on one topic until it runs out. Sometimes it is music. Sometimes it is the city.',
        array($june, $nova),
        'mixed',
        $dock,
        array_filter(array($talk))
    );

    $desk = radio_station_blank_schedule();

    foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday') as $day) {
        $desk[$day] = array(
            'enabled' => 1,
            'start'   => '12:00',
            'end'     => '13:00',
        );
    }

    radio_station_seed_show(
        'City Desk',
        'A midday news hour. June reads the harbor, the council, and the stories that did not make the morning show.',
        array($june),
        'talk',
        $desk,
        array_filter(array($news))
    );

    $whistle = radio_station_blank_schedule();
    $whistle['saturday'] = array(
        'enabled' => 1,
        'start'   => '14:00',
        'end'     => '16:00',
    );

    radio_station_seed_show(
        'Final Whistle',
        'Saturday sports with Lena Ortiz. Local scores, one guest from the field, and the calls people are still arguing about.',
        array($lena),
        'talk',
        $whistle,
        array_filter(array($sports))
    );

    update_option('radio_station_seeded', 'yes');
}

/**
 * Insert one DJ.
 *
 * @param string $name     On-air name.
 * @param string $bio      Profile copy.
 * @param string $real     Real name.
 * @param string $favorite Favorite genre label.
 * @return int
 */
function radio_station_seed_dj($name, $bio, $real, $favorite) {
    $id = wp_insert_post(
        array(
            'post_type'    => 'djs',
            'post_status'  => 'publish',
            'post_title'   => $name,
            'post_content' => $bio,
        )
    );

    if (is_wp_error($id) || !$id) {
        return 0;
    }

    update_post_meta($id, '_dj_real_name', $real);
    update_post_meta($id, '_dj_favorite_genre', $favorite);

    return (int) $id;
}

/**
 * Insert one show with plugin meta.
 *
 * @param string $title      Show title.
 * @param string $content    Description.
 * @param int[]  $dj_ids     Host IDs.
 * @param string $genre      Genre slug.
 * @param array  $schedule   Weekly schedule.
 * @param int[]  $categories Category term IDs.
 * @return int
 */
function radio_station_seed_show($title, $content, $dj_ids, $genre, $schedule, $categories) {
    $id = wp_insert_post(
        array(
            'post_type'    => 'shows',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_content' => $content,
        )
    );

    if (is_wp_error($id) || !$id) {
        return 0;
    }

    update_post_meta($id, '_show_djs', array_map('absint', $dj_ids));
    update_post_meta($id, '_show_schedule', $schedule);
    update_post_meta($id, '_show_genre', $genre);
    update_post_meta($id, '_show_status', 'active');

    $categories = array_values(array_filter(array_map('absint', $categories)));

    if ($categories) {
        wp_set_object_terms($id, $categories, 'show_category');
    }

    return (int) $id;
}

/**
 * A same-day slot that is on the air when the library is first created.
 *
 * @return array{day: string, start: string, end: string}
 */
function radio_station_seed_live_slot() {
    $now   = current_datetime();
    $start = $now->modify('-30 minutes');
    $end   = $now->modify('+3 hours');

    $start_time = ($start->format('Y-m-d') === $now->format('Y-m-d')) ? $start->format('H:i') : '00:00';
    $end_time   = ($end->format('Y-m-d') === $now->format('Y-m-d')) ? $end->format('H:i') : '23:59';

    if ($start_time >= $end_time) {
        $start_time = '00:00';
        $end_time   = '23:59';
    }

    return array(
        'day'   => strtolower($now->format('l')),
        'start' => $start_time,
        'end'   => $end_time,
    );
}

/**
 * Find or create a show category.
 *
 * @param string $name     Term name.
 * @param string $taxonomy Taxonomy.
 * @return int
 */
function radio_station_ensure_term($name, $taxonomy) {
    $existing = term_exists($name, $taxonomy);

    if ($existing) {
        return (int) (is_array($existing) ? $existing['term_id'] : $existing);
    }

    $created = wp_insert_term($name, $taxonomy);

    if (is_wp_error($created)) {
        return 0;
    }

    return (int) $created['term_id'];
}

/**
 * Bring an already-seeded library up to the required sample set.
 *
 * Runs after the first bootstrap so Pressable and local sites that already
 * have KITE content pick up the About page, extra shows, and same-day slots.
 */
function radio_station_upgrade_library() {
    if ((int) get_option('radio_station_content_version') >= 2) {
        return;
    }

    if (!post_type_exists('shows') || !post_type_exists('djs')) {
        return;
    }

    radio_station_ensure_pages();
    radio_station_ensure_menu();
    radio_station_ensure_about_menu_item();
    radio_station_repair_sample_library();
    radio_station_ensure_schedule_block();
    update_option('radio_station_content_version', 2);
}
add_action('init', 'radio_station_upgrade_library', 100);

/**
 * Add About to the Station menu when an older menu is missing it.
 */
function radio_station_ensure_about_menu_item() {
    $menu = wp_get_nav_menu_object('Station');

    if (!$menu) {
        return;
    }

    $about_url = radio_station_page_url('about');

    if (!$about_url) {
        return;
    }

    $items = wp_get_nav_menu_items($menu->term_id);

    if (!is_array($items)) {
        $items = array();
    }

    $position = count($items) + 1;

    foreach ($items as $item) {
        if (radio_station_urls_match($item->url, $about_url)) {
            return;
        }
    }

    foreach ($items as $item) {
        if (!radio_station_urls_match($item->url, radio_station_page_url('listen'))) {
            continue;
        }

        $position = (int) $item->menu_order;
        wp_update_nav_menu_item(
            (int) $menu->term_id,
            (int) $item->ID,
            array(
                'menu-item-title'    => $item->title,
                'menu-item-url'      => $item->url,
                'menu-item-status'   => 'publish',
                'menu-item-type'     => 'custom',
                'menu-item-position' => $position + 1,
            )
        );
        break;
    }

    wp_update_nav_menu_item(
        (int) $menu->term_id,
        0,
        array(
            'menu-item-title'    => __('About', 'radio-station'),
            'menu-item-url'      => $about_url,
            'menu-item-status'   => 'publish',
            'menu-item-type'     => 'custom',
            'menu-item-position' => $position,
        )
    );
}

/**
 * Add the missing sample records and shorten overnight sample slots.
 */
function radio_station_repair_sample_library() {
    radio_station_ensure_term('Music', 'show_category');
    $news   = radio_station_ensure_term('News', 'show_category');
    $sports = radio_station_ensure_term('Sports', 'show_category');
    radio_station_ensure_term('Talk', 'show_category');

    $afterglow = radio_station_find_titled_post('shows', 'Afterglow');

    if ($afterglow) {
        radio_station_force_same_day($afterglow);
    }

    $june = radio_station_find_titled_post('djs', 'June Park');
    $lena = radio_station_find_titled_post('djs', 'Lena Ortiz');

    if (!$lena) {
        $lena = radio_station_seed_dj(
            'Lena Ortiz',
            'Lena calls the local games and stays on after the final whistle to talk through what just happened.',
            'Elena Ortiz',
            'Sports'
        );
    }

    if ($june && !radio_station_find_titled_post('shows', 'City Desk')) {
        $desk = radio_station_blank_schedule();

        foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday') as $day) {
            $desk[$day] = array(
                'enabled' => 1,
                'start'   => '12:00',
                'end'     => '13:00',
            );
        }

        radio_station_seed_show(
            'City Desk',
            'A midday news hour. June reads the harbor, the council, and the stories that did not make the morning show.',
            array($june),
            'talk',
            $desk,
            array_filter(array($news))
        );
    }

    if ($lena && !radio_station_find_titled_post('shows', 'Final Whistle')) {
        $whistle = radio_station_blank_schedule();
        $whistle['saturday'] = array(
            'enabled' => 1,
            'start'   => '14:00',
            'end'     => '16:00',
        );

        radio_station_seed_show(
            'Final Whistle',
            'Saturday sports with Lena Ortiz. Local scores, one guest from the field, and the calls people are still arguing about.',
            array($lena),
            'talk',
            $whistle,
            array_filter(array($sports))
        );
    }
}

/**
 * Look up a published-or-draft record by its exact title.
 *
 * @param string $post_type Post type.
 * @param string $title     Title.
 * @return int
 */
function radio_station_find_titled_post($post_type, $title) {
    global $wpdb;

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status NOT IN ('trash', 'auto-draft') ORDER BY ID ASC LIMIT 1",
            $post_type,
            $title
        )
    );

    return $id ? (int) $id : 0;
}

/**
 * Keep a sample show inside a single calendar day.
 *
 * @param int $post_id Show ID.
 */
function radio_station_force_same_day($post_id) {
    $schedule = get_post_meta($post_id, '_show_schedule', true);

    if (!is_array($schedule)) {
        return;
    }

    $changed = false;

    foreach ($schedule as $day => $slot) {
        if (empty($slot['enabled']) || empty($slot['start']) || empty($slot['end'])) {
            continue;
        }

        if ($slot['end'] >= $slot['start']) {
            continue;
        }

        if ($slot['start'] >= '23:30') {
            $schedule[$day]['start'] = '21:00';
        }

        $schedule[$day]['end'] = '23:30';
        $changed = true;
    }

    if ($changed) {
        update_post_meta($post_id, '_show_schedule', $schedule);
    }
}

/**
 * Place the full-week schedule block on an empty Schedule page.
 */
function radio_station_ensure_schedule_block() {
    $pages = get_option('radio_station_pages');

    if (empty($pages['schedule'])) {
        return;
    }

    $page = get_post((int) $pages['schedule']);

    if (!$page || trim($page->post_content) !== '') {
        return;
    }

    wp_update_post(
        array(
            'ID'           => $page->ID,
            'post_content' => '<!-- wp:radio-station/show-schedule {"day":"week"} /-->',
        )
    );
}

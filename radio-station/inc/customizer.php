<?php
/**
 * Station identity and listen links.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function radio_station_customize_register($wp_customize) {
    $wp_customize->add_section(
        'radio_station_identity',
        array(
            'title'    => __('Station identity', 'radio-station'),
            'priority' => 30,
        )
    );

    $wp_customize->add_section(
        'radio_station_listen',
        array(
            'title'       => __('Where to listen', 'radio-station'),
            'description' => __('Frequency, stream, and the other places listeners can tune in.', 'radio-station'),
            'priority'    => 35,
        )
    );

    $text_fields = array(
        'name'         => array('radio_station_identity', __('Station name', 'radio-station')),
        'frequency'    => array('radio_station_identity', __('Frequency', 'radio-station')),
        'tagline'      => array('radio_station_identity', __('Tagline', 'radio-station')),
        'city'         => array('radio_station_identity', __('City', 'radio-station')),
        'request_line'   => array('radio_station_listen', __('Request line', 'radio-station')),
        'studio_address' => array('radio_station_identity', __('Studio address', 'radio-station')),
    );

    foreach ($text_fields as $key => $field) {
        radio_station_add_customizer_text($wp_customize, $key, $field[0], $field[1]);
    }

    $wp_customize->add_setting(
        'radio_station_contact_email',
        array(
            'default'           => radio_station_defaults()['contact_email'],
            'sanitize_callback' => 'sanitize_email',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'radio_station_contact_email',
        array(
            'label'   => __('Contact email', 'radio-station'),
            'section' => 'radio_station_identity',
            'type'    => 'email',
        )
    );

    $wp_customize->add_setting(
        'radio_station_about',
        array(
            'default'           => radio_station_defaults()['about'],
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'radio_station_about',
        array(
            'label'   => __('About the station', 'radio-station'),
            'section' => 'radio_station_identity',
            'type'    => 'textarea',
        )
    );

    $url_fields = array(
        'stream_url'  => __('Stream URL', 'radio-station'),
        'tunein_url'  => __('TuneIn URL', 'radio-station'),
        'spotify_url' => __('Spotify URL', 'radio-station'),
        'apple_url'   => __('Apple Podcasts URL', 'radio-station'),
    );

    foreach ($url_fields as $key => $label) {
        $wp_customize->add_setting(
            'radio_station_' . $key,
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'radio_station_' . $key,
            array(
                'label'       => $label,
                'description' => ($key === 'stream_url') ? __('A direct Icecast, AAC, or MP3 mount plays in the browser.', 'radio-station') : '',
                'section'     => 'radio_station_listen',
                'type'        => 'url',
            )
        );
    }
}
add_action('customize_register', 'radio_station_customize_register');

/**
 * Add one plain-text Customizer field.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @param string               $key          Setting key.
 * @param string               $section      Section ID.
 * @param string               $label        Control label.
 */
function radio_station_add_customizer_text($wp_customize, $key, $section, $label) {
    $defaults = radio_station_defaults();

    $wp_customize->add_setting(
        'radio_station_' . $key,
        array(
            'default'           => isset($defaults[$key]) ? $defaults[$key] : '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'radio_station_' . $key,
        array(
            'label'   => $label,
            'section' => $section,
            'type'    => 'text',
        )
    );
}

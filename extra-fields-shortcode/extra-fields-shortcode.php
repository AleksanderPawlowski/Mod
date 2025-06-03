<?php
/**
 * Plugin Name: Extra Fields Shortcode
 * Description: Register additional custom fields and display them via shortcode.
 * Version: 1.0.0
 * Author: OpenAI Codex
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Global array to hold registered fields
$GLOBALS['efs_fields'] = array();

/**
 * Register a custom field for a post type.
 *
 * Example usage: efs_register_field('subtitle', 'Subtitle', 'post');
 *
 * @param string $key       Meta key
 * @param string $label     Field label shown in admin
 * @param string $post_type Post type (default: post)
 */
function efs_register_field( $key, $label = '', $post_type = 'post' ) {
    $fields = isset( $GLOBALS['efs_fields'][ $post_type ] ) ? $GLOBALS['efs_fields'][ $post_type ] : array();
    $fields[ $key ] = $label;
    $GLOBALS['efs_fields'][ $post_type ] = $fields;
}

/**
 * Register meta and add meta boxes for all fields.
 */
function efs_setup_fields() {
    foreach ( $GLOBALS['efs_fields'] as $post_type => $fields ) {
        foreach ( $fields as $key => $label ) {
            register_post_meta( $post_type, $key, array( 'show_in_rest' => true, 'single' => true, 'type' => 'string' ) );
        }
    }
}
add_action( 'init', 'efs_setup_fields' );

/**
 * Add meta boxes for registered fields.
 */
function efs_add_meta_boxes() {
    foreach ( $GLOBALS['efs_fields'] as $post_type => $fields ) {
        add_meta_box( 'efs_meta_box', __( 'Extra Fields', 'efs' ), 'efs_render_meta_box', $post_type, 'normal', 'default', $fields );
    }
}
add_action( 'add_meta_boxes', 'efs_add_meta_boxes' );

/**
 * Render the meta box fields.
 */
function efs_render_meta_box( $post, $metabox ) {
    $fields = $metabox['args'];
    wp_nonce_field( 'efs_save_meta', 'efs_meta_nonce' );
    foreach ( $fields as $key => $label ) {
        $value = get_post_meta( $post->ID, $key, true );
        echo '<p>';
        echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $label ? $label : $key ) . '</label><br />';
        echo '<input type="text" class="widefat" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
        echo '</p>';
    }
}

/**
 * Save meta box fields.
 */
function efs_save_post( $post_id ) {
    if ( ! isset( $_POST['efs_meta_nonce'] ) || ! wp_verify_nonce( $_POST['efs_meta_nonce'], 'efs_save_meta' ) ) {
        return;
    }
    foreach ( $GLOBALS['efs_fields'] as $fields ) {
        foreach ( $fields as $key => $label ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
            }
        }
    }
}
add_action( 'save_post', 'efs_save_post' );

/**
 * Shortcode to display a field value for the current post or a specific post ID.
 *
 * Usage: [efs_field name="my_field" post_id="123"]
 */
function efs_field_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'name'    => '',
        'post_id' => get_the_ID(),
    ), $atts, 'efs_field' );

    if ( empty( $atts['name'] ) ) {
        return '';
    }

    $value = get_post_meta( $atts['post_id'], $atts['name'], true );
    return esc_html( $value );
}
add_shortcode( 'efs_field', 'efs_field_shortcode' );


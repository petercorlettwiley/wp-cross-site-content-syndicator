<?php
/**
 * Plugin Name:  Get Content via REST API
 * Description:  Gets ACF and standard WP content from another Wordpress site via the REST API.
 * Plugin URI:   http://www.pcwiley.net
 * Author:       Peter Wiley
 * Version:      0.1
 * Text Domain:  getcontentviarestapi
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package getcontentviarestapi
 */
// Disable direct file access.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
/**
 * Get full page content via REST API.
 */
function get_page_content_via_rest($atts) {
  extract(shortcode_atts(array(
    "url" => null,
    "page_id" => null
  ), $atts));

  $response = wp_remote_get( $url . '/wp-json/wp/v2/pages/' . $page_id );
  // Exit if error.
  if ( is_wp_error( $response ) ) {
    return;
  }
  // Get the body.
  $post = json_decode( wp_remote_retrieve_body( $response ) );

  // Exit if nothing is returned.
  if ( empty( $post ) ) {
    return;
  }
  // If there are posts.
  $rendered_page = '';
  if ( ! empty( $post ) ) {
    // Page metadata
    $page_title = $post->title->rendered;

    // Page header
    $page_header = '<header>' . $post->acf->page_header_content;
    $rendered_page .= $page_header;
    $rendered_page .= '</header>';

    // Content blocks (ACF repeater field for displaying content)
    $content_blocks = $post->acf->content_block;
    foreach ( $content_blocks as $content_block ) {
      $content_block_width = $content_block->content_block_width;
      $content_block_hr = ($content_block->content_block_hr_divider == true ? ' content_block_hr' : '');

      $rendered_page .= '<div class="content_block ' . $content_block_width . $content_block_hr . '">' . $content_block->content_block_text . '</div>';
    }

    // Page content
    $page_content = '<div class="content_block">' . $post->content->rendered . '</div>';
    $rendered_page .= $page_content;
    
    //return $allposts;
    return $rendered_page;
  }
}
// Register as a shortcode to be used on the site.
add_shortcode( 'display_external_page', 'get_page_content_via_rest' );

/**
 * Get footer content via REST API.
 */
function get_footer_content_via_rest($atts) {
  extract(shortcode_atts(array(
    "url" => null
  ), $atts));

  $response = wp_remote_get( $url . '/wp-json/acf/v3/options/options' );
  // Exit if error.
  if ( is_wp_error( $response ) ) {
    return;
  }
  // Get the body.
  $options = json_decode( wp_remote_retrieve_body( $response ) );

  // Exit if nothing is returned.
  if ( empty( $options ) ) {
    return;
  }
  // If there are posts.
  $rendered_footer = '';
  if ( ! empty( $options ) ) {

    // Footer columns (ACF repeater field for displaying content)
    $footer_columns = $options->acf->footer_columns;
    foreach ( $footer_columns as $footer_column ) {
      $footer_column_width = $footer_column->footer_column_width;
      $rendered_footer .= $footer_column->footer_column;
    }
    
    //return $allposts;
    return $rendered_footer;
  }
}
// Register as a shortcode to be used on the site.
add_shortcode( 'display_external_footer', 'get_footer_content_via_rest' );
<?php
/**
 * Plugin Name:  Get Content via REST API
 * Description:  Gets ACF and standard WP content from another Wordpress site via the REST API.
 * Plugin URI:   http://www.pcwiley.net
 * Author:       Peter Wiley
 * Version:      0.1.2
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
    "page_id" => null,
    "class" => null
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
    $page_header = '<header><div class="wrap">' . $post->acf->page_header_content;
    $rendered_page .= $page_header;
    $rendered_page .= '</div></header>';

    // Content blocks (ACF repeater field for displaying content)
    $content_blocks = $post->acf->content_block;
    foreach ( $content_blocks as $content_block ) {
      $content_block_width = $content_block->content_block_width;
      $content_block_hr = ($content_block->content_block_hr_divider == true ? ' content_block_hr' : '');

      $rendered_page .= '<div class="content_block ' . $content_block_width . $content_block_hr . '"><div class="wrap">' . $content_block->content_block_text . '</div></div>';
    }

    // Page content
    $page_content = '<div class="content_block"><div class="wrap">' . $post->content->rendered . '</div></div>';
    $rendered_page .= $page_content;
    
    if ($class) {
      return '<div class="' . $class . '">' . $rendered_page . '</div>';
    } else {
      return $rendered_page;
    }
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
      $rendered_footer .= '<div class="footer_column column" style="width: ' . $footer_column_width . '%;">' . $footer_column->footer_column . '</div>';
    }
    
    //return $allposts;
    return '<div class="footer_columns">' . $rendered_footer . '</div>';
  }
}
// Register as a shortcode to be used on the site.
add_shortcode( 'display_external_footer', 'get_footer_content_via_rest' );

/**
 * Get Board link, logo, and color content via REST API.
 */
function get_board_content_via_rest($atts) {
  extract(shortcode_atts(array(
    "url" => null,
    "ignore" => null
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
  if ( ! empty( $options ) ) {

    $ignore = explode(",", $ignore);
    $ignore = array_map('trim', $ignore);

    // Board info (ACF repeater field for displaying content)
    $boards = $options->acf->community_branding;
    $rendered_boards = '';
    foreach ( $boards as $board ) {

      // check if board slug is in ignore list, if not then render
      $board_slug = $board->community_slug;

      if (!in_array($board_slug, $ignore)) {

        // name, url
  
        $board_name = $board->community_name;
  
        $board_url = ($board->external_community == true ? $board->external_community_url : $url . '/' . $board->community_root_url);

        // color
  
        $board_color = $board->community_color;
  
        // logo
  
        $board_logo = $board->community_logo;
  
        $board_logo_ko = $board->community_logo_knockout;
  
        $board_logo_width = $board->community_logo_width;
  
        $board_logo_height = $board->community_logo_height;
  
        $board_block = <<< EOT
<div class="board_logo">
  <a href="{$board_url}" target="_blank" class="{$board_slug}" style="border-color: {$board_color};">
    <img src="{$board_logo}" alt="{$board_name}">
  </a>
</div>
EOT;
      
        $rendered_boards .= $board_block;

      }
    }
    
    //return $allposts;
    return '<div class="board_logo_block">' . $rendered_boards . '</div>';
  }
}
// Register as a shortcode to be used on the site.
add_shortcode( 'display_boards', 'get_board_content_via_rest' );
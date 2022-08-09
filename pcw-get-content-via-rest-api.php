<?php
/**
 * Plugin Name:  Get Content via REST API
 * Description:  Gets ACF and standard WP content from another Wordpress site via the REST API.
 * Plugin URI:   http://www.pcwiley.net
 * Author:       Peter Wiley
 * Version:      0.1.3
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
    "class" => null,
    "exclude_header" => false
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
    if ( !$exclude_header ){
      $page_header = '<header><div class="wrap">' . $post->acf->page_header_content;
      $rendered_page .= $page_header;
      $rendered_page .= '</div></header>';
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

  $response_acf = wp_remote_get( $url . '/wp-json/acf/v3/options/options' );
  // Exit if error.
  if ( is_wp_error( $response_acf ) ) {
    return;
  }
  // Get the body.
  $options = json_decode( wp_remote_retrieve_body( $response_acf ) );

  // Exit if nothing is returned.
  if ( empty( $options ) ) {
    return;
  }
  // If there are posts.
  $rendered_footer = '<div class="footer_content"><div class="column-group">';

  if ( $options->acf->footer_content_extra ) {
    $rendered_footer .= '<div class="column">' . $options->acf->footer_content_extra . '</div>';
  }

  $communities = $options->acf->community;

  if ( $communities ) {
    $rendered_footer .= '<div class="column">Our Communities: ';
    $community_count = 0;
    $community_index = 0;

    foreach( $communities as $community ) {
      if ( $community->active_community ) {
        $community_count++;
      }
    }

    foreach( $communities as $community ) {
      if ( $community->active_community ) {
        if ( $community->local_community ) {
          $rendered_footer .= '<a href="https://board.org/' . $community->slug . '">';
        } else {
          $rendered_footer .= '<a href="' . $community->community_url . '">';
        }
        $rendered_footer .= $community->name;// . ' ' . $i . '/' . count($communities);
        $rendered_footer .= '</a>';
        if ( $community_index < $community_count - 1 ) {
          $rendered_footer .= ', ';
        } else if ( $community_index == $community_count - 1 ) {
          $rendered_footer .= ', and ';
        }
      }
      $community_index++;
    }
    $rendered_footer .= '</div>';
  }

  $response_menu = wp_remote_get( $url . '/wp-json/menus/v1/locations/menu-1' );

  if ( ! is_wp_error( $response_menu ) ) {
    // Get the body.
    $main_menu = json_decode( wp_remote_retrieve_body( $response_menu ) );

    if ( $main_menu->items ) {
      $rendered_footer .= '<div class="column"><ul class="main-menu">';

      foreach( $main_menu->items as $menu_item ) {
        $rendered_footer .= '<li><a href="' . $menu_item->url . '">' . $menu_item->title . '</a></li>';
      }

      $rendered_footer .= '</ul></div>';
    }
  }  

  $rendered_footer .= '</div></div>';

  return $rendered_footer;
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
    $boards = $options->acf->community;
    $rendered_boards = '';
    foreach ( $boards as $board ) {

      // check if board slug is in ignore list, if not then render
      $board_slug = $board->slug;

      if (!in_array($board_slug, $ignore)) {

        // name, url
  
        $board_name = $board->name;
  
        $board_url = ( $board->local_community == true ? $url . '/' . $board_slug : $board->community_url );

        // color
  
        $board_color = $board->color;
  
        // logo
  
        $board_logo = $board->logo->url;
  
        $board_logo_ko = $board->logo_white->url;
  
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
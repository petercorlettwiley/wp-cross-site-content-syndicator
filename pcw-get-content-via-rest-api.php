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
 * Get posts via REST API.
 */
function get_content_via_rest() {
  // Enter the name of your blog here followed by /wp-json/wp/v2/posts and add filters like this one that limits the result to 2 posts.
  $response_acf = wp_remote_get( 'http://boardorg.staging.wpengine.com/wp-json/acf/v3/pages' );
  $response_wp = wp_remote_get( 'http://boardorg.staging.wpengine.com/wp-json/wp/v2/pages/2' );
  // Exit if error.
  if ( is_wp_error( $response_acf ) || is_wp_error( $response_wp ) ) {
    return;
  }
  // Get the body.
  $post_wp = json_decode( wp_remote_retrieve_body( $response_wp ) );
  $post_acf = json_decode( wp_remote_retrieve_body( $response_acf ) );

  // Exit if nothing is returned.
  if ( empty( $post_wp ) || empty( $post_acf ) ) {
    return;
  }
  // If there are posts.
  $rendered_post = '';
  if ( ! empty( $post_wp ) ) {
    // For each post.
    //foreach ( $posts as $post ) {
      // Use print_r($post); to get the details of the post and all available fields
      // Format the date.
      //$fordate = date( 'n/j/Y', strtotime( $post->modified ) );
      // Show a linked title and post date.
      //$allposts .= '<a href="' . esc_url( $post->link ) . '" target=\"_blank\">' . esc_html( $post->title->rendered ) . '</a>  ' . esc_html( $fordate ) . '<br />';
    //}

    $content_blocks = $post_wp->acf->content_block;

    foreach ( $content_blocks as $content_block ) {
      $rendered_post .= var_dump($content_block);
    }
    
    //return $allposts;
    return $rendered_post;
  }
}
// Register as a shortcode to be used on the site.
add_shortcode( 'display_external_page', 'get_content_via_rest' );
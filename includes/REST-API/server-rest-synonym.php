<?php

namespace RRZE\Synonym\Server;


/**
 * Add REST API support to an already registered post type.
 * 
 * curl -X GET -i 'https://wordpress.dev/wp-json/wp/v2/synonym'
 * curl -X GET -k -i 'https://wordpress.dev/wp-json/wp/v2/synonym/2204279'
 */

add_action( 'init', 'RRZE\Synonym\Server\my_custom_post_type_rest_support', 25 );

function my_custom_post_type_rest_support() {
      global $wp_post_types;

      //be sure to set this to the name of your post type!
      $post_type_name = 'synonym';
      if( isset( $wp_post_types[ $post_type_name ] ) ) {
              $wp_post_types[$post_type_name]->show_in_rest = true;
              $wp_post_types[$post_type_name]->rest_base = $post_type_name;
              $wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
      }

}

add_action( 'rest_api_init', 'RRZE\Synonym\Server\create_api_posts_meta_field' );
 
function create_api_posts_meta_field() {
 
    // register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
    register_rest_field( 'synonym', 'synonym', array(
           'get_callback'    => 'RRZE\Synonym\Server\get_post_meta_for_api',
           'schema'          => null,
        )
    );
}
 
function get_post_meta_for_api( $object ) {
    //get the id of the post object array
    $post_id = $object['id'];
 
    //return the post meta
    return get_post_meta( $post_id );
}
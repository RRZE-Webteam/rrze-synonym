<?php

namespace RRZE\Synonym\Server;

function synonym_post_type() {	

    $labels = array(
            'name'                => _x( 'Synonyms', 'Post Type General Name', 'rrze-synonym-server' ),
            'singular_name'       => _x( 'Synonym', 'Post Type Singular Name', 'rrze-synonym-server' ),
            'menu_name'           => __( 'Synonyms', 'rrze-synonym-server' ),
            'parent_item_colon'   => __( 'Parent Synonyms', 'rrze-synonym-server' ),
            'all_items'           => __( 'All Synonyms', 'rrze-synonym-server' ),
            'view_item'           => __( 'Show Synonym', 'rrze-synonym-server' ),
            'add_new_item'        => __( 'Add Synonym', 'rrze-synonym-server' ),
            'add_new'             => __( 'New Synonym', 'rrze-synonym-server' ),
            'edit_item'           => __( 'Edit Synonym', 'rrze-synonym-server' ),
            'update_item'         => __( 'Update Synonym', 'rrze-synonym-server' ),
            'search_items'        => __( 'Search Synonym', 'rrze-synonym-server' ),
            'not_found'           => __( 'No Synonyms found', 'rrze-synonym-server' ),
            'not_found_in_trash'  => __( 'No Synonyms found in trash', 'rrze-synonym-server' ),
    );
    $rewrite = array(
            'slug'                => 'synonym',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
    );
    $args = array(
            'label'               => __( 'synonym', 'rrze-synonym-server' ),
            'description'         => __( 'Synonym informations', 'rrze-synonym-server' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'menu_icon'		=> 'dashicons-translation',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'query_var'           => 'synonym',
            'rewrite'             => $rewrite,
            'capability_type' => 'synonym',
            'capabilities' => array(
                'edit_post' => 'edit_synonym',
                'read_post' => 'read_synonym',
                'delete_post' => 'delete_synonym',
                'edit_posts' => 'edit_synonyms',
                'edit_others_posts' => 'edit_others_synonyms',
                'publish_posts' => 'publish_synonyms',
                'read_private_posts' => 'read_private_synonyms',
                'delete_posts' => 'delete_synonyms',
                'delete_private_posts' => 'delete_private_synonyms',
                'delete_published_posts' => 'delete_published_synonyms',
                'delete_others_posts' => 'delete_others_synonyms',
                'edit_private_posts' => 'edit_private_synonyms',
                'edit_published_posts' => 'edit_published_synonyms'
            ),
            'map_meta_cap' => true,
            'show_in_rest'       => true,
            'rest_base'          => 'synonym',
            'rest_controller_class' => 'WP_REST_Posts_Controller',

    );
    register_post_type( 'synonym', $args );
}

add_action( 'init', 'RRZE\Synonym\Server\synonym_post_type', 0 );
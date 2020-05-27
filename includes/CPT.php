<?php

namespace RRZE\Synonym;

defined( 'ABSPATH' ) || exit;

/**
 * Custom Post Type "synonym"
 */
class CPT {
    
    private $lang = '';

    public function __construct() {
        $this->lang = substr( get_locale(), 0, 2 );
        add_action( 'init', [$this, 'registerCPT'], 0 );
        // add_action( 'init', [$this, 'registerFaqTaxonomy'], 0 );
        add_action( 'publish_synonym', [$this, 'setPostMeta'], 10, 1 );
        // add_action( 'create_synonym_category', [$this, 'setTermMeta'], 10, 1 );
        // add_action( 'create_synonym_tag', [$this, 'setTermMeta'], 10, 1 );
    }

    
    public function registerCPT() {	    
        $labels = array(
                'name'                => _x( 'synonym', 'synonyms', 'rrze-synonym' ),
                'singular_name'       => _x( 'synonym', 'Single synonym', 'rrze-synonym' ),
                'menu_name'           => __( 'Synonym', 'rrze-synonym' ),
                'add_new'             => __( 'Add synonym', 'rrze-synonym' ),
                'add_new_item'        => __( 'Add new synonym', 'rrze-synonym' ),
                'edit_item'           => __( 'Edit synonym', 'rrze-synonym' ),
                'all_items'           => __( 'All synonyms', 'rrze-synonym' ),
                'search_items'        => __( 'Search synonym', 'rrze-synonym' ),
        );
        $rewrite = array(
                'slug'                => 'synonym',
                'with_front'          => true,
                'pages'               => true,
                'feeds'               => true,
        );
        $args = array(
                'label'               => __( 'synonym', 'rrze-synonym' ),
                'description'         => __( 'synonym informations', 'rrze-synonym' ),
                'labels'              => $labels,
                'supports'            => array( 'title' ),
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => true,
                'menu_icon'		  => 'dashicons-translation',
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => true,
                'publicly_queryable'  => true,
                'query_var'           => 'synonym',
                'rewrite'             => $rewrite,
                'show_in_rest'        => true,
                'rest_base'           => 'synonym',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
        );
        register_post_type( 'synonym', $args );
    }

    // public function registerFaqTaxonomy() {
    //     $tax = [
    //         [ 
    //             'name' => 'synonym_category',
    //             'label' => __('Category', 'rrze-synonym'),
    //             'slug' => 'category',
    //             'rest_base' => 'synonym_category',
    //             'hierarchical' => TRUE,
    //             'labels' => array(
    //                 'singular_name' => __('Category', 'rrze-synonym'),
    //                 'add_new' => __('Add new category', 'rrze-synonym'),
    //                 'add_new_item' => __('Add new category', 'rrze-synonym'),
    //                 'new_item' => __('New category', 'rrze-synonym'),
    //                 'view_item' => __('Show category', 'rrze-synonym'),
    //                 'view_items' => __('Show categories', 'rrze-synonym'),
    //                 'search_items' => __('Search categories', 'rrze-synonym'),
    //                 'not_found' => __('No category found', 'rrze-synonym'),
    //                 'all_items' => __('All categories', 'rrze-synonym'),
    //                 'separate_items_with_commas' => __('Separate categories with commas', 'rrze-synonym'),
    //                 'choose_from_most_used' => __('Choose from the most used categories', 'rrze-synonym'),
    //                 'edit_item' => __('Edit category', 'rrze-synonym'),
    //                 'update_item' => __('Update category', 'rrze-synonym')
    //             )
    //         ],
    //         [ 
    //             'name' => 'synonym_tag',
    //             'label' => __('Tags', 'rrze-synonym'),
    //             'slug' => 'tag',
    //             'rest_base' => 'synonym_tag',
    //             'hierarchical' => FALSE,
    //             'labels' => array(
    //                 'singular_name' => __('Tag', 'rrze-synonym'),
    //                 'add_new' => __('Add new tag', 'rrze-synonym'),
    //                 'add_new_item' => __('Add new tag', 'rrze-synonym'),
    //                 'new_item' => __('New tag', 'rrze-synonym'),
    //                 'view_item' => __('Show tag', 'rrze-synonym'),
    //                 'view_items' => __('Show tags', 'rrze-synonym'),
    //                 'search_items' => __('Search tags', 'rrze-synonym'),
    //                 'not_found' => __('No tag found', 'rrze-synonym'),
    //                 'all_items' => __('All tags', 'rrze-synonym'),
    //                 'separate_items_with_commas' => __('Separate tags with commas', 'rrze-synonym'),
    //                 'choose_from_most_used' => __('Choose from the most used tags', 'rrze-synonym'),
    //                 'edit_item' => __('Edit tag', 'rrze-synonym'),
    //                 'update_item' => __('Update tag', 'rrze-synonym')
    //             )
    //         ],
    //     ];
            
    //     foreach ($tax as $t){
    //         $ret = register_taxonomy(
    //             $t['name'],  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
    //             'synonym',   		 //post type name
    //             array(
    //                 'hierarchical'	=> $t['hierarchical'],
    //                 'label' 		=> $t['label'], //Display name
    //                 'labels'        => $t['labels'],
    //                 'show_ui'       => TRUE,
    //                 'show_admin_column' => TRUE,
    //                 'query_var' 	=> TRUE,
    //                 'rewrite'		=> array(
    //                        'slug'	    => $t['slug'], // This controls the base slug that will display before each term
    //                        'with_front'	    => TRUE // Don't display the category base before
    //                 ),
    //                 'show_in_rest'       => TRUE,
    //                 'rest_base'          => $t['rest_base'],
    //                 'rest_controller_class' => 'WP_REST_Terms_Controller'
    //             )
    //         );
    //         register_term_meta(
    //             $t['name'], 
    //             'source', 
    //             array(
    //                 'query_var' 	=> TRUE,
    //                 'type' => 'string',
    //                 'single' => TRUE,
    //                 'show_in_rest' => TRUE,
    //                 'rest_base'          => 'source',
    //                 'rest_controller_class' => 'WP_REST_Terms_Controller'
    //         ));
    //         register_term_meta(
    //             $t['name'], 
    //             'lang', 
    //             array(
    //                 'query_var' 	=> TRUE,
    //                 'type' => 'string',
    //                 'single' => TRUE,
    //                 'show_in_rest' => TRUE,
    //                 'rest_base'          => 'lang',
    //                 'rest_controller_class' => 'WP_REST_Terms_Controller'
    //         ));
    //     }
    // }
       
    public function setPostMeta( $postID ){
        add_post_meta( $postID, 'source', 'website', TRUE );
        add_post_meta( $postID, 'lang', $this->lang, TRUE );
        add_post_meta( $postID, 'remoteID', $postID, TRUE );
        $remoteChanged = get_post_timestamp( $postID, 'modified' );
        add_post_meta( $postID, 'remoteChanged', $remoteChanged, TRUE );
    }
    
    // public function setTermMeta( $termID ){
    //     add_term_meta( $termID, 'source', 'website', TRUE );
    //     add_term_meta( $termID, 'lang', $this->lang, TRUE );
    // }
    

}
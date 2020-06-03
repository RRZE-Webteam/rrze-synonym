<?php

namespace RRZE\Synonym;

defined( 'ABSPATH' ) || exit;

// use function RRZE\Synonym\API\getDomains;
use RRZE\Synonym\API;


/**
 * Layout settings for "synonym"
 */
class Layout {

    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'addSynonymMetaboxes'] );

        add_filter( 'pre_get_posts', [$this, 'makeSortable'] );

        // show content in box if not editable ( = source is not "website" )
        add_action( 'admin_menu', [$this, 'toggleEditor'] );
        // Table "All synonym"
        add_filter( 'manage_synonym_posts_columns', [$this, 'addColumns'] );        
        add_action( 'manage_synonym_posts_custom_column', [$this, 'getColumnsValues'], 10, 2 );
        add_filter( 'manage_edit-synonym_sortable_columns', [$this, 'addSortableColumns'] );

        // save longform
        add_action( 'save_post_synonym', [$this, 'saveLongform'] );        
    }


    public function makeSortable( $wp_query ) {
        if ( is_admin() ) {    
            $post_type = $wp_query->query['post_type'];    
            if ( $post_type == 'synonym') {
                if( ! isset($wp_query->query['orderby'])) {
                    $wp_query->set('orderby', 'title');
                    $wp_query->set('order', 'ASC');
                }
            }
        }
    }

    public function fillContentBox( $post ) {
        $mycontent = get_post_meta( $post->ID, 'longform', TRUE );
        echo '<h1>' . $post->post_title . '</h1><br>' . $mycontent;
    }

    public function fillShortcodeBox( $post ) { 
        $ret = '';
        if ( $post->ID > 0 ) {
            $ret .= '<p>[synonym id="' . $post->ID . '"]</p>';
            if ( $post->post_name ){
                $ret .= '<p>[synonym slug="' . $post->post_name . '"]</p>';
            }
            $ret .= '<p>[fau_abbr id="' . $post->ID . '"]</p>';
            if ( $post->post_name ){
                $ret .= '<p>[fau_abbr slug="' . $post->post_name . '"]</p>';
            }
        }
        echo $ret;    
    }

    public function addSynonymMetaboxes(){
        add_meta_box(
            'longformbox', // id, used as the html id att
            __( 'Full form', 'rrze-faq'), // meta box title 
            [$this, 'longformBoxCallback'], // callback function, spits out the content
            'synonym', // post type or page. This adds to posts only
            'normal',
            'high'
        ); 
        add_meta_box(
            'shortcode_box', // id, used as the html id att
            __( 'Integration in pages and posts', 'rrze-synonym'), // meta box title
            [$this, 'fillShortcodeBox'], // callback function, spits out the content
            'synonym', // post type or page. This adds to posts only
            'normal' // context, where on the screen
        );

    }

    public function toggleEditor(){
        $post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : ( isset ( $_POST['post_ID'] ) ? $_POST['post_ID'] : 0 ) ) ;
        if ( $post_id ){            
            if ( get_post_type( $post_id ) == 'synonym' ) {
                $source = get_post_meta( $post_id, "source", TRUE );
                if ( $source ){
                    $position = 'normal';
                    if ( $source != 'website' ){
                        $api = new API();
                        $domains = $api->getDomains();
                        $source = get_post_meta( $post_id, "source", TRUE );
                        $remoteID = get_post_meta( $post_id, "remoteID", TRUE );
                        $link = $domains[$source] . 'wp-admin/post.php?post=' . $remoteID . '&action=edit';
                        remove_post_type_support( 'synonym', 'title' );
                        remove_meta_box( 'submitdiv', 'synonym', 'side' );            
                        add_meta_box(
                            'read_only_content_box', // id, used as the html id att
                            __( 'This synonym cannot be edited because it is synchronized', 'rrze-synonym') . '. <a href="' . $link . '" target="_blank">' . __('You can edit it at the source', 'rrze-synonym') . '</a>',
                            [$this, 'fillContentBox'], // callback function, spits out the content
                            'synonym', // post type or page. This adds to posts only
                            'normal', // context, where on the screen
                            'high' // priority, where should this go in the context
                        );
                        $position = 'side';    
                    }
                }
            }    
        }
    }

    public function addColumns( $columns ) {
        $columns['source'] = __( 'Source', 'rrze-synonym' );
        $columns['id'] = __( 'ID', 'rrze-synonym' );
        return $columns;
    }

    public function addSortableColumns( $columns ) {
        $columns['source'] = __( 'Source', 'rrze-synonym' );
        $columns['id'] = __( 'ID', 'rrze-synonym' );
        return $columns;
    }

    public function longformBoxCallback( $meta_id ) {
        $source = get_post_meta( $meta_id->ID, 'source', TRUE );
        $longform = get_post_meta( $meta_id->ID, 'longform', TRUE );

        if ( $source == '' || $source == 'website'  ){
            $output = '<textarea rows="3" cols="60" name="longform" id="longform" class="longform">'. esc_attr($longform) .'</textarea>';
            $output .= '<p class="description">' . __( 'Enter here the long, written form of the synonym. This text will later replace the shortcode. Attention: Breaks or HTML statements are not accepted.', 'rrze-faq' ) . '</p>';
        } else {
            $output = $longform;
        }
        echo $output;
    }

    public function getColumnsValues( $column_name, $post_id ) {
        if( $column_name == 'id' ) {
            echo $post_id;
        }
        if( $column_name == 'source' ) {
            $source = get_post_meta( $post_id, 'source', true );
            echo $source;
        }
    }

    public function saveLongform( $post_id ){
        if ( ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['longform'] ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ){
            return $post_id;
        }
        update_post_meta( $post_id, 'longform', sanitize_text_field( $_POST['longform'] ) );        
    }


}

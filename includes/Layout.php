<?php

namespace RRZE\Synonym;

defined( 'ABSPATH' ) || exit;

// use function RRZE\Synonym\API\getDomains;
use RRZE\Synonym\API;

define( 'DEFAULTLANGCODES', array(
    "de" => __('German','rrze-synonym'),
    "en" => __('English','rrze-synonym'),
    "es" => __('Spanish','rrze-synonym'),
    "fr" => __('French','rrze-synonym'),
    "zh" => __('Chinese','rrze-synonym'),
    "ru" => __('Russian','rrze-synonym')
    ));

/**
 * Layout settings for "synonym"
 */
class Layout {

    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'addSynonymMetaboxes'], 10 );

        add_filter( 'pre_get_posts', [$this, 'makeSortable'] );

        // show content in box if not editable ( = source is not "website" )
        add_action( 'admin_menu', [$this, 'toggleEditor'], 11 );
        // Table "All synonym"
        add_filter( 'manage_synonym_posts_columns', [$this, 'addColumns'] );        
        add_action( 'manage_synonym_posts_custom_column', [$this, 'getColumnsValues'], 10, 2 );
        add_filter( 'manage_edit-synonym_sortable_columns', [$this, 'addSortableColumns'] );

        // save longform and titleLang
        add_action( 'save_post_synonym', [$this, 'savePostMeta'] );        
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
        $fields = $this->getPostMetas( $post->ID );
        $output = '';

        $output .= '<h1>' . $post->post_title . '</h1><br>';
        $output .= '<strong>' . __( 'Full form', 'rrze-faq') . ':</strong>';
        $output .= '<p>' . $fields['longform'] . '</p>';
        $output .= '<p><i>' . __( 'Pronunciation', 'rrze-synonym' ) . ': ' . DEFAULTLANGCODES[$fields['titleLang']] . '</i></p>';

        echo $output;
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
        $post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : ( isset ( $_POST['post_ID'] ) ? $_POST['post_ID'] : 0 ) ) ;
        $source = get_post_meta( $post_id, "source", TRUE );
        if ( ( $source == '' ) || ( $source == 'website' ) ){
            add_meta_box(
                'postmetabox',
                __( 'Full form', 'rrze-faq'),
                [$this, 'postmetaCallback'],
                'synonym',
                'normal',
                'high'
            ); 
        }
        add_meta_box(
            'shortcode_box',
            __( 'Integration in pages and posts', 'rrze-synonym'),
            [$this, 'fillShortcodeBox'],
            'synonym',
            'normal'
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
                        // remove_meta_box( 'postmetabox', 'synonym', 'normal' );
                        add_meta_box(
                            'read_only_content_box',
                            __( 'This synonym cannot be edited because it is synchronized', 'rrze-synonym') . '. <a href="' . $link . '" target="_blank">' . __('You can edit it at the source', 'rrze-synonym') . '</a>',
                            [$this, 'fillContentBox'],
                            'synonym',
                            'normal',
                            'high'
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

    public function getPostMetas( $postID ){
        return array( 
            'source' => get_post_meta( $postID, 'source', TRUE ),
            'longform' => get_post_meta( $postID, 'longform', TRUE ),
            'titleLang' => get_post_meta( $postID, 'titleLang', TRUE )
        );
    }

    public function postmetaCallback( $meta_id ) {
        $fields = $this->getPostMetas( $meta_id->ID );

        // longform
        $output = '<textarea rows="3" cols="60" name="longform" id="longform" class="longform">'. esc_attr( $fields['longform'] ) .'</textarea>';
        $output .= '<p class="description">' . __( 'Enter here the long, written form of the synonym. This text will later replace the shortcode. Attention: Breaks or HTML statements are not accepted.', 'rrze-faq' ) . '</p>';// Geben Sie hier die lange, ausgeschriebene Form des Synonyms ein. Mit diesem Text wird dann im späteren Gebrauch der verwendete Shortcode ersetzt. Achtung: Umbrüche oder HTML-Anweisungen werden nicht übernommen.

        // langTitle
        $output .= '<br><select class="" id="titleLang" name="titleLang">';
        foreach( DEFAULTLANGCODES as $lang => $desc ){
            // $locale = substr( get_locale(), 0, 5);
        
            $selected = ( $fields['titleLang'] == $lang ? ' selected' : '' );
            $output .= '<option value="' . $lang . '"' . $selected . '>' . $desc . '</option>';
        }
        $output .= '</select>';
        $output .= '<p class="description">' . __( 'Choose the language in which the long form is pronounced.', 'rrze-synonym' ) . '</p>'; // Wählen Sie die Sprache, in der die Langform ausgesprochen wird. 

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

    public function savePostMeta( $post_id ){
        if ( ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['longform'] ) || ! isset( $_POST['titleLang'] ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ){
            return $post_id;
        }

        update_post_meta( $post_id, 'longform', sanitize_text_field( $_POST['longform'] ) );        
        update_post_meta( $post_id, 'titleLang', sanitize_text_field( $_POST['titleLang'] ) );        
    }


}

<?php

namespace RRZE\Synonym;

defined('ABSPATH') || exit;
use function RRZE\Synonym\Config\getShortcodeSettings;
use RRZE\Synonym\API;


/**
 * Shortcode
 */
class Shortcode {

    /**
     * Settings-Objekt
     * @var object
     */
    private $settings = '';

    public function __construct() {
        $this->settings = getShortcodeSettings();
        add_action( 'admin_enqueue_scripts', [$this, 'enqueueGutenberg'] );
        add_action( 'init',  [$this, 'initGutenberg'] );
        add_shortcode( 'synonym', [$this, 'shortcodeOutput'] ); // liefert Langform (custom field) entweder nach slug oder id
        add_shortcode( 'fau_abbr', [$this, 'shortcodeOutput'] ); // liefert <abbr title=" synonym (custom field) " lang=" titleLang (custom field)" > title </abbr> nach slug oder id
    }


    private function getPostBySlug( $slug ){
        $ret = get_posts( [
            'name'           => $slug,
            'post_type'      => 'synonym',
            'post_status'    => 'publish',
            'posts_per_page' => 1
            ] );

        return ( isset( $ret[0] ) ? $ret[0] : FALSE );
    }

    private function getPostsByCPT( $cpt ){
        $postQuery = array('post_type' => $cpt, 'post_status' => 'publish', 'numberposts' => -1, 'suppress_filters' => false);
        return get_posts( $postQuery );
    }

    public function shortcodeOutput( $atts, $content = "", $shortcode_tag = "" ) {
        $myPosts = FALSE;

        // in case shortcode is used with slug although Gutenberg is enabled, we need to store the given value because slug has been unset() in fillGutenbergOptions() for usability reasons
        if (isset($atts['slug'])){
            $slug = $atts['slug'];
        }

        // merge given attributes with default ones
        $atts_default = array();
        foreach( $this->settings as $k => $v ){
            if ( $k != 'block' ){
                $atts_default[$k] = $v['default'];
            }
        }
        $atts = shortcode_atts( $atts_default, $atts );        

        extract( $atts );

        if ( isset( $slug ) && $slug ){
            $myPosts = array( $this->getPostBySlug( $slug ) );
        } elseif( $id ) {
            $myPosts = array( get_post( $id ) );
        } else {
            // show all
            $myPosts = $this->getPostsByCPT( 'synonym' );
        }

        if ($gutenberg_shortcode_type){
            // Gutenberg
            $shortcode_tag = $gutenberg_shortcode_type;
        }

        $output = '';
        if ( $myPosts ){
            switch( $shortcode_tag ){
                case 'fau_abbr'  :
                    if (count($myPosts) == 1 ){
                        $post = $myPosts[0];
                        $output = '<abbr title="' . get_post_meta( $post->ID, 'synonym', TRUE ) . '" lang="' . get_post_meta( $post->ID, 'titleLang', TRUE ) . '">'. html_entity_decode( $post->post_title ) . '</abbr>';
                    }else{
                        foreach( $myPosts as $post ){
                            $output .= '<div class="fau_abbr">';
                            $output .= '<abbr title="' . get_post_meta( $post->ID, 'synonym', TRUE ) . '" lang="' . get_post_meta( $post->ID, 'titleLang', TRUE ) . '">'. html_entity_decode( $post->post_title ) . '</abbr>';
                            $output .= '</div>';
                        }
                    }
                break;
                case 'synonym'  :
                    if (count($myPosts) == 1 ){
                        $post = $myPosts[0];
                        $output = get_post_meta( $post->ID, 'synonym', TRUE );
                    }else{
                        foreach( $myPosts as $post ){
                            $output .= '<div class="synonym">';
                            $output .= '<h2 class="small">' . html_entity_decode( $post->post_title ) . '</h2>';
                            $output .= '<p>' . get_post_meta( $post->ID, 'synonym', TRUE ) . '</p>';
                            $output .= '<div>';
                        }
                    }
                break;
            }
            if ( count( $myPosts ) > 1 ){
                $output = '<div class="synonym-outer">' . $output . '</div>';
            }
        }
        return $output;
    }

    public function isGutenberg(){
        $postID = get_the_ID();
        if ($postID && !use_block_editor_for_post($postID)){
            return false;
        }

        return true;        
    }

    public function fillGutenbergOptions() {
        // we don't need attribute "slug" in Gutenberg and can use "id" solely
        unset( $this->settings['slug'] );

        // fill select id ( = synonym )
        $synonyms = get_posts( array(
            'nopaging' => true,
            'post_type' => 'synonym',
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        $this->settings['id']['field_type'] = 'select';
        $this->settings['id']['type'] = 'number';
        $this->settings['id']['values'][] = ['id' => 0, 'val' => __( '-- all --', 'rrze-synonym' )];
        $this->settings['id']['default'] = 0;
        foreach ( $synonyms as $synonym){
            $this->settings['id']['values'][] = [
                'id' => $synonym->ID,
                'val' => str_replace( "'", "", str_replace( '"', "", $synonym->post_title ) )
            ];
        }

        return $this->settings;
    }

    public function initGutenberg() {
        if (! $this->isGutenberg()){
            return;
        }

        // get prefills for dropdowns
        $this->settings = $this->fillGutenbergOptions();

        // register js-script to inject php config to call gutenberg lib
        $editor_script = $this->settings['block']['blockname'] . '-block';        
        $js = '../assets/js/' . $editor_script . '.js';

        wp_register_script(
            $editor_script,
            plugins_url( $js, __FILE__ ),
            array(
                'RRZE-Gutenberg',
            ),
            NULL
        );
        wp_localize_script( $editor_script, $this->settings['block']['blockname'] . 'Config', $this->settings );

        // register block
        register_block_type( $this->settings['block']['blocktype'], array(
            'editor_script' => $editor_script,
            'render_callback' => [$this, 'shortcodeOutput'],
            'attributes' => $this->settings
            ) 
        );
    }

    public function enqueueGutenberg(){
        if (! $this->isGutenberg()){
            return;
        }

        // include gutenberg lib
        wp_enqueue_script(
            'RRZE-Gutenberg',
            plugins_url( '../assets/js/gutenberg.js', __FILE__ ),
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor'
            ),
            NULL
        );
    }
}

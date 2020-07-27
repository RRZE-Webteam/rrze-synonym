<?php

namespace RRZE\Synonym;

defined('ABSPATH') || exit;
use function RRZE\Synonym\Config\getShortcodeSettings;
use RRZE\Synonym\API;



$settings;

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
        add_action( 'init',  [$this, 'initGutenberg'] );
        add_shortcode( 'synonym', [ $this, 'shortcodeOutput' ] ); // liefert Langform (custom field) entweder nach slug oder id
        add_shortcode( 'fau_abbr', [ $this, 'shortcodeOutput' ] ); // liefert <abbr title=" synonym (custom field) " lang=" titleLang (custom field)" > title </abbr> nach slug oder id
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

        if ( $shortcode_tag == '' ){
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


    public function fillGutenbergOptions() {
        $options = get_option( 'rrze-synonym' );

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
        $this->settings['id']['values'][0] = __( '-- all --', 'rrze-synonym' );
        $this->settings['id']['default'] = 0;
        foreach ( $synonyms as $synonym){
            $this->settings['id']['values'][$synonym->ID] = str_replace( "'", "", str_replace( '"', "", $synonym->post_title ) );
        }

        return $this->settings;
    }

    public function initGutenberg() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;        
        }

        // check if RRZE-Settings if classic editor is enabled
        $rrze_settings = (array) get_option( 'rrze_settings' );
        if ( isset( $rrze_settings['writing'] ) ) {
            $rrze_settings = (array) $rrze_settings['writing'];
            if ( isset( $rrze_settings['enable_classic_editor'] ) && $rrze_settings['enable_classic_editor'] ) {
                return;
            }
        }

        $this->settings = $this->fillGutenbergOptions();

        $js = '../assets/js/gutenberg.min.js';
        $editor_script = $this->settings['block']['blockname'] . '-blockJS';

        wp_register_script(
            $editor_script,
            plugins_url( $js, __FILE__ ),
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor'
            ),
            filemtime( dirname( __FILE__ ) . '/' . $js )
        );
        wp_localize_script( $editor_script, 'blockname', $this->settings['block']['blockname'] );

        $css = '../assets/css/gutenberg.min.css';
        $editor_style = 'gutenberg-css';
        wp_register_style( $editor_style, plugins_url( $css, __FILE__ ) );
        register_block_type( $this->settings['block']['blocktype'], array(
            'editor_script' => $editor_script,
            'style' => $editor_style,
            'render_callback' => [$this, 'shortcodeOutput'],
            'attributes' => $this->settings
            ) 
        );

        wp_localize_script( $editor_script, $this->settings['block']['blockname'] . 'Config', $this->settings );
    }
}

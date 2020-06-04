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
        add_shortcode( 'fau_abbr', [ $this, 'shortcodeOutput' ] ); // liefert <abbr title=" Langform (custom field) " lang=" " > title </abbr> nach slug oder id
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

    private function getLongform( $postID ){
        return get_post_meta( $postID, 'longform', TRUE );
    }

    private function getTitleLang( $postID ){
        return get_post_meta( $postID, 'titleLang', TRUE );
    }

    public function shortcodeOutput( $atts, $content = "", $shortcode_tag = "" ) {
        $output = '';
        $thisPost = FALSE;
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
            $thisPost = $this->getPostBySlug( $slug );
        } elseif( $id ) {
            $thisPost = get_post( $id );
        }

        if ( $shortcode_tag == '' ){
            // Gutenberg
            $shortcode_tag = $gutenberg_shortcode_tag;
        }

        if ( $thisPost ){
            switch( $shortcode_tag ){
                case 'fau_abbr'  :
                    $output = '<abbr title="' . $this->getLongform( $thisPost->ID ) . '" lang="' . $this->getTitleLang( $thisPost->ID ) . '">'. $thisPost->post_title . '</abbr>';
                break;
                case 'synonym'  :
                    $output = $this->getLongform( $thisPost->ID );
                break;
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
            'posts_per_page'  => -1,
            'post_type' => 'synonym',
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        $this->settings['id']['field_type'] = 'select';
        $this->settings['id']['type'] = 'number';
        $defaultSet = FALSE;
        foreach ( $synonyms as $synonym){
            if ( !$defaultSet ){
                $this->settings['id']['default'] = $synonym->ID;
                $defaultSet = TRUE;
            }
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

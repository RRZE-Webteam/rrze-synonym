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
        // add_action( 'init',  [$this, 'initGutenberg'] );
        add_shortcode( 'synonym', [ $this, 'shortcodeOutput' ], 10, 2 );
        add_shortcode( 'static_content', [ $this, 'shortcodeOutput' ], 10, 2 ); // alternative shortcode
        add_shortcode( 'fau_abbr', [ $this, 'shortcodeOutput' ], 10, 2 ); // alternative shortcode
    }


    private function get_posts_by_urlslug( $the_slug ){
        $args = array(
            'name'           => $the_slug,
            'post_type'      => 'synonym',
            'post_status'    => 'publish',
            'posts_per_page' => 1
        );
        return get_posts( $args );
    }
     


    // [static_content slug="" id=""] : Ausgabe des Synonyms ohne Titel entweder nach slug oder id
    // [synonym slug="" id=""] : liefert die Langform entweder nach slug oder id
    // [fau_abbr slug="fau" id=""] : liefert Langform mit den abbr-Tags nach slug oder id     

    public function shortcodeOutput( $atts, $content = "", $shortcode_tag ) {
        $content = '';
        if ( !$atts ){
            $atts = array();
        }

        $atts = shortcode_atts( $atts_default, $atts );

        extract( $atts );

        switch( $shortcode_tag ){
            case 'static_content'  :
                if ( $slug ){
                    $thisPost = get_posts_by_urlslug( $slug );
                } elseif( $id ) {
                    $thisPost = get_post( $id );
                }
            break;
            case 'synonym'  :
            break;
            case 'fau_abbr'  :
            break;
        }

        return $content;
    }


    public function fillGutenbergOptions() {
        $options = get_option( 'rrze-synonym' );

        // fill selects "category" and "tag"
        $fields = array( 'category', 'tag' );
        foreach ( $fields as $field ) {
            // set new params for gutenberg / the old ones are used for shortcode in classic editor
            $this->settings[$field]['values'] = array();
            $this->settings[$field]['field_type'] = 'multi_select';
            $this->settings[$field]['default'] = array('');
            $this->settings[$field]['type'] = 'array';
            $this->settings[$field]['items'] = array( 'type' => 'string' );
            $this->settings[$field]['values'][0] = __( '-- all --', 'rrze-synonym' );

            // get categories and tags from this website
            $terms = get_terms([
                'taxonomy' => 'synonym_' . $field,
                'hide_empty' => TRUE,
                'orderby' => 'name',
                'order' => 'ASC'
                ]);


            foreach ( $terms as $term ){
                $this->settings[$field]['values'][$term->slug] = $term->name;
            }
        }

        // fill select id ( = synonym )
        $synonyms = get_posts( array(
            'posts_per_page'  => -1,
            'post_type' => 'synonym',
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        $this->settings['id']['field_type'] = 'multi_select';
        $this->settings['id']['default'] = array('');
        $this->settings['id']['type'] = 'array';
        $this->settings['id']['items'] = array( 'type' => 'number' );
        $this->settings['id']['values'][0] = __( '-- all --', 'rrze-synonym' );
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
            // 'attributes' => $this->fillGutenbergOptions()
            ) 
        );

        wp_localize_script( $editor_script, $this->settings['block']['blockname'] . 'Config', $this->settings );
        // wp_localize_script( $editor_script, $this->settings['block']['blockname'] . 'Config', $this->fillGutenbergOptions() );
    }
}

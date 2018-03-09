<?php

namespace RRZE\Synonym\Server;

defined('ABSPATH') || exit;

class Class_Build_Synomym_Shortcode {
    
    function __construct() {
        add_shortcode('synonym', array($this, 'fau_synonym'));
        add_option('flag', '0');
    }
    
    function fau_synonym($atts) {
        $this->fau_shortcode = shortcode_atts( array(
            "slug"      => '',
            "id"        => '',
            "domain"    => '',
            "rest"      => 0,
       ), $atts );

       return $this->output($this->fau_shortcode);
    }
    
    function output($atts) {
        global $post;
        
        $domainId = $atts['domain'];
       
        if((!empty($atts['id']) || !empty($atts['slug'])) && $atts['rest'] == 1 && $atts['domain'] > 0) {
            $url = $this->getRequestedDomain($domainId);
            if($url) {
                $return = $this->getSynonym($url, $atts['id'], $atts['slug']);
            } else {
                $return = '';
            }
        } elseif((empty($atts['id']) || empty($atts['slug'])) && $atts['rest'] == 1 && $atts['domain'] > 0) {
            $return = __( 'Enter the id or slug of the respective domain', 'rrze-synonym-server' ); 
        } elseif (isset($atts['id']) && intval($atts['id']) && $atts['id']>0) {
            // $post = get_posts(array('id' => $id, 'post_type' => 'synonym', 'post_status' => 'publish'));	
            $return = get_post_meta( $atts['id'], 'synonym', true );
        } else {
            $post = get_posts(array('name' => $atts['slug'], 'post_type' => 'synonym', 'post_status' => 'publish', 'numberposts' => 1));
            if ($post)  {
                   $id = $post[0]->ID;	
                   $return = get_post_meta( $id, 'synonym', true );
            }
        }
       return $return;
    }
    
    function getSynonym($url, $id, $slug) {
        
        $response = $this->testRequest($url, $id, $slug);
        if (empty($response)) {
            return 'Dieser Shortcode gibt keine Daten zurück!';
        } else {
            $list = json_decode($response, true);
            if($id) {  
                $return = $list['synonym']['synonym'][0];
            } else {
                $return = $list[0]['synonym']['synonym'][0];
            }
            return $return;
        }
    }
    
    function testRequest($url, $id, $slug) {
        
        $args = array(
            'sslverify'   => false,
        );
        
        if($id) {
            $content = wp_remote_get("https://{$url}/wp-json/wp/v2/synonym/{$id}", $args );
        } else {
            $content = wp_remote_get("https://{$url}/wp-json/wp/v2/synonym?slug={$slug}", $args );
        }
        
        $status_code = wp_remote_retrieve_response_code( $content );
        
        if (200 === $status_code) {
            $response = $content['body'];
            return $response;
        } else {
            return '';
        }
    }
    
    function getRequestedDomain($domainId) {
        $z = get_option('registerServer');
        if(!empty($z)) {
            $b = array_flip($z);
            if(in_array($domainId, $b)) {
                $s = array_search($domainId, $b);
                return $s;
            } else {
                return;
            }
        }
    }
}
<?php

namespace RRZE\Synonym;

defined( 'ABSPATH' ) || exit;

/**
 * REST API for "synonym"
 */
class RESTAPI {
    public function __construct() {
        add_action( 'rest_api_init', [$this, 'createPostMeta'] );
        // add_action( 'rest_api_init', [$this, 'createTaxDetails'] );
        // add_action( 'rest_api_init', [$this, 'createChildren'] );
        add_action( 'rest_api_init', [$this, 'addFilters'] );
    }

    public function getPostSource( $object ) {
        return get_post_meta( $object['id'], 'source', TRUE );
    }

    public function getPostLang( $object ) {
        return get_post_meta( $object['id'], 'lang', TRUE );
    }

    public function getPostRemoteID( $object ) {
        return get_post_meta( $object['id'], 'remoteID', TRUE );
    }

    public function getPostRemoteChanged( $object ) {
        return get_post_meta( $object['id'], 'remoteChanged', TRUE );
    }

    // make API deliver source and lang for synonyms
    public function createPostMeta() {
        register_rest_field( 'synonym', 'source', array(
            'get_callback'    => [$this, 'getPostSource'],
            'schema'          => null,
        ));
        register_rest_field( 'synonym', 'lang', array(
            'get_callback'    => [$this, 'getPostLang'],
            'schema'          => null,
        ));
        register_rest_field( 'synonym', 'remoteID', array(
            'get_callback'    => [$this, 'getPostRemoteID'],
            'schema'          => null,
        ));
        register_rest_field( 'synonym', 'remoteChanged', array(
            'get_callback'    => [$this, 'getPostRemoteChanged'],
            'schema'          => null,
        ));
    }
   
    public function addFilterParam( $args, $request ) {
        if ( empty( $request['filter'] ) || ! is_array( $request['filter'] ) ) {
                return $args;
        }
        global $wp;
        $filter = $request['filter'];

        $vars = apply_filters( 'query_vars', $wp->public_query_vars );
        foreach ( $vars as $var ) {
                if ( isset( $filter[ $var ] ) ) {
                        $args[ $var ] = $filter[ $var ];
                }
        }
        return $args;
    }
    
    public function addFilters() {
        add_filter( 'rest_synonym_query', [$this, 'addFilterParam'], 10, 2 );
    }

}

<?php

namespace RRZE\Synonym\Server;

function synonym_post_types_admin_order( $wp_query ) {
    if (is_admin()) {
        $post_type = $wp_query->query['post_type'];
        if ( $post_type == 'synonym') {
            if( ! isset($wp_query->query['orderby']))
            {
                    $wp_query->set('orderby', 'title');
                    $wp_query->set('order', 'ASC');
            }
        }
    }
}

add_filter('pre_get_posts', 'RRZE\Synonym\Server\synonym_post_types_admin_order');
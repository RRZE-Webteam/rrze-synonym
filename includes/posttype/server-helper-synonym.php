<?php

namespace RRZE\Synonym\Server;

function fau_get_synonym($id =0) {

    if (isset($id) && intval($id) && $id>0) {
        // $post = get_posts(array('id' => $id, 'post_type' => 'synonym', 'post_status' => 'publish'));	

        $title = get_the_title($id);
        $synonym = get_post_meta( $id, 'synonym', true );

        $return = '<div class="synonym">';
        $return .= '<h2 class="small">'.$title."</h2>\n";
        $return .= '<p>'.$synonym."</p>\n";
        $return .= "</div>\n";

    } else {
        $posts = get_posts(array('post_type' => 'synonym', 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC', 'numberposts' => -1));
         if ($posts)  {
             $return = '<table class="synonym">';
             foreach($posts as $post) {
                $synonym = get_post_meta( $post->ID, 'synonym', true );
                $return .= '<tr>';
                $return .= '<th>'.get_the_title($post->ID).'</th>';
                $return .= '<td>'.$synonym.'</td>';
                $return .= "</tr>\n";			
             }
            $return .= "</table>\n";



         }
    }
    return $return;
}

function fau_synonym_rte_add_buttons( $plugin_array ) {
    $plugin_array['synonymrteshortcodes'] = get_template_directory_uri().'/js/tinymce-synonym.js';
    return $plugin_array;
}

add_filter( 'mce_external_plugins','RRZE\Synonym\Server\fau_synonym_rte_add_buttons');
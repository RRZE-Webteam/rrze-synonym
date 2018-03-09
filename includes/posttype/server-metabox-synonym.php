<?php

namespace RRZE\Synonym\Server;

function fau_synonym_metabox() {
    add_meta_box(
        'fau_synonym_metabox',
        __( 'Eigenschaften', 'fau' ),
        'RRZE\Synonym\Server\fau_synonym_metabox_content',
        'synonym',
        'normal',
        'high'
    );
}

function fau_synonym_metabox_content( $object, $box ) { 
    
    global $defaultoptions;
    global $post;
	
    wp_nonce_field( basename( __FILE__ ), 'fau_synonym_metabox_content_nonce' ); 
    if ( !current_user_can( 'edit_post', $object->ID) )
	    // Oder sollten wir nach publish_pages  fragen? 
	    // oder nach der Rolle? vgl. http://docs.appthemes.com/tutorials/wordpress-check-user-role-function/ 
	return;
    
   
    $desc  = get_post_meta( $object->ID, 'synonym', true );		
    fau_form_textarea('fau_synonym', $desc, __('Written form','rrze-synonym-server'), 60, 3, __('Enter here the long, written form of the synonym. This text will later replace the shortcode. Attention: Breaks or HTML statements are not accepted.','rrze-synonym-server'));
	    
    if ($post->ID >0) {
	$helpuse = __('<p>Integration in pages and contributions via: </p>','rrze-synonym-server');
	$helpuse .= '<pre> [synonym id="'.$post->ID.'"] </pre>';
	if ($post->post_name) {
	    $helpuse .= __(' or','rrze-synonym-server') . ' <br> <pre> [synonym slug="'.$post->post_name.'"] </pre>';
	}
	echo $helpuse;
    }
    
    return;
}

add_action( 'add_meta_boxes', 'RRZE\Synonym\Server\fau_synonym_metabox' );

function fau_synonym_metabox_content_save( $post_id ) {
    global $options;
    if (  'synonym'!= get_post_type()  ) {
	return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;
    if ( !isset( $_POST['fau_synonym_metabox_content_nonce'] ) || !wp_verify_nonce( $_POST['fau_synonym_metabox_content_nonce'], basename( __FILE__ ) ) )
	    return $post_id;
    if ( !current_user_can( 'edit_post', $post_id ) )
    return;
    
    $newval = ( isset( $_POST['fau_synonym'] ) ? sanitize_text_field( $_POST['fau_synonym'] ) : 0 );
    $oldval =  get_post_meta( $post_id, 'synonym', true );
	
    if (!empty(trim($newval))) {
	if (isset($oldval)  && ($oldval != $newval)) {
	    update_post_meta( $post_id, 'synonym', $newval );
	} else {
	    add_post_meta( $post_id, 'synonym', $newval, true );
	}
    } elseif ($oldval) {
	delete_post_meta( $post_id, 'synonym', $oldval );	
    } 
    
	
}

add_action( 'save_post', 'RRZE\Synonym\Server\fau_synonym_metabox_content_save' );
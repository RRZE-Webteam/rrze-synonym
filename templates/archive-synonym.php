<?php
/* 
Template Name: CPT synonym Archive Template
*/

get_header();

while ( have_posts() ){
    the_post();
    the_title();
    echo '<br>';
    echo 'Ausgeschriebene Form : <strong>' . get_post_meta( get_the_ID(), 'synonym', TRUE ) . '</strong><br>';
    echo '---- <br>';
}

get_footer();


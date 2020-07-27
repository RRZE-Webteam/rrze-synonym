<?php
/* 
Template Name: CPT synonym Single Template
*/

get_header();

global $post;

echo '<strong>' . $post->post_title . '</strong><br>';
echo '<strong>' . get_post_meta( $post->ID, 'synonym', TRUE ) . '</strong><br>';
// echo 'In dieser Sprache wird der Titel ausgesprochen (nötig für z.B: < abbr title="Digital Subscriber Line" lang="en" >DSL< /abbr >) : <strong>' . DEFAULTLANGCODES[get_post_meta( $post->ID, 'titleLang', TRUE )] . '</strong><br>';

get_footer();
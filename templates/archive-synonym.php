<?php
/* 
Template Name: CPT synonym Archive Template
*/
use RRZE\Synonym\Layout;

$theme = wp_get_theme();
$isFauTheme = in_array($theme->Name, FAUTHEMES);

get_header();

if ($isFauTheme) {
    get_template_part('template-parts/hero', 'index'); ?>
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <main id="droppoint">
                        <h1 class="screen-reader-text"><?php echo __('Index','fau'); ?></h1>
<?php } else { ?>
    <div id="sidebar" class="sidebar">
        <?php get_sidebar(); ?>
    </div>
    <div id="primary" class="content-area">
		<main id="main" class="site-main">
<?php }

echo '<h2>'. __('Synonyms','rrze-synonym') . '</h2>';
if (have_posts()) {
    echo '<table class="synonym">';
    while ( have_posts() ){
        the_post();
        echo '<tr>';
        echo '<th scope="row">' . get_the_title() . '</th>' ;
        echo '<td>' . get_post_meta( $post->ID, 'synonym', TRUE ) . Layout::getPronunciation($post->ID) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

if ($isFauTheme) { ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
        </main>
    </div>
<?php }
get_footer();


<?php
/* 
Template Name: CPT synonym Archive Template
*/

$fau_themes = [
    'FAU-Einrichtungen',
    'FAU-Einrichtungen-BETA',
    'FAU-Medfak',
    'FAU-RWFak',
    'FAU-Philfak',
    'FAU-Techfak',
    'FAU-Natfak'
];
$theme = wp_get_theme();
$fau = in_array($theme->Name, $fau_themes) ? true : false;

get_header();

if ($fau) {
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

while ( have_posts() ){
    the_post();
    the_title();
    echo '<br>';
    echo '<strong>' . get_post_meta( get_the_ID(), 'synonym', TRUE ) . '</strong><br>';
    echo '---- <br>';
}

if ($fau) { ?>
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


<?php
/* 
Template Name: CPT synonym Single Template
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

global $post;

if ($fau) {
    get_template_part('template-parts/hero', 'small'); ?>
    <main id="content">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <main id="droppoint">
                        <h1 class="screen-reader-text"><?php echo $post->post_title; ?></h1>
<?php } else { ?>
    <div id="sidebar" class="sidebar">
        <?php get_sidebar(); ?>
    </div>
    <div id="primary" class="content-area">
		<main id="main" class="site-main">
<?php
}
echo '<div id="post-'. get_the_ID() . '" class="' . implode(' ', get_post_class()) .'">';
echo '<strong>' . $post->post_title . '</strong><br>';
echo '<strong>' . get_post_meta( $post->ID, 'synonym', TRUE ) . '</strong><br>';
// echo 'In dieser Sprache wird der Titel ausgesprochen (nötig für z.B: < abbr title="Digital Subscriber Line" lang="en" >DSL< /abbr >) : <strong>' . DEFAULTLANGCODES[get_post_meta( $post->ID, 'titleLang', TRUE )] . '</strong><br>';
echo '</div>';

if ($fau) { ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
    <?php get_template_part('template-parts/footer', 'social');
} else { ?>
        </main>
    </div>
<?php }

get_footer();
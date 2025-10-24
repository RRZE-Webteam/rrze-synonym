<?php
/* 
Template Name: CPT synonym Single Template
*/
use RRZE\Synonym\Layout;

$bFAUTheme = Layout::isFAUTheme();

get_header();

global $post;

if ($bFAUTheme) {
    $currentTheme = wp_get_theme();		
    $vers = $currentTheme->get( 'Version' );
      if (version_compare($vers, "2.3", '<')) {      
        get_template_part('template-parts/hero', 'small'); 
      }
?>
    <main id="content">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <main id="droppoint">
                        <h1 class="screen-reader-text"><?php echo $post->post_title; ?></h1>
<?php } else { ?>
   
		<main id="main" class="rrze-synonym">
            
<?php }

echo '<div id="post-' . get_the_ID() . '" class="' . implode(' ', get_post_class()) .'">';
echo '<h1>' . $post->post_title . '</h1>';
echo '<p>';
echo get_post_meta( $post->ID, 'synonym', TRUE ) . Layout::getPronunciation($post->ID);
echo '</p>';
echo '</div>';

if ($bFAUTheme) { ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
    <?php get_template_part('template-parts/footer', 'social');
} else { ?>
        </main>
<?php }

get_footer();
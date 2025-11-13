<?php
/* 
Template Name: CPT synonym Archive Template
*/
use RRZE\Synonym\Layout;

$bFAUTheme = Layout::isFAUTheme();

get_header();

if ($bFAUTheme) {
    $currentTheme = wp_get_theme();		
    $vers = $currentTheme->get( 'Version' );
      if (version_compare($vers, "2.3", '<')) {      
        get_template_part('template-parts/hero', 'index'); 
      }
    ?>
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <main id="droppoint">
                        <h1 class="screen-reader-text"><?php echo __('Index','fau'); ?></h1>
<?php } else { ?>
		<main id="main" class="rrze-synonym">
<?php }

echo '<h1>'. __('Synonyms','rrze-synonym') . '</h1>';
if (have_posts()) {
    
   
    
    echo '<table>';
    while ( have_posts() ){
        
            $langattr = '';
            $lang = get_post_meta($post->ID, 'titleLang', TRUE);
            if (!empty($lang)) {
                $langattr = ' lang="'.$lang.'"';
            }

    
        
        the_post();
        echo '<tr>';
        echo '<th scope="row"'.$langattr.'>' . get_the_title() . '</th>' ;
        echo '<td'.$langattr.'>' . get_post_meta( $post->ID, 'synonym', TRUE ) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

if ($bFAUTheme) { ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
        </main>

<?php }
get_footer();


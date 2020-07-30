<?php

/*
Plugin Name:     RRZE Synonym
Plugin URI:      https://gitlab.rrze.fau.de/rrze-webteam/rrze-synonym
Description:     Plugin, um Synonyme zu erstellen, von Websites aus dem FAU-Netzwerk zu synchronisieren und mittels Shortcodes ([synonym ...] und [fau_abbr ...]) oder als Gutenberg Editor Block (Synonym oder Abkürzung = Dropdown) einzubinden.
Version:         2.9.4
Author:          RRZE Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License v2
License URI:     http://www.gnu.org/licenses/gpl-2.0.html
Domain Path:     /languages
Text Domain:     rrze-synonym
*/

namespace RRZE\Synonym;


defined('ABSPATH') || exit;

require_once 'config/config.php';
use RRZE\Synonym\Main;

$s = array(
    '/^((http|https):\/\/)?(www.)+/i',
    '/\//',
    '/[^A-Za-z0-9\-]/'
);
$r = array(
    '',
    '-',
    '-'
);

define( 'SYNONYMLOGFILE', plugin_dir_path( __FILE__) . 'rrze-synonym-' . preg_replace( $s, $r,  get_bloginfo( 'url' ) ) . '.log' );


const RRZE_PHP_VERSION = '7.3';
const RRZE_WP_VERSION = '5.2';
const RRZE_PLUGIN_FILE = __FILE__;



// Automatische Laden von Klassen.
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Registriert die Plugin-Funktion, die bei Aktivierung des Plugins ausgeführt werden soll.
register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
// Registriert die Plugin-Funktion, die ausgeführt werden soll, wenn das Plugin deaktiviert wird.
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');
// Wird aufgerufen, sobald alle aktivierten Plugins geladen wurden.
add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * Einbindung der Sprachdateien.
 */
function load_textdomain(){
    load_plugin_textdomain('rrze-synonym', false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

/**
 * Überprüft die minimal erforderliche PHP- u. WP-Version.
 */
function system_requirements(){
    $error = '';
    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        /* Übersetzer: 1: aktuelle PHP-Version, 2: erforderliche PHP-Version */
        $error = sprintf(__('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-synonym'), PHP_VERSION, RRZE_PHP_VERSION);
    } elseif (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        /* Übersetzer: 1: aktuelle WP-Version, 2: erforderliche WP-Version */
        $error = sprintf(__('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-synonym'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }
    return $error;
}


function addMetadata(){
    $postIds = get_posts(
        ['post_type' => 'synonym', 
        'nopaging' => true, 
        'fields' => 'ids'
        ]
    );

    $lang = substr( get_locale(), 0, 2); // Website-Sprache als default titelLang

    foreach( $postIds as $postID ){
        if (metadata_exists('post', $postID, 'source') === false){
            update_post_meta( $postID, 'source', 'website' );        
            update_post_meta( $postID, 'remoteID', $postID );
            // post_meta 'synonym' existiert bereits        
            update_post_meta( $postID, 'titleLang', $lang );
            $remoteChanged = get_post_timestamp( $postID, 'modified' );
            update_post_meta( $postID, 'remoteChanged', $remoteChanged );
        }
    }    
}

/**
 * Wird durchgeführt, nachdem das Plugin aktiviert wurde.
 */
function activation() {
    // Sprachdateien werden eingebunden.
    load_textdomain();

    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
    if ($error = system_requirements()) {
        deactivate_plugins(plugin_basename(__FILE__), false, true);
        wp_die($error);
    }

    // Ab hier können die Funktionen hinzugefügt werden,
    // die bei der Aktivierung des Plugins aufgerufen werden müssen.
    // Bspw. wp_schedule_event, flush_rewrite_rules, etc.

    // Einmaliger Aufruf: vom alten Plugin oder Fallback vom Theme gespeicherte Daten zum CPT "synonym" um Metadaten ergaenzen, damit rrze-synonym (version >= 2.0) funktioniert:
    if ( get_option( 'rrze-synonym-metadata' ) != 'added' ) {
        addMetadata();
        update_option( 'rrze-synonym-metadata', 'added' );
    }
}

/**
 * Wird durchgeführt, nachdem das Plugin deaktiviert wurde.
 */
function deactivation() {
    // Hier können die Funktionen hinzugefügt werden, die
    // bei der Deaktivierung des Plugins aufgerufen werden müssen.
    // Bspw. delete_option, wp_clear_scheduled_hook, flush_rewrite_rules, etc.

    // delete_option(Options::get_option_name());
    wp_clear_scheduled_hook( 'rrze_synonym_auto_sync' );
}

/**
 * Wird durchgeführt, nachdem das WP-Grundsystem hochgefahren
 * und alle Plugins eingebunden wurden.
 */
function loaded() {
    // Sprachdateien werden eingebunden.
    load_textdomain();

    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    if ($error = system_requirements()) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_data = get_plugin_data(__FILE__);
        $plugin_name = $plugin_data['Name'];
        $tag = is_network_admin() ? 'network_admin_notices' : 'admin_notices';
        add_action($tag, function () use ($plugin_name, $error) {
            printf('<div class="notice notice-error"><p>%1$s: %2$s</p></div>', esc_html($plugin_name), esc_html($error));
        });
    } else {
        // Hauptklasse (Main) wird instanziiert.
        $main = new Main(__FILE__);
        $main->onLoaded();
    }
}

<?php

/**
 * Plugin Name:     Synonym
 * Plugin URI:      
 * Description:     WordPress-Plugin: Shortcode zur Einbindung von eigenen Synonymen sowie von Synonymen aus dem FAU-Netzwerk.
 * Version:         1.2.1
 * Author:          RRZE-Webteam
 * Author URI:      https://blogs.fau.de/webworking/
 * License:         GNU General Public License v2
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:     /languages
 * Text Domain:     rrze-synonym-server
 */

namespace RRZE\Synonym\Server;

const RRZE_PHP_VERSION              = '7.0';
const RRZE_WP_VERSION               = '4.9';
    
add_action('plugins_loaded', 'RRZE\Synonym\Server\init');
add_action ('synonymhook', 'RRZE\Synonym\Server\updateUrlOption');
register_activation_hook(__FILE__, 'RRZE\Synonym\Server\activation');


function init() {
    textdomain();
    include_once('includes/posttype/server-posttype-synonym.php');
    include_once('includes/posttype/server-metabox-synonym.php');
    include_once('includes/posttype/server-admin-synonym.php');
    include_once('includes/posttype/server-helper-synonym.php');
    include_once('includes/synonym/server-class-synonym-helper.php');
    include_once('includes/shortcode/server-shortcode-synonym.php');
    new Class_Build_Synomym_Shortcode();
    include_once('includes/REST-API/server-rest-synonym.php');
    include_once('includes/synonym/server-class-synonym-list.php');
    include_once('includes/domain/server-class-domain-add.php');
    new AddServer();
    include_once('includes/domain/server-class-domain-list.php');
    include_once('includes/domain/server-class-domain-get.php');
    new DomainWPListTable();
}

function textdomain() {
    load_plugin_textdomain('rrze-synonym-server', FALSE, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

function activation() {
    textdomain();
    system_requirements();
    synonymcron();
    
    $caps_synonym = get_caps('synonym');
    add_caps('administrator', $caps_synonym);
}

function deactivation() {
    wp_clear_scheduled_hook('synonymhook');
    delete_option ('serversynonyms');
    delete_option ('registerServer');
    
    $caps_synonym = get_caps('synonym');
    remove_caps('administrator',  $caps_synonym);
    flush_rewrite_rules();
}

function get_caps($cap_type) {
    $caps = array(
        "edit_" . $cap_type,
        "read_" . $cap_type,
        "delete_" . $cap_type,
        "edit_" . $cap_type . "s",
        "edit_others_" . $cap_type . "s",
        "publish_" . $cap_type . "s",
        "read_private_" . $cap_type . "s",
        "delete_" . $cap_type . "s",
        "delete_private_" . $cap_type . "s",
        "delete_published_" . $cap_type . "s",
        "delete_others_" . $cap_type . "s",
        "edit_private_" . $cap_type . "s",
        "edit_published_" . $cap_type . "s",                
    );
    
    return $caps;
}

function add_caps($role, $caps) {
    $role = get_role($role);
    foreach($caps as $cap) {
        $role->add_cap($cap);
    }        
}

function remove_caps($role, $caps) {
    $role = get_role($role);
    foreach($caps as $cap) {
        $role->remove_cap($cap);
    }        
}    


function system_requirements() {
    $error = '';

    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(__('Your server is running PHP version %s. Please upgrade at least to PHP version %s.', 'rrze-plugin-help'), PHP_VERSION, RRZE_PHP_VERSION);
    }

    if (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(__('Your Wordpress version is %s. Please upgrade at least to Wordpress version %s.', 'rrze-plugin-help'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }

    // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
    if (!empty($error)) {
        deactivate_plugins(plugin_basename(__FILE__), FALSE, TRUE);
        wp_die($error);
    }
}

function synonym_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    return $schedules;
}

add_filter('cron_schedules','RRZE\Synonym\Server\synonym_cron_schedules');

function synonymcron() {
    if (!wp_next_scheduled( 'synonymhook' )) {
      wp_schedule_event( time(), '5min', 'synonymhook' );
    }
}

function updateUrlOption() {
    
    //delete_option('urls');
    //getSynonymsForWPListTable
    
    $synonym_option = 'serversynonyms';
    $syn = WplisttableHelper::getSynonymsForWPListTable();
    
    if( get_option($synonym_option) !== false) {
        update_option($synonym_option, $syn);
    } else {
        $autoload = 'no';
        add_option ($synonym_option, $syn);
    }
}
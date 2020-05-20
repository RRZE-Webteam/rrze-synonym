<?php

namespace RRZE\Synonym;

defined('ABSPATH') || exit;

use function RRZE\Synonym\Config\logIt;
use function RRZE\Synonym\Config\deleteLogfile;
use RRZE\Synonym\API;
use RRZE\Synonym\CPT;
use RRZE\Synonym\Layout;
use RRZE\Synonym\RESTAPI;
use RRZE\Synonym\Settings;
use RRZE\Synonym\Shortcode;


/**
 * Hauptklasse (Main)
 */
class Main {
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */
    protected $pluginFile;

    protected $settings;

    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Pfad- und Dateiname der Plugin-Datei
     */
    public function __construct($pluginFile) {
        $this->pluginFile = $pluginFile;
    }

    /**
     * Es wird ausgeführt, sobald die Klasse instanziiert wird.
     */
    public function onLoaded() {
        add_action( 'wp_enqueue_scripts', [$this, 'enqueueScripts'] );
        // Actions: sync, add domain, delete domain, delete logfile
        add_action( 'update_option_rrze-synonym', [$this, 'checkSync'] );
        add_filter( 'pre_update_option_rrze-synonym',  [$this, 'switchTask'], 10, 1 );

        $cpt = new CPT(); 

        $this->settings = new Settings($this->pluginFile);
        $this->settings->onLoaded();

        $restAPI = new RESTAPI();
        $layout = new Layout();
        $shortcode = new Shortcode();

        // Auto-Sync
        add_action( 'rrze_synonym_auto_sync', [$this, 'runsynonymCronjob'] );
    }


    /**
     * Enqueue der globale Skripte.
     */
    public function enqueueScripts() {
        wp_register_style('rrze-synonym-styles', plugins_url('assets/css/rrze-synonym.min.css', plugin_basename($this->pluginFile)));
    }





    /**
     * Click on buttons "sync", "add domain", "delete domain" or "delete logfile"
     */
    public function switchTask( $options ) {
        $api = new API();
        $domains = $api->getDomains();
        $tab = ( isset($_GET['synonymdoms'] ) ? 'synonymdoms' : ( isset( $_GET['sync'] ) ? 'sync' : ( isset( $_GET['del'] ) ? 'del' : '' ) ) );

        switch ( $tab ){
            case 'synonymdoms':
                if ( isset( $_POST['rrze-synonym']['synonymdoms_new_url'] ) && $_POST['rrze-synonym']['synonymdoms_new_url'] != '' ){
                    // add domain
                    $domains = $api->setDomain( $_POST['rrze-synonym']['synonymdoms_new_name'], $_POST['rrze-synonym']['synonymdoms_new_url'] );
                    if ( !$domains ){
                        $domains = $api->getDomains();
                        add_settings_error( 'synonymdoms_new_url', 'synonymdoms_new_error', $_POST['rrze-synonym']['synonymdoms_new_url'] . ' is not valid.', 'error' );        
                    }
                    $options['synonymdoms_new_name'] = '';
                    $options['synonymdoms_new_url'] = '';
                } else {
                    // delete domain(s)
                    foreach ( $_POST as $key => $url ){
                        if ( substr( $key, 0, 11 ) === "del_domain_" ){
                            foreach( $options as $field => $val ){
                                if ( ( stripos( $field, 'synonymsync_url' ) === 0 ) && ( $val == $url ) ){
                                    $parts = explode( '_', $field );
                                    $shortname = $parts[2];
                                    $api->deleteDomain( $shortname );
                                    unset( $options['synonymsync_shortname_' . $shortname] );
                                    unset( $options['synonymsync_url_' . $shortname] );
                                    unset( $options['synonymsync_categories_' . $shortname] );
                                    // unset( $options['synonymsync_mode_' . $shortname] );
                                    unset( $options['synonymsync_hr_' . $shortname] );
                                    if ( ( $key = array_search( $url, $domains ) ) !== false) {
                                        unset( $domains[$key] );
                                    }           
                                    logIt( __( 'Domain', 'rrze-synonym' ) . ' "' . $shortname . '" ' . __( 'deleted', 'rrze-synonym') );
                                }
                            }   
                        }
                    }
                }    
            break;
            case 'sync':
                $options['timestamp'] = time();
            break;
            case 'del':
                deleteLogfile();
            break;
        }

        if ( !$domains ){
            unset( $options['registeredDomains'] );
        } else {
            $options['registeredDomains'] = $domains;
        }

        return $options;
    }


    public function checkSync() {
        if ( isset( $_GET['sync'] ) ){
            $sync = new Sync();
            $sync->doSync( 'manual' );

            $this->setSynonymCronjob();
        }
    }

    public function runSynonymCronjob() {
        // sync hourly
        $sync = new Sync();
        $sync->doSync( 'automatic' );
    }

    public function setSynonymCronjob() {
        date_default_timezone_set( 'Europe/Berlin' );

        $options = get_option( 'rrze-synonym' );

        if ( $options['synonymsync_autosync'] != 'on' ) {
            wp_clear_scheduled_hook( 'rrze_synonym_auto_sync' );
            return;
        }

        $nextcron = 0;
        switch( $options['synonymsync_frequency'] ){
            case 'daily' : $nextcron = 86400;
                break;
            case 'twicedaily' : $nextcron = 43200;
                break;
        }

        $nextcron += time();
        wp_clear_scheduled_hook( 'rrze_synonym_auto_sync' );
        wp_schedule_event( $nextcron, $options['synonymsync_frequency'], 'rrze_synonym_auto_sync' );

        $timestamp = wp_next_scheduled( 'rrze_synonym_auto_sync' );
        $message = __( 'Next automatically synchronization:', 'rrze-synonym' ) . ' ' . date( 'd.m.Y H:i:s', $timestamp );
        add_settings_error( 'AutoSyncComplete', 'autosynccomplete', $message , 'updated' );
        settings_errors();
    }
}

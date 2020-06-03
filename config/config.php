<?php

namespace RRZE\Synonym\Config;

defined('ABSPATH') || exit;

/**
 * Gibt der Name der Option zurück.
 * @return array [description]
 */
function getOptionName() {
    return 'rrze-synonym';
}


/**
 * Gibt die Einstellungen des Menus zurück.
 * @return array [description]
 */
function getMenuSettings() {
    return [
        'page_title'    => __('RRZE Synonym', 'rrze-synonym'),
        'menu_title'    => __('RRZE Synonym', 'rrze-synonym'),
        'capability'    => 'manage_options',
        'menu_slug'     => 'rrze-synonym',
        'title'         => __('RRZE Synonym Settings', 'rrze-synonym'),
    ];
}

/**
 * Gibt die Einstellungen der Inhaltshilfe zurück.
 * @return array [description]
 */
function getHelpTab() {
    return [
        [
            'id'        => 'rrze-synonym-help',
            'content'   => [
                '<p>' . __('Here comes the Context Help content.', 'rrze-synonym') . '</p>'
            ],
            'title'     => __('Overview', 'rrze-synonym'),
            'sidebar'   => sprintf('<p><strong>%1$s:</strong></p><p><a href="https://blogs.fau.de/webworking">RRZE Webworking</a></p><p><a href="https://github.com/RRZE Webteam">%2$s</a></p>', __('For more information', 'rrze-synonym'), __('RRZE Webteam on Github', 'rrze-synonym'))
        ]
    ];
}

/**
 * Gibt die Einstellungen der Optionsbereiche zurück.
 * @return array [description]
 */

function getSections() {
	return [ 
		[
			'id'    => 'synonymdoms',
			'title' => __('Domains', 'rrze-synonym' )
		],
		[
			'id'    => 'synonymsync',
			'title' => __('Synchronize', 'rrze-synonym' )
		],
		[
		  	'id' => 'synonymlog',
		  	'title' => __('Logfile', 'rrze-synonym' )
		]
	];   
}

/**
 * Gibt die Einstellungen der Optionsfelder zurück.
 * @return array [description]
 */

function getFields() {
	return [
		'synonymdoms' => [
			[
				'name' => 'new_name',
				'label' => __('Short name', 'rrze-synonym' ),
				'desc' => __('Enter a short name for this domain.', 'rrze-synonym' ),
				'type' => 'text'
			],
			[
				'name' => 'new_url',
				'label' => __('URL', 'rrze-synonym' ),
				'desc' => __('Enter the domain\'s URL you want to receive synonyms from.', 'rrze-synonym' ),
				'type' => 'text'
			]
		],
		'synonymsync' => [
			[
				'name' => 'shortname',
				'label' => __('Short name', 'rrze-synonym' ),
				'desc' => __('Use this name as attribute \'domain\' in shortcode [synonym]', 'rrze-synonym' ),
				'type' => 'plaintext',
				'default' => ''
			],
			[
				'name' => 'url',
				'label' => __('URL', 'rrze-synonym' ),
				'desc' => '',
				'type' => 'plaintext',
				'default' => ''
			],
			// [
			// 	'name' => 'categories',
			// 	'label' => __('Categories', 'rrze-synonym' ),
			// 	'desc' => __('Please select the categories you\'d like to fetch synonyms to.', 'rrze-synonym' ),
			// 	'type' => 'multiselect',
			// 	'options' => []
			// ],
			[
				'name' => 'donotsync',
				'label' => __('Synchronize', 'rrze-synonym' ),
				'desc' => __('Do not synchronize', 'rrze-synonym' ),
				'type' => 'checkbox',
			],
			// [
			// 	'name' => 'mode',
			// 	'label' => __('Synchronize', 'rrze-synonym' ),
			// 	'desc' => __( 'All synonyms that match to the selected categories will be updated or inserted. Already synchronized synonyms that refer to categories which are not selected will be deleted. synonyms that have been deleted at the remote website will be deleted on this website, too.', 'rrze-synonym' ),
			// 	'default' => '',
			// 	'options' => [
			// 		'' => __('Do not synchronize.', 'rrze-synonym' ),
			// 		'manual' => __('Synchronize one time now.', 'rrze-synonym' ),
			// 		'auto' => __('Synchronize now and then automatically.', 'rrze-synonym' ),
			// 	],
			// 	'type' => 'radio'
			// ],
			[
				'name' => 'hr',
				'label' => '',
				'desc' => '',
				'type' => 'line'
			],
			[
				'name' => 'info',
				'label' => __('Info', 'rrze-synonym' ),
				'desc' => __( 'All synonyms will be updated or inserted. Synonyms that have been deleted at the remote website will be deleted on this website, too.', 'rrze-synonym' ),
				'type' => 'plaintext',
				'default' => __( 'All synonyms will be updated or inserted. Synonyms that have been deleted at the remote website will be deleted on this website, too.', 'rrze-synonym' ),
			],
			[
				'name' => 'autosync',
				'label' => __('Mode', 'rrze-synonym' ),
				'desc' => __('Synchronize automatically', 'rrze-synonym' ),
				'type' => 'checkbox',
			],
			[
				'name' => 'frequency',
				'label' => __('Frequency', 'rrze-synonym' ),
				'desc' => '',
				'default' => 'daily',
				'options' => [
					'daily' => __('daily', 'rrze-synonym' ),
					'twicedaily' => __('twicedaily', 'rrze-synonym' )
				],
				'type' => 'select'
			],
		],		
    	'synonymlog' => [
        	[
          		'name' => 'synonymlogfile',
          		'type' => 'logfile',
          		'default' => synonymlogFILE
        	]
      	]
	];
}


/**
 * Gibt die Einstellungen der Parameter für Shortcode für den klassischen Editor und für Gutenberg zurück.
 * @return array [description]
 */

function getShortcodeSettings(){
	return [
		'block' => [
            'blocktype' => 'rrze-synonym/synonym',
			'blockname' => 'synonym',
			'title' => 'RRZE Synonyms',
			'category' => 'widgets',
            'icon' => 'translation',
            'show_block' => 'content',
			'message' => __( 'Find the settings on the right side', 'rrze-synonym' )
		],
		'slug' => [
			'default' => '',
			'field_type' => 'text',
			'label' => __( 'Slug', 'rrze-synonym' ),
			'type' => 'text'
        ],
		'id' => [
			'default' => 0,
			'field_type' => 'text',
			'label' => __( 'Synonym', 'rrze-synonym' ),
			'type' => 'number'
		],
		'gutenberg_shortcode_tag' => [
			'values' => [
				'fau_abbr' => __( 'fau_abbr', 'rrze-synonym' ),
				'synonym' => __( 'synonym', 'rrze-synonym' )
			],
			'default' => 'synonym',
			'field_type' => 'radio',
			'label' => __( 'Type of output', 'rrze-synonym' ),
			'type' => 'string'
		],		
		// 'additional_class' => [
		// 	'default' => '',
		// 	'field_type' => 'text',
		// 	'label' => __( 'Additonal CSS-class(es) for sourrounding DIV', 'rrze-synonym' ),
		// 	'type' => 'text'
		// ],
    ];
}

function logIt( $msg ){
	date_default_timezone_set('Europe/Berlin');
	$msg = date("Y-m-d H:i:s") . ' | ' . $msg;
	if ( file_exists( synonymlogFILE ) ){
		$content = file_get_contents( synonymlogFILE );
		$content = $msg . "\n" . $content;
	}else {
		$content = $msg;
	}
	file_put_contents( synonymlogFILE, $content, LOCK_EX);
}
  
function deleteLogfile(){
	unlink( synonymlogFILE );
}
  


<?php
/*
Plugin Name: Sail CMP & GTM
Description: Sail CMP & GDPR friendly GTM loader.
Author: Are Kristoffer Lamo
Version: 2.0.0
Requires PHP: 8.1
Requires at least: 5.8
Author URI: https://sail.no/
Text Domain: sail-cmp
Domain Path: /languages
Update URI: https://consent.sail.no/wordpress/sail-cmp.json
*/

namespace Sail\CMP_GTM;

/**
 * Hostname in the Update URI header. WordPress 5.8+ asks the filter
 * update_plugins_{hostname} for updates instead of wordpress.org.
 */
if ( ! defined( 'SAIL_CMP_UPDATE_HOST' ) ) {
    define( 'SAIL_CMP_UPDATE_HOST', 'consent.sail.no' );
}

define( 'CMP_GTM_DB_VERSION', '1.0' );
define( 'CMP_GTM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CMP_GTM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( CMP_GTM_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once( CMP_GTM_PLUGIN_DIR . 'vendor/autoload.php' );
}

$plugin_data = get_file_data( __FILE__, [
          'Version'    => 'Version',
          'TextDomain' => 'Text Domain'
      ], 'plugin' );

$plugin = new Plugin($plugin_data);
  
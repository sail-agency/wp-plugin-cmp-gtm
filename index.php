<?php
/*
Plugin Name: CMP & GTM loader
Description: GDPR friendlier GTM loader.
Author: Are Kristoffer Lamo
Version: 1.1.0
Author URI: https://sail.no/folk/are-kristoffer-lamo/
Text Domain: cmp-gtm
*/

namespace Sail\CMP_GTM;

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
  
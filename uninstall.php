<?php
/**
 * Removes the plugin's settings when the plugin is deleted.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Carbon Fields stores each theme option under its name with a leading underscore.
$options = [
    '_cmp_provider',
    '_sail_cmp_site_id',
    '_gtm_id',
    '_gtm_consent',
    '_gtm_wait_time',
    '_gtm_load_delay',
    // From the standalone Sail CMP plugin, if it was never migrated.
    'sail_cmp_site_id',
];

foreach ( $options as $option ) {
    delete_option( $option );
}

delete_site_transient( 'sail_cmp_manifest' );

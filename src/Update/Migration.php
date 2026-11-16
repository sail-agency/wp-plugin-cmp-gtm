<?php

namespace Sail\CMP_GTM\Update;

use Sail\CMP_GTM\Field\Option;

/**
 * Carries settings over from the standalone Sail CMP plugin, which stored the site ID in the
 * option "sail_cmp_site_id". Without this a site updated from that plugin loses its banner.
 */
class Migration
{
    const LEGACY_SITE_ID_OPTION = 'sail_cmp_site_id';

    public function __construct()
    {
        add_action( 'plugins_loaded', [ $this, 'legacy_site_id' ] );
    }

    /**
     * Carbon Fields keeps a theme option in wp_options under its name with a leading underscore.
     * The options are written directly, since Carbon Fields is not booted this early.
     */
    public function legacy_site_id()
    {
        $legacy = get_option( self::LEGACY_SITE_ID_OPTION, null );
        if ( null === $legacy ) {
            return;
        }

        $legacy = strtolower( trim( (string) $legacy ) );

        // Settings made in this plugin win. Only fill in what is missing.
        if ( Option::is_valid_sail_cmp_site_id( $legacy ) && ! Option::is_valid_sail_cmp_site_id( get_option( '_sail_cmp_site_id' ) ) ) {
            update_option( '_sail_cmp_site_id', $legacy );

            $provider = get_option( '_cmp_provider' );
            if ( empty( $provider ) || 'none' === $provider ) {
                update_option( '_cmp_provider', 'sail-cmp' );
            }
        }

        delete_option( self::LEGACY_SITE_ID_OPTION );
    }
}

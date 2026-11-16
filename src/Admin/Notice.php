<?php

namespace Sail\CMP_GTM\Admin;

use Sail\CMP_GTM\Field\Option;

/**
 * Tells administrators when the site has no consent banner: no CMP provider is chosen,
 * or Sail CMP is chosen but has no site ID.
 */
class Notice
{
    const SETTINGS_SCREEN = 'settings_page_sail-cmp';

    public function __construct()
    {
        add_action( 'admin_notices', [ $this, 'no_consent_banner' ] );
    }

    public function no_consent_banner()
    {
        if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'carbon_get_theme_option' ) ) {
            return;
        }

        $provider = Option::get_cmp_provider();

        if ( empty( $provider ) || 'none' === $provider ) {
            $message = __( 'No CMP provider is selected, so the site has no consent banner.', 'sail-cmp' );
            $here    = __( 'Choose a CMP provider below.', 'sail-cmp' );
            $link    = __( 'Choose a CMP provider', 'sail-cmp' );
        } elseif ( 'sail-cmp' === $provider && '' === Option::get_sail_cmp_site_id() ) {
            $message = __( 'Sail CMP is not active yet, so the site has no consent banner.', 'sail-cmp' );
            $here    = __( 'Fill in the site ID you got from Sail below.', 'sail-cmp' );
            $link    = __( 'Fill in the site ID', 'sail-cmp' );
        } else {
            return;
        }

        $message = esc_html( $message );
        $screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        if ( $screen && self::SETTINGS_SCREEN === $screen->id ) {
            // Already on the settings page: point at the field instead of linking to this page.
            $message .= ' ' . esc_html( $here );
        } else {
            $message .= sprintf(
                ' <a href="%s">%s</a>',
                esc_url( admin_url( 'options-general.php?page=sail-cmp' ) ),
                esc_html( $link )
            );
        }

        printf( '<div class="notice notice-warning sail-cmp-notice"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

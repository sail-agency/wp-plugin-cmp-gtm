<?php

namespace Sail\CMP_GTM\Field;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Option
{
    public function __construct()
    {
      Container::make( 'theme_options', 'Sail CMP' )
      ->set_page_file( 'sail-cmp' )
      ->set_page_parent( 'options-general.php' )
      ->add_fields( [
        Field::make( 'select', 'cmp_provider', __('CMP Provider', 'sail-cmp') )
          ->add_options( [
            'none' => __( 'None', 'sail-cmp' ),
            'sail-cmp' => __( 'Sail CMP', 'sail-cmp' ),
            'cookieinformation' => __( 'Cookie Information', 'sail-cmp' ),
            // 'cookieyes' => __( 'CookieYes', 'sail-cmp' ),
            // 'custom' => __( 'Custom', 'sail-cmp' )
          ] ),
        Field::make( 'text', 'sail_cmp_site_id', __('Sail CMP Site ID', 'sail-cmp') )
          ->set_help_text( __('The site ID for Sail CMP.', 'sail-cmp') )
          ->set_conditional_logic( [
            [
              'field' => 'cmp_provider',
              'value' => 'sail-cmp'
            ]
          ] ),
        Field::make( 'html', 'sail_cmp_help' )
          ->set_html( self::sail_cmp_help() )
          ->set_conditional_logic( [
            [
              'field' => 'cmp_provider',
              'value' => 'sail-cmp'
            ]
          ] ),
        Field::make( 'text', 'gtm_id', 'GTM ID' )
        ->set_help_text( __('Multiple IDs can be entered separated by comma (no spaces).', 'sail-cmp') ),
        Field::make( 'checkbox', 'gtm_consent', __('Consent mode', 'sail-cmp') )
          ->set_option_value( 'yes' ),
        Field::make( 'text', 'gtm_wait_time', __('GTM wait time', 'sail-cmp') )
          ->set_attribute( 'type', 'number' )
          ->set_attribute( 'min', 0 )
          ->set_attribute( 'step', 100 )
          ->set_attribute( 'max', 5000 )
          ->set_default_value( 500 ),  
        Field::make( 'select', 'gtm_load_delay', __('GTM load delay', 'sail-cmp') )
          ->add_options( [
            'none' => __( 'None', 'sail-cmp' ),
            'functional' => __( 'Functional cookies', 'sail-cmp' ),
            'statistic' => __( 'Statistic cookies', 'sail-cmp' )
          ] )
      ] );

    }

    /**
     * How to add the cookie declaration and the settings link, shown when Sail CMP is the provider.
     */
    private static function sail_cmp_help() : string
    {
        return sprintf(
            '<h3>%1$s</h3><p>%2$s</p><p>%3$s</p>',
            esc_html__( 'Cookie declaration and settings', 'sail-cmp' ),
            sprintf(
                /* translators: %s: the [sail_cmp_declaration] shortcode. */
                esc_html__( 'Add the shortcode %s to the privacy page. It shows a table of the cookies, fetched from Sail.', 'sail-cmp' ),
                '<code>[sail_cmp_declaration]</code>'
            ),
            sprintf(
                /* translators: 1: the [sail_cmp_settings_link] shortcode, 2: the #sail-cmp-open link target. */
                esc_html__( '%1$s gives a link that opens the consent settings again. In menus you can add a custom link to %2$s.', 'sail-cmp' ),
                '<code>[sail_cmp_settings_link]</code>',
                '<code>#sail-cmp-open</code>'
            )
        );
    }

    public static function get_gtm_id()
    {
        $gtm_ids = carbon_get_theme_option( 'gtm_id' );
        if ( ! $gtm_ids ) {
            return null;
        }
        $gtm_ids = explode( ',', $gtm_ids );
        return $gtm_ids;
    }

    public static function get_gtm_consent()
    {
        return carbon_get_theme_option( 'gtm_consent' );
    }

    public static function get_wait_time()
    {
        return carbon_get_theme_option( 'gtm_wait_time' );
    }

    public static function get_load_delay()
    {
        return carbon_get_theme_option( 'gtm_load_delay' );
    }

    public static function get_cmp_provider()
    {
        return carbon_get_theme_option( 'cmp_provider' );
    }

    public static function get_sail_cmp_site_id()
    {
        $site_id = carbon_get_theme_option( 'sail_cmp_site_id' );
        if ( ! preg_match( '/^[a-z0-9]([a-z0-9-]{0,48}[a-z0-9])?$/', $site_id ) ) {
            $site_id = '';
        }
        return $site_id;
    }

    /**
     * Where the script and the plugin updates are served from. Can be changed in wp-config.php:
     * define( 'SAIL_CMP_ORIGIN', 'https://sail-cmp.pages.dev' );
     *
     * @return string Origin without a trailing slash.
     */
    public static function get_sail_cmp_origin() {
      $origin = defined( 'SAIL_CMP_ORIGIN' ) ? (string) SAIL_CMP_ORIGIN : 'https://consent.sail.no';
      return untrailingslashit( (string) apply_filters( 'sail_cmp_origin', $origin ) );
    }

    public static function get_sail_cmp_url( string $site_id) {
      return apply_filters( 'sail_cmp_script_url', self::get_sail_cmp_origin() . '/' . $site_id . '.js', $site_id );
    }
}

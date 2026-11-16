<?php

namespace Sail\CMP_GTM\Field;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Option
{
    public function __construct()
    {
      Container::make( 'theme_options', 'Sail CMP' )
      ->set_page_parent( 'options-general.php' )
      ->add_fields( [
        Field::make( 'select', 'cmp_provider', __('CMP Provider', 'sail-cmp-gtm') )
          ->add_options( [
            'none' => __( 'None', 'sail-cmp-gtm' ),
            'sail-cmp' => __( 'Sail CMP', 'sail-cmp-gtm' ),
            'cookieinformation' => __( 'Cookie Information', 'sail-cmp-gtm' ),
            // 'cookieyes' => __( 'CookieYes', 'sail-cmp-gtm' ),
            // 'custom' => __( 'Custom', 'sail-cmp-gtm' )
          ] ),
        Field::make( 'text', 'sail_cmp_site_id', __('Sail CMP Site ID', 'sail-cmp-gtm') )
          ->set_help_text( __('The site ID for Sail CMP.', 'sail-cmp-gtm') )
          ->set_conditional_logic( [
            [
              'field' => 'cmp_provider',
              'value' => 'sail-cmp'
            ]
          ] ),
        Field::make( 'text', 'gtm_id', 'GTM ID' )
        ->set_help_text( __('Multiple IDs can be entered separated by comma (no spaces).', 'sail-cmp-gtm') ),
        Field::make( 'checkbox', 'gtm_consent', __('Consent mode', 'sail-cmp-gtm') )
          ->set_option_value( 'yes' ),
        Field::make( 'text', 'gtm_wait_time', __('GTM wait time', 'sail-cmp-gtm') )
          ->set_attribute( 'type', 'number' )
          ->set_attribute( 'min', 0 )
          ->set_attribute( 'step', 100 )
          ->set_attribute( 'max', 5000 )
          ->set_default_value( 500 ),  
        Field::make( 'select', 'gtm_load_delay', __('GTM load delay', 'sail-cmp-gtm') )
          ->add_options( [
            'none' => __( 'None', 'sail-cmp-gtm' ),
            'functional' => __( 'Functional cookies', 'sail-cmp-gtm' ),
            'statistic' => __( 'Statistic cookies', 'sail-cmp-gtm' )
          ] )
      ] );

    }

    public static function get_gtm_id()
    {
        return carbon_get_theme_option( 'gtm_id' );
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

    public static function get_sail_cmp_url( string $site_id) {
      $origin = defined( 'SAIL_CMP_ORIGIN' ) ? (string) SAIL_CMP_ORIGIN : 'https://consent.sail.no';
	    $origin = untrailingslashit( (string) apply_filters( 'sail_cmp_origin', $origin ) );
      return apply_filters( 'sail_cmp_script_url', $origin . '/' . $site_id . '.js', $site_id );
    }
}

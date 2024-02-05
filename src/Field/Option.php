<?php

namespace Sail\CMP_GTM\Field;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Option
{
    public function __construct()
    {
      Container::make( 'theme_options', 'GTM Settings' )
      ->add_fields( [
          Field::make( 'text', 'gtm_id', 'GTM ID' ),
          Field::make( 'checkbox', 'gtm_consent', __('Consent mode', 'sail-cmp-gtm') )
            ->set_option_value( 'yes' ),
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

    public static function get_load_delay()
    {
        return carbon_get_theme_option( 'gtm_load_delay' );
    }
}

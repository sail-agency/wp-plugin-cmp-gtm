<?php

namespace Sail\CMP_GTM\Field;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Option
{
    public function __construct()
    {
      Container::make( 'theme_options', 'GTM Settings' )
      ->add_fields( array(
          Field::make( 'text', 'gtm_id', 'GTM ID' ),
      ) );

    }

    public static function get_gtm_id()
    {
        return carbon_get_theme_option( 'gtm_id' );
    }
}

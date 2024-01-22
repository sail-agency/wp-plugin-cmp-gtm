<?php

namespace Sail\CMP_GTM\Integration;

class Elementor
{
    public function __construct()
    {
        if( class_exists('\Elementor\Plugin') ) {
          add_filter('sail/cmp_gtm/is_preview', [ $this, 'preview' ]);
        }
    }

    public static function preview( $preview ) : bool
    {
      if ( $preview ) return true;
      return \Elementor\Plugin::$instance->preview->is_preview() ?? false;
    }
}

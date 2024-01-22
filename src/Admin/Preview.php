<?php

namespace Sail\CMP_GTM\Admin;

class Preview
{
    public static function is_preview()
    {
        return apply_filters( 'sail/cmp_gtm/is_preview', self::wp_preview() );
    }

    private static function wp_preview() :bool
    {
        return is_preview() || is_customize_preview() || is_admin();
    }
}

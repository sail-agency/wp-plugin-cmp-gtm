<?php

namespace Sail\CMP_GTM\Filter;

/**
 * External scripts added by themes and plugins with wp_enqueue_script(), e.g. the Google Maps
 * JavaScript API or the Facebook SDK.
 */
class Asset
{
    /** @var Blocker */
    private $blocker;

    public function __construct( Blocker $blocker )
    {
        $this->blocker = $blocker;

        add_filter( 'script_loader_tag', [ $this, 'filter' ], 999 );
    }

    /**
     * @param mixed $tag The script tag, with any inline scripts added before or after it.
     * @return mixed
     */
    public function filter( $tag )
    {
        return $this->blocker->filter( $tag );
    }
}

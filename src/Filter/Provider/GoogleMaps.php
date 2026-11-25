<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Google Maps: embedded maps (google.com/maps/embed) and the Maps JavaScript API.
 *
 * Functional, as in the Sail CMP scanner: a map is content the visitor asks for (directions to
 * the business), not advertising. The site's config must have the functional category, or the
 * map can never be shown.
 */
class GoogleMaps extends Provider
{
    public function id() : string
    {
        return 'google-maps';
    }

    public function category() : string
    {
        return 'functional';
    }

    protected function domains() : array
    {
        return [ 'google.com', 'google.no', 'maps.google.com', 'maps.googleapis.com' ];
    }

    protected function paths() : array
    {
        return [ '#^/maps(/|$)#', '#^/maps/api/#' ];
    }
}

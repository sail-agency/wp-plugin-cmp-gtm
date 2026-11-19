<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Google Maps: embedded maps (google.com/maps/embed) and the Maps JavaScript API.
 */
class GoogleMaps extends Provider
{
    public function id() : string
    {
        return 'google-maps';
    }

    public function category() : string
    {
        return 'marketing';
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

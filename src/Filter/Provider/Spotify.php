<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Spotify players (open.spotify.com/embed iframes and the embed script).
 */
class Spotify extends Provider
{
    public function id() : string
    {
        return 'spotify';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'open.spotify.com' ];
    }

    protected function paths() : array
    {
        return [ '#^/embed#' ];
    }
}

<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Vimeo videos (player.vimeo.com iframes and the player script).
 *
 * Analytics, as in the Sail CMP scanner: Vimeo's cookies count plays and do not track the
 * visitor for advertising.
 */
class Vimeo extends Provider
{
    public function id() : string
    {
        return 'vimeo';
    }

    public function category() : string
    {
        return 'analytics';
    }

    protected function domains() : array
    {
        return [ 'vimeo.com' ];
    }
}

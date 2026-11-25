<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * SoundCloud players (w.soundcloud.com iframes).
 *
 * Functional, as in the Sail CMP scanner: a player the visitor chooses to use.
 */
class SoundCloud extends Provider
{
    public function id() : string
    {
        return 'soundcloud';
    }

    public function category() : string
    {
        return 'functional';
    }

    protected function domains() : array
    {
        return [ 'w.soundcloud.com', 'api.soundcloud.com' ];
    }
}

<?php

namespace Sail\CMP_GTM\Filter;

/**
 * SoundCloud players (w.soundcloud.com iframes).
 */
class SoundCloud extends Service
{
    public function id() : string
    {
        return 'soundcloud';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'w.soundcloud.com', 'api.soundcloud.com' ];
    }
}

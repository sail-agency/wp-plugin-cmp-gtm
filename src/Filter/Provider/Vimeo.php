<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Vimeo videos (player.vimeo.com iframes and the player script).
 */
class Vimeo extends Provider
{
    public function id() : string
    {
        return 'vimeo';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'vimeo.com' ];
    }
}

<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Instagram posts (the embed.js script and embed iframes).
 */
class Instagram extends Provider
{
    public function id() : string
    {
        return 'instagram';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'instagram.com' ];
    }

    protected function paths() : array
    {
        return [ '#/embed(\\.js|/|$)#' ];
    }
}

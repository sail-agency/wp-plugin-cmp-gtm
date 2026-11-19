<?php

namespace Sail\CMP_GTM\Filter;

/**
 * Instagram posts (the embed.js script and embed iframes).
 */
class Instagram extends Service
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

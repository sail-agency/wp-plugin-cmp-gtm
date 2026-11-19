<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * TikTok videos (embed.js and embed iframes).
 */
class TikTok extends Provider
{
    public function id() : string
    {
        return 'tiktok';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'tiktok.com' ];
    }

    protected function paths() : array
    {
        return [ '#^/embed#', '#^/player/#' ];
    }
}

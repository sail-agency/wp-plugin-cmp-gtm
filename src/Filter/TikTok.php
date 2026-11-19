<?php

namespace Sail\CMP_GTM\Filter;

/**
 * TikTok videos (embed.js and embed iframes).
 */
class TikTok extends Service
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

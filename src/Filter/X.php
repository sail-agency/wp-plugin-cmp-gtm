<?php

namespace Sail\CMP_GTM\Filter;

/**
 * X (Twitter): embedded posts and timelines (widgets.js and its iframes).
 */
class X extends Service
{
    public function id() : string
    {
        return 'x';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'platform.twitter.com', 'platform.x.com', 'syndication.twitter.com' ];
    }
}

<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * Facebook: page, post and video plugins (iframes), the SDK and the pixel script.
 */
class Facebook extends Provider
{
    public function id() : string
    {
        return 'facebook';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'facebook.com', 'facebook.net' ];
    }

    protected function paths() : array
    {
        return [ '#^/plugins/#', '#/sdk(\\.js|/)#', '#/fbevents\\.js$#' ];
    }
}

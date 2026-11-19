<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * YouTube videos (iframe embeds and the iframe API script).
 */
class YouTube extends Provider
{
    public function id() : string
    {
        return 'youtube';
    }

    public function category() : string
    {
        return 'marketing';
    }

    protected function domains() : array
    {
        return [ 'youtube.com', 'youtube-nocookie.com', 'youtu.be' ];
    }
}

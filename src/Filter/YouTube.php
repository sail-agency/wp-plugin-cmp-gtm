<?php

namespace Sail\CMP_GTM\Filter;

/**
 * YouTube videos (iframe embeds and the iframe API script).
 */
class YouTube extends Service
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

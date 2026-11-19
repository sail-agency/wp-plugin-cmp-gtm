<?php

namespace Sail\CMP_GTM\Filter;

/**
 * Post content: blocks, shortcodes and oEmbeds (YouTube links pasted in the editor etc.).
 * Runs late, after shortcodes and embeds have been turned into HTML.
 */
class Content
{
    /** @var Blocker */
    private $blocker;

    public function __construct( Blocker $blocker )
    {
        $this->blocker = $blocker;

        add_filter( 'the_content', [ $this, 'filter' ], 999 );

        // Elementor templates outside the post content (theme builder header, footer, popups).
        add_filter( 'elementor/frontend/the_content', [ $this, 'filter' ], 999 );
    }

    /**
     * @param mixed $content
     * @return mixed
     */
    public function filter( $content )
    {
        return $this->blocker->filter( $content );
    }
}

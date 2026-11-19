<?php

namespace Sail\CMP_GTM\Filter;

/**
 * Widgets where embed codes are usually pasted: the classic Text and Custom HTML widgets and
 * block widgets (WordPress 5.8+).
 */
class Widget
{
    /** @var Blocker */
    private $blocker;

    public function __construct( Blocker $blocker )
    {
        $this->blocker = $blocker;

        add_filter( 'widget_text', [ $this, 'filter' ], 999 );
        add_filter( 'widget_custom_html_content', [ $this, 'filter' ], 999 );
        add_filter( 'widget_block_content', [ $this, 'filter' ], 999 );
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

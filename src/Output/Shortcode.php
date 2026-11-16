<?php

namespace Sail\CMP_GTM\Output;

use Sail\CMP_GTM\Field\Option;

class Shortcode
{
    public function __construct()
    {
      if ( Option::get_cmp_provider() === 'sail-cmp' ) {
        add_shortcode('sail_cmp_declaration', [$this, 'sail_cmp_declaration']);
        add_shortcode('sail_cmp_settings_link', [$this, 'sail_cmp_settings_link']);
      }
    }

  /**
   * [sail_cmp_declaration] – the cookie declaration table for the privacy page.
   * heading_level="2" changes the heading levels (default 3).
   *
   * @param array|string $atts Shortcode attributes.
   * @return string
   */
    public function sail_cmp_declaration( $atts ) {
        $atts  = shortcode_atts( array( 'heading_level' => '' ), $atts, 'sail_cmp_declaration' );
        $level = absint( $atts['heading_level'] );
        $attr  = ( $level >= 2 && $level <= 6 ) ? ' data-heading-level="' . $level . '"' : '';
        return "<div id=\"sail-cmp-declaration\"" . $attr . "></div>";
    }

    /**
     * [sail_cmp_settings_link] – a link that opens the consent settings again.
     * [sail_cmp_settings_link]Endre samtykke[/sail_cmp_settings_link] sets the link text.
     *
     * @param array|string $atts    Shortcode attributes.
     * @param string|null  $content Link text.
     * @return string
     */
    function sail_cmp_settings_link( $atts, $content = null ) {
      $atts = shortcode_atts( array( 'text' => __( 'Cookie settings', 'sail-cmp' ) ), $atts, 'sail_cmp_settings_link' );
      $text = ( null !== $content && '' !== trim( $content ) ) ? $content : $atts['text'];
      return '<a href="#sail-cmp-open" data-sail-cmp-open>' . esc_html( wp_strip_all_tags( $text ) ) . '</a>';
    }
}

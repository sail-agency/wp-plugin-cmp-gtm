<?php

namespace Sail\CMP_GTM\Filter;

use Sail\CMP_GTM\Field\Option;

/**
 * Holds back embeds and scripts from third-party services until the visitor has consented,
 * using the markup Sail CMP looks for:
 *
 *   <iframe src="…">    becomes  <iframe data-sail-src="…" data-sail-category="marketing">
 *   <script src="…">    becomes  <script type="text/plain" data-sail-category="marketing" src="…">
 *
 * Sail CMP shows a placeholder in blocked iframes and activates everything once the category
 * is accepted. The whole page is filtered, so embeds in content, widgets and templates are covered.
 */
class Blocker
{
    /** @var Service[]|null */
    private $services = null;

    public function __construct()
    {
        add_action( 'template_redirect', [ $this, 'start' ], 0 );
    }

    /**
     * The services to filter. One class per service in this folder.
     *
     * @return Service[]
     */
    public function services() : array
    {
        if ( null === $this->services ) {
            $services = apply_filters( 'sail/cmp_gtm/filter/services', [
                new YouTube(),
                new Vimeo(),
                new GoogleMaps(),
                new Spotify(),
                new SoundCloud(),
                new Facebook(),
                new Instagram(),
                new X(),
                new TikTok(),
            ] );

            $this->services = array_values( array_filter( (array) $services, function ( $service ) {
                return $service instanceof Service;
            } ) );
        }

        return $this->services;
    }

    /**
     * Only on normal front-end pages, and only when the Sail CMP script is there to unblock again.
     */
    public function is_enabled() : bool
    {
        $enabled = Option::is_sail_cmp_filter_enabled()
            && ! is_admin()
            && ! is_feed()
            && ! is_robots()
            && ! is_customize_preview()
            && ! wp_doing_ajax()
            && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );

        return (bool) apply_filters( 'sail/cmp_gtm/filter/enabled', $enabled );
    }

    public function start()
    {
        if ( $this->is_enabled() ) {
            ob_start( [ $this, 'filter' ] );
        }
    }

    /**
     * Rewrites matching iframes and scripts in a page of HTML.
     *
     * @param string $html
     * @return string
     */
    public function filter( $html )
    {
        if ( ! is_string( $html ) || false === stripos( $html, '<html' ) ) {
            return $html;
        }

        $filtered = preg_replace_callback( '/<(iframe|script)\b[^>]*>/i', [ $this, 'filter_tag' ], $html );

        // preg returns null on failure (e.g. backtrack limit): leave the page as it was.
        return null === $filtered ? $html : $filtered;
    }

    /**
     * @param array $match 0: the opening tag, 1: the tag name.
     */
    private function filter_tag( $match ) : string
    {
        $tag = $match[0];

        // Already marked, by hand or by an earlier pass.
        if ( false !== stripos( $tag, 'data-sail-' ) ) {
            return $tag;
        }

        if ( ! preg_match( '/\ssrc\s*=\s*("([^"]*)"|\'([^\']*)\')/i', $tag, $src ) ) {
            return $tag;
        }

        $url     = isset( $src[3] ) && '' !== $src[3] ? $src[3] : $src[2];
        $service = $this->service_for( $url );
        if ( ! $service ) {
            return $tag;
        }

        $category = (string) apply_filters( 'sail/cmp_gtm/filter/category', $service->category(), $service->id(), $url );
        if ( ! in_array( $category, [ 'functional', 'analytics', 'marketing' ], true ) ) {
            return $tag;
        }

        $marker = ' data-sail-category="' . esc_attr( $category ) . '" data-sail-service="' . esc_attr( $service->id() ) . '"';

        if ( 'iframe' === strtolower( $match[1] ) ) {
            // src -> data-sail-src, so the browser does not load it.
            $tag = preg_replace( '/(\s)src(\s*=)/i', '$1data-sail-src$2', $tag, 1 );
        } else {
            // A script keeps its src but gets a type the browser does not run. Sail CMP puts the
            // original type back from data-sail-type.
            if ( preg_match( '/\stype\s*=\s*("([^"]*)"|\'([^\']*)\')/i', $tag, $type ) ) {
                $original = isset( $type[3] ) && '' !== $type[3] ? $type[3] : $type[2];
                $tag      = str_replace( $type[0], '', $tag );
                if ( '' !== $original && 'text/javascript' !== strtolower( $original ) ) {
                    $marker .= ' data-sail-type="' . esc_attr( $original ) . '"';
                }
            }
            $marker = ' type="text/plain"' . $marker;
        }

        return preg_replace( '/^<(iframe|script)/i', '<$1' . $marker, $tag, 1 );
    }

    /**
     * @return Service|null
     */
    private function service_for( string $url )
    {
        foreach ( $this->services() as $service ) {
            if ( $service->matches( $url ) ) {
                return $service;
            }
        }

        return null;
    }
}

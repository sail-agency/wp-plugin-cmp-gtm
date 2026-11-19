<?php

namespace Sail\CMP_GTM\Filter;

use Sail\CMP_GTM\Admin\Preview;
use Sail\CMP_GTM\Field\Option;

/**
 * Holds back embeds and scripts from third-party providers until the visitor has consented,
 * using the markup Sail CMP looks for:
 *
 *   <iframe src="…">    becomes  <iframe data-sail-src="…" data-sail-category="marketing">
 *   <script src="…">    becomes  <script type="text/plain" data-sail-category="marketing" src="…">
 *
 * Sail CMP shows a placeholder in blocked iframes and activates everything once the category
 * is accepted.
 *
 * This class does the rewriting. Where it is applied:
 *   Content  post content (blocks, shortcodes, oEmbeds, Elementor)
 *   Widget   text, custom HTML and block widgets
 *   Asset    scripts added with wp_enqueue_script()
 */
class Blocker
{
    public const CATEGORIES = [ 'functional', 'analytics', 'marketing' ];

    /** @var Provider\Provider[]|null */
    private $providers = null;

    /** @var bool|null */
    private $enabled = null;

    public function __construct()
    {
        new Content( $this );
        new Widget( $this );
        new Asset( $this );
    }

    /**
     * The providers to block. One class per provider in Provider/.
     *
     * @return Provider\Provider[]
     */
    public function providers() : array
    {
        if ( null === $this->providers ) {
            $providers = apply_filters( 'sail/cmp_gtm/filter/providers', [
                new Provider\YouTube(),
                new Provider\Vimeo(),
                new Provider\GoogleMaps(),
                new Provider\Spotify(),
                new Provider\SoundCloud(),
                new Provider\Facebook(),
                new Provider\Instagram(),
                new Provider\X(),
                new Provider\TikTok(),
            ] );

            $this->providers = array_values( array_filter( (array) $providers, function ( $provider ) {
                return $provider instanceof Provider\Provider;
            } ) );
        }

        return $this->providers;
    }

    /**
     * Only on normal front-end pages, and only when the Sail CMP script is there to unblock again.
     * Not in previews and page builder editors, where editors need to see the content.
     */
    public function is_enabled() : bool
    {
        if ( null !== $this->enabled ) {
            return $this->enabled;
        }

        $enabled = Option::is_sail_cmp_filter_enabled()
            && ! Preview::is_preview()
            && ! is_feed()
            && ! is_robots()
            && ! wp_doing_ajax()
            && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );

        $enabled = (bool) apply_filters( 'sail/cmp_gtm/filter/enabled', $enabled );

        // The answer depends on the main query, so it is only kept once that has run.
        if ( did_action( 'wp' ) ) {
            $this->enabled = $enabled;
        }

        return $enabled;
    }

    /**
     * Rewrites matching iframes and scripts in a piece of HTML.
     *
     * @param mixed $html
     * @return mixed The same type as given.
     */
    public function filter( $html )
    {
        if ( ! is_string( $html ) || ( false === stripos( $html, '<iframe' ) && false === stripos( $html, '<script' ) ) ) {
            return $html;
        }

        if ( ! $this->is_enabled() ) {
            return $html;
        }

        $filtered = preg_replace_callback( '/<(iframe|script)\b[^>]*>/i', [ $this, 'filter_tag' ], $html );

        // preg returns null on failure (e.g. backtrack limit): leave the HTML as it was.
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

        $url      = isset( $src[3] ) && '' !== $src[3] ? $src[3] : $src[2];
        $provider = $this->provider_for( $url );
        if ( ! $provider ) {
            return $tag;
        }

        $category = (string) apply_filters( 'sail/cmp_gtm/filter/category', $provider->category(), $provider->id(), $url );
        if ( ! in_array( $category, self::CATEGORIES, true ) ) {
            return $tag;
        }

        $marker = ' data-sail-category="' . esc_attr( $category ) . '" data-sail-provider="' . esc_attr( $provider->id() ) . '"';

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
     * @return Provider\Provider|null
     */
    private function provider_for( string $url )
    {
        foreach ( $this->providers() as $provider ) {
            if ( $provider->matches( $url ) ) {
                return $provider;
            }
        }

        return null;
    }
}

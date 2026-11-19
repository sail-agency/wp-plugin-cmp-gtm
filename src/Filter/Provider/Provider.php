<?php

namespace Sail\CMP_GTM\Filter\Provider;

/**
 * A third-party service whose embeds and scripts must wait for consent.
 *
 * Add a provider by creating a class in this folder that extends Provider and listing it in
 * Blocker::providers() (or through the "sail/cmp_gtm/filter/providers" filter).
 */
abstract class Provider
{
    /**
     * Short identifier, e.g. "youtube". Used in filters.
     */
    abstract public function id() : string;

    /**
     * The Sail CMP category the visitor must accept: functional, analytics or marketing.
     * The category must be in use on the site in Sail CMP, or the content can never be shown.
     */
    abstract public function category() : string;

    /**
     * Domains the service loads from, without "www.". Subdomains are included.
     *
     * @return string[]
     */
    abstract protected function domains() : array;

    /**
     * Limits the match to certain paths (regular expressions matched against the URL path).
     * Empty means every URL on the domains.
     *
     * @return string[]
     */
    protected function paths() : array
    {
        return [];
    }

    /**
     * Whether a src URL belongs to this service.
     */
    public function matches( string $url ) : bool
    {
        $url = html_entity_decode( trim( $url ), ENT_QUOTES );
        if ( 0 === strpos( $url, '//' ) ) {
            $url = 'https:' . $url;
        }

        $parts = wp_parse_url( $url );
        if ( empty( $parts['host'] ) ) {
            return false;
        }

        $host = strtolower( $parts['host'] );
        $path = isset( $parts['path'] ) ? $parts['path'] : '/';

        foreach ( $this->domains() as $domain ) {
            if ( $host !== $domain && substr( $host, -strlen( '.' . $domain ) ) !== '.' . $domain ) {
                continue;
            }

            $paths = $this->paths();
            if ( empty( $paths ) ) {
                return true;
            }

            foreach ( $paths as $pattern ) {
                if ( preg_match( $pattern, $path ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}

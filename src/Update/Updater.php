<?php

namespace Sail\CMP_GTM\Update;

use Sail\CMP_GTM\Field\Option;

/**
 * Updates through Sail's own channel instead of wordpress.org:
 * <origin>/wordpress/sail-cmp.json describes the latest version.
 *
 * The plugin header "Update URI" points at that manifest. WordPress 5.8+ then asks the filter
 * update_plugins_{hostname} (SAIL_CMP_UPDATE_HOST) for updates.
 */
class Updater
{
    const SLUG      = 'sail-cmp';
    const TRANSIENT = 'sail_cmp_manifest';

    /** @var string Plugin file relative to the plugins directory. */
    private $plugin_file;

    public function __construct( string $plugin_file )
    {
        $this->plugin_file = $plugin_file;

        add_filter( 'update_plugins_' . SAIL_CMP_UPDATE_HOST, [ $this, 'check_update' ], 10, 3 );
        add_filter( 'plugins_api', [ $this, 'plugin_information' ], 10, 3 );
        add_filter( 'upgrader_source_selection', [ $this, 'keep_plugin_directory' ], 10, 4 );
        add_action( 'upgrader_process_complete', [ $this, 'clear_manifest_cache' ] );
    }

    /**
     * URL of the update manifest.
     */
    public static function manifest_url() : string
    {
        return Option::get_sail_cmp_origin() . '/wordpress/sail-cmp.json';
    }

    /**
     * Reads the update manifest. Cached for six hours, one hour after an error.
     *
     * @param bool $force Skip the cache.
     * @return array|null
     */
    public static function fetch_manifest( $force = false )
    {
        $cached = get_site_transient( self::TRANSIENT );
        if ( ! $force && is_array( $cached ) ) {
            return isset( $cached['version'] ) ? $cached : null;
        }

        $manifest = null;
        $response = wp_safe_remote_get(
            self::manifest_url(),
            [
                'timeout' => 10,
                'headers' => [ 'Accept' => 'application/json' ],
            ]
        );

        if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( is_array( $data ) && isset( $data['version'], $data['package'] ) && is_string( $data['version'] ) && is_string( $data['package'] ) && preg_match( '/^\d+\.\d+\.\d+$/', $data['version'] ) ) {
                $manifest = $data;
            }
        }

        set_site_transient( self::TRANSIENT, $manifest ? $manifest : [ 'error' => true ], $manifest ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );

        return $manifest;
    }

    /**
     * Absolute URL of the zip in the manifest. Relative paths are resolved against the origin.
     *
     * @param array $manifest Update manifest.
     */
    public static function package_url( $manifest ) : string
    {
        $package = (string) $manifest['package'];
        if ( preg_match( '#^https://#', $package ) ) {
            return $package;
        }

        return Option::get_sail_cmp_origin() . '/wordpress/' . ltrim( $package, '/' );
    }

    /**
     * Answers WordPress' update check for this plugin.
     *
     * @param array|false $update      Update data from earlier filters.
     * @param array       $plugin_data Plugin headers.
     * @param string      $plugin_file Plugin file relative to the plugins directory.
     * @return array|false
     */
    public function check_update( $update, $plugin_data, $plugin_file )
    {
        if ( $this->plugin_file !== $plugin_file ) {
            return $update;
        }

        // "Check again" on the Updates screen skips the cache.
        $force    = is_admin() && isset( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $manifest = self::fetch_manifest( $force );
        if ( ! $manifest ) {
            return $update;
        }

        return [
            'id'           => isset( $plugin_data['UpdateURI'] ) ? $plugin_data['UpdateURI'] : '',
            'slug'         => self::SLUG,
            'version'      => $manifest['version'],
            'url'          => isset( $manifest['homepage'] ) ? (string) $manifest['homepage'] : 'https://sail.no',
            'package'      => self::package_url( $manifest ),
            'tested'       => isset( $manifest['tested'] ) ? (string) $manifest['tested'] : '',
            'requires_php' => isset( $manifest['requires_php'] ) ? (string) $manifest['requires_php'] : '',
        ];
    }

    /**
     * "View details" for the update.
     *
     * @param false|object|array $result The result object or array.
     * @param string             $action The type of information being requested.
     * @param object             $args   Plugin API arguments.
     * @return false|object|array
     */
    public function plugin_information( $result, $action, $args )
    {
        if ( 'plugin_information' !== $action || ! is_object( $args ) || ! isset( $args->slug ) || self::SLUG !== $args->slug ) {
            return $result;
        }

        $manifest = self::fetch_manifest();
        if ( ! $manifest ) {
            return $result;
        }

        $sections = isset( $manifest['sections'] ) && is_array( $manifest['sections'] ) ? $manifest['sections'] : [];

        //die(var_dump($manifest));

        return (object) [
            //'name'          => isset( $manifest['name'] ) ? (string) $manifest['name'] : 'Sail CMP',
            'name' => $manifest['name'],
            'slug'          => self::SLUG,
            'version'       => $manifest['version'],
            'author'        => isset( $manifest['author'] ) ? (string) $manifest['author'] : 'Sail AS',
            'homepage'      => isset( $manifest['homepage'] ) ? (string) $manifest['homepage'] : 'https://sail.no',
            'requires'      => isset( $manifest['requires'] ) ? (string) $manifest['requires'] : '',
            'tested'        => isset( $manifest['tested'] ) ? (string) $manifest['tested'] : '',
            'requires_php'  => isset( $manifest['requires_php'] ) ? (string) $manifest['requires_php'] : '',
            'last_updated'  => isset( $manifest['last_updated'] ) ? (string) $manifest['last_updated'] : '',
            'download_link' => self::package_url( $manifest ),
            'sections'      => array_map( 'wp_kses_post', array_map( 'strval', $sections ) ),
        ];
    }

    /**
     * The zip unpacks to "sail-cmp/", but the plugin may be installed in another directory
     * (e.g. "sail-cmp-gtm" or "cmp-gtm"). WordPress installs an update into the directory name
     * from the zip, which would leave the active plugin file missing. Rename it to the installed one.
     *
     * @param string|\WP_Error $source        Unpacked plugin directory.
     * @param string           $remote_source Directory the zip was unpacked in.
     * @param \WP_Upgrader     $upgrader      Upgrader instance.
     * @param array            $hook_extra    Extra arguments, with the plugin being updated.
     * @return string|\WP_Error
     */
    public function keep_plugin_directory( $source, $remote_source, $upgrader, $hook_extra = [] )
    {
        global $wp_filesystem;

        if ( is_wp_error( $source ) || ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_file ) {
            return $source;
        }

        $directory = dirname( $this->plugin_file );
        if ( '.' === $directory || basename( untrailingslashit( $source ) ) === $directory ) {
            return $source;
        }

        $target = trailingslashit( $remote_source ) . $directory . '/';
        if ( ! $wp_filesystem || ! $wp_filesystem->move( $source, $target, true ) ) {
            return new \WP_Error( 'sail_cmp_rename_failed', __( 'Could not prepare the Sail CMP update.', 'sail-cmp' ) );
        }

        return $target;
    }

    /**
     * Forgets the cached manifest after an update, so the next check sees the new version.
     */
    public function clear_manifest_cache()
    {
        delete_site_transient( self::TRANSIENT );
    }
}

<?php

namespace Sail\CMP_GTM\Integration;

use Sail\CMP_GTM\Filter\Blocker;

class Elementor
{
    /** @var Blocker */
    private $blocker;

    /** @var \WeakMap Video widgets whose output was replaced by a blocked iframe. */
    private $blocked_videos;

    public function __construct( Blocker $blocker )
    {
        if( class_exists('\Elementor\Plugin') ) {
          $this->blocker        = $blocker;
          $this->blocked_videos = new \WeakMap();

          add_filter('sail/cmp_gtm/is_preview', [ $this, 'preview' ]);
          add_filter('elementor/widget/render_content', [ $this, 'block_video' ], 10, 2);
          add_action('elementor/element/after_add_attributes', [ $this, 'disable_video_handler' ]);
          add_action('carbon_fields_theme_options_container_saved', [ $this, 'clear_element_cache' ], 10, 2);
        }
    }

    public static function preview( $preview ) : bool
    {
      if ( $preview ) return true;
      return \Elementor\Plugin::$instance->preview->is_preview() ?? false;
    }

    /**
     * The Video widget builds YouTube players in the browser with the YouTube IFrame API, and
     * Vimeo players too when lazy load or the lightbox is on. There is no iframe in the HTML for
     * Filter\Content to block, so the video would load without consent.
     *
     * Replaces the widget's output with a plain iframe marked for Sail CMP, built with Elementor's
     * own embed helper so the player options (start, mute, controls, privacy mode …) are kept.
     * Image overlay and lightbox are dropped: Sail CMP's placeholder is shown until consent.
     *
     * @param string                $content Widget HTML.
     * @param \Elementor\Widget_Base $widget
     * @return string
     */
    public function block_video( $content, $widget )
    {
        if ( ! is_string( $content ) || ! is_object( $widget ) || ! method_exists( $widget, 'get_name' ) ) {
            return $content;
        }

        $name = $widget->get_name();
        if ( ( 'video' !== $name && 'e-youtube' !== $name ) || ! $this->blocker->is_enabled() ) {
            return $content;
        }

        if ( 'e-youtube' === $name ) {
            return $this->block_atomic_youtube( $content );
        }

        $settings = $widget->get_settings_for_display();
        $type     = isset( $settings['video_type'] ) ? (string) $settings['video_type'] : '';
        $url      = isset( $settings[ $type . '_url' ] ) ? (string) $settings[ $type . '_url' ] : '';

        // Self-hosted videos are a <video> on the site itself.
        if ( '' === $url || 'hosted' === $type || ! method_exists( $widget, 'get_embed_params' ) || ! class_exists( '\Elementor\Embed' ) ) {
            return $content;
        }

        if ( ! \Elementor\Embed::get_video_properties( $url ) ) {
            return $content;
        }

        $options = [];
        if ( 'youtube' === $type ) {
            $options['privacy'] = ! empty( $settings['yt_privacy'] );
        } elseif ( 'vimeo' === $type ) {
            $options['start'] = isset( $settings['start'] ) ? $settings['start'] : '';
        }

        $attributes = ! empty( $settings['lazy_load'] ) ? [ 'loading' => 'lazy' ] : [];
        $iframe     = \Elementor\Embed::get_embed_html( $url, $widget->get_embed_params(), $options, $attributes );
        if ( ! is_string( $iframe ) || '' === $iframe ) {
            return $content;
        }

        $blocked = $this->blocker->filter( $iframe );
        if ( $blocked === $iframe ) {
            // No provider for this service (e.g. Dailymotion), or its category was filtered away.
            return $content;
        }

        $this->blocked_videos[ $widget ] = true;

        return '<div class="elementor-wrapper elementor-open-inline">' . $blocked . '</div>';
    }

    /**
     * The YouTube element in Elementor 4 (e-youtube) is an empty div with the player options in
     * data-settings. Its script finds it by data-e-type and loads the YouTube IFrame API.
     *
     * Puts a blocked iframe with the same options in the div, and renames data-e-type and removes
     * data-settings so the script leaves it alone. Classes, ID and interactions are kept.
     */
    private function block_atomic_youtube( string $content ) : string
    {
        if ( ! preg_match( '/^(\s*)<div\b([^>]*)>\s*<\/div>(\s*)$/i', $content, $element ) ) {
            return $content;
        }

        $attributes = $element[2];
        if ( ! preg_match( '/\sdata-settings="([^"]*)"/i', $attributes, $data ) ) {
            return $content;
        }

        $settings = json_decode( html_entity_decode( $data[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
        if ( ! is_array( $settings ) || empty( $settings['source'] ) || ! is_string( $settings['source'] ) ) {
            return $content;
        }

        // Same pattern as Elementor's youtube-handler.js.
        if ( ! preg_match( '#^(?:https?://)?(?:www\.)?(?:m\.)?(?:youtu\.be/|youtube\.com/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user|shorts)/))([^?&"\'>]+)#', $settings['source'], $id ) ) {
            return $content;
        }
        $video_id = $id[1];

        // Player options as Elementor's script passes them to YouTube.
        $params = [
            'controls'       => ! empty( $settings['controls'] ) ? 1 : 0,
            'rel'            => ! empty( $settings['rel'] ) ? 0 : 1,
            'cc_load_policy' => ! empty( $settings['cc_load_policy'] ) ? 1 : 0,
            'autoplay'       => ! empty( $settings['autoplay'] ) ? 1 : 0,
        ];
        if ( ! empty( $settings['mute'] ) ) {
            $params['mute'] = 1;
        }
        if ( ! empty( $settings['loop'] ) ) {
            $params['loop']     = 1;
            $params['playlist'] = $video_id;
        }
        foreach ( [ 'start', 'end' ] as $key ) {
            if ( isset( $settings[ $key ] ) && is_numeric( $settings[ $key ] ) && $settings[ $key ] > 0 ) {
                $params[ $key ] = (int) $settings[ $key ];
            }
        }

        $host = ! empty( $settings['privacy'] ) ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
        $src  = $host . '/embed/' . rawurlencode( $video_id ) . '?' . http_build_query( $params );

        $iframe = sprintf(
            '<iframe src="%s" title="%s" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen%s style="display:block;width:100%%;height:100%%;border:0"></iframe>',
            esc_url( $src ),
            esc_attr__( 'YouTube video', 'sail-cmp' ),
            ! empty( $settings['lazyload'] ) ? ' loading="lazy"' : ''
        );

        $blocked = $this->blocker->filter( $iframe );
        if ( $blocked === $iframe ) {
            return $content;
        }

        $attributes = str_replace( $data[0], '', $attributes );
        $attributes = preg_replace( '/\sdata-e-type="e-youtube"/i', ' data-e-type="e-youtube-blocked"', $attributes );

        return $element[1] . '<div' . $attributes . '>' . $blocked . '</div>' . $element[3];
    }

    /**
     * Elementor's video script reads the widget's data-settings and, for YouTube, loads the
     * YouTube IFrame API on its own. Remove the video settings from replaced widgets so the
     * script does nothing. Other settings (animations, motion effects) are kept.
     *
     * @param \Elementor\Element_Base $element
     */
    public function disable_video_handler( $element )
    {
        if ( ! is_object( $element ) || ! isset( $this->blocked_videos[ $element ] ) ) {
            return;
        }

        $values   = $element->get_render_attributes( '_wrapper', 'data-settings' );
        $settings = is_array( $values ) && isset( $values[0] ) ? json_decode( (string) $values[0], true ) : null;
        if ( ! is_array( $settings ) ) {
            return;
        }

        foreach ( [ 'video_type', 'youtube_url', 'vimeo_url', 'dailymotion_url', 'videopress_url', 'lightbox', 'show_image_overlay', 'image_overlay', 'lazy_load', 'autoplay' ] as $key ) {
            unset( $settings[ $key ] );
        }

        if ( $settings ) {
            $element->add_render_attribute( '_wrapper', 'data-settings', wp_json_encode( $settings ), true );
        } else {
            $element->remove_render_attribute( '_wrapper', 'data-settings' );
        }
    }

    /**
     * Elementor's element cache keeps rendered pages for up to 24 hours. Clear it when the
     * Sail CMP settings are saved, so turning blocking on or off shows at once.
     *
     * @param mixed                                          $user_data
     * @param \Carbon_Fields\Container\Theme_Options_Container $container
     */
    public function clear_element_cache( $user_data, $container = null )
    {
        if ( ! is_object( $container ) || ! method_exists( $container, 'get_page_file' ) || 'sail-cmp' !== $container->get_page_file() ) {
            return;
        }

        if ( defined( 'Elementor\Core\Base\Document::CACHE_META_KEY' ) ) {
            delete_post_meta_by_key( \Elementor\Core\Base\Document::CACHE_META_KEY );
        }
    }
}

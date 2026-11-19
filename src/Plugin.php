<?php

namespace Sail\CMP_GTM;

use Carbon_Fields\Carbon_Fields;

class Plugin
{
    private $version_name = 'CMP_GTM_DB_VERSION';
    private $db_version;
    private $plugin_version;

    /** @var Filter\Blocker */
    private $blocker;

    public function __construct($plugin_data)
    {
        $this->plugin_version = $plugin_data['Version'];

        new Update\Migration();
        new Update\Updater( plugin_basename( CMP_GTM_PLUGIN_DIR . 'index.php' ) );

        add_action('after_setup_theme', [ $this, 'boot' ]);
        add_action('plugins_loaded', [ $this, 'translation' ]);
        add_action('plugins_loaded', [ $this, 'integration' ]);
        add_action('carbon_fields_register_fields', [ $this, 'register_fields' ]);
        add_action('wp_head', [ $this, 'header' ], PHP_INT_MIN);
        add_action('wp_head', [ $this, 'header_preconnect' ], PHP_INT_MIN);

        new Admin\Notice();
        $this->blocker = new Filter\Blocker();

        add_filter('plugin_action_links_' . plugin_basename(CMP_GTM_PLUGIN_DIR . 'index.php'), [ $this, 'plugin_action_links' ]);
    }

    public function version()
    {
        $this->db_version = get_option($this->version_name);
    }

    public function translation()
    {
        load_plugin_textdomain( 'sail-cmp', false, dirname( plugin_basename( CMP_GTM_PLUGIN_DIR . 'index.php' ) ) . '/languages' );
    }

    public function integration()
    {
        new Integration\Elementor( $this->blocker );
    }

    public function boot()
    {
        Carbon_Fields::boot();
    }

    public function register_fields()
    {
        new Field\Option();
        new Output\Shortcode();
    }

    public function header()
    {
        if( ! Admin\Preview::is_preview() || ! is_feed() ) {
            new Output\Header();
        }
    }

    public function header_preconnect()
    {
        Output\Header::pre_connect();
    }

    public function plugin_action_links($links)
    {
        $settings_link = '<a href="options-general.php?page=sail-cmp">' . __('Settings', 'sail-cmp') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
<?php

namespace Sail\CMP_GTM;

use Carbon_Fields\Carbon_Fields;

class Plugin
{
    private $version_name = 'CMP_GTM_DB_VERSION';
    private $db_version;
    private $plugin_version;
    private $header = null;

    public function __construct($plugin_data)
    {
        $this->plugin_version = $plugin_data['Version'];

        add_action('after_setup_theme', [ $this, 'boot' ]);
        add_action('plugins_loaded', [ $this, 'translation' ]);
        add_action('plugins_loaded', [ $this, 'integration' ]);
        add_action('carbon_fields_register_fields', [ $this, 'register_fields' ]);
        add_action('wp_head', [ $this, 'preConnect' ], 1);
        add_action('wp_head', [ $this, 'header' ], 100);
    }

    public function version()
    {
        $this->db_version = get_option($this->version_name);
    }

    public function translation()
    {
        load_plugin_textdomain('sail-cmp-gtm', false, CMP_GTM_PLUGIN_DIR . '/languages/');
    }

    public function integration()
    {
        new Integration\Elementor();
    }

    public function boot()
    {
        Carbon_Fields::boot();

        $this->header = new Output\Header();
    }

    public function register_fields()
    {
        new Field\Option();
    }

    public function preConnect()
    {
        $this->header->outputPreConnect();
    }

    public function header()
    {
        $this->header->outputScripts();
    }
}
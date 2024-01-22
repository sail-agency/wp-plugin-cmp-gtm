<?php

namespace Sail\CMP_GTM;

use Carbon_Fields\Carbon_Fields;

class Plugin
{
    private $version_name = 'CMP_GTM_DB_VERSION';
    private $db_version;
    private $plugin_version;

    public function __construct($plugin_data)
    {
        $this->plugin_version = $plugin_data['Version'];

        add_action('after_setup_theme', [ $this, 'boot' ]);
        add_action('plugins_loaded', [ $this, 'translation' ]);
        add_action('plugins_loaded', [ $this, 'integration' ]);
        add_action('carbon_fields_register_fields', [ $this, 'register_fields' ]);
        add_action('wp_head', [ $this, 'header' ]);        
    }

    public function version()
    {
        $this->db_version = get_option($this->version_name);
    }

    public function translation()
    {
        load_plugin_textdomain('sail', false, CMP_GTM_PLUGIN_DIR . '/languages/');
    }

    public function integration()
    {
        new Integration\Elementor();
    }

    public function boot()
    {
        Carbon_Fields::boot();
    }

    public function register_fields()
    {
        new Field\Option();
    }

    public function header()
    {
      new Output\Header();
    }
}
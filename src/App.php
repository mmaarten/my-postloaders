<?php

namespace My\Postloaders;

class App
{
    public static function init()
    {
        add_action('init', [__CLASS__, 'loadTextdomain']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'registerAssets'], 5);
    }

    public static function loadTextdomain()
    {
        load_plugin_textdomain('my-postloaders', false, dirname(plugin_basename(POSTLOADER_PLUGIN_FILE)) . '/languages');
    }

    public static function registerAssets()
    {
        wp_register_script('my-postloaders-script', plugins_url('postloader.js', POSTLOADER_PLUGIN_FILE), ['jquery']);
        wp_localize_script('my-postloaders-script', 'PostloaderOptions', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_register_style('my-postloaders-style', plugins_url('postloader.css', POSTLOADER_PLUGIN_FILE));
    }

    /**
     * Enqueue assets
     */
    public static function enqueueAssets()
    {
        wp_enqueue_script('my-postloaders-script');
        wp_enqueue_style('my-postloaders-style');
    }
}

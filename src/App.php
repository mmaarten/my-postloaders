<?php

namespace My\Postloaders;

class App
{
    const SHORTCODE = 'postloader';

    public static function init()
    {
        add_action('init', [__CLASS__, 'loadTextdomain']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'registerAssets'], 5);
        add_action('wp_enqueue_scripts', [__CLASS__, 'autoEnqueueAssets'], 10);
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

    /**
     * Auto enqueue assets
     */
    public static function autoEnqueueAssets()
    {
        $post = get_post();

        if (is_a($post, '\WP_Post') && has_shortcode($post->post_content, self::SHORTCODE)) {
            self::enqueueAssets();
        }
    }

    public static function shortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => '',
        ], $atts, self::SHORTCODE);

        $postloader = new Postloader($atts['id']);

        ob_start();
        $postloader->render();
        return ob_get_clean();
    }
}

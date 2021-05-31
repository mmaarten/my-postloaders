<?php

namespace My\Postloaders;

final class App
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'registerScripts']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueScripts'], PHP_INT_MAX);
        add_action('init', [__CLASS__, 'loadTextdomain');

        add_shortcode(MY_POSTLOADERS_SHORTCODE_TAG, [__CLASS__, 'postloader_shortcode']);
    }

    public static function registerPostloader($loader)
    {
        Postloaders::getInstance()->register($loader);
    }

    public static function renderPostloader($loader_id)
    {
        Postloaders::getInstance()->render($loader_id);
    }

    public static function registerScripts()
    {
        wp_register_script('my-postloaders-script', plugins_url('postloader.js', MY_POSTLOADERS_PLUGIN_FILE), ['jquery']);
        wp_localize_script('my-postloaders-script', 'PostloaderOptions', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_register_style('my-postloaders-style', plugins_url('postloader.css', MY_POSTLOADERS_PLUGIN_FILE));
    }

    public static function enqueueScripts()
    {
        $post = get_post();

        if (! is_a($post, 'WP_Post')) {
            return;
        }

        if (! has_shortcode($post->post_content, MY_POSTLOADERS_SHORTCODE_TAG)) {
            return;
        }

        wp_enqueue_script('my-postloaders-script');
        wp_enqueue_style('my-postloaders-style');
    }

    public static function shortcode($atts)
    {
        $atts = shortcode_atts(['id' => ''], $atts, MY_POSTLOADERS_SHORTCODE_TAG);

        ob_start();

        postloader($atts['id']);

        return ob_get_clean();
    }

    public static function loadTextdomain()
    {
        load_plugin_textdomain('postloader', false, dirname(plugin_basename(MY_POSTLOADERS_PLUGIN_FILE)) . '/languages');
    }
}

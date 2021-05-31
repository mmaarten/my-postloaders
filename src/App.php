<?php
/**
 * Application
 *
 * @package My/Postloaders
 */
namespace My\Postloaders;

final class App
{
    /**
     * Instance
     *
     * @var mixed
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return App
     */
    public static function getInstance()
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct
     */
    private function __construct()
    {
    }

    /**
     * Init
     */
    public function init()
    {
        add_action('init', [$this, 'loadTextdomain']);
        add_action('wp_enqueue_scripts', [$this, 'registerScripts'], 5);
    }

    /**
     * Load textdomain
     */
    public function loadTextdomain()
    {
        load_plugin_textdomain('my-postloaders', false, dirname(plugin_basename(MY_POSTLOADERS_PLUGIN_FILE)) . '/languages');
    }

    /**
     * Register scripts and styles
     */
    public function registerScripts()
    {
        wp_register_script('my-postloaders-script', plugins_url('postloader.js', MY_POSTLOADERS_PLUGIN_FILE), ['jquery']);
        wp_localize_script('my-postloaders-script', 'PostloaderOptions', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_register_style('my-postloaders-style', plugins_url('postloader.css', MY_POSTLOADERS_PLUGIN_FILE));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueueScripts()
    {
        wp_enqueue_script('my-postloaders-script');
        wp_enqueue_style('my-postloaders-style');
    }
}

<?php

namespace MyNamespace;

use \My\Postloaders\Postloader as Postloader;

class Sample extends Postloader
{
    public function __construct()
    {
        parent::__construct('sample');
    }

    /**
     * Render form content
     */
    public function form()
    {
        parent::form();
    }

    /**
     * Render content
     *
     * @param WP_Query $the_query
     */
    public function content($the_query)
    {
        if ($the_query->have_posts()) {
            echo '<div class="row">';
            while ($the_query->have_posts()) {
                $the_query->the_post();
                echo '<div class="col-md-4">';
                get_template_part('template-parts/content', get_post_type());
                echo '</div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            printf(
                '<div class="alert alert-info" role="alert">%s</div>',
                esc_html__('No posts found.', 'my-textdomain')
            );
        }
    }

    /**
     * Render load more content
     */
    public function more()
    {
        printf(
            '<button type="button" class="postloader-more-button btn btn-primary">%1$s</button',
            esc_html__('Load more', 'my-textdomain')
        );
    }

    /**
     * Alter query arguments
     *
     * @param array $query_args
     * @return array
     */
    public function queryArgs($query_args)
    {
        return $query_args;
    }
}

// Create postloader.
$postloader = new Sample();

// Shortcode to render postloader
add_shortcode('postloader', function () use ($postloader) {
    ob_start();
    $postloader->render();
    return ob_get_clean();
});

add_action('wp_enqueue_scripts', function () {
    $post = get_post();
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'postloader')) {
        \My\Postloaders\App::getInstance()->enqueueScripts();
    }
});

<?php

/**
 * Render form
 *
 * @param My\Postloaders\Postloader $loader
 */
add_action('postloader_form/loader=my_postloader', function ($loader) {

    $terms = get_terms([
        'taxonomy'   => 'category',
        'hide_empty' => true,
    ]);

    if ($terms) {
        echo '<ul class="list-inline">';
        echo '<li class="list-inline-item">';
        printf(
            '<label class="btn btn-outline-secondary btn-sm active"><input type="radio" class="autoload d-none" name="terms[]" value="%1$s" checked> %2$s</label>',
            '',
            __('Show all')
        );
        echo '</li>';
        foreach ($terms as $term) {
            echo '<li class="list-inline-item">';
            printf(
                '<label class="btn btn-outline-primary btn-sm"><input type="radio" class="autoload d-none" name="terms[]" value="%1$s"> %2$s</label>',
                esc_attr($term->term_id),
                esc_html($term->name)
            );
            echo '</li>';
        }
        echo '</ul>';
    }
});

/**
 * Render content
 *
 * @param WP_Query $the_query
 * @param My\Postloaders\Postloader $loader
 */
add_action('postloader_content/loader=my_postloader', function ($the_query, $loader) {

    $loader->theLoop($the_query, [
        'template'    => 'template-parts/content',
        'before'      => '<div class="row">',
        'before_post' => '<div class="col-md-4">',
        'after_post'  => '</div>',
        'after'       => '</div>',
    ]);

    $loader->noPostsMessage($the_query, '<div class="alert alert-info" role="alert">', '</div>');
}, 10, 2);

/**
 * Alter query arguments
 *
 * @param array $query_args
 * @param My\Postloaders\Postloader $loader
 * @return array
 */
add_filter('postloader_query_args/loader=my_postloader', function ($query_args, $loader) {

    $loader->applyTaxQuery('category', 'terms', $query_args);

    return $query_args;
}, 10, 2);

/**
 * Render load more
 *
 * @param string $more
 * @param My\Postloaders\Postloader $loader
 * @return string
 */
add_action('postloader_more/loader=my_postloader', function ($more, $loader) {
    // Create Bootstrap button.
    return str_replace(' class="postloader-more-button', ' class="postloader-more-button btn btn-primary', $more);
}, 10, 2);

// Create postloader.
$postloader = new My\Postloaders\Postloader('my_postloader');

// Shortcode to render postloader
add_shortcode('postloader', function () use ($postloader) {
    ob_start();
    $postloader->render();
    return ob_get_clean();
});

add_action('wp_enqueue_scripts', function () {
    $post = get_post();
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'postloader')) {
        My\Postloaders\App::enqueueScripts();
    }
});

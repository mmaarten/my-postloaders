<?php

function my_postloader_form($loader)
{
    $terms = get_terms(['taxonomy' => 'category', 'hide_empty' => true]);

    if ($terms) {
        echo '<ul class="list-inline">';
            printf(
                '<li class="list-inline-item"><label class="btn btn-outline-primary btn-sm active"><input type="radio" class="autoload d-none" name="terms[]" value="%1$s" checked="checked"> %2$s</label></li>',
                esc_attr(0),
                esc_html('All')
            );
        foreach ($terms as $term) {
            printf(
                '<li class="list-inline-item"><label class="btn btn-outline-primary btn-sm"><input type="radio" class="autoload d-none" name="terms[]" value="%1$s"> %2$s</label></li>',
                esc_attr($term->term_id),
                esc_html($term->name)
            );
        }
        echo '</ul>';
    }
}

add_action('postloader_form/loader=my_postloader', 'my_postloader_form');

function my_postloader_content($wp_query, $loader)
{
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            get_template_part('template-parts/content', get_post_type());
        }
        wp_reset_postdata();
    } else {
        // No posts found message…
        $loader->noPostsMessage($wp_query);
    }
}

add_action('postloader_content/loader=my_postloader', 'my_postloader_content', 10, 2);

function my_postloader_more($more, $loader)
{
    return $more;
}

add_filter('postloader_more/loader=my_postloader', 'my_postloader_more', 10, 2);

function my_postloader_query_args($query_args, $loader)
{
    $terms = isset($_POST['terms']) ? array_filter(array_map('intval', (array)$_POST['terms'])) : [];

    if ($terms) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $terms,
                'compare'  => '=',
            ],
        ];
    }

    return $query_args;
}

add_filter('postloader_query_args/loader=my_postloader', 'my_postloader_query_args', 10, 2);

function my_register_postloaders()
{
    \My\Postloaders\App::registerPostloader('my_postloader');
}

add_action('init', 'my_register_postloaders');

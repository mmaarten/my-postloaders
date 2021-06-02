<?php

namespace My\Postloaders\Postloaders;

class Sample extends Base
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct('sample');
    }

    /**
     * Render form
     *
     * @param array $args
     */
    public function form($args = [])
    {
        parent::form($args);
    }

    /**
     * Render content
     *
     * @param WP_Query $query
     */
    public function content($query)
    {
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/content', get_post_type());
            }
        } else {
            // No posts found.
        }
        wp_reset_postdata();
    }

    /**
     * Alter query arguments.
     *
     * @param array $query_args
     * @return array
     */
    public function queryArgs($query_args)
    {
        return $query_args;
    }
}

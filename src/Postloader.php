<?php

namespace My\Postloaders;

class Postloader
{
    public $id = '';

    public function __construct($id)
    {
        $this->id = $id;

        add_action("wp_ajax_postloader_{$this->id}_process", [$this, 'process']);
        add_action("wp_ajax_nopriv_postloader_{$this->id}_process", [$this, 'process']);
    }

    public function render()
    {
        ?>

        <div class="postloader" id="<?php echo esc_attr($this->id); ?>-postloader">

            <form class="postloader-form" method="post">
                <?php $this->form(); ?>
            </form>

            <div class="postloader-content"></div>

            <div class="postloader-more">
                <?php $this->more(); ?>
            </div>

        </div>

        <?php
    }

    public function form()
    {
        wp_nonce_field("postloader_{$this->id}_form", MY_POSTLOADERS_NONCE_NAME);

        echo '<input type="hidden" name="action" value="postloader_' . esc_attr($this->id) . '_process">';
        echo '<input type="hidden" name="loader" value="' . esc_attr($this->id) . '">';
        echo '<input type="hidden" name="page" value="1">';

        do_action("postloader_form/loader={$this->id}", $this);
    }

    public function content($wp_query)
    {
        do_action("postloader_content/loader={$this->id}", $wp_query, $this);
    }

    public function more()
    {
        $more = sprintf('<button type="button" class="postloader-more-button">%s</button>', esc_html__('Load more', 'my-postloaders'));

        echo apply_filters("postloader_more/loader={$this->id}", $more, $this);
    }

    public function queryArgs($query_args)
    {
        return apply_filters("postloader_query_args/loader={$this->id}", $query_args, $this);
    }

    public function response($response)
    {
        return apply_filters("postloader_response/loader={$this->id}", $response, $this);
    }

    public function noPostsMessage($wp_query, $before = '', $after = '')
    {
        $post_type_names = [];

        foreach ((array) $wp_query->get('post_type') as $post_type) {
            $post_type_object = get_post_type_object($post_type);
            if ($post_type_object) {
                $post_type_names[$post_type] = strtolower($post_type_object->labels->name);
            }
        }

        echo $before;

        if ($post_type_names) {
            if (count($post_type_names) === 1) {
                // Translators: %s: Post type name.
                printf(esc_html__('No %s found.', 'my-postloaders'), $post_type_names[0]);
            } else {
                // Translators: %1$s: Post type name. %2$s: Post type name.
                printf(
                    esc_html__('No %1$s or %2$s found.', 'my-postloaders'),
                    array_slice($post_type_names, 0, count($post_type_names) - 1),
                    array_slice($post_type_names, count($post_type_names) - 1)
                );
            }
        } else {
            esc_html_e('No items found.', 'my-postloaders');
        }

        echo $after;
    }

    public function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_ajax_referer("postloader_{$this->id}_form", MY_POSTLOADERS_NONCE_NAME);

        $query_args = $this->queryArgs([
            'paged' => $_POST['page'],
        ]);

        $wp_query = new \WP_Query($query_args);

        ob_start();

        $this->content($wp_query);

        $content = ob_get_clean();

        $response = $this->response([
            'content'       => $content,
            'page'          => $wp_query->get('paged'),
            'max_num_pages' => $wp_query->max_num_pages,
        ]);

        wp_send_json($response);
    }
}

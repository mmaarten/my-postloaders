<?php
/**
 * Postloader base class
 *
 * @package My/Postloaders
 */
namespace My\Postloaders;

class Postloader
{
    const NONCE_NAME = 'postloader_nonce';

    /**
     * ID
     *
     * @var string
     */
    protected $id = '';

    /**
     * Construct
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;

        add_action("wp_ajax_postloader_{$this->id}_process", [$this, 'process']);
        add_action("wp_ajax_nopriv_postloader_{$this->id}_process", [$this, 'process']);
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Render
     */
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

    /**
     * Render form content
     */
    public function form()
    {
        wp_nonce_field("postloader_{$this->id}_form", self::NONCE_NAME);

        echo '<input type="hidden" name="action" value="postloader_' . esc_attr($this->id) . '_process">';
        echo '<input type="hidden" name="page" value="">';

        do_action("postloader_form/loader={$this->id}", $this);
    }

    /**
     * Render content
     *
     * @param WP_Query $the_query
     */
    public function content($the_query)
    {
        do_action("postloader_content/loader={$this->id}", $the_query, $this);
    }

    /**
     * Render load more content
     */
    public function more()
    {
        $more = sprintf(
            '<button type="button" class="postloader-more-button">%1$s</button',
            esc_html__('Load more', 'my-postloaders')
        );

        echo apply_filters("postloader_more/loader={$this->id}", $more, $this);
    }

    /**
     * Alter query arguments
     *
     * @param array $query_args
     * @return array
     */
    public function queryArgs($query_args)
    {
        return apply_filters("postloader_query_args/loader={$this->id}", $query_args, $this);
    }

    /**
     * Alter response
     *
     * @param array $response
     * @return array
     */
    public function response($response)
    {
        return apply_filters("postloader_response/loader={$this->id}", $response, $this);
    }

    /**
     * Process
     */
    public function process()
    {
        // Check if ajax call.
        if (! wp_doing_ajax()) {
            return;
        }

        // Check if the request is comming from the right place.
        check_ajax_referer("postloader_{$this->id}_form", self::NONCE_NAME);

        // Set query arguments.
        $query_args = $this->queryArgs([
            'paged' => $_POST['page'],
        ]);

        // Setup WP Query.
        $the_query = new \WP_Query($query_args);

        // Set content.
        ob_start();
        $this->content($the_query);
        $content = ob_get_clean();

        // Set response.
        $response = $this->response([
            'content'       => $content,
            'page'          => $the_query->get('paged'),
            'max_num_pages' => $the_query->max_num_pages,
        ]);

        // Output
        wp_send_json($response);
    }

    /**
     * Loop
     *
     * @param WP_Query $the_query
     * @param array    $args
     */
    public function theLoop($the_query, $args = [])
    {
        $args = wp_parse_args($args, [
            'template'    => '',
            'before'      => '',
            'before_post' => '',
            'after_post'  => '',
            'after'       => '',
        ]);

        if ($the_query->have_posts()) {
            echo $args['before'];
            while ($the_query->have_posts()) {
                $the_query->the_post();
                echo $args['before_post'];
                get_template_part($args['template'], get_post_type());
                echo $args['after_post'];
            }
            echo $args['after'];
            wp_reset_postdata();
        }
    }

    /**
     * No posts found message
     *
     * @param WP_Query $the_query
     * @param string   $before
     * @param string   $after
     */
    public function noPostsMessage($the_query, $before = '', $after = '')
    {
        if ($the_query->have_posts()) {
            return;
        }

        $post_type_names = [];
        foreach ((array) $the_query->get('post_type') as $post_type) {
            $post_type_object = get_post_type_object($post_type);
            if ($post_type_object) {
                $post_type_names[$post_type_object->name] = strtolower($post_type_object->labels->name);
            }
        }

        echo $before;

        if ($post_type_names) {
            if (count($post_type_names) === 1) {
                printf(esc_html__('No %s found.', 'my-postloaders'), $post_type_names[0]);
            } else {
                printf(
                    esc_html__('No %1$s or %2$s found.', 'my-postloaders'),
                    array_slice($post_type_names, 0, -1),
                    array_slice($post_type_names, -1, 1)
                );
            }
        } else {
            esc_html_e('No items found.', 'my-postloaders');
        }

        echo $after;
    }

    /**
     * Apply tax query
     *
     * @param string $taxonomy
     * @param string $field
     * @param array  $query_args
     */
    public function applyTaxQuery($taxonomy, $field, &$query_args)
    {
        $terms = isset($_POST[$field]) ? (array) $_POST[$field] : [];
        $terms = array_map('intval', $terms);
        $terms = array_filter($terms);

        if ($terms) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'category',
                'field'    =>'term_id',
                'terms'    => $terms,
                'compare'  => '=',
            ];
        }
    }
}

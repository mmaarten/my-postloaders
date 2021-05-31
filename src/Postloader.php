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
            '<button type="button" class="postloader-more-button">%1$s</button>',
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
}

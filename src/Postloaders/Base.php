<?php

namespace My\Postloaders\Postloaders;

abstract class Base
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

        add_action("wp_ajax_{$this->id}_postloader_process", [$this, 'process']);
        add_action("wp_ajax_nopriv_{$this->id}_postloader_process", [$this, 'process']);
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
     * Main render method
     *
     * @param array $args
     */
    public function render($args = [])
    {
        ?>

        <div class="postloader" id="<?php echo esc_attr($this->id) ?>-postloader">

            <form class="postloader-form" method="post">
                <?php $this->form($args); ?>
            </form>

            <div class="postloader-content"></div>

            <div class="postloader-more">
                <?php $this->more(); ?>
            </div>

        </div>

        <?php
    }

    /**
     * Render form
     *
     * @param array $args
     */
    public function form($args = [])
    {
        wp_nonce_field("{$this->id}_postloader_form", self::NONCE_NAME);

        echo '<input type="hidden" name="action" value="' . esc_attr($this->id) . '_postloader_process">';
        echo '<input type="hidden" name="page" value="">';
    }

    /**
     * Render content
     *
     * @param WP_Query $query
     */
    abstract public function content($query);

    /**
     * Render load more content
     */
    public function more()
    {
        printf(
            '<button type="button" class="postloader-more-button">%s</button>',
            esc_html__('Load more', 'postloader')
        );
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

    /**
     * Alter reponse.
     *
     * @param array $response
     * @return array
     */
    public function response($response)
    {
        return $response;
    }

    /**
     * Process
     */
    public function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_ajax_referer("{$this->id}_postloader_form", self::NONCE_NAME);

        $query_args = $this->queryArgs([
            'paged' => $_POST['page'],
        ]);

        $query = new \WP_Query($query_args);

        ob_start();
        $this->content($query);
        $content = ob_get_clean();

        $response = $this->response([
            'content'    => $content,
            'page'       => $query->get('paged'),
            'totalPages' => $query->max_num_pages,
        ]);

        wp_send_json($response);
    }
}

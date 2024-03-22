<?php

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Mapping_Abstract extends Hipay_Admin_Page
{
    /**
     * @var Hipay_Log
     */
    protected $logs;

    /**
     * @var Hipay_Config
     */
    public $confHelper;


    /**
     * @var string
     */
    protected $postType;

    /**
     * Error messages.
     *
     * @var array
     */
    protected static $errors = array();

    /**
     * Update messages.
     *
     * @var array
     */
    protected static $messages = array();

    /**
     * Hipay_Mapping_Category_Helper constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->confHelper = new Hipay_Config();
        $this->logs = new Hipay_Log($this);
    }

    /**
     * @return array
     */
    public function getDefaultArgs()
    {
        return array();
    }


    /**
     * Update meta data for an POST Mapping
     *
     * @param int $post_id
     * @param array $args
     * @return int|WP_Error
     */
    public function updateMapping($post_id, $args = array())
    {
        $args = wp_parse_args($args, $this->getDefaultArgs());
        foreach ($args as $arg => $value) {
            update_post_meta($post_id, $arg, $value);
        }
        return $post_id;
    }

    /**
     * @return array
     */
    protected function getPosts()
    {
        return get_posts(
            array(
                'post_type' => $this->postType,
                'post_status' => 'any',
                'numberposts' => "-1"
            )
        );
    }


    /**
     * Create an mapping
     *
     * @param array $args
     * @return int|WP_Error
     */
    public function createMapping($args = array())
    {
        $args = wp_parse_args($args, $this->getDefaultArgs());
        $post_args = array(
            'post_title' => sprintf(
                __('Mapping  %s', 'hipayenterprise'),
                strftime('%b %d %Y @ %H %M %S', time())
            ),
            'post_type' => $this->postType,
            'post_status' => 'publish',
            'post_author' => 0
        );
        $post_id = wp_insert_post($post_args);
        foreach ($args as $arg => $value) {
            update_post_meta($post_id, $arg, $value);
        }
        return $post_id;
    }

    /**
     * Add a message.
     *
     * @param string $text Message.
     */
    public static function add_message($text)
    {
        self::$messages[] = $text;
    }

    /**
     * Add an error.
     *
     * @param string $text Message.
     */
    public static function add_error($text)
    {
        self::$errors[] = $text;
    }

    /**
     * Output messages + errors.
     */
    public static function show_messages()
    {
        if (count(self::$errors) > 0) {
            foreach (self::$errors as $error) {
                echo '<div id="message" class="error inline"><p><strong>' . esc_html($error) . '</strong></p></div>';
            }
        } elseif (count(self::$messages) > 0) {
            foreach (self::$messages as $message) {
                echo '<div id="message" class="updated inline"><p><strong>' . esc_html($message) . '</strong></p></div>';
            }
        }
    }
}

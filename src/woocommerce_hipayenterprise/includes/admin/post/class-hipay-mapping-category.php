<?php

defined( 'ABSPATH' ) || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Mapping_Category
{
    /**
     * @var WP_Post
     */
    public $post;

    /**
     * @var string
     */
    public $id;

    /**
     * Hipay_Mapping_Category constructor
     *
     * @param $post
     */
    public function __construct($post)
    {
        $this->id = $post->ID;
        $this->post = $post;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get( $key )
    {
        return get_post_meta( $this->id, $key, true );
    }
}



<?php
/**
 * HiPay Enterprise SDK WooCommerce
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Operations_Helper
{
    /*
     * @var Hipay_Transactions
     */
    protected static $instance = null;

    const OPERATION_ORDER_ID = "order_id" ;

    const OPERATION_TRANSACTION_TYPE = "transaction_type" ;

    const OPERATION_TRANSACTION_REF = "transaction_ref" ;

    const OPERATION_ATTEMPT= "attempt" ;


    /**
     * @param $transaction
     * @param $type
     * @param $operationId
     */
    public function saveOperation($orderId,
                                  $transaction,
                                  $type,
                                  $operationId) {
        $post = array(
            self::OPERATION_ORDER_ID => $orderId,
            self::OPERATION_TRANSACTION_TYPE => $type,
            self::OPERATION_ATTEMPT => preg_split("/-/",$operationId)[2],
            self::OPERATION_TRANSACTION_REF => $transaction->getTransactionReference(),
        );

        $this->createPostTypeOperations($post);
    }

    /**
     * @return array
     */
    public function getDefaultArgs()
    {
        return array(
            self::OPERATION_ORDER_ID => "",
            self::OPERATION_TRANSACTION_TYPE => "",
            self::OPERATION_TRANSACTION_REF => "",
        );
    }

    /**
     *  Create an post type operations
     *
     * @param array $args
     * @return int|WP_Error
     */
    private function createPostTypeOperations($args = array())
    {
        $args = wp_parse_args($args, $this->getDefaultArgs());
        $post_args = array(
            'post_title' => sprintf(__('Operations  %s', 'hipayenterprise'),
                strftime('%b %d %Y @ %H %M %S', time())),
            'post_type' => Hipay_Admin_Post_Types::POST_TYPE_OPERATION,
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
     *  Count Operation
     *
     * @param $operation
     * @param $orderId
     * @return int
     */
    public function getNbOperationAttempt($operation, $orderId) {
        global $wpdb;
        $attempt = $wpdb->get_col( $wpdb->prepare(
            "SELECT max(r.meta_value) FROM {$wpdb->posts} AS posts 
                    LEFT JOIN {$wpdb->postmeta} AS p ON p.post_id = posts.ID
                    LEFT JOIN {$wpdb->postmeta} AS q ON q.post_id = posts.ID 
                    LEFT JOIN {$wpdb->postmeta} AS r ON r.post_id = posts.ID  
                    WHERE posts.post_type = '" . Hipay_Admin_Post_Types::POST_TYPE_OPERATION . "' 
                    AND p.meta_key = '" . self::OPERATION_ORDER_ID ."' AND p.meta_value = %s
                    AND q.meta_key = '" . self::OPERATION_TRANSACTION_TYPE ."' AND q.meta_value = %s
                    AND r.meta_key = 'attempt' ", $orderId ,$operation) );
        return !empty($attempt) &&  $attempt[0] != null ? $attempt[0] : 0 ;
    }

    /**
     * @param $plugin
     * @return Hipay_Transactions
     */
    public static function initHiPayOperationsHelper($plugin)
    {
        if (null === self::$instance) {
            self::$instance = new self($plugin);
        }
        return self::$instance;
    }

}

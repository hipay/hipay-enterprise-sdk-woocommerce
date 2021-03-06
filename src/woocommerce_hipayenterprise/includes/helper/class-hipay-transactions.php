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

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Transactions_Helper
{
    /*
     * @var Hipay_Transactions
     */
    protected static $instance = null;

    const TRANSACTION_ORDER_ID = "order_id";

    const TRANSACTION_TRANSACTION_REF = "transaction_ref";

    const TRANSACTION_STATE = "state";

    const TRANSACTION_STATUS = "status";

    const TRANSACTION_MESSAGE = "message";

    const TRANSACTION_AMOUNT = "amount";

    const TRANSACTION_CAPTURED_AMOUNT = "captured_amount";

    const TRANSACTION_REFUND_AMOUNT = "refunded_amount";

    const TRANSACTION_REFUND_PAYMENT_PRODUCT = "payment_product";

    const TRANSACTION_PAYMENT_START = "payment_start";

    const TRANSACTION_PAYMENT_AUTHORIZED = "payment_authorized";

    const TRANSACTION_AUTHORIZATION_CODE = "authorization_code";

    const TRANSACTION_BASKET = "basket";

    const TRANSACTION_MULTI_USE = 'attempt_create_multi_use';

    const TRANSACTION_CUSTOMER_ID = 'customer_id';

    /**
     *  Save Post type Transaction
     *
     * @param $order
     * @param $transaction
     */
    public function saveTransaction($order, $transaction)
    {
        $post = array(
            self::TRANSACTION_ORDER_ID => $order->get_id(),
            self::TRANSACTION_TRANSACTION_REF => $transaction->getTransactionReference(),
            self::TRANSACTION_STATE => $transaction->getState(),
            self::TRANSACTION_STATUS => $transaction->getStatus(),
            self::TRANSACTION_MESSAGE => $transaction->getMessage(),
            self::TRANSACTION_AMOUNT => $transaction->getAuthorizedAmount(),
            self::TRANSACTION_CAPTURED_AMOUNT => $transaction->getCapturedAmount(),
            self::TRANSACTION_REFUND_AMOUNT => $transaction->getRefundedAmount(),
            self::TRANSACTION_REFUND_PAYMENT_PRODUCT => $transaction->getPaymentProduct(),
            self::TRANSACTION_PAYMENT_START => $transaction->getDateCreated(),
            self::TRANSACTION_PAYMENT_AUTHORIZED => $transaction->getDateAuthorized(),
            self::TRANSACTION_AUTHORIZATION_CODE => $transaction->getAuthorizationCode(),
            self::TRANSACTION_BASKET => $transaction->getBasket(),
            self::TRANSACTION_MULTI_USE => !empty($transaction->getCustomData()) && isset($transaction->getCustomData()["createOneClick"]) ? $transaction->getCustomData()["createOneClick"] : 0,
            self::TRANSACTION_CUSTOMER_ID => $order->get_user_id(),
        );

        $this->createPostTypeTransactions($post);
    }

    /**
     * @return array
     */
    public function getDefaultArgs()
    {
        return array(
            self::TRANSACTION_ORDER_ID => "",
            self::TRANSACTION_TRANSACTION_REF => "",
            self::TRANSACTION_STATE => "",
            self::TRANSACTION_STATUS => "",
            self::TRANSACTION_MESSAGE => "",
            self::TRANSACTION_AMOUNT => "",
            self::TRANSACTION_CAPTURED_AMOUNT => "",
            self::TRANSACTION_REFUND_AMOUNT => "",
            self::TRANSACTION_REFUND_PAYMENT_PRODUCT => "",
            self::TRANSACTION_PAYMENT_START => "",
            self::TRANSACTION_PAYMENT_AUTHORIZED => "",
            self::TRANSACTION_AUTHORIZATION_CODE => "",
            self::TRANSACTION_BASKET => ""
        );
    }

    /**
     *  Create an post type transaction
     *
     * @param array $args
     * @return int|WP_Error
     */
    private function createPostTypeTransactions($args = array())
    {
        $args = wp_parse_args($args, $this->getDefaultArgs());
        $post_args = array(
            'post_title' => sprintf(__('Transactions  %s', 'hipayenterprise'),
                strftime('%b %d %Y @ %H %M %S', time())),
            'post_type' => Hipay_Admin_Post_Types::POST_TYPE_TRANSACTION,
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
     *  Check if last transaction for capture is without
     *
     * @param $orderId
     * @return bool
     */
    public function existCaptureWithoutBasket($orderId)
    {
        global $wpdb;
        $basket = $wpdb->get_col($wpdb->prepare(
            "SELECT r.meta_value FROM {$wpdb->posts} AS posts 
                    INNER JOIN {$wpdb->postmeta} AS p ON p.post_id = posts.ID
                    INNER JOIN {$wpdb->postmeta} AS q ON q.post_id = posts.ID 
                    INNER JOIN {$wpdb->postmeta} AS r ON r.post_id = posts.ID 
                    WHERE posts.post_type = '" . Hipay_Admin_Post_Types::POST_TYPE_TRANSACTION . "' 
                    AND p.meta_key = '" . self::TRANSACTION_ORDER_ID . "' AND p.meta_value = %s
                    AND q.meta_key = '" . self::TRANSACTION_STATUS . "' AND q.meta_value = %s
                    AND r.meta_key = 'basket' order by posts.post_date desc", $orderId, TransactionStatus::CAPTURED));
        return !empty($basket) && empty($basket[0]) ? 1 : 0;
    }

    /**
     * Get basket value for authorization transaction
     *
     * @param $orderId
     * @return bool/json
     */
    public function getOriginalBasket($orderId)
    {
        global $wpdb;
        $basket = $wpdb->get_col($wpdb->prepare(
            "SELECT r.meta_value FROM {$wpdb->posts} AS posts 
                    INNER JOIN {$wpdb->postmeta} AS p ON p.post_id = posts.ID
                    INNER JOIN {$wpdb->postmeta} AS q ON q.post_id = posts.ID 
                    INNER JOIN {$wpdb->postmeta} AS r ON r.post_id = posts.ID 
                    WHERE posts.post_type = '" . Hipay_Admin_Post_Types::POST_TYPE_TRANSACTION . "' 
                    AND p.meta_key = '" . self::TRANSACTION_ORDER_ID . "' AND p.meta_value = %s
                    AND q.meta_key = '" . self::TRANSACTION_STATUS . "' AND q.meta_value = %s
                    AND r.meta_key = 'basket' ", $orderId, TransactionStatus::AUTHORIZED));
        return !empty($basket) && $basket[0] != null ? json_decode($basket[0], true) : false;
    }


    /**
     * @param $plugin
     * @return Hipay_Transactions
     */
    public static function initHiPayTransactionsHelper($plugin)
    {
        if (null === self::$instance) {
            self::$instance = new self($plugin);
        }
        return self::$instance;
    }

    /**
     *  Nb Attempt for create token per user from a date
     *
     * @param $customerId
     * @param $paymentStart
     * @return int
     */
    public static function nbAttemptCreateCard($customerId, $paymentStart)
    {
        $status_sql = array(
            TransactionStatus::AUTHORIZED,
            TransactionStatus::DENIED,
            TransactionStatus::CANCELLED,
            TransactionStatus::EXPIRED,
            TransactionStatus::REFUSED,
            TransactionStatus::CAPTURED
        );

        $query_args = array(
            'post_type' => Hipay_Admin_Post_Types::POST_TYPE_TRANSACTION,
            'no_found_rows' => true,
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'after' => $paymentStart,
                    'inclusive' => true,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => self::TRANSACTION_CUSTOMER_ID,
                    'value' => $customerId,
                    'compare' => '='
                ),
                array(
                    'key' => self::TRANSACTION_STATUS,
                    'value' => $status_sql,
                    'compare' => 'IN'
                ),
                array(
                    'key' => self::TRANSACTION_MULTI_USE,
                    'value' => "1",
                    'compare' => '='
                )
            )
        );

        $postTransactions = new WP_Query($query_args);

        $transactionIds = array();

        foreach ($postTransactions->get_posts() as $transaction) {
            $transactionIds[] = $transaction->{self::TRANSACTION_TRANSACTION_REF};
        }

        return count(array_unique($transactionIds));
    }

    /**
     * @param $customerId
     * @param $paymentStart
     * @param $paymentMethods
     * @return int
     */
    public static function getNbPaymentAttempt($customerId, $paymentStart, $paymentMethods)
    {

        $query_args = array(
            'post_type' => Hipay_Admin_Post_Types::POST_TYPE_TRANSACTION,
            'no_found_rows' => true,
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'after' => $paymentStart,
                    'inclusive' => true,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => self::TRANSACTION_CUSTOMER_ID,
                    'value' => $customerId,
                    'compare' => '='
                ),
                array(
                    'key' => self::TRANSACTION_REFUND_PAYMENT_PRODUCT,
                    'value' => $paymentMethods,
                    'compare' => 'IN'
                )
            )
        );

        $postTransactions = new WP_Query($query_args);

        $transactionIds = array();

        foreach ($postTransactions->get_posts() as $transaction) {
            $transactionIds[] = $transaction->{self::TRANSACTION_TRANSACTION_REF};
        }

        return count(array_unique($transactionIds));
    }

    public static function isTransactionCancelled($orderId){
        $query_args = array(
            'post_type' => Hipay_Admin_Post_Types::POST_TYPE_TRANSACTION,
            'no_found_rows' => true,
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => self::TRANSACTION_ORDER_ID,
                    'value' => $orderId,
                    'compare' => '='
                ),
                array(
                    'key' => self::TRANSACTION_STATUS,
                    'value' => array(TransactionStatus::CANCELLED, TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED),
                    'compare' => 'IN'
                )
            )
        );

        $query = new WP_Query($query_args);
        if($query->have_posts()){
            return true;
        }

        return false;
    }
}

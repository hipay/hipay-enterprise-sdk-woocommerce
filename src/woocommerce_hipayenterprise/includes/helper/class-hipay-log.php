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
 * Class Hipay_Log
 * @TODO : Créer un custom handler
 */
class Hipay_Log
{

    private $plugin;

    private $logger;

    const DEBUG_KEYS_MASK = '****';

    private $privateDataKeys = array(
        'token',
        'cardtoken',
        'card_number',
        'cvc',
        'api_password_sandbox',
        'api_tokenjs_username_sandbox',
        'api_tokenjs_password_publickey_sandbox',
        'api_secret_passphrase_sandbox',
        'api_password_production',
        'api_tokenjs_username_production',
        'api_tokenjs_password_publickey_production',
        'api_secret_passphrase_production',
        'api_moto_username_production',
        'api_moto_password_production',
        'api_moto_secret_passphrase_production'
    );

    /**
     * Hipay_Log constructor.
     * @param $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->logger = wc_get_logger();
    }

    /**
     *  Log exception
     *
     * @param GatewayException
     */
    public function logException(Exception $exception)
    {
        $this->logErrors($exception->getMessage());
        $this->logErrors($exception->getTraceAsString());
    }

    /**
     *  Log error
     *
     * @param $msg
     */
    public function logErrors($msg)
    {
        $this->logger->error($this->getExecutionContext() . ':' . $msg, array('source' => $this->plugin->id));
    }

    /**
     *  Log infos ( HiPay Technical Logs )
     *
     * @param $msg
     */
    public function logInfos($msg)
    {
        if ((bool)$this->plugin->confHelper->getPaymentGlobal()[SettingsField::PAYMENT_GLOBAL_LOGS_INFOS]) {
            if (is_array($msg)) {
                $this->logger->info(print_r($this->filterDebugData($msg), true), array('source' => $this->plugin->id));
            } else {
                $this->logger->info($msg, array('source' => $this->plugin->id));
            }
        }
    }

    /**
     *  Logs Callback ( HiPay notification )
     *
     * @param $transaction
     */
    public function logCallback($transaction)
    {
        $this->logger->debug(
            print_r($this->filterDebugData($this->toArray($transaction)), true),
            array('source' => $this->plugin->id)
        );
    }

    /**
     * Logs Request ( HiPay Request )
     *
     * @param $request
     */
    public function logRequest($request)
    {
        $this->logger->debug(
            print_r($this->filterDebugData($this->toArray($request)), true),
            array('source' => $this->plugin->id)
        );
    }

    /**
     * Recursive filter data for privacy data
     *
     * @param array $debugData
     * @return array
     */
    protected function filterDebugData(array $debugData)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $this->privateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key]);
            } elseif (is_object($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($this->toArray($debugData[$key]));
            }
        }
        return $debugData;
    }

    /**
     * Get execution context
     *
     * @return Execution
     */
    protected function getExecutionContext()
    {
        $debug = debug_backtrace();
        if (isset($debug[2])) {
            return $debug[2]['class'] . ':' . $debug[2]['function'];
        }
        return null;
    }

    /**
     *  Convert Object to Array
     *
     * @param $object
     * @return array
     */
    private function toArray($object)
    {
        return (array)$object;
    }
}

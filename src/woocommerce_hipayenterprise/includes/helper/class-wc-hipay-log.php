<?php
if (! defined('ABSPATH')) {
    exit;
}

class WC_HipayEnterprise_Log
{
    const LOG_HIPAY_ERROR = "error";

    const LOG_HIPAY_INFOS = "infos";

    const LOG_HIPAY_REQUEST = "request";

    const LOG_HIPAY_CALLBACK = "callback";

    private $basePath;

    private $plugin;

    const DEBUG_KEYS_MASK = '****';

    private $privateDataKeys = array('token', 'cardtoken', 'card_number', 'cvc', 'api_password_sandbox',
        'api_tokenjs_username_sandbox', 'api_tokenjs_password_publickey_sandbox', 'api_secret_passphrase_sandbox',
        'api_password_production', 'api_tokenjs_username_production',
        'api_tokenjs_password_publickey_production', 'api_secret_passphrase_production', 'api_moto_username_production',
        'api_moto_password_production', 'api_moto_secret_passphrase_production');

    /**
     *
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->basePath = WC_HIPAYENTERPRISE_PATH . 'logs/';
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
        $this->writeLogs(self::LOG_HIPAY_ERROR, $this->getExecutionContext() . ':' . $msg);
    }

    /**
     *  Log infos ( HiPay Technical Logs )
     *
     * @param $msg
     */
    public function logInfos($msg)
    {
        //if ($this->module->hipayConfigTool->getPaymentGlobal()["log_infos"]) {
            if (is_array($msg)) {
                $this->writeLogs(self::LOG_HIPAY_INFOS, print_r($this->filterDebugData($msg), true));
            } else {
                $this->writeLogs(self::LOG_HIPAY_INFOS, $msg);
            }
        //}
    }

    /**
     *  Logs Callback ( HiPay notification )
     *
     * @param $transaction
     */
    public function logCallback($transaction)
    {
        $this->writeLogs(
            self::LOG_HIPAY_CALLBACK,
            print_r($this->filterDebugData($this->toArray($transaction)), true)
        );
    }

    /**
     * Logs Request ( HiPay Request )
     *
     * @param $request
     */
    public function logRequest($request)
    {
        $this->writeLogs(self::LOG_HIPAY_REQUEST, print_r($this->filterDebugData($this->toArray($request)), true));
    }

    /**
     * Format log message and write log in system file
     *
     * @param $type string
     * @param $message string
     * @return bool|int
     */
    private function writeLogs($type, $message)
    {
        $formatted_message = date('Y/m/d - H:i:s') . ': ' . $message . "\r\n";
        return file_put_contents($this->getFilename($type), $formatted_message, FILE_APPEND);
    }

    /**
     * Get log filename according  type of error
     *
     * @param $type string
     * @return string
     */
    private function getFilename($type)
    {
        return $this->basePath . date('Y-m-d') . '-' . 'hipay' . '-' . $type . '.log';
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
}

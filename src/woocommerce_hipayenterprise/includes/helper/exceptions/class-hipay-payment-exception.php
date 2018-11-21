<?php

if (!defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}


class Hipay_Payment_Exception extends Exception
{

    private $redirectUrl;

    public function __construct($message = "", $redirectUrl = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

}

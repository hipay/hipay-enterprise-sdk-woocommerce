<?php

if (!defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}


class Hipay_Payment_Exception extends Exception
{

    private $redirectUrl;

    private $type;

    /**
     * Hipay_Payment_Exception constructor.
     * @param string $message
     * @param string $redirectUrl
     * @param string $type
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = "",
        $redirectUrl = "",
        $type = "success",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->redirectUrl = $redirectUrl;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}

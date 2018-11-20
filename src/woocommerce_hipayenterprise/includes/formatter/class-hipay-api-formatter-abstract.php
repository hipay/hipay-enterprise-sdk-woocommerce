<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class Hipay_Api_Formatter_Abstact
{
    protected $plugin;

    protected $order;

    public function __construct($plugin, $order)
    {
        $this->plugin = $plugin;
        $this->order = $order;
    }

    abstract public function generate();

    abstract protected function mapRequest(&$request);
}

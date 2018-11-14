<?php

namespace HiPay\Fullservice\Gateway\Model;

use HiPay\Fullservice\Model\AbstractModel;

/**
 *
 * @package HiPay\Fullservice
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2017 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 * @link https://github.com/hipay/hipay-fullservice-sdk-php
 * @api
 */
class OperationResponse extends AbstractModel
{

    /**
     * @var string $_type Type of maintenance operation
     */
    private $_type;

    /**
     * @var string $_id
     */
    private $_id;

    /**
     * @var string
     */
    private $_reference;

    /**
     * @var float $_amount
     */
    private $_amount;

    /**
     * @var string $_currency
     * @type currency
     */
    private $_currency;

    /**
     * @var string $_dateAuthorized
     */
    private $_dateAuthorized;

    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\Fullservice\Gateway\Model\AbstractTransaction::__construct()
     */
    public function __construct(
        $type,
        $id,
        $reference,
        $amount,
        $currency,
        $dateAuthorized)
    {
        $this->_type = $type;
        $this->_id = $id;
        $this->_reference = $reference;
        $this->_amount = $amount;
        $this->_currency = $currency;
        $this->_dateAuthorized = $dateAuthorized;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->_reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->_reference = $reference;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->_amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    /**
     * @return string
     */
    public function getDateAuthorized()
    {
        return $this->_dateAuthorized;
    }

    /**
     * @param string $dateAuthorized
     */
    public function setDateAuthorized($dateAuthorized)
    {
        $this->_dateAuthorized = $dateAuthorized;
    }

}
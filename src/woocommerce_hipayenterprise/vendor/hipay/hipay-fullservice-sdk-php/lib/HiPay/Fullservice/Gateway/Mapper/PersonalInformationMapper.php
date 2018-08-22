<?php
/**
 * HiPay Fullservice SDK PHP
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\Fullservice\Gateway\Mapper;

use HiPay\Fullservice\Mapper\AbstractMapper;
use HiPay\Fullservice\Gateway\Model\Order;
use HiPay\Fullservice\Gateway\Model\PersonalInformation;

/**
 * Mapper for Personal Informations Model Object
 *  
 * @package HiPay\Fullservice
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 * @link https://github.com/hipay/hipay-fullservice-sdk-php
 * @api
 */
class PersonalInformationMapper extends AbstractMapper {
    
    protected $_modelClassName;

    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\Fullservice\Mapper\AbstractMapper::mapResponseToModel()
     */
    protected function mapResponseToModel()
    {
        $source = $this->_getSource();
        $firstname = $source['firstname'] ?: null;
        $lastname = $source['lastname'] ?: null;
        $streetAddress = $source['streetAddress'] ?: null;
        $locality = $source['locality'] ?: null;
        $postalCode = $source['postalCode'] ?: null;
        $country = $source['country'] ?: null;
        $msisdn = $source['msisdn'] ?: null;
        $phone = $source['phone'] ?: null;
        $phoneOperator = $source['phoneOperator'] ?: null;
        $email = $source['email'] ?: null;

        $this->_modelObject = new PersonalInformation($firstname, $lastname, $streetAddress, $locality, $postalCode, $country, $msisdn, $phone, $phoneOperator, $email);
        
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\Fullservice\Mapper\AbstractMapper::validate()
     */
    protected function validate()
    {
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \HiPay\Fullservice\Mapper\AbstractMapper::getModelClassName()
     */
    protected function getModelClassName()
    {
        return '\HiPay\Fullservice\Gateway\Model\PersonalInformation';
    }



}
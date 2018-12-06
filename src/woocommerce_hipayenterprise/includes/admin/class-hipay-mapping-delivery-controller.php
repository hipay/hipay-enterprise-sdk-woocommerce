<?php

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Mapping_Delivery_Controller extends Hipay_Mapping_Abstract
{
    /**
     * @var string
     */
    const ID_WC_DELIVERY_METHOD = "idWcDeliveryMethod";

    /**
     * @var string
     */
    const ORDER_PREPARATION = "orderPreparation";

    /**
     * @var string
     */
    const DELIVERY_ESTIMATED = "deliveryEstimated";

    /*
     * @var string
     */
    const MODE = "mode";

    /**
     * @var string
     */
    const SHIPPING = "shipping";


    /**
     * Hipay_Mapping_Category_Helper constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->postType = Hipay_Admin_Post_Types::POST_TYPE_MAPPING_DELIVERY;
    }


    /**
     * Handles output
     */
    public function output()
    {
        if (!empty($_POST)) {
            $this->saveDeliveryMapping();
        }

        Hipay_Helper::process_template(
            "admin-mapping-delivery-settings.php",
            "admin",
            array(
                "current_page" => "hipay-mapping-delivery-method",
                "wcDeliveryMethods" => Hipay_Helper_Mapping::getWcDeliveryMethods(),
                'hipayCarriers' => Hipay_Helper_Mapping::getHipayCarriers(),
                'mappedDelivery' => $this->getAllDeliveryMapping()
            )
        );
    }

    /**
     *  Save delivery method mapping
     */
    public function saveDeliveryMapping()
    {
        $this->logs->logInfos("# saveMappingDelivery ");
        try {
            $wcDeliveryMethods = Hipay_Helper_Mapping::getWcDeliveryMethods();
            foreach ($wcDeliveryMethods as $deliveryMethod) {

                $idPost = $_POST['wc_map_' . $deliveryMethod->id];
                $orderPreparation = $_POST['mapping_order_preparation_' . $deliveryMethod->id];
                $deliveryEstimated = $_POST['mapping_delivery_estimated_' . $deliveryMethod->id];
                $mode = $_POST['mapping_mode_' . $deliveryMethod->id];
                $shipping = $_POST['mapping_shipping_' . $deliveryMethod->id];

                if (!empty($mode) && !empty($shipping)) {
                    $mapping = array(
                        self::ID_WC_DELIVERY_METHOD => $deliveryMethod->id,
                        self::ORDER_PREPARATION => $orderPreparation,
                        self::DELIVERY_ESTIMATED => $deliveryEstimated,
                        self::MODE => $mode,
                        self::SHIPPING => $shipping
                    );

                    if (isset($idPost) && !empty($idPost)) {
                        $this->logs->logInfos("# updateMapping " . print_r($mapping, true));
                        $this->updateMapping($mapping,$idPost);
                    } else {
                        $this->logs->logInfos("# createDeliveryMapping " . print_r($mapping, true));
                        $this->createDeliveryMapping($mapping);
                    }

                } else {
                    $this->logs->logInfos("# Mapping is empty " . $deliveryMethod->id);
                }
            }
            $this->logs->logInfos("# Delivery Method is saved");
            self::add_message("Your settings have been saved.");
        } catch (Exception $e) {
            $this->logs->logException($e);
            self::add_error(
                __("An error occured during while saving the mapping. ", "hipayenterprise")
            );
        }
    }

    /**
     * @return array
     */
    public function getDefaultArgs()
    {
        return array(
            self::ORDER_PREPARATION => 0,
            self::DELIVERY_ESTIMATED => 0,
            self::MODE => '',
            self::SHIPPING => ''
        );
    }

    /**
     * Return all Delivery Mapping
     *
     * @return array
     */
    private function getAllDeliveryMapping()
    {
        $posts = $this->getPosts();
        $mappings = array();
        foreach ($posts as $post) {
            $mappingDelivery = new Hipay_Mapping_Delivery($post);
            $mappings[$mappingDelivery->getIdWcDeliveryMethod()] = array(
                "idPost" => $mappingDelivery->id,
                self::ID_WC_DELIVERY_METHOD => $mappingDelivery->getIdWcDeliveryMethod(),
                self::ORDER_PREPARATION => $mappingDelivery->getOrderPreparation(),
                self::DELIVERY_ESTIMATED => $mappingDelivery->getDeliveryEstimated(),
                self::MODE => $mappingDelivery->getMode(),
                self::SHIPPING => $mappingDelivery->getShipping()
            );
        }
        return $mappings;
    }
}

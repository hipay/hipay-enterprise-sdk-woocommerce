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
class Hipay_Cart_Formatter implements Hipay_Api_Formatter
{
    /**
     * The single instance of the class.
     *
     * @var Wc_Hipay_Admin_Assets|null
     */
    protected static $instance = null;

    /**
     * @var
     */
    protected $params;

    /**
     * @var
     */
    protected $items;

    /**
     * @var
     */
    protected $operation;

    /**
     * @var
     */
    protected $itemOperation;

    /**
     * @var Hipay_Transactions
     */
    protected $transactionsHelper;

    /**
     * @var array
     */
    private $originalBasket;

    /**
     *  Generate cart and return json representation for cart or specific items
     *
     * @return json
     */
    public function generate($items = array(), $operation = "", $itemOperation = null, $originalBasket = null)
    {
        if (!empty($operation)) {
            $this->itemOperation = $itemOperation;
            $this->items = $items;
            $this->operation = $operation;
            $this->originalBasket = $originalBasket;
        }

        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

        return $cart->toJson();
    }

    /**
     * Map Request
     *
     * @param Hipay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    public function mapRequest(&$cart)
    {
        // Item Type good
        $cartItems = $this->items;
        if (empty($this->items) && empty($this->operation)) {
            $cartItems = WC()->cart->get_cart_contents();
        }

        foreach ($cartItems as $cartItemKey => $value) {
            if ($this->operation == \HiPay\Fullservice\Enum\Transaction\Operation::REFUND
                || $this->operation == \HiPay\Fullservice\Enum\Transaction\Operation::CAPTURE) {
                $itemTypeGood = $this->initItemTypeGoodMaintenance($value);
            } else {
                $itemTypeGood = $this->initItemTypeGood($value);
            }
            $cart->addItem($itemTypeGood);
        }

        // Item Type Fee
        if (($this->operation == \HiPay\Fullservice\Enum\Transaction\Operation::REFUND
            || $this->operation == \HiPay\Fullservice\Enum\Transaction\Operation::CAPTURE)) {
            if (abs($this->itemOperation->get_shipping_total()) > 0) {
                $itemTypeFee = $this->initItemTypeFeeMaintenance();
                $cart->addItem($itemTypeFee);
            }
        } else {
            $feesItems = WC()->cart->calculate_shipping();
            foreach ($feesItems as $feeItemKey => $fee) {
                $itemTypeFee = $this->initItemTypeFee($fee);
                $cart->addItem($itemTypeFee);
            }
        }

        // Item Type discount (coupon)
        if (empty($this->operation)) {
            $coupons = WC()->cart->get_coupons();
            if (count($coupons) > 0) {
                $itemTypeDiscount = $this->initItemTypeDiscount($coupons);
                $cart->addItem($itemTypeDiscount);
            }
        }
    }


    /**
     *  Init discount item
     *
     * @param array $coupons
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function initItemTypeDiscount($coupons)
    {
        $product_reference = array();
        $name = array();
        $discount_description = array();

        foreach ($coupons as $coupon) {
            $product_reference[] = $coupon->get_code();
            $name[] = $coupon->get_code();
            $discount_description[] = $coupon->get_amount();
        }
        $productReference = join("/", $product_reference);
        $name = join("/", $name);
        $discount_description = join("/", $discount_description);

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeDiscount(
            $productReference,
            $name,
            0,
            0,
            0,
            $discount_description,
            0
        );

        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    /**
     *  Init item type fee
     *
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function initItemTypeFeeMaintenance()
    {
        $fee = $this->getOriginalItemFee();

        $productReference = $fee["product_reference"];
        $name = $fee["name"];
        $unitPrice = (float)$fee["unit_price"];
        $taxRate = $fee["tax_rate"];

        $discount = 0.00;
        $totalAmount = $fee["total_amount"];
        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees(
            $productReference,
            $name,
            $unitPrice,
            $taxRate,
            $discount,
            $totalAmount
        );

        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    /**
     *  Init item type fee
     *
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function initItemTypeFee($fee)
    {
        $productReference = $fee->get_method_id();
        $name = $fee->get_label();
        $unitPrice = (float)$fee->get_cost() + $fee->get_shipping_tax();
        $taxRate = 0;

        if (count(WC_Tax::get_shipping_tax_rates()) > 0) {
            $taxRate = (float)WC_Tax::get_shipping_tax_rates()[0]["rate"];
        }

        $discount = 0.00;
        $totalAmount = $unitPrice;
        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees(
            $productReference,
            $name,
            $unitPrice,
            $taxRate,
            $discount,
            $totalAmount
        );

        // forced category
        $item->setProductCategory(1);

        return $item;
    }


    /**
     * Init a type good item
     *
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function initItemTypeGood($cartItem)
    {
        $product = $cartItem['data'];
        if ($cartItem instanceof WC_Order_Item_Product) {
            $product = wc_get_product($cartItem->get_product_id());
        }
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();

        $quantity = abs($cartItem["quantity"]);
        $discount = 0;
        if ($product->is_on_sale()) {
            $discount = -1 *
                round(
                    ($product->get_regular_price() * $quantity) -
                    ($product->get_sale_price() * $quantity),
                    2
                );
        }

        $totalAmount = abs($cartItem["line_total"]) + abs($cartItem["line_tax"]);
        $unitPrice = round(($totalAmount - $discount) / $quantity, 3);
        $taxRate = WC_Tax::get_rates($product->get_tax_class())[1]["rate"];

        // Get First Category because non default cat on
        $productCategories = get_the_terms($product->get_id(), 'product_cat');
        $productCategory = (int)Hipay_Helper_Mapping::getHipayCategoryFromMapping($productCategories[0]->term_id);

        $item->__constructItem(
            null,
            $product->get_sku(),
            "good",
            $product->get_name(),
            $quantity,
            $unitPrice,
            $taxRate,
            $discount,
            $totalAmount,
            null,
            $product->get_short_description(),
            null,
            null,
            null,
            null,
            $productCategory,
            null
        );

        return $item;
    }


    /**
     *  Retrieve item from original cart (Cart authorized-
     *
     * @param $productReference
     * @return mixed
     */
    private function getOriginalItem($productReference)
    {
        foreach ($this->originalBasket as $key => $value) {
            if ($value["product_reference"] == $productReference) {
                return $value;
            }
        }
        return "";
    }


    /**
     *  Retrieve item from original cart Type fee (Cart authorized-
     * @return mixed
     */
    private function getOriginalItemFee()
    {
        foreach ($this->originalBasket as $key => $value) {
            if ($value["type"] == "fee") {
                return $value;
            }
        }
        return "";
    }

    /**
     * @param $cartItem
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function initItemTypeGoodMaintenance($cartItem)
    {
        $product = wc_get_product($cartItem->get_product_id());
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();

        $productReference = $product->get_sku();
        $originalItem = $this->getOriginalItem($productReference);
        $quantityOperation = abs($cartItem["quantity"]);

        $totalDiscount = $originalItem["discount"] ? $originalItem["discount"] : -0;
        $totalAmountItem = $originalItem["total_amount"];

        $discount = round(($totalDiscount * $quantityOperation) / $originalItem["quantity"], 3);
        $totalAmount = round(($totalAmountItem * $quantityOperation) / $originalItem["quantity"], 3);

        $item->__constructItem(
            null,
            $productReference,
            "good",
            $originalItem["name"],
            $quantityOperation,
            $originalItem["unit_price"],
            $originalItem["tax_rate"],
            $discount,
            $totalAmount,
            null,
            $originalItem["product_description"],
            null,
            null,
            null,
            null,
            $originalItem["product_category"],
            null
        );

        return $item;

    }

    public static function initHiPayCartFormatter()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

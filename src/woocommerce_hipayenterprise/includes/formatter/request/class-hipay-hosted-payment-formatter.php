<?php



class Hipay_Hosted_Payment_Formatter extends Hipay_Request_Formatter_Abstract
{
    /**
     * @var string
     */
    protected $productList;

    /**
     * @var boolean
     */
    protected $iframe;

    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $params, $order);
        $this->iframe = $params["iframe"];
        $this->productList = $params["productlist"];
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
     */
    public function generate()
    {
        $orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();

        $this->mapRequest($orderRequest);

        return $orderRequest;
    }

    /**
     * Map order
     *
     * @param type $order
     */
    protected function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);

        $orderRequest->template = (!$this->iframe) ? "basic-js" : "iframe-js";
        $orderRequest->css = $this->plugin->settingsHipay["payment"]["global"]["css_url"];
        $orderRequest->display_selector = $this->plugin->settingsHipay["payment"]["global"]["display_card_selector"];
        $orderRequest->payment_product_list = $this->productList;
        $orderRequest->payment_product_category_list = '';
    }
}

<?php
if (!defined('ABSPATH')) {
    exit;
}

class Hipay_Bnpp3x extends Gateway_Hipay
{

    public function __construct()
    {
        parent::__construct();
        $this->id = 'hipayenterprise_bnppf';
        $this->paymentProduct = 'bnpp-3xcb';
        $this->method_title = __('Bnppf-3xcb', 'hipayenterprise');
        $this->supports = array('products');
        $this->title = __('3x Carte Bancaire - BNP Personal Finance', 'hipayenterprise');
    }


    public function payment_fields()
    {
        _e(
            'You will be redirected to an external payment page. Please do not refresh the page during the process.',
            $this->domain
        );
    }

    function process_payment($order_id)
    {
        try {
            $this->logs->logInfos(" # Process Payment for  " . $order_id);

            $redirectUrl = $this->apiRequestHandler->handleLocalPayment(
                array(
                    "order_id" => $order_id,
                    "paymentProduct" => $this->paymentProduct
                )
            );

            return array(
                'result' => 'success',
                'redirect' => $redirectUrl,
            );

        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            $this->logs->logException($e);
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

    }
}

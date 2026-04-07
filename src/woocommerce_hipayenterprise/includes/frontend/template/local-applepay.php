<?php if (!defined('ABSPATH')) { exit; } ?>

<div class="hipay-applepay-wrapper">
    <div id="apple-pay-tos-notice" style="display:none" class="woocommerce-info"></div>
    <div id="apple-pay-button-container"></div>
    <div id="error-js-<?php echo esc_attr($localPaymentName); ?>" style="display:none" class="woocommerce-error"></div>
</div>

<input type="hidden" name="applepay-card-token"       id="applepay-card-token"       value="" />
<input type="hidden" name="applepay-card-holder"      id="applepay-card-holder"      value="" />
<input type="hidden" name="applepay-payment-product"  id="applepay-payment-product"  value="" />
<input type="hidden" name="applepay-device_fingerprint" value="" class="ioBB" />

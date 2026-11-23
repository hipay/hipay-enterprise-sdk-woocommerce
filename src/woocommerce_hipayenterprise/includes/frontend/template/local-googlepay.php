<?php if (!defined('ABSPATH')) { exit; } ?>

<div class="hipay-googlepay-wrapper"
     data-amount="<?php echo esc_attr($amount); ?>"
     data-currency="<?php echo esc_attr($currency); ?>"
     data-country="<?php echo esc_attr($country); ?>">
    <div id="google-pay-tos-notice" style="display:none" class="woocommerce-info"></div>
    <div id="hipay-googlepay"></div>
    <div id="error-js-<?php echo esc_attr($localPaymentName); ?>" style="display:none" class="woocommerce-error"></div>
</div>

<input type="hidden" name="googlepay-card-token"         id="googlepay-card-token"         value="" />
<input type="hidden" name="googlepay-card-holder"        id="googlepay-card-holder"        value="" />
<input type="hidden" name="googlepay-payment-product"    id="googlepay-payment-product"    value="" />
<input type="hidden" name="googlepay-browser-info"       id="googlepay-browser-info"       value="" />
<input type="hidden" name="googlepay-device_fingerprint" id="googlepay-device_fingerprint" value="" class="ioBB" />

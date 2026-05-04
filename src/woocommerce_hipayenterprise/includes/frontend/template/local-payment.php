<?php if (!empty($informativeMessage)) : ?>
    <p class="hipay-local-payment-info"><?php echo esc_html($informativeMessage); ?></p>
<?php endif; ?>
<div class="hipay-container-hosted-fields" id="hipayHF-container-<?php echo $localPaymentName ?>"></div>
<div id="error-js-<?php echo $localPaymentName ?>" style="display:none" class="woocommerce-hipay-error"></div>

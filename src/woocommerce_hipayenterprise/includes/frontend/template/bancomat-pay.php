<?php if (!defined('ABSPATH')) { exit; } ?>
<p class="hipay-local-payment-info"><?php echo esc_html($informativeMessage); ?></p>

<p id="hipay-bancomatpay-tos-notice" class="woocommerce-error" style="font-size:.9em">
    <?php esc_html_e('Please accept the terms and conditions to use Bancomat Pay.', 'hipayenterprise'); ?>
</p>

<div class="hipay-phone-hosted-field" style="display:none">
    <div
        class="hipay-container-hosted-fields"
        id="hipayHF-container-<?php echo esc_attr($localPaymentName); ?>"
    ></div>
</div>
<div
    id="error-js-<?php echo esc_attr($localPaymentName); ?>"
    style="display:none"
    class="woocommerce-hipay-error"
></div>

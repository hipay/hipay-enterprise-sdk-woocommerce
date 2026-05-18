<div class="hipay-bancomat-pending" id="hipay-bancomat-status">

    <div class="hipay-bancomat-pending__state hipay-bancomat-pending__state--pending">
        <div class="hipay-bancomat-pending__icon">
            <span class="hipay-bancomat-pending__spinner"></span>
        </div>
        <h3 class="hipay-bancomat-pending__title">
            <?php esc_html_e('Payment pending', 'hipayenterprise'); ?>
        </h3>
        <p class="hipay-bancomat-pending__message">
            <?php esc_html_e('The payment will need to be validated on your Bancomat Pay application.', 'hipayenterprise'); ?>
        </p>
        <p class="hipay-bancomat-pending__hint">
            <?php esc_html_e('This page will update automatically once the payment is confirmed.', 'hipayenterprise'); ?>
        </p>
    </div>

    <div class="hipay-bancomat-pending__state hipay-bancomat-pending__state--success" style="display:none">
        <div class="hipay-bancomat-pending__icon hipay-bancomat-pending__icon--success">&#10003;</div>
        <h3 class="hipay-bancomat-pending__title hipay-bancomat-pending__title--success">
            <?php esc_html_e('Payment confirmed', 'hipayenterprise'); ?>
        </h3>
        <p class="hipay-bancomat-pending__message">
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %s: order number */
                    __('Thank you! Order #%s is now being processed.', 'hipayenterprise'),
                    $order->get_order_number()
                )
            );
            ?>
        </p>
    </div>

    <div class="hipay-bancomat-pending__state hipay-bancomat-pending__state--failed" style="display:none">
        <div class="hipay-bancomat-pending__icon hipay-bancomat-pending__icon--failed">&#10007;</div>
        <h3 class="hipay-bancomat-pending__title hipay-bancomat-pending__title--failed">
            <?php esc_html_e('Payment failed', 'hipayenterprise'); ?>
        </h3>
        <p class="hipay-bancomat-pending__message">
            <?php esc_html_e('Your payment could not be processed. Please try again.', 'hipayenterprise'); ?>
        </p>
    </div>

</div>

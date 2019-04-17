<div
    <?php if (!$token->get_is_default()): ?> style="display: none" <?php endif; ?>
        id="hipay-token-force-cvv-<?php echo $token->get_id() ?>"
        class="hipay-token-force-cvv"
>
    <input id="hipay-token-value-<?php echo $token->get_id(); ?>" type="hidden"
           value="<?php echo $token->get_token() ?>"/>
    <input id="hipay-token-year-<?php echo $token->get_id() ?>" type="hidden"
           value="<?php echo $token->get_expiry_year() ?>"/>
    <input id="hipay-token-month-<?php echo $token->get_id() ?>" type="hidden"
           value="<?php echo $token->get_expiry_month() ?>"/>
    <input id="hipay-token-type-<?php echo $token->get_id() ?>" type="hidden"
           value="<?php echo $token->get_payment_product() ?>"/>

    <p><?php _e('For security reason, your card CVC must be updated.', 'hipayenterprise'); ?></p>

    <div id="hipay-container-oneclick-<?php echo $token->get_id() ?>" class="hipay-form-row hipay-container-oneclick">

        <div class="hipay-field-container hipay-field-container-half hipay-container-oneclick-cvv">
            <div class="hipay-field hipay-field-oneclick ">
                <input type="text" class="hipay-cvv-oneclick"
                       id="hipay-token-cvv-<?php echo $token->get_id() ?>"
                       placeholder="<?php _e('CVC', "hipayenterprise"); ?>"/>

                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512"
                     tabindex="-1" color="#00ADE9" height="15px" width="15px" class="oneclick-cvv-help-button"
                     style="color: rgb(0, 173, 233); margin-right: 8px; cursor: pointer;">
                    <path d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"></path>
                </svg>
            </div>
            <div class="hipay-field-baseline"></div>
            <div class="hipay-field-error" data-hipay-id='hipay-oneclick-<?php echo $token->get_id() ?>-field-error-cvc'></div>
        </div>
        <button id="hipay-token-update-<?php echo $token->get_id() ?>" class="hipay-token-update button alt"><?php _e(
                'Update',
                'hipayenterprise'
            ) ?></button>
    </div>

    <div class="hipay-form-row">
        <div class="hipay-element-container">
            <div id="hipay-help-cvc-oneclick-<?php echo $token->get_id() ?>"  data-hipay-id='hipay-help-cvc-oneclick'></div>
        </div>
    </div>

    <div id="error-js-oneclick-<?php echo $token->get_id() ?>" style="display:none"
         class="woocommerce-hipay-error"></div>
    <div id="success-js-oneclick-<?php echo $token->get_id() ?>" style="display:none"
         class="woocommerce-hipay-success"></div>
</div>

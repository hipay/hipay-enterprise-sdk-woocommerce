<div class="panel panel-default panel-primary-hipay">
    <div class="panel-heading">
        <h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
            <i class='dashicons'></i>
            <?php _e('Local payment', "hipayenterprise"); ?>
        </h3>
    </div>
    <div class="panel-body">
        <div class="col-md-12">
            <div class="col-md-12">
                <?php _e(
                    'Here are the different methods of local payments proposed by Hipay. To configure them click on:',
                    "hipayenterprise"
                ); ?>
            </div>
            <div class="col-md-12">
                <ul class="hipay-local-payment-list">
                    <?php foreach ($availableHipayGateways as $gateway => $title) : ?>
                        <li><a title="Open configuration 2" target="_blank" href="<?php echo admin_url(
                            'admin.php?page=wc-settings&tab=checkout&section=' . $gateway
                        ) ?>">
                                <?php echo $title ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

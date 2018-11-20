<p>
    <?php
    echo sprintf(
        __('All other general Stripe settings can be adjusted <a href="%s">here</a>.', 'woocommerce-gateway-stripe'),
        admin_url('admin.php?page=wc-settings&tab=checkout&section=hipayenterprise_credit_card')
    );
    ?>
</p>
<p>
    <?php $this->generate_settings_html($this->local); ?>
</p>

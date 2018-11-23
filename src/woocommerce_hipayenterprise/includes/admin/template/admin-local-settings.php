<p>
    <?php
    echo sprintf(
        __('All other general HiPay settings can be adjusted <a href="%s">here</a>.', Hipay_Gateway_Abstract::TEXT_DOMAIN),
        admin_url('admin.php?page=wc-settings&tab=checkout&section=hipayenterprise_credit_card')
    );
    ?>
</p>
<p>
    <?php $this->generate_settings_html($this->local); ?>
</p>

<p>
    <?php
    echo sprintf(
        __('All other general HiPay settings can be adjusted <a href="%s">here</a>.', "hipayenterprise"),
        admin_url('admin.php?page=wc-settings&tab=checkout&section=hipayenterprise_credit_card')
    );
    ?>
</p>
<p>
    <?php $plugin->generate_settings_html($plugin->local); ?>
</p>

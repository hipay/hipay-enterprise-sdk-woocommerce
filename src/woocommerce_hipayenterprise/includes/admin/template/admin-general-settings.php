<div class="bg-primary-hipay col-md-12" id="version-plugin">
    <span class=""><strong><?php _e('Plugin Version', "hipayenterprise"); ?>:</strong></span> <?php echo $currentPluginVersion; ?>
</div>

<?php if (!empty($notifications)) : ?>
    <table class="wc_emails widefat" cellspacing="0">
        <tbody>
        <tr>
            <td class="wc-email-settings-table-status"><span class="dashicons dashicons-warning text-danger"></span></td>
            <td class="wc-email-settings-table-name"><?php _e('Notifications', "hipayenterprise"); ?></td>
            <td>
                <ul class="alert-notifications">
                <?php
                if (!empty($notifications)) {
                    foreach ($notifications as $notification) {
                        echo '<li class="text-danger">' . $notification . '</li>';
                    }
                }
                ?>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
<?php endif; ?>

<div class="" id="requirments-hipay">
<table class="wc_emails widefat" cellspacing="0">
    <tbody>
    <tr>
        <td class="wc-email-settings-table-status">
            <?php if ($curl_active): ?>
                <span class="dashicons dashicons-yes"></span>
            <?php else: ?>
                <span class="dashicons dashicons-no"></span>
            <?php endif; ?>
        </td>

        <td class="wc-email-settings-table-name"><?php _e('cURL Extension', "hipayenterprise"); ?></td>
        <td>
            <?php if (!$curl_active) {
                _e('Please install and activate cURL extension.', "hipayenterprise");
            } else {
                _e('cURL Extension is correcly installed.', "hipayenterprise");
            }
            ?>
        </td>
    </tr>

    <tr>
        <td class="wc-email-settings-table-status">
            <?php if ($simplexml_active) : ?>
                <span class="dashicons dashicons-yes"></span>
            <?php else: ?>
                <span class="dashicons dashicons-no"></span>
            <?php endif; ?>
        </td>
        <td class="wc-email-settings-table-name"><?php _e('SimpleXML Extension', "hipayenterprise"); ?></td>
        <td>
            <?php if (!$simplexml_active) {
                _e('Please install and activate SimpleXML Extension.', "hipayenterprise");
            } else {
                _e('SimpleXML Extension is correcly installed.', "hipayenterprise");
            }
            ?>
        </td>
    </tr>

    <tr>
        <td class="wc-email-settings-table-status">
                        <span class="dashicons <?php echo $https_active ? 'dashicons-lock' : 'dashicons-warning'; ?>"
                              <?php echo (!$https_active) ? 'style="color:orange"' : ''; ?>;></span>
        </td>
        <td class="wc-email-settings-table-name"><?php _e('SSL Certificate', "hipayenterprise"); ?></td>
        <td>
            <?php
            if (!$https_active) {
                _e('You need a SSL Certificate to process credit card paymets using HiPay.', "hipayenterprise");
            }
            ?>
        </td>
    </tr>

    <tr>
        <td class="wc-email-settings-table-status">
            <span class="dashicons dashicons-wordpress"></span>
        </td>
        <td class="wc-email-settings-table-name"><?php _e('Woocommerce REST API', "hipayenterprise"); ?></td>
        <td>
            <?php
            _e('Please ensure you have Woocommerce REST API activated.', "hipayenterprise");
            ?>
        </td>
    </tr>

    <?php if ($updateInformation && $updateInformation->updateInfo->remoteVersion !== $currentPluginVersion) : ?>
    <tr>
        <td class="wc-email-settings-table-status"><span class="dashicons dashicons-warning text-danger"></span></td>
        <td class="wc-email-settings-table-name"><?php _e('Module update', "hipayenterprise"); ?></td>
        <td>
            <?php
            echo sprintf(__("There is a new version of WooCommerce HiPay Enterprise available. <a href='%s'>View version %s details</a> or <a href='/wp-admin/update-core.php'>update now</a>.", "hipayenterprise"), $updateInformation->updateInfo->remoteUrl, $updateInformation->updateInfo->remoteVersion);
            ?>
        </td>
    </tr>
    <?php endif; ?>
    </tbody>
</table>
</div>

<div role="tabpanel" id="hipay-container-admin" class="tab col-md-12">
    <ul class="hipay-enterprise nav nav-tabs" role="tablist">
        <li role="presentation" class="">
            <a href="#accounts" id="accounts-tab" class="" data-toggle="tab">
                <i class="dashicons dashicons-admin-generic"></i>
                <?php _e("Plugin Settings", "hipayenterprise"); ?>
            </a>
        </li>
        <li role="presentation">
            <a href="#methods" id="methods-tab" class="" data-toggle="tab">
                <span class="dashicons dashicons-cart"></span>
                <?php _e("Payment Methods", "hipayenterprise"); ?>
            </a>
        </li>
        <li role="presentation">
            <a href="#fraud" id="fraud-tab" class="" data-toggle="tab">
                <span class="dashicons dashicons-warning"></span> <?php _e("Fraud", "hipayenterprise"); ?>
            </a>
        </li>
        <li role="presentation">
            <a href="#faqs" id="faqs-tab" class="" data-toggle="tab">
                <span class="dashicons dashicons-admin-comments"></span> <?php _e("FAQ", "hipayenterprise"); ?>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="accounts">
            <?php $this->generate_settings_html($this->account); ?>
        </div>
        <div role="tabpanel" id="methods" class="tab-pane">
            <?php $this->generate_settings_html($this->methods); ?>
        </div>
        <div role="tabpanel" id="fraud" class="tab-pane">
            <?php $this->generate_settings_html($this->fraud); ?>
        </div>
        <div role="tabpanel" id="faqs" class="tab-pane">
            <?php $this->generate_settings_html($this->faqs); ?>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        var url = location.href.replace(/\/$/, "");

        if (location.hash) {
            const hash = url.split("#");
            $('#hipay-container-admin a[href="#' + hash[1] + '"]').tab("show");
            url = location.href.replace(/\/#/, "#");
            history.replaceState(null, null, url);
            setTimeout(function () {
                $(window).scrollTop(0);
            }, 400);
        }

        $('a[data-toggle="tab"]').on("click", function () {
            var newUrl;
            const hash = $(this).attr("href");
            if (hash == "#home") {
                newUrl = url.split("#")[0];
            } else {
                newUrl = url.split("#")[0] + hash;
            }
            newUrl += "/";
            history.replaceState(null, null, newUrl);
        });
    });
</script>

<?php settings_errors(); ?>
<table class="wc_emails widefat" cellspacing="0">
    <tbody>
    <tr>
        <td class="wc-email-settings-table-status"><span class="dashicons dashicons-warning"></span></td>
        <td class="wc-email-settings-table-name"><?php _e('Notifications', "hipayenterprise"); ?></td>
        <td>
            <?php
            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    echo $notification;
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="wc-email-settings-table-status">
            <?php
            if ($curl_active) { ?>
                <span class="dashicons dashicons-yes"></span>
                <?php
            } else { ?>
                <span class="dashicons dashicons-no"></span>
                <?php
            } ?>
        </td>

        <td class="wc-email-settings-table-name"><?php _e('cURL Extension', "hipayenterprise"); ?></td>

        <td>
            <?php
            if (!$curl_active) {
                _e('Please install and activate cURL extension.', "hipayenterprise");
            } else {
                _e('cURL Extension is correcly installed.', "hipayenterprise");
            }

            ?>
        </td>
    </tr>

    <tr>
        <td class="wc-email-settings-table-status">
            <?php
            if ($simplexml_active) { ?>
                <span class="dashicons dashicons-yes"></span>
                <?php
            } else { ?>
                <span class="dashicons dashicons-no"></span>
                <?php
            } ?>
        </td>
        <td class="wc-email-settings-table-name"><?php _e('SimpleXML Extension', "hipayenterprise"); ?></td>
        <td>
            <?php
            if (!$simplexml_active) {
                _e('Please install and activate SimpleXML Extension.', "hipayenterprise");
            } else {
                _e('SimpleXML Extension is correcly installed.', "hipayenterprise");
            }
            ?>
        </td>
    </tr>

    <?php
    if ($this->sandbox == "yes") {
        ?>
        <tr>
            <td class="wc-email-settings-table-status">
                <span class="dashicons dashicons-warning" style="color:orange;"></span>
            </td>
            <td class="wc-email-settings-table-name"><?php _e('Mode TEST activated', "hipayenterprise"); ?></td>
            <td>
                <?php
                _e('This plugin is configured to use a Test Account.', "hipayenterprise");
                ?>
            </td>
        </tr>
        <?php
    }
    ?>
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
    </tbody>
</table>

<div role="tabpanel" class="col-md-12 hipay-container-admin">
    <ul class="hipay-enterprise nav nav-tabs nav-pills" role="tablist">
        <li role="presentation" class="active">
            <a href="#accounts" id="accounts-tab" class="" data-toggle="tab">
                <i class="dashicons dashicons-admin-generic"></i>
                <?php _e("Plugin Settings", "hipayenterprise"); ?>
            </a>
        </li>
        <li role="presentation">
            <a href="#methods" id="methods-tab" class="" data-toggle="tab">
                <span  class="dashicons dashicons-cart"></span>
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

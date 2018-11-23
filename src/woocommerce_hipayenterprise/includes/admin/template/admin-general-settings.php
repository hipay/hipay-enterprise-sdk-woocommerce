<?php settings_errors(); ?>
<table class="wc_emails widefat" cellspacing="0">
    <tbody>
    <tr>
        <td class="wc-email-settings-table-status"><span class="dashicons dashicons-warning"></span></td>
        <td class="wc-email-settings-table-name"><?php _e('Notifications', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
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

        <td class="wc-email-settings-table-name"><?php _e('cURL Extension', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>

        <td>
            <?php
            if (!$curl_active) {
                _e('Please install and activate cURL extension.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
            } else {
                _e('cURL Extension is correcly installed.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
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
        <td class="wc-email-settings-table-name"><?php _e('SimpleXML Extension', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
        <td>
            <?php
            if (!$simplexml_active) {
                _e('Please install and activate SimpleXML Extension.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
            } else {
                _e('SimpleXML Extension is correcly installed.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
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
            <td class="wc-email-settings-table-name"><?php _e('Mode TEST activated', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
            <td>
                <?php
                _e('This plugin is configured to use a Test Account.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
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
        <td class="wc-email-settings-table-name"><?php _e('SSL Certificate', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
        <td>
            <?php
            if (!$https_active) {
                _e('You need a SSL Certificate to process credit card paymets using HiPay.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
            }
            ?>
        </td>
    </tr>

    <tr>
        <td class="wc-email-settings-table-status">
            <span class="dashicons dashicons-wordpress"></span>
        </td>
        <td class="wc-email-settings-table-name"><?php _e('Woocommerce REST API', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
        <td>
            <?php
            _e('Please ensure you have Woocommerce REST API activated.', Hipay_Gateway_Abstract::TEXT_DOMAIN);
            ?>
        </td>
    </tr>


    </tbody>
</table>


<div class="wrap">

    <h2 class="nav-tab-wrapper">
        <a href="#accounts" id="accounts-tab" class="nav-tab hipayenterprise-tab" data-toggle="accounts"><i
                    class="dashicons dashicons-admin-generic"></i> <?php _e("Plugin Settings"); ?></a>
        <a href="#methods" id="methods-tab" class="nav-tab hipayenterprise-tab" data-toggle="methods"><span
                    class="dashicons dashicons-cart"></span> <?php _e("Payment Methods"); ?></a>
        <a href="#fraud" id="fraud-tab" class="nav-tab hipayenterprise-tab" data-toggle="fraud"><span
                    class="dashicons dashicons-warning"></span> <?php _e("Fraud"); ?></a>
        <a href="#faqs" id="faqs-tab" class="nav-tab hipayenterprise-tab" data-toggle="faqs"><span
                    class="dashicons dashicons-admin-comments"></span> <?php _e("FAQ"); ?></a>
    </h2>


    <div id="accounts" class="hidden hipayenterprise-tab-content">
        <?php
        $this->generate_settings_html($this->account);
        ?>
    </div>
    <div id="methods" class="hidden hipayenterprise-tab-content">
        <table class="form-table">
            <?php
            $this->generate_settings_html($this->methods);
            ?>
        </table>
    </div>
    <div id="fraud" class="hidden hipayenterprise-tab-content">
        <table class="form-table">
            <?php
            $this->generate_settings_html($this->fraud);
            ?>
        </table>
    </div>
    <div id="faqs" class="hidden hipayenterprise-tab-content">
        <table class="form-table">
            <?php
            $this->generate_settings_html($this->faqs);
            ?>
        </table>
    </div>
</div><!-- /.wrap -->

<script type="text/javascript">
    jQuery(function () {


        $hipayTab = window.location.hash;
        if ($hipayTab == "") {
            jQuery("#accounts").removeClass("hidden").show(111);
            jQuery("#accounts-tab").addClass("nav-tab-active");
        } else {
            jQuery($hipayTab).removeClass("hidden").show(111);
            jQuery($hipayTab + "-tab").addClass("nav-tab-active");
        }
        jQuery('.hipayenterprise-tab').click(function (event) {
            event.preventDefault();
            var tab = jQuery(this).attr('data-toggle');
            window.location.hash = tab;
            jQuery('.hipayenterprise-tab-content').hide();
            jQuery("#" + tab).removeClass("hidden").show(111);
            jQuery('.hipayenterprise-tab').removeClass("nav-tab-active");
            jQuery(this).addClass("nav-tab-active");
            return false;
        });
    });
</script>

<?php
/**
 * Created by PhpStorm.
 * User: nfillion
 * Date: 08/11/2018
 * Time: 12:33
 */


$confLocalPayment = $this->settings["woocommerce_hipayenterprise_methods_payments_local"];

?>

<tr valign="top">
    <th scope="row" class="">

        <?php
        $sel_btn = " local_payment_admin_menu_sel";
        foreach ($current_methods as $the_method) {
            if ((bool)$the_method->get_is_local_payment()) {
                echo "<div data-id='" . $the_method->get_key() . "' class='local_payment_admin_menu" . $sel_btn . "'>" . __($the_method->get_title(), 'hipayenterprise') . "<br></div>";
                $sel_btn = "";
            }
        }
        ?>

    </th>
    <td class="forminp" valign="top">
        <fieldset>

            <?php
            $sel_btn = "";
            foreach ($current_methods as $the_method) {
                if ((bool)$the_method->get_is_local_payment()) {
                    echo "<div data-id='" . $the_method->get_key() . "' class='local_payment_admin_config_" . $the_method->get_key() . " local_payment_admin_config" . $sel_btn . "'>";
                    echo "<b>" . __($the_method->get_title(), 'hipayenterprise') . "</b><hr>";
                    ?>
                    <table>

                        <tr valign="top">
                            <td align="right"><?php _e('Activated', 'hipayenterprise'); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e('Use Oneclick', 'hipayenterprise'); ?></span>
                                    </legend>
                                    <input class="" type="checkbox"
                                           name="woocommerce_hipayenterprise_methods_lp_activated[<?php echo $the_method->get_key(); ?>]"
                                           id="woocommerce_hipayenterprise_methods_lp_activated" style=""
                                           value="1" <?php if ($the_method->get_is_active()) echo 'checked="checked"'; ?>>
                                    <br>
                                </fieldset>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td valign="top"
                                align="right"><?php _e('Minimum order amount', 'hipayenterprise'); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e("Minimum order amount", 'hipayenterprise'); ?></span>
                                    </legend>
                                    <input class="input-text regular-input " type="text"
                                           name="woocommerce_hipayenterprise_methods_lp_min_amount[<?php echo $the_method->get_key(); ?>]"
                                           id="woocommerce_hipayenterprise_methods_lp_min_amount" style=""
                                           value="<?php echo $the_method->get_min_amount(); ?>"
                                           placeholder="">
                                </fieldset>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td align="right"><?php _e('Maximum order amount', 'hipayenterprise'); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e("Maximum order amount", 'hipayenterprise'); ?></span>
                                    </legend>
                                    <input class="input-text regular-input " type="text"
                                           name="woocommerce_hipayenterprise_methods_lp_max_amount[<?php echo $the_method->get_key(); ?>]"
                                           id="woocommerce_hipayenterprise_methods_lp_max_amount" style=""
                                           value="<?php echo $the_method->get_max_amount(); ?>"
                                           placeholder="">
                                </fieldset>
                            </td>
                        </tr>


                        <tr valign="top">
                            <td valign="top"
                                align="right"><?php _e('Currencies', 'hipayenterprise'); ?></td>

                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e("Currencies", 'hipayenterprise'); ?></span></legend>
                                    <?php
                                    $authorized_currencies = array();
                                    $available_currencies = array();
                                    if ($the_method->get_authorized_currencies() != "") $authorized_currencies = explode(",", $the_method->get_authorized_currencies());
                                    if ($the_method->get_available_currencies() != "") $available_currencies = explode(",", $the_method->get_available_currencies());
                                    if (is_array($currencies_details["woocommerce_hipayenterprise_currencies_active"])) {
                                        foreach ($currencies_details["woocommerce_hipayenterprise_currencies_active"] as $keyc => $valuec) {
                                            if (empty($authorized_currencies) || array_search($keyc, $authorized_currencies) !== false) {
                                                echo '<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_lp_currencies[' . $the_method->get_key() . '][' . $keyc . ']" id="woocommerce_hipayenterprise_methods_lp_currencies" style="" value="1"';
                                                if (array_search($keyc, $available_currencies) !== false)
                                                    echo ' checked="checked"';
                                                echo "><span style='padding-right:18px;'>" . $list_of_currencies[$keyc] . "</span>";
                                            }
                                        }
                                    }
                                    ?>
                                </fieldset>
                            </td>

                        </tr>

                        <tr valign="top">
                            <td valign="top" align="right"
                                style='vertical-align:top;'><?php _e('Countries', 'hipayenterprise'); ?></td>

                            <td class="forminp">
                                <fieldset>
                                    <div style="float:left;">
                                        <span><?php _e("Available Countries", 'hipayenterprise'); ?></span><br>
                                        <select multiple
                                                class="input-text woocommerce_hipayenterprise_methods_lp_countries regular-input woocommerce_hipayenterprise_methods_lp_countries_<?php echo $the_method->get_key(); ?>"
                                                name="woocommerce_hipayenterprise_methods_lp_countries[<?php echo $the_method->get_key(); ?>]"
                                                id="woocommerce_hipayenterprise_methods_lp_countries[<?php echo $the_method->get_key(); ?>]">
                                            <?php
                                            $authorized_countries_list = array();
                                            $authorized_countries = array();
                                            $available_countries = array();
                                            if ($the_method->get_authorized_countries() != "") $authorized_countries = explode(",", $the_method->get_authorized_countries());
                                            if ($the_method->get_available_countries() != "") $available_countries = explode(",", $the_method->get_available_countries());

                                            $countries_wc = new WC_Countries();
                                            $countries = $countries_wc->__get('countries');

                                            foreach ($countries as $keycc => $valuecc) {
                                                if (empty($authorized_countries) || array_search($keycc, $authorized_countries) !== false) {
                                                    if (array_search($keycc, $available_countries) !== false)
                                                        $authorized_countries_list[$keycc] = $valuecc;
                                                    else
                                                        echo "<option value='" . $keycc . "'>" . $valuecc . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div
                                            style='float:left;margin:0 30px;vertical-align:middle;padding-top:30px;'>
                                        <div
                                                class="dashicons dashicons-controls-forward is_pointer add_country_lp"
                                                data-id="<?php echo $the_method->get_key(); ?>"></div>
                                        <br>
                                        <div
                                                class="dashicons dashicons-controls-back is_pointer rem_country_lp"
                                                data-id="<?php echo $the_method->get_key(); ?>"></div>
                                    </div>
                                    <div style="float:left;">
                                        <span><?php _e("Authorized Countries", 'hipayenterprise'); ?></span><br>
                                        <select multiple
                                                class="input-text woocommerce_hipayenterprise_methods_lp_countries_available regular-input woocommerce_hipayenterprise_methods_lp_countries_available_<?php echo $the_method->get_key(); ?>"
                                                name="woocommerce_hipayenterprise_methods_lp_countries_available[<?php echo $the_method->get_key(); ?>]"
                                                id="woocommerce_hipayenterprise_methods_lp_countries_available[<?php echo $the_method->get_key(); ?>]">
                                            <?php
                                            $input_countries_list = "";
                                            foreach ($authorized_countries_list as $keycc => $valuecc) {
                                                echo "<option value='" . $keycc . "'>" . $valuecc . "</option>";
                                                $input_countries_list .= $keycc . ",";
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden"
                                               class="woocommerce_hipayenterprise_methods_lp_countries_available_list<?php echo $the_method->get_key(); ?>"
                                               id="woocommerce_hipayenterprise_methods_lp_countries_available_list[<?php echo $the_method->get_key(); ?>]"
                                               name="woocommerce_hipayenterprise_methods_lp_countries_available_list[<?php echo $the_method->get_key(); ?>]"
                                               value="<?php echo $input_countries_list; ?>">
                                    </div>
                                </fieldset>
                            </td>

                        </tr>


                    </table>
                    <?php
                    echo "</div>";
                    $sel_btn = " hidden";
                }
            }
            ?>

        </fieldset>
    </td>
</tr>


<script type="text/javascript">
    jQuery(function () {

        jQuery('.add_country_lp').click(function () {
            $id = jQuery(this).attr("data-id");

            jQuery('.woocommerce_hipayenterprise_methods_lp_countries_' + $id + ' :selected').each(function () {
                jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id).val(jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id).val() + jQuery(this).val() + ",");
            });

            jQuery('.woocommerce_hipayenterprise_methods_lp_countries_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_lp_countries_available_' + $id).removeAttr('selected');
            //add to list
            return false;
        });

        jQuery('.rem_country_lp').click(function () {
            $id = jQuery(this).attr("data-id");

            jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_' + $id + ' :selected').each(function () {
                $countries_list = jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id).val();
                $countries_list = $countries_list.replace(jQuery(this).val() + ",", "");
                $countries_list = jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id).val($countries_list);
            });

            jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_lp_countries_' + $id).removeAttr('selected');
            return false;
        });

        jQuery('.local_payment_admin_menu').click(function () {
            $id = jQuery(this).attr("data-id");
            jQuery('.local_payment_admin_menu').removeClass("local_payment_admin_menu_sel");
            jQuery(this).addClass("local_payment_admin_menu_sel");
            jQuery('.local_payment_admin_config').addClass("hidden");
            jQuery('.local_payment_admin_config_' + $id).removeClass("hidden");
            return false;
        });


    });
</script>


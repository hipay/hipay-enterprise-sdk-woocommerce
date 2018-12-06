<div class="hipay-container">
    <h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
        <i class='dashicons dashicons-admin-network'></i>
        <?php _e('Delivery method mapping', "hipayenterprise"); ?>
    </h3>
    <div class="row" id="error-messages">
        <?php Hipay_Mapping_Delivery_Controller::show_messages() ?>
    </div>
    <form method="POST" class="form-horizontal col-md-12"
          action="<?php echo add_query_arg(array('page' => $current_page), admin_url('admin.php')) ?>">
        <div class="alert alert-info col-md-12">
            <p><?php echo __("You must define your delivery methods with those of HiPay.", "hipay_enterprise"); ?></p>
            <p><?php echo __("Mapping of delivery methods is mandatory for Oney payment methods or if you enable the 'Send Customer Cart' option.", "hipay_enterprise"); ?></p>
        </div>
        <div class="panel">
            <div class="form-wrapper">
                <table class="table table-striped">
                    <thead>
                    <th><?php echo __('Woocommerce delivery method', "hipay_enterprise"); ?></th>
                    <th><?php echo __("Order preparation estimated time", "hipay_enterprise"); ?></th>
                    <th><?php echo __('Delivery estimated time', "hipay_enterprise"); ?></th>
                    <th><?php echo __('HiPay delivery mode', "hipay_enterprise"); ?></th>
                    <th><?php echo __('HiPay delivery method', "hipay_enterprise"); ?></th>
                    </thead>
                    <tbody>
                    <?php foreach ($wcDeliveryMethods as $wcDeliveryMethod): ?>
                        <tr>
                            <td>
                                <?php if (isset($mappedDelivery[$wcDeliveryMethod->id])) : ?>
                                    <input type="hidden"
                                           value="<?php echo $mappedDelivery[$wcDeliveryMethod->id]["idPost"] ?>"
                                           name="wc_map_<?php echo $wcDeliveryMethod->id; ?>"/>
                                <?php endif; ?>
                                <?php echo $wcDeliveryMethod->method_title; ?>
                            </td>
                            <td>
                                <input type="text"
                                       class="form-type decimal-input"
                                       value="<?php if (isset($mappedDelivery[$wcDeliveryMethod->id])) {
                                           echo $mappedDelivery[$wcDeliveryMethod->id][Hipay_Mapping_Delivery_Controller::ORDER_PREPARATION];
                                       } ?>"
                                       name="mapping_order_preparation_<?php echo $wcDeliveryMethod->id; ?>"/>
                            </td>
                            <td>
                                <input type="text"
                                       id="decimal-input"
                                       class="form-type decimal-input"
                                       value="<?php if (isset($mappedDelivery[$wcDeliveryMethod->id])) {
                                           echo $mappedDelivery[$wcDeliveryMethod->id][Hipay_Mapping_Delivery_Controller::DELIVERY_ESTIMATED];
                                       } ?>"
                                       name="mapping_delivery_estimated_<?php echo $wcDeliveryMethod->id; ?>"/>
                            </td>
                            <td>
                                <select name="mapping_mode_<?php echo $wcDeliveryMethod->id; ?>">
                                    <option value=""><?php echo __(" - Select carrier mode - ", "hipay_enterprise") ?></option>
                                    <?php foreach ($hipayCarriers["mode"] as $mode): ?>
                                        <option value="<?php echo $mode->getCode(); ?>"
                                            <?php if (isset($mappedDelivery[$wcDeliveryMethod->id]) &&
                                                $mappedDelivery[$wcDeliveryMethod->id][Hipay_Mapping_Delivery_Controller::MODE] == $mode->getCode()): ?>
                                                selected
                                            <?php endif; ?>
                                        ><?php echo $mode->getDisplayName("fr") ?></option>
                                    <? endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="mapping_shipping_<?php echo $wcDeliveryMethod->id; ?>">
                                    <option value=""><?php echo __(" - Select carrier shipping - ", "hipay_enterprise") ?></option>
                                    <?php foreach ($hipayCarriers["shipping"] as $shipping): ?>
                                        <option
                                            <?php if (isset($mappedDelivery[$wcDeliveryMethod->id]) &&
                                                $mappedDelivery[$wcDeliveryMethod->id][Hipay_Mapping_Delivery_Controller::SHIPPING] == $shipping->getCode()): ?>
                                                selected
                                            <?php endif; ?>
                                                value="<?php echo $shipping->getCode(); ?>"><?php echo $shipping->getDisplayName("fr") ?></option>
                                    <? endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <button name="save" class="button-primary" type="submit"
                        value="Save changes"><?php echo __('Save changes', "hipayenterprise"); ?></button>
            </div>
        </div>
    </form>
</div>



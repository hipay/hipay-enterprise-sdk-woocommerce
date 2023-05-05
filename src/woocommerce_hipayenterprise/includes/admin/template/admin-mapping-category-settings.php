<div class="hipay-container">
    <h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
        <i class='dashicons dashicons-admin-network'></i>
        <?php _e('Mapping Category', "hipayenterprise"); ?>
    </h3>
    <div class="row" id="error-messages">
        <?php Hipay_Mapping_Category_Controller::show_messages() ?>
    </div>
    <form method="POST" class="form-horizontal col-md-12"
          action="<?php echo add_query_arg(array('page' => $current_page), admin_url('admin.php')) ?>">
        <div class="alert alert-info col-md-12">
            <p><?php echo __("You must define your product categories with those of HiPay.", "hipayenterprise"); ?></p>
            <p><?php echo __("Mapping of categories is mandatory for Oney payment methods or if you enable the 'Send Customer Cart' option.", "hipayenterprise"); ?></p>
        </div>
        <div class="panel">
            <div class="form-wrapper">
                <table class="table table-striped">
                    <thead>
                    <th><?php echo __('Woocommerce Product Category', "hipayenterprise"); ?></th>
                    <th><?php echo __('HiPay Product Category', "hipayenterprise"); ?></th>
                    </thead>
                    <tbody>
                    <?php foreach ($wcCategories as $wcCategory): ?>
                        <tr>
                            <td>
                                <?php if (isset($mappedCategories[$wcCategory->term_id])) : ?>
                                    <input type="hidden"
                                           value="<?php echo $mappedCategories[$wcCategory->term_id]["idPost"] ?>"
                                           name="wc_map_<?php echo $wcCategory->term_id; ?>"/>
                                <?php endif; ?>
                                <?php echo $wcCategory->name; ?>
                            </td>
                            <td>
                                <select class="form-control" name="hipay_map_<?php echo $wcCategory->term_id; ?>">
                                    <?php if (!isset($mappedCategories[$wcCategory->term_id])): ?>
                                        <option value="">-<?php echo __('Select Category', "hipayenterprise"); ?>-</option>
                                    <?php endif; ?>
                                    <?php foreach ($hipayCategories as $category): ?>
                                        <option value="<?php echo $category->getCode(); ?>"
                                            <?php if (isset($mappedCategories[$wcCategory->term_id]) && $mappedCategories[$wcCategory->term_id]["idHipayCategory"] == $category->getCode()): ?>
                                                selected
                                            <?php endif; ?>
                                        ><?php echo $category->getLocal('fr'); ?></option>
                                    <?php endforeach; ?>
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



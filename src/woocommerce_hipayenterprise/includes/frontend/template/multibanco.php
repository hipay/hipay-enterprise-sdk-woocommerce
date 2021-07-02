<table class="woocommerce-table woocommerce-table-hipay-multibanco" style="margin: 0 0 1.41575em;">
    <thead>
        <tr>
            <th colspan="3" class="woocommerce-table-hipay-title">
                <?php _e("Pay the reference at an ATM or with your Homebanking account.")?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="3" class="woocommerce-table-hipay-field"><img style="max-width: 200px;align-content: center"
                    id="multibanco-hipay" alt="multibanco" src="<?php echo $logo;?>" /></th>
        </tr>
        <tr>
            <th class="woocommerce-table-hipay-field"><?php _e("ENTITY")?>:</th>
            <th class="third-column"><?php echo $entity;?></th>
        </tr>
        <tr>
            <th class="woocommerce-table-hipay-field"><?php _e("REFERENCE")?>:</th>
            <th class="woocommerce-table-hipay-field-value"><?php echo $reference;?></th>
        </tr>
        <tr>
            <th class="woocommerce-table-hipay-field"><?php _e("AMOUNT")?>:</th>
            <th class="woocommerce-table-hipay-field-value"><?php echo $amount;?> &euro;</th>
        </tr>
        <tr>
            <th class="woocommerce-table-hipay-field"><?php _e("EXPIRATION DATE")?>:</th>
            <th class="woocommerce-table-hipay-field-value"><?php echo $expirationDate;?></th>
        </tr>
    </tbody>
</table>

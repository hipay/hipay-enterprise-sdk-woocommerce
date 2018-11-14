<table class="wc_emails widefat" cellspacing="0">
    <tbody>
    <tr>
        <th class="wc-email-settings-table-name"><?php echo __("TYPE", "hipayenterprise"); ?></th>
        <th class="wc-email-settings-table-name"><?php echo __("ORDER", "hipayenterprise"); ?></th>
        <th class="wc-email-settings-table-name"><?php echo __("DATE", "hipayenterprise"); ?></th>
        <th class="wc-email-settings-table-name"><?php echo __("DESCRIPTION", "hipayenterprise"); ?></th>
    </tr>

    <?php
    //$logs_list = $wpdb->get_results("SELECT id,create_date,log_desc,order_id,type FROM $this->plugin_table_logs ORDER BY id DESC LIMIT 100");
    foreach ($logs_list as $value) {
        ?>
        <tr>
            <td><?php echo $value->type; ?></td>
            <td><?php echo $value->order_id; ?></td>
            <td><?php echo $value->create_date; ?></td>
            <td><?php echo $value->log_desc; ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>


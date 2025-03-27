<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 650px; margin: 0 auto; background-color: #ffffff;">
    <tr>
        <td style="padding: 20px;">
            <!-- HiPay ID -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="padding-bottom: 0;margin-bottom: 0">
                <tr>
                    <td style="padding-bottom: 0; font-size: 16px; color: #333333;text-align: center;">
                        <?php echo __('HiPay ID', "hipayenterprise")?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0; font-size: 36px; font-weight: bold; color: #000000; text-align: center">
                        <?php echo esc_html($reference); ?>
                    </td>
                </tr>
            </table>

            <!-- Divider -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 0">
                <tr>
                    <td width="35%" style="padding: 0"></td>
                    <td width="30%" style="padding: 0; height: 1px; line-height: 1px; font-size: 1px; border-bottom: 1px solid #e0e0e0;"></td>
                    <td width="35%" style="padding: 0"></td>
                </tr>
            </table>

            <!-- Barcode -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding: 0">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 0">
                            <tr>
                                <td width="15%"></td>
                                <td width="70%" align="center" style="padding: 10px 0 0 0">
                                    <img src="<?php echo $barCodeImg ?>" alt="Payment Barcode" style="display: block; margin: 0 auto; max-width: 300px; width: 100%;" />
                                </td>
                                <td width="15%"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 0; font-size: 16px; color: #333333; text-align: center;">
                        373680<?php echo esc_html($reference); ?>36
                    </td>
                </tr>
            </table>

            <!-- Payment Information -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding-bottom: 0; font-size: 16px; line-height: 1.5; color: #000000;">
                        <?php echo __('You have chosen cash payment with Mooney.', "hipayenterprise")?>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 0; font-size: 16px; line-height: 1.5; color: #000000;">
                        <?php echo __('The operation will have an additional cost of €2.00 as a collection fee to be paid at the cash desk.', "hipayenterprise")?>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 20px; font-size: 16px; line-height: 1.5; color: #000000;">
                        <?php echo __('You will receive an e-mail summarising your purchase, in which you will find the modalities for making the cash payment at Mooney points.', "hipayenterprise")?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
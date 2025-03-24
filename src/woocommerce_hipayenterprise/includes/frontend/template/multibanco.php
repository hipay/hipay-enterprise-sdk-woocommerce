<style>
    .multibanco-tpl table {
        margin-bottom: 0;
    }
</style>
<div class="multibanco-tpl">
    <table cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 400px; border-collapse: collapse;">
        <!-- Logo -->
        <tr>
            <td style="padding: 15px 10px 15px 0;">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="width: 50%; color: #8a7d63; font-size: 13px; font-weight: bold; vertical-align: top;">
                            <img src="https://support.hipay.com/hc/article_attachments/21021176698898" alt="MULTIBANCO" style="max-width: 80px;"></td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Entity -->
        <tr>
            <td style="padding: 5px 0;">
                <table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; color: #8a7d63; font-size: 13px; font-weight: bold; vertical-align: top;">ENTITY:</td>
                        <td style="color: #333333; font-size: 14px; font-weight: bold; padding-left: 15px;"><?php echo $entity ?></td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Reference -->
        <tr>
            <td style="padding: 5px 0;">
                <table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; color: #8a7d63; font-size: 13px; font-weight: bold; vertical-align: top;">REFERENCE:</td>
                        <td style="color: #333333; font-size: 14px; font-weight: bold; padding-left: 15px;"><?php echo $reference ?></td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Amount -->
        <tr>
            <td style="padding: 5px 0;">
                <table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; color: #8a7d63; font-size: 13px; font-weight: bold; vertical-align: top;">AMOUNT:</td>
                        <td style="color: #333333; font-size: 14px; font-weight: bold; padding-left: 15px;"><?php echo $amount ?> €</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Expiration Date -->
        <tr>
            <td style="padding: 5px 0;">
                <table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
                    <tr>
                        <td style="width: 50%; color: #8a7d63; font-size: 13px; font-weight: bold; vertical-align: top;">EXPIRATION DATE:</td>
                        <td style="color: #333333; font-size: 14px; font-weight: bold; padding-left: 15px;"><?php echo $expirationDate ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
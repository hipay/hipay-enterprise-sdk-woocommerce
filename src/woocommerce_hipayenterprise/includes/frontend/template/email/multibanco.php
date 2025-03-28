
<style type="text/css">
    #multibancoTemplate {
        padding: 0;
        margin: 0 0 20px;
    }
    #multibancoTemplate table, #multibancoTemplate td, #multibancoTemplate div, #multibancoTemplate p {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }
    #multibancoTemplate .multibanco-container {
        width: 786.41px;
        height: 380px;
        margin: 0 auto;
        padding: 0;
        background-color: #f8f8f8;
    }
    #multibancoTemplate .logo-container {
        text-align: center;
        padding: 20px 0 10px 0;
        vertical-align: middle;
    }
    #multibancoTemplate .logo-img {
        max-width: 100px;
        height: auto;
        margin: 0 auto;
        display: block;
    }
    #multibancoTemplate .data-row {
        padding: 4px 0;
    }
    #multibancoTemplate .label {
        font-weight: normal;
        font-size: 22px;
        color: #333;
        text-align: right;
        padding-right: 20px;
        vertical-align: middle;
    }
    #multibancoTemplate .value {
        font-weight: normal;
        font-size: 22px;
        color: #000;
        text-align: left;
        padding-left: 20px;
        vertical-align: middle;
    }
    #multibancoTemplate .footer-text {
        padding: 0 80px 20px 80px;
        font-size: 16px;
        color: #333;
        line-height: 1.5;
        text-align: center;
    }
    #multibancoTemplate table:not( .has-background ) tbody td {
        background-color: inherit;
    }
</style>

<div id="multibancoTemplate">
    <table class="multibanco-container" width="786.41" height="380" cellpadding="0" cellspacing="0" border="0">
        <!-- Logo row -->
        <tr>
            <td class="logo-container" align="center" valign="middle">
                <img src="<?php echo plugins_url('/assets/images/multibanco.png', WC_HIPAYENTERPRISE_BASE_FILE)?>" alt="MB MULTIBANCO" class="logo-img">
            </td>
        </tr>

        <!-- Data rows -->
        <tr>
            <td>
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <!-- Entity row -->
                    <tr>
                        <td class="data-row">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="label" width="50%" align="right"><?php echo __('Entity:', "hipayenterprise")?></td>
                                    <td class="value" width="50%" align="left"><?php echo $entity ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Reference row -->
                    <tr>
                        <td class="data-row">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="label" width="50%" align="right"><?php echo __('Reference:', "hipayenterprise")?></td>
                                    <td class="value" width="50%" align="left"><?php echo $reference ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Amount row -->
                    <tr>
                        <td class="data-row">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="label" width="50%" align="right"><?php echo __('Amount:', "hipayenterprise")?></td>
                                    <td class="value" width="50%" align="left"><?php echo $amount ?> €</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Expiration date row -->
                    <tr>
                        <td class="data-row">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="label" width="50%" align="right"><?php echo __('Expiration Date:', "hipayenterprise")?></td>
                                    <td class="value" width="50%" align="left"><?php echo $expirationDate ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Footer text -->
        <tr>
            <td class="footer-text">

                <?php echo __("To pay a Multibanco Reference on your e-banking or at an ATM, choose 'payments', then 'services'", "hipayenterprise")?>
            </td>
        </tr>
    </table>
</div>
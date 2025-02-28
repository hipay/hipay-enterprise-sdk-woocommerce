<div style="background-color: #f8f8f8; padding:24px; margin-bottom: 24px">
    <div id="referenceToPay"></div>
    <script type="text/javascript">
        var hipaySdk = new HiPay({
            username: 'hosted',
            password: 'hosted',
            environment: 'production',
            lang: '<?php echo substr(get_locale(), 0, 2) ?>'
        });
        hipaySdk.createReference('multibanco', {
            selector: 'referenceToPay',
            reference: '<?php echo $reference ?>',
            entity: '<?php echo $entity ?>',
            amount: '<?php echo $amount ?>',
            expirationDate: '<?php echo $expirationDate ?>',
        });
    </script>
</div>

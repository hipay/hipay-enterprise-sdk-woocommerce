<div style="background-color: #f8f8f8; padding:24px; margin-bottom: 24px">
    <div id="referenceToPay"></div>
    <script src='<?php echo $sdkJsUrl ?>'></script>
    <script type="text/javascript">
        var hipaySdk = new HiPay({
            username: 'hosted',
            password: 'hosted',
            environment: 'production',
            lang: '<?php substr(get_locale(), 0, 2) ?>'
        });
        hipaySdk.createReference('sisal', {
            selector: 'referenceToPay',
            reference: '<?php echo $reference ?>',
            barCode: '<?php echo $barCode ?>',
        });
    </script>
</div>
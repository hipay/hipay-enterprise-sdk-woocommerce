<?php foreach ($additionalFields['formFields'] as $fieldName => $field): ?>
    <label> <?php echo _e($field["label"]["en"], "hipayenterprise"); ?></label>
    <div>
        <?php if($field["type"] === "gender"): ?>
            <div >
                <select id="<?php echo $localPaymentName . '-' . $fieldName ?>"
                        name="<?php echo $localPaymentName . '-' . $fieldName ?>"
                        class="<?php echo$localPaymentName ?>"
                    <?php if(isset($field["required"]) && $field["required"]){ echo 'required';} ?>
                >
                    <option value="M"><?php echo _e('Mr', "hipayenterprise") ?></option>
                    <option value="F"><?php echo _e('Mrs', "hipayenterprise") ?></option>
                </select>
            </div>
        <?php else: ?>
        <input
                id="<?php echo $localPaymentName . '-' . $fieldName ?>" name="<?php echo $localPaymentName . '-' . $fieldName ?>" type="text" value=""
                <?php if(isset($field["required"]) && $field["required"]){ echo 'required';}  ?>
                class="<?php echo$localPaymentName ?>"
        />
        <?php endif; ?>
    </div>
    <script>
        hiPayInputControl.addInput('<?php echo $localPaymentName ?>', '<?php echo $localPaymentName."-".$fieldName ?>', '<?php echo $field["controlType"] ?>', <?php if(isset($field['required'])){ echo $field['required']; }else{ echo "false";} ?>);
    </script>
<?php endforeach; ?>
<div class="msg-local-payment">
    <?php
        if($localPaymentName !== "sdd"){
            _e(
                'You will be redirected to an external payment page. Please do not refresh the page during the process.',
                 "hipayenterprise"
            );
        }
    ?>
</div>

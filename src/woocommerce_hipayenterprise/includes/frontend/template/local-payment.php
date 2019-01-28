<?php foreach ($additionalFields['formFields'] as $fieldName => $field): ?>
    <label class="hipay-row" > <?php echo $field["label"]["fr"]; ?></label>
    <div class="hipay-row">
        <input
                id="<?php echo $localPaymentName . '-' . $fieldName ?>" name="<?php echo $localPaymentName . '-' . $fieldName ?>" type="text" value=""
                <?php if(isset($field["required"]) && $field["required"]){ echo 'required';} ?> class="hipay-row"
        />
    </div>
<?php endforeach; ?>

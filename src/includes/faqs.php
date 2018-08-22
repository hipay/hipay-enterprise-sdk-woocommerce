<?php 
$faqs =  array();
$faq = new stdClass;
$faq->question 	= "HOW DO I GET MY HIPAY API CREDENTIALS ?";
$faq->answer	 	= "You need to generate <b>API credentials</b> to send requests to the HiPay Enterprise platform. To do so, go to the \"Integration\" section of your HiPay Enterprise back office, then to \"Security Settings\".
<br><br>
To be sure that your credentials have the proper accessibility:<br><br>

- Scroll down to \"Api credentials\". - Click on the edit icon next to the credentials you want to use.
<br><br>
<b>Private credentials</b>
<br><br>
Your credentials must be granted to:<br>
<b>Order</b>
<br><br>
&bull; Create a payment page<br>
&bull; Process an order through the API<br>
&bull; Get transaction informations
<br><br>
<b>Maintenance</b>
<br><br>
&bull; Capture<br>
&bull; Refund<br>
&bull; Accept/Deny<br>
&bull; Cancel<br>
&bull; Finalize
<br><br>
<b>Public credentials</b>
<br><br>
Your credentials must be granted to:<br>
<b>Tokenization</b>
<br><br>
&bull; Tokenize a card<br>
<br>
<b>Order</b><br>
<br>
&bull; Get transaction details with public credentials<br>
&bull; Process an order through the API with public credentials<br>
&bull; Create a payment page with public credentials";
array_push($faqs, $faq);

$faq = new stdClass;
$faq->question 	= "HOW DO I FILL IN THE API IDS IN THE PLUGIN ?";
$faq->answer	 	= "In the plugin configuration, go to “Modules > Modules & Services”. In the HiPay Enterprise configuration, click on the “Module Settings” tab.
<br><br>
If your module is in Test mode, you can specify the IDs in the Test area. If it is in Production mode, do the same in the Production area.
<br><br>
Enter the corresponding username, password and secret passphrase.
<br><br>
Public credentials are mandatory if you do not use the payment form hosted by HiPay. After specifying these identifiers, make a test payment to check that they are valid and that they have the proper rights.";
array_push($faqs, $faq);

$faq = new stdClass;
$faq->question 	= "WHY DO ORDERS NEVER REACH THE “ACCEPTED PAYMENT” STATUS?";
$faq->answer	 	= "First, check if the notification URL is correctly entered in your HiPay Enterprise back office. In the configuration of the PrestaShop module, retrieve the callback URL in the \"Module Settings\" tab.
<br><br>
Then, in your HiPay Enterprise back office, in the \"Integration\" section , click on \"Notifications\".
<br><br>
Notification URL: http: // www.[Your-domain.com] /index.php?fc=module&module=hipay_enterprise&controller=notify
Request method: HTTP POST
I want to be notified for the following transaction statuses: ALL
Then make a test payment. From the \"Notifications\" section of your HiPay Enterprise back office, in the transaction details, you can also check the status of the call.
<br><br>
If notifications are sent, there may be an internal module error. To check if an error occurred during the notification, check the hipay-error and hipay-callback logs.";
array_push($faqs, $faq);

$faq = new stdClass;
$faq->question 	= "WHAT TO DO WHEN PAYMENT ERRORS OCCUR ?";
$faq->answer	 	= "&bull; Make sure that your credentials are correctly set and that the module is in the mode you want (Test or Production).<br>
&bull; Check that the related payment methods are activated in your contract(s).<br>
&bull; Check the version of the installed module, and upgrade the module if the version is old.<br>
&bull; Check HiPay logs to see if any errors appear. Then send these logs to the HiPay Support team.<br>
&bull; Check that your servers are not behind a proxy. If so, provide the proxy information in the module configuration.";
array_push($faqs, $faq);

$faq = new stdClass;
$faq->question 	= "HOW COME MY PAYMENT METHOD(S) DO(ES) NOT APPEAR IN THE ORDER FUNNEL ?";
$faq->answer	 	= "&bull; Check that the HiPay Enterprise module is properly set up with your currencies and your carriers in the “Improve > Payment > Preference” menu. When adding a carrier or a currency, you should always activate them in this setup screen.<br>
&bull; Check in the HiPay module configuration that the payment method(s) is/are enabled for test countries and currencies.";
array_push($faqs, $faq);


$faq = new stdClass;
$faq->question 	= "HOW CAN I DO A MANUAL CAPTURE AND REFUND?";
$faq->answer	 	= "If your plugin is configured as \"Capture: Manual\", you must make captures manually.<br>
Two possibilities are offered to you: either from your HiPay Enterprise back office, or directly from the order form on your Woocommerce site.";
array_push($faqs, $faq);


$faq = new stdClass;
foreach ($faqs as $key => $faq) {	?>
	<tr valign="top">
		<td scope="row" class="wc-email-settings-table-name">
			<hr>
			<?php _e( $faq->question, 'hipayenterprise' ); ?> <span class="dashicons dashicons-arrow-down-alt2 hipayenterprise_faq_down" data-toggle="<?php echo $key;?>" style="cursor:pointer;" id="hipayenterprise_faq_down_<?php echo $key;?>"></span><i class="dashicons dashicons-arrow-up-alt2 hipayenterprise_faq_up hidden" data-toggle="<?php echo $key;?>" style="cursor:pointer;" id="hipayenterprise_faq_up_<?php echo $key;?>" style="cursor:pointer;"></i>
			<div id="message_<?php echo $key;?>" class="updated woocommerce-message inline hidden" >
				<p class="description"><?php echo nl2br(_e($faq->answer,'hipayenterprise'));?></p><p><hr></p>
			</div>
		</td>
	</tr>

<?php
}	


?>

<script type="text/javascript">
	jQuery(function() {

		jQuery('.hipayenterprise_faq_down').click( function(){
			$hpe_down_id = jQuery(this).attr("data-toggle");
			jQuery(this).hide();
			jQuery("#hipayenterprise_faq_up_"+$hpe_down_id).removeClass("hidden").show();
			jQuery("#message_"+$hpe_down_id).removeClass("hidden").show();
			return false;
		});

		jQuery('.hipayenterprise_faq_up').click( function(){
			$hpe_up_id = jQuery(this).attr("data-toggle");
			jQuery(this).hide();
			jQuery("#hipayenterprise_faq_down_"+$hpe_up_id).show();
			jQuery("#message_"+$hpe_up_id).hide();
			return false;
		});

	});
</script>

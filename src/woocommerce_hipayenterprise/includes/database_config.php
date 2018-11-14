<?php 
global $wpdb;
	
if($wpdb->get_var("show tables like '$this->plugin_table'") != $this->plugin_table)
{
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $this->plugin_table (
	  `id` bigint(20) NOT NULL AUTO_INCREMENT,
	  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `reference` varchar(150) NULL DEFAULT '0',
	  `order_id` bigint(20) NOT NULL,
	  `processed_date` datetime NOT NULL,
	  `amount` varchar(10) NOT NULL,
	  `status` varchar(7) NOT NULL,
	  `operation` varchar(100) NOT NULL,
	  `processed` tinyint(4) NOT NULL DEFAULT '0',
  	  `refunded` tinyint(4) NOT NULL DEFAULT '0',
      `captured` tinyint(4) NOT NULL DEFAULT '0',
      `stocks` tinyint(4) NOT NULL DEFAULT '0',
      `url` varchar(1024) DEFAULT NULL,
	  `additionalInfo` varchar(150) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

} 


$has_entity_column = false;
$table_name = $this->plugin_table;
$fivesdrafts = $wpdb->get_results( "DESCRIBE $table_name");
foreach($fivesdrafts as $fivesdraft){
	if ($fivesdraft->Field == "additionalInfo"){
		$has_entity_column = true;
		break;
	}
}
if (!$has_entity_column){
    $t = $wpdb->get_results("ALTER TABLE $table_name ADD COLUMN additionalInfo varchar(150) NOT NULL;");
}


if($wpdb->get_var("show tables like '$this->plugin_table_logs'") != $this->plugin_table_logs)
{
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $this->plugin_table_logs (
	  `id` bigint(20) NOT NULL AUTO_INCREMENT,
	  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `log_desc` varchar(2222) NOT NULL,
	  `order_id` bigint(20) NOT NULL,
	  `type` varchar(22) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

} 


if($wpdb->get_var("show tables like '$this->plugin_table_token'") != $this->plugin_table_token)
{
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $this->plugin_table_token (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(45) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `pan` varchar(20) NOT NULL,
  `card_holder` varchar(255) NOT NULL,
  `card_expiry_month` int(2) UNSIGNED NOT NULL,
  `card_expiry_year` int(4) UNSIGNED NOT NULL,
  `issuer` varchar(512) NOT NULL,
  `country` varchar(15) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

} 


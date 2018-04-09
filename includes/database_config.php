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
	UNIQUE KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

} 

if($wpdb->get_var("show tables like '$this->plugin_table'") != $this->plugin_table_logs)
{
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $this->plugin_table_logs (
	  `id` bigint(20) NOT NULL AUTO_INCREMENT,
	  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `log_desc` varchar(2222) NOT NULL,
	  `order_id` bigint(20) NOT NULL,
	  `status` varchar(22) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

} 


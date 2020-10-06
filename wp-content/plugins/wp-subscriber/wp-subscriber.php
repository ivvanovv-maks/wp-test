<?php
/**
 * @package WP_Subscriber
 * @version 0.1
 */
/*
Plugin Name: WP Subscriber
Description: Plugin to implement simple subscribe form. No additional settings needed, just enable plugin and use it's shortcode.
Version: 0.1
Author: ivvanovv
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPS_DB_TABLE', 'wps_subscribers' );


require_once( WPS_PLUGIN_DIR . 'class.WPSAdmin.php' );
require_once( WPS_PLUGIN_DIR . 'class.WPSForm.php' );

if ( is_admin() ) {
  WPSAdmin::init();
} else {
  WPSForm::init();
}

register_activation_hook( __FILE__, 'wps_plugin_activation' );
add_action( 'rest_api_init', 'register_api' );

function register_api() {
  register_rest_route( 'wps', 'subscribe', [
    'methods'  => 'GET',
    'callback' => [WPSForm::class, 'process_form'],
  ] );
  register_rest_route( 'wps', 'import', [
    'methods'  => 'POST',
    'callback' => [WPSAdmin::class, 'import_csv'],
  ] );
  register_rest_route( 'wps', 'export', [
    'methods'  => 'GET',
    'callback' => [WPSAdmin::class, 'export_csv'],
  ] );
}

function wps_plugin_activation() {
  global $wpdb;

  $charset_collate = $wpdb->get_charset_collate();

  if ( $wpdb->get_var("SHOW TABLES LIKE '" . WPS_DB_TABLE . "'" ) != WPS_DB_TABLE ) {

    $query = "CREATE TABLE `" . WPS_DB_TABLE . "` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `full_name` varchar(191) NOT NULL,
              `email` varchar(191) NOT NULL,
              `gave_concent` tinyint(1) DEFAULT 1,
              PRIMARY KEY (`id`)
    ) {$charset_collate};";
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    dbDelta( $query );
  }
}

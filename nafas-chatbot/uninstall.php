<?php
/**
 * حذف کامل افزونه: پاکسازی گزینه‌ها و جدول دیتابیس.
 *
 * @package NafasChatbot
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// حذف گزینه‌ها.
delete_option( 'nafas_chatbot_settings' );
delete_option( 'nafas_chatbot_chat_stats' );
delete_option( 'nafas_chatbot_db_version' );
delete_option( 'nafas_chatbot_qa_migrated' );
delete_option( 'nafas_chatbot_csat' );
delete_option( 'nafas_chatbot_stats_migrated' );

// پاک‌سازی کرون.
wp_clear_scheduled_hook( 'nafas_chatbot_daily_cleanup' );

// حذف جداول.
global $wpdb;
$table   = $wpdb->prefix . 'nafas_chatbot_submissions';
$chatlog = $wpdb->prefix . 'nafas_chatbot_chatlog';
$qa      = $wpdb->prefix . 'nafas_chatbot_qa';
$kb      = $wpdb->prefix . 'nafas_chatbot_kb';
$stats   = $wpdb->prefix . 'nafas_chatbot_stats';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB
$wpdb->query( "DROP TABLE IF EXISTS {$chatlog}" ); // phpcs:ignore WordPress.DB
$wpdb->query( "DROP TABLE IF EXISTS {$qa}" ); // phpcs:ignore WordPress.DB
$wpdb->query( "DROP TABLE IF EXISTS {$kb}" ); // phpcs:ignore WordPress.DB
$wpdb->query( "DROP TABLE IF EXISTS {$stats}" ); // phpcs:ignore WordPress.DB

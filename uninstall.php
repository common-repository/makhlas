<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
} 

// Delete All Makhlas posts mata
delete_metadata('post', 0, '_makhlas_key', '', true);
delete_metadata('post', 0, '_makhlas_short_url', '', true);

// Delete All Makhlas options
global $wpdb;
$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'makhlas_%'" );
foreach( $plugin_options as $option ) {
    delete_option( $option->option_name );
}

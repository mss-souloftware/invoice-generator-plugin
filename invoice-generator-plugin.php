<?php
/**
 * 
 * Plugin Name: Invoice Generator & Product Manager
 * plugin URI: https://souloftware.com/
 * version:1.0.0
 * Description: A custom invoice generator and product management dynamic plugin.
 * Author: Souloftware
 * Author URI: https://souloftware.com/
 */


// Include database setup
require_once plugin_dir_path(__FILE__) . 'includes/database.php';

// Hook to create tables on plugin activation
register_activation_hook(__FILE__, 'igpm_create_database_tables');

// Load admin menu
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'utils/functions.php';
}

function igpmScripts()
{
    wp_enqueue_script('scriptForFrontend', 'https://code.jquery.com/jquery-3.7.1.min.js', array(), '1.0.0', true);

}
add_action('wp_enqueue_scripts', 'igpmScripts');

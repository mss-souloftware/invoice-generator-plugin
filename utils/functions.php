<?php
require_once plugin_dir_path(__DIR__) . '/admin/create-invoice-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/products-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/invoices-list-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/view-invoice-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/edit-invoice-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/pdf-generator.php';



add_action('admin_menu', 'igpm_register_admin_menu');
function igpm_register_admin_menu()
{
    // Main Menu
    add_menu_page(
        'Invoice Generator',
        'Invoice Generator',
        'manage_options',
        'igpm_dashboard',
        'igpm_product_page', // callback for default page (e.g. product list)
        'dashicons-media-document',
        26
    );

    // Submenu: Products (optional – this one matches the main page)
    add_submenu_page(
        'igpm_dashboard',
        'Products',
        'Products',
        'manage_options',
        'igpm_dashboard', // slug must match main page slug to show as default
        'igpm_product_page'
    );

    // Submenu: Create Invoice
    add_submenu_page(
        'igpm_dashboard',
        'Create Invoice',
        'Create Invoice',
        'manage_options',
        'igpm_create_invoice',
        'igpm_create_invoice_page'
    );

    add_submenu_page(
        'igpm_dashboard',
        'Invoice List',
        'Invoice List',
        'manage_options',
        'igpm_invoices_list',
        'igpm_invoices_list_page'
    );

    add_submenu_page(
        null,
        'View Invoice',
        'View Invoice',
        'manage_options',
        'igpm_view_invoice',
        'igpm_view_invoice_page'
    );

    add_submenu_page(
        'igpm_dashboard',
        'Edit Invoice',
        '',
        'manage_options',
        'igpm_edit_invoice',
        'igpm_edit_invoice_page'
    );

}


add_action('admin_enqueue_scripts', 'igpm_enqueue_admin_scripts');

function igpm_enqueue_admin_scripts($hook)
{
    // Debug: uncomment to see $hook values
    // error_log($hook);

    // Load script on Create and Edit Invoice pages
    if (
        $hook !== 'toplevel_page_igpm_dashboard' &&
        $hook !== 'invoice-generator_page_igpm_create_invoice' &&
        $hook !== 'invoice-generator_page_igpm_edit_invoice'
    ) {
        return;
    }

    wp_enqueue_script(
        'igpm-invoice-script',
        plugin_dir_url(__DIR__) . 'assets/script.js',
        ['jquery'],
        '1.0',
        true
    );
}



// Handle AJAX request to delete invoice
add_action('wp_ajax_delete_invoice', 'igpm_delete_invoice');

function igpm_delete_invoice()
{
    if (isset($_POST['invoice_id'])) {
        global $wpdb;
        $invoices_table = $wpdb->prefix . 'custom_invoices';

        $invoice_id = intval($_POST['invoice_id']);
        $result = $wpdb->delete($invoices_table, ['id' => $invoice_id]);

        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
    wp_die();
}

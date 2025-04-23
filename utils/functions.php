<?php
require_once plugin_dir_path(__DIR__) . '/admin/create-invoice-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/products-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/invoices-list-page.php';
require_once plugin_dir_path(__DIR__) . '/admin/view-invoice-page.php';
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
}


add_action('admin_enqueue_scripts', 'igpm_enqueue_admin_scripts');

function igpm_enqueue_admin_scripts($hook)
{
    // Load script only on our invoice creation page
    if ($hook !== 'toplevel_page_igpm_dashboard' && $hook !== 'invoice-generator_page_igpm_create_invoice') {
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

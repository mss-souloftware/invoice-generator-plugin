<?php
function igpm_create_database_tables()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $products_table = $wpdb->prefix . 'custom_products';
    $invoices_table = $wpdb->prefix . 'custom_invoices';
    $invoice_items_table = $wpdb->prefix . 'custom_invoice_items';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Products Table
    $sql1 = "CREATE TABLE $products_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        unit VARCHAR(50) NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        discount_percent DECIMAL(5, 2) DEFAULT 0
    ) $charset_collate;";
    dbDelta($sql1);

    // Invoices Table
    $sql2 = "CREATE TABLE $invoices_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_no VARCHAR(50) NOT NULL,
        date DATE NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_address TEXT,
        subtotal DECIMAL(10, 2) NOT NULL,
        total_discount DECIMAL(10, 2),
        round_off DECIMAL(10, 2),
        total DECIMAL(10, 2),
        received DECIMAL(10, 2),
        balance DECIMAL(10, 2),
        amount_in_words TEXT
    ) $charset_collate;";
    dbDelta($sql2);

    // Invoice Items Table
    $sql3 = "CREATE TABLE $invoice_items_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        unit VARCHAR(50),
        unit_price DECIMAL(10, 2),
        discount_percent DECIMAL(5, 2),
        amount DECIMAL(10, 2),
        FOREIGN KEY (invoice_id) REFERENCES $invoices_table(id) ON DELETE CASCADE
    ) $charset_collate;";
    dbDelta($sql3);
}

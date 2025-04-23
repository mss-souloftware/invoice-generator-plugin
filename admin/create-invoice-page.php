<?php
function igpm_create_invoice_page()
{
    global $wpdb;
    $products_table = $wpdb->prefix . 'custom_products';
    $invoice_table = $wpdb->prefix . 'custom_invoices';
    $invoice_items_table = $wpdb->prefix . 'custom_invoice_items';

    $products = $wpdb->get_results("SELECT * FROM $products_table");

    // Handle form submission
    if (isset($_POST['igpm_save_invoice'])) {
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_address = sanitize_textarea_field($_POST['customer_address']);
        $date = sanitize_text_field($_POST['invoice_date']);
        $subtotal = floatval($_POST['subtotal']);
        $total_discount = floatval($_POST['total_discount']);
        $round_off = floatval($_POST['round_off']);
        $total = floatval($_POST['total']);
        $received = floatval($_POST['received']);
        $balance = floatval($_POST['balance']);
        $amount_in_words = sanitize_text_field($_POST['amount_in_words']);

        $invoice_no = 'INV-' . time();

        $wpdb->insert($invoice_table, [
            'invoice_no' => $invoice_no,
            'date' => $date,
            'customer_name' => $customer_name,
            'customer_address' => $customer_address,
            'subtotal' => $subtotal,
            'total_discount' => $total_discount,
            'round_off' => $round_off,
            'total' => $total,
            'received' => $received,
            'balance' => $balance,
            'amount_in_words' => $amount_in_words,
        ]);

        $invoice_id = $wpdb->insert_id;

        foreach ($_POST['product'] as $i => $product_name) {
            $wpdb->insert($invoice_items_table, [
                'invoice_id' => $invoice_id,
                'product_name' => sanitize_text_field($product_name),
                'quantity' => intval($_POST['quantity'][$i]),
                'unit' => sanitize_text_field($_POST['unit'][$i]),
                'unit_price' => floatval($_POST['unit_price'][$i]),
                'discount_percent' => floatval($_POST['discount'][$i]),
                'amount' => floatval($_POST['amount'][$i]),
            ]);
        }

        echo '<div class="updated"><p>Invoice created successfully!</p></div>';
    }
    ?>

    <div class="wrap">
        <h1>Create Invoice</h1>
        <form method="POST" id="invoice-form">
            <h2>Customer Info</h2>
            <table class="form-table">
                <tr>
                    <th>Name</th>
                    <td><input name="customer_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><textarea name="customer_address" class="large-text" required></textarea></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><input name="invoice_date" type="date" class="regular-text" value="<?= date('Y-m-d') ?>" required>
                    </td>
                </tr>
            </table>

            <h2>Products</h2>
            <table class="widefat" id="invoice-products">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Discount %</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="product-row">
                        <td>
                            <select name="product[]" class="product-select">
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= esc_attr($p->name) ?>" data-unit="<?= esc_attr($p->unit) ?>"
                                        data-price="<?= esc_attr($p->unit_price) ?>"
                                        data-discount="<?= esc_attr($p->discount_percent) ?>">
                                        <?= esc_html($p->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="quantity[]" class="quantity" value="1" required></td>
                        <td><input type="text" name="unit[]" class="unit" readonly></td>
                        <td><input type="text" name="unit_price[]" class="unit-price" readonly></td>
                        <td><input type="text" name="discount[]" class="discount" readonly></td>
                        <td><input type="text" name="amount[]" class="amount" readonly></td>
                        <td><button type="button" class="button remove-row">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            <p><button type="button" id="add-row" class="button">Add Product</button></p>

            <h2>Summary</h2>
            <table class="form-table">
                <tr>
                    <th>Subtotal</th>
                    <td><input name="subtotal" class="regular-text" readonly></td>
                </tr>
                <tr>
                    <th>Total Discount</th>
                    <td><input name="total_discount" class="regular-text" readonly></td>
                </tr>
                <tr>
                    <th>Round Off</th>
                    <td><input name="round_off" class="regular-text" value="0.00"></td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td><input name="total" class="regular-text" readonly></td>
                </tr>
                <tr>
                    <th>Received</th>
                    <td><input name="received" class="regular-text" value="0.00"></td>
                </tr>
                <tr>
                    <th>Balance</th>
                    <td><input name="balance" class="regular-text" readonly></td>
                </tr>
                <tr>
                    <th>Amount in Words</th>
                    <td><input name="amount_in_words" class="regular-text" required></td>
                </tr>
            </table>

            <p><input type="submit" name="igpm_save_invoice" class="button button-primary" value="Save Invoice"></p>
        </form>
    </div>

    <?php

}
?>
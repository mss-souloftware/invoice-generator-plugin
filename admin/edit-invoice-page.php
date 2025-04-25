<?php
function igpm_edit_invoice_page()
{
    global $wpdb;
    $invoice_table = $wpdb->prefix . 'custom_invoices';
    $invoice_items_table = $wpdb->prefix . 'custom_invoice_items';
    $products_table = $wpdb->prefix . 'custom_products';

    if (!isset($_GET['invoice_id'])) {
        echo '<div class="notice notice-error"><p>Invoice ID missing.</p></div>';
        return;
    }

    $invoice_id = intval($_GET['invoice_id']);

    // Fetch invoice + items
    $invoice = $wpdb->get_row("SELECT * FROM $invoice_table WHERE id = $invoice_id");
    $items = $wpdb->get_results("SELECT * FROM $invoice_items_table WHERE invoice_id = $invoice_id");
    $products = $wpdb->get_results("SELECT * FROM $products_table");

    if (!$invoice) {
        echo '<div class="notice notice-error"><p>Invoice not found.</p></div>';
        return;
    }

    // Handle update form
    if (isset($_POST['igpm_update_invoice'])) {
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

        // Update invoice main table
        $wpdb->update($invoice_table, [
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
        ], ['id' => $invoice_id]);

        // Delete old items and re-insert new
        $wpdb->delete($invoice_items_table, ['invoice_id' => $invoice_id]);

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

        echo '<div class="updated"><p>Invoice updated successfully!</p></div>';

        // Refresh data
        $invoice = $wpdb->get_row("SELECT * FROM $invoice_table WHERE id = $invoice_id");
        $items = $wpdb->get_results("SELECT * FROM $invoice_items_table WHERE invoice_id = $invoice_id");
    }

    ?>

    <div class="wrap">
        <h1>Edit Invoice #<?php echo esc_html($invoice->invoice_no); ?></h1>
        <form method="POST" id="invoice-form">
            <h2>Customer Info</h2>
            <table class="form-table">
                <tr>
                    <th>Name</th>
                    <td><input name="customer_name" class="regular-text" value="<?php echo esc_attr($invoice->customer_name); ?>" required></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><textarea name="customer_address" class="regular-text" required><?php echo esc_textarea($invoice->customer_address); ?></textarea></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><input name="invoice_date" type="date" class="regular-text" value="<?php echo esc_attr($invoice->date); ?>" required></td>
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
                        <th>Amount</th>
                        <th>Discount %</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="product-row">
                            <td>
                                <select name="product[]" class="product-select">
                                    <option value="" disabled>Select product</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo esc_attr($p->name); ?>"
                                            data-unit="<?php echo esc_attr($p->unit); ?>"
                                            data-price="<?php echo esc_attr($p->unit_price); ?>"
                                            data-discount="<?php echo esc_attr($p->discount_percent); ?>"
                                            <?php selected($p->name, $item->product_name); ?>>
                                            <?php echo esc_html($p->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="quantity" value="<?php echo esc_attr($item->quantity); ?>" required></td>
                            <td><input type="text" name="unit[]" class="unit" value="<?php echo esc_attr($item->unit); ?>" readonly></td>
                            <td><input type="text" name="unit_price[]" class="unit-price" value="<?php echo esc_attr($item->unit_price); ?>" readonly></td>
                            <td><input type="text" name="amount[]" class="amount" value="<?php echo esc_attr($item->amount); ?>" readonly></td>
                            <td>
                                <input type="number" name="discount[]" class="discount" value="<?php echo esc_attr($item->discount_percent); ?>">
                                <div class="discounted-amount" style="font-size: 11px; color: #0073aa;">Discount: ₨ 0.00</div>
                            </td>
                            <td><button type="button" class="button remove-row">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="button" id="add-row" class="button">Add Product</button></p>

            <h2>Summary</h2>
            <table class="form-table">
                <tr>
                    <th>Subtotal</th>
                    <td><input name="subtotal" class="regular-text" value="<?php echo esc_attr($invoice->subtotal); ?>" readonly></td>
                </tr>
                <tr>
                    <th>Total Discount</th>
                    <td><input name="total_discount" class="regular-text" value="<?php echo esc_attr($invoice->total_discount); ?>" readonly></td>
                </tr>
                <tr>
                    <th>Round Off</th>
                    <td><input type="number" step="0.01" name="round_off" class="regular-text" value="<?php echo esc_attr($invoice->round_off); ?>"></td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td><input name="total" class="regular-text" value="<?php echo esc_attr($invoice->total); ?>" readonly></td>
                </tr>
                <tr>
                    <th>Received</th>
                    <td><input type="number" name="received" class="regular-text" value="<?php echo esc_attr($invoice->received); ?>"></td>
                </tr>
                <tr>
                    <th>Balance</th>
                    <td><input name="balance" class="regular-text" value="<?php echo esc_attr($invoice->balance); ?>" readonly></td>
                </tr>
                <tr>
                    <th>Amount in Words</th>
                    <td><input name="amount_in_words" class="regular-text" value="<?php echo esc_attr($invoice->amount_in_words); ?>"></td>
                </tr>
            </table>

            <p><input type="submit" name="igpm_update_invoice" class="button button-primary" value="Update Invoice"></p>
        </form>
    </div>
    <?php
}

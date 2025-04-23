<?php

// Product Manager Page
function igpm_product_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_products';

    // Handle form submissions
    if (isset($_POST['igpm_add_product'])) {
        $name = sanitize_text_field($_POST['name']);
        $unit = sanitize_text_field($_POST['unit']);
        $unit_price = floatval($_POST['unit_price']);
        $discount = floatval($_POST['discount_percent']);

        $wpdb->insert($table_name, [
            'name' => $name,
            'unit' => $unit,
            'unit_price' => $unit_price,
            'discount_percent' => $discount
        ]);
        echo '<div class="updated"><p>Product added!</p></div>';
    }

    if (isset($_GET['delete'])) {
        $wpdb->delete($table_name, ['id' => intval($_GET['delete'])]);
        echo '<div class="updated"><p>Product deleted!</p></div>';
    }

    $products = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1>Product Manager</h1>
        <form method="POST">
            <h2>Add New Product</h2>
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input name="name" required type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="unit">Unit</label></th>
                    <td><input name="unit" required type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="unit_price">Unit Price</label></th>
                    <td><input name="unit_price" required type="number" step="0.01" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="discount_percent">Discount %</label></th>
                    <td><input name="discount_percent" type="number" step="0.01" class="regular-text" value="0"></td>
                </tr>
            </table>
            <p><input type="submit" class="button button-primary" name="igpm_add_product" value="Add Product"></p>
        </form>

        <h2>Existing Products</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Unit</th>
                    <th>Unit Price</th>
                    <th>Discount (%)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= esc_html($product->name) ?></td>
                        <td><?= esc_html($product->unit) ?></td>
                        <td>₨ <?= number_format($product->unit_price, 2) ?></td>
                        <td><?= number_format($product->discount_percent, 2) ?></td>
                        <td><a href="?page=igpm_dashboard&delete=<?= $product->id ?>"
                                onclick="return confirm('Delete this product?')">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

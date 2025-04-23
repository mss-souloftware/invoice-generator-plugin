<?php
function igpm_view_invoice_page()
{
    global $wpdb;

    if (!isset($_GET['invoice_id'])) {
        echo '<div class="notice notice-error"><p>Invoice ID missing.</p></div>';
        return;
    }

    $invoice_id = intval($_GET['invoice_id']);
    $invoice = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}custom_invoices WHERE id = $invoice_id");

    if (!$invoice) {
        echo '<div class="notice notice-error"><p>Invoice not found.</p></div>';
        return;
    }

    $items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}custom_invoice_items WHERE invoice_id = $invoice_id");
    ?>
    <div class="wrap" style="font-family: Arial;">
        <h2 style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px;">A.G MARKETING Invoice</h2>

        <div style="margin-top: 20px;">
            <strong>Bill To:</strong><br>
            <?= esc_html($invoice->customer_name) ?><br>
            <?= esc_html($invoice->customer_address) ?><br>
            <strong>Invoice No.:</strong> <?= esc_html($invoice->invoice_no) ?><br>
            <strong>Date:</strong> <?= esc_html(date('d/m/Y', strtotime($invoice->date))) ?>
        </div>

        <table class="widefat striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Line Price</th>
                    <th>Discount</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item):
                    $line_price = $item->quantity * $item->unit_price;
                    $discount_amt = $line_price * $item->discount_percent / 100;
                    $final_amount = $line_price - $discount_amt;
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc_html($item->product_name) ?></td>
                        <td><?= $item->quantity ?>         <?= esc_html($item->unit) ?></td>
                        <td>₨ <?= number_format($item->unit_price, 2) ?></td>
                        <td>₨ <?= number_format($line_price, 2) ?></td>
                        <td>(<?= number_format($item->discount_percent, 0) ?>%)<br>₨ <?= number_format($discount_amt, 2) ?></td>
                        <td>₨ <?= number_format($final_amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="max-width: 300px; float: right; margin-top: 30px;">
            <table class="widefat">
                <tr>
                    <th>Sub Total</th>
                    <td>₨ <?= number_format($invoice->subtotal, 2) ?></td>
                </tr>
                <tr>
                    <th>Discount</th>
                    <td>₨ <?= number_format($invoice->total_discount, 2) ?></td>
                </tr>
                <tr>
                    <th>Round Off</th>
                    <td>₨ <?= number_format($invoice->round_off, 2) ?></td>
                </tr>
                <tr>
                    <th><strong>Total</strong></th>
                    <td><strong>₨ <?= number_format($invoice->total, 2) ?></strong></td>
                </tr>
                <tr>
                    <th>Received</th>
                    <td>₨ <?= number_format($invoice->received, 2) ?></td>
                </tr>
                <tr>
                    <th>Balance</th>
                    <td>₨ <?= number_format($invoice->balance, 2) ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>
        <br><br>
        <strong>Invoice Amount In Words:</strong> <?= esc_html($invoice->amount_in_words) ?><br><br>
        <strong>Terms And Conditions:</strong><br>
        Thanks for doing business with us!<br><br>

        <div style="text-align: right;"><strong>For, A.G MARKETING</strong><br><em>Authorized Signatory</em></div>
    </div>
<?php } ?>
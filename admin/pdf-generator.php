<?php
require_once plugin_dir_path(__DIR__) . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

add_action('admin_post_igpm_generate_pdf', 'igpm_generate_invoice_pdf');

function igpm_generate_invoice_pdf()
{
    if (!isset($_GET['invoice_id'])) {
        wp_die('Invoice ID missing.');
    }

    $invoice_id = intval($_GET['invoice_id']);
    global $wpdb;

    $invoice = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}custom_invoices WHERE id = $invoice_id");
    if (!$invoice) {
        wp_die('Invoice not found.');
    }

    $items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}custom_invoice_items WHERE invoice_id = $invoice_id");

    ob_start();
    ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        .summary td {
            border: none;
            padding: 4px;
        }
    </style>

    <h1>A.G MARKETING Invoice</h1>

    <p>
        <strong>Bill To:</strong><br>
        <?= $invoice->customer_name ?><br>
        <?= $invoice->customer_address ?><br>
        <strong>Invoice No.:</strong> <?= $invoice->invoice_no ?><br>
        <strong>Date:</strong> <?= date('d/m/Y', strtotime($invoice->date)) ?>
    </p>

    <table>
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
                $line = $item->quantity * $item->unit_price;
                $disc = $line * $item->discount_percent / 100;
                $final = $line - $disc;
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= $item->product_name ?></td>
                    <td><?= $item->quantity ?>         <?= $item->unit ?></td>
                    <td>₨ <?= number_format($item->unit_price, 2) ?></td>
                    <td>₨ <?= number_format($line, 2) ?></td>
                    <td>(<?= $item->discount_percent ?>%)<br>₨ <?= number_format($disc, 2) ?></td>
                    <td>₨ <?= number_format($final, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>
    <table class="summary" align="right" style="width: 40%;">
        <tr>
            <td><strong>Sub Total:</strong></td>
            <td>₨ <?= number_format($invoice->subtotal, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Discount:</strong></td>
            <td>₨ <?= number_format($invoice->total_discount, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Round Off:</strong></td>
            <td>₨ <?= number_format($invoice->round_off, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Total:</strong></td>
            <td><strong>₨ <?= number_format($invoice->total, 2) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Received:</strong></td>
            <td>₨ <?= number_format($invoice->received, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Balance:</strong></td>
            <td>₨ <?= number_format($invoice->balance, 2) ?></td>
        </tr>
    </table>

    <div style="clear: both;"></div>
    <br><br>
    <strong>Invoice Amount In Words:</strong> <?= $invoice->amount_in_words ?><br><br>
    <strong>Terms And Conditions:</strong> Thanks for doing business with us!<br><br>
    <div style="text-align: right;"><strong>For, A.G MARKETING</strong><br><em>Authorized Signatory</em></div>

    <?php
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('invoice_' . $invoice->invoice_no . '.pdf', ['Attachment' => false]);

    exit;
}

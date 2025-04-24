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
            color: #000;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .info {
            margin-top: 20px;
        }

        .info strong {
            display: inline-block;
            width: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .summary-table {
            width: 40%;
            float: right;
            border: 1px solid #000;
            margin-top: 20px;
        }

        .summary-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .footer {
            margin-top: 60px;
            clear: both;
        }

        .footer p {
            margin: 5px 0;
        }

        .signature {
            text-align: right;
            margin-top: 40px;
        }
    </style>

    <h1>A.G MARKETING Invoice</h1>

    <div class="info">
        <strong>Bill To:</strong> <?= nl2br(esc_html($invoice->customer_name)) ?><br>
        <strong>Address:</strong> <?= nl2br(esc_html($invoice->customer_address)) ?><br>
        <strong>Invoice No.:</strong> <?= esc_html($invoice->invoice_no) ?><br>
        <strong>Date:</strong> <?= date('d/m/Y', strtotime($invoice->date)) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
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
                    <td><?= esc_html($item->product_name) ?></td>
                    <td><?= $item->quantity . ' ' . esc_html($item->unit) ?></td>
                    <td>Rs <?= number_format($item->unit_price, 2) ?></td>
                    <td>Rs <?= number_format($line, 2) ?></td>
                    <td>(<?= number_format($item->discount_percent, 0) ?>%)<br>Rs <?= number_format($disc, 2) ?></td>
                    <td>Rs <?= number_format($final, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Sub Total</strong></td>
            <td>Rs <?= number_format($invoice->subtotal, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Discount</strong></td>
            <td>Rs <?= number_format($invoice->total_discount, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Round Off</strong></td>
            <td>Rs <?= number_format($invoice->round_off, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>Rs <?= number_format($invoice->total, 2) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Received</strong></td>
            <td>Rs <?= number_format($invoice->received, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Balance</strong></td>
            <td>Rs <?= number_format($invoice->balance, 2) ?></td>
        </tr>
    </table>

    <div class="footer">
        <p><strong>Invoice Amount In Words:</strong> <?= esc_html($invoice->amount_in_words) ?></p>
        <p><strong>Terms And Conditions:</strong> Thanks for doing business with us!</p>
        <div class="signature">
            <strong>For, A.G MARKETING</strong><br>
            <em>Authorized Signatory</em>
        </div>
    </div>

    <?php
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('invoice_' . $invoice->invoice_no . '.pdf', ['Attachment' => false]);

    exit;
}

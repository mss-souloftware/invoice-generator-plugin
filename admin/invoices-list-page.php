<?php
function igpm_invoices_list_page() {
    global $wpdb;
    $invoices_table = $wpdb->prefix . 'custom_invoices';

    // Filter logic
    $where = "WHERE 1=1";
    if (!empty($_GET['search_name'])) {
        $name = esc_sql($_GET['search_name']);
        $where .= " AND customer_name LIKE '%$name%'";
    }
    if (!empty($_GET['start_date'])) {
        $start = esc_sql($_GET['start_date']);
        $where .= " AND date >= '$start'";
    }
    if (!empty($_GET['end_date'])) {
        $end = esc_sql($_GET['end_date']);
        $where .= " AND date <= '$end'";
    }

    $query = "SELECT * FROM $invoices_table $where ORDER BY date DESC";
    $invoices = $wpdb->get_results($query);
    ?>

    <div class="wrap">
        <h1>Invoices</h1>

        <form method="GET">
            <input type="hidden" name="page" value="igpm_invoices_list" />
            <input type="text" name="search_name" placeholder="Customer name" value="<?= isset($_GET['search_name']) ? esc_attr($_GET['search_name']) : '' ?>">
            <input type="date" name="start_date" value="<?= isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : '' ?>">
            <input type="date" name="end_date" value="<?= isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : '' ?>">
            <button type="submit" class="button">Filter</button>
            <a href="<?php echo admin_url('admin.php?page=igpm_invoices_list'); ?>" class="button">Reset</a>
        </form>

        <br>

        <?php if (count($invoices) > 0): ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Received</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><?= esc_html($inv->invoice_no) ?></td>
                            <td><?= esc_html(date('d M Y', strtotime($inv->date))) ?></td>
                            <td><?= esc_html($inv->customer_name) ?></td>
                            <td>₨ <?= number_format($inv->total, 2) ?></td>
                            <td>₨ <?= number_format($inv->received, 2) ?></td>
                            <td>₨ <?= number_format($inv->balance, 2) ?></td>
                            <td>
                                <a href="<?= admin_url('admin.php?page=igpm_view_invoice&invoice_id=' . $inv->id) ?>" class="button button-small">View</a>
                                <a href="<?= admin_url('admin-post.php?action=igpm_generate_pdf&invoice_id=' . $inv->id) ?>" class="button button-small" target="_blank">PDF</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No invoices found for the selected criteria.</p>
        <?php endif; ?>
    </div>
    <?php
}

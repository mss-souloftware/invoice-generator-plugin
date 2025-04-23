jQuery(document).ready(function ($) {
    function calculateRow($row) {
        console.log("Script is working")
        const qty = parseFloat($row.find('.quantity').val()) || 0;
        const price = parseFloat($row.find('.unit-price').val()) || 0;
        const discount = parseFloat($row.find('.discount').val()) || 0;
        const amount = qty * price * (1 - discount / 100);
        $row.find('.amount').val(amount.toFixed(2));
        return { amount, discount: qty * price * (discount / 100) };
    }

    function updateSummary() {
        let subtotal = 0, totalDiscount = 0;
        $('#invoice-products tbody tr').each(function () {
            const { amount, discount } = calculateRow($(this));
            subtotal += amount + discount;
            totalDiscount += discount;
        });

        $('input[name="subtotal"]').val(subtotal.toFixed(2));
        $('input[name="total_discount"]').val(totalDiscount.toFixed(2));

        const roundOff = parseFloat($('input[name="round_off"]').val()) || 0;
        const total = subtotal - totalDiscount + roundOff;
        $('input[name="total"]').val(total.toFixed(2));

        const received = parseFloat($('input[name="received"]').val()) || 0;
        $('input[name="balance"]').val((total - received).toFixed(2));
    }

    $('#invoice-products').on('change', '.product-select', function () {
        const $row = $(this).closest('tr');
        const option = $(this).find('option:selected');
        $row.find('.unit').val(option.data('unit'));
        $row.find('.unit-price').val(option.data('price'));
        $row.find('.discount').val(option.data('discount'));
        calculateRow($row);
        updateSummary();
    });

    $('#invoice-products').on('input', '.quantity, input[name="round_off"], input[name="received"]', function () {
        updateSummary();
    });

    $('#invoice-products').on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        updateSummary();
    });

    $('#add-row').click(function () {
        const $lastRow = $('#invoice-products tbody tr:last');
        const $newRow = $lastRow.clone();
        $newRow.find('input').val('');
        $('#invoice-products tbody').append($newRow);
    });

    // Initial calculation
    $('#invoice-products tbody tr').each(function () {
        $(this).find('.product-select').trigger('change');
    });
});

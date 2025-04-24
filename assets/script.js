jQuery(document).ready(function ($) {
    function calculateRow($row) {
        const qty = parseFloat($row.find('.quantity').val()) || 0;
        const price = parseFloat($row.find('.unit-price').val()) || 0;
        const discountPercent = parseFloat($row.find('.discount').val()) || 0;

        const lineTotal = qty * price;
        const discountAmount = lineTotal * (discountPercent / 100);

        // Show original amount (not discounted) in input
        $row.find('.amount').val(lineTotal.toFixed(2));

        // Show discounted amount separately
        $row.find('.discounted-amount').text(`Discount: ₨ ${discountAmount.toFixed(2)}`);

        return {
            linePrice: lineTotal,
            discountAmount: discountAmount
        };
    }

    function updateSummary() {
        let subtotal = 0;
        let totalDiscount = 0;

        $('#invoice-products tbody tr').each(function () {
            const { linePrice, discountAmount } = calculateRow($(this));
            subtotal += linePrice;
            totalDiscount += discountAmount;
        });

        const roundOff = parseFloat($('input[name="round_off"]').val()) || 0;
        const received = parseFloat($('input[name="received"]').val()) || 0;
        const total = subtotal - totalDiscount + roundOff;
        const balance = total - received;

        $('input[name="subtotal"]').val(subtotal.toFixed(2));
        $('input[name="total_discount"]').val(totalDiscount.toFixed(2));
        $('input[name="total"]').val(total.toFixed(2));
        $('input[name="balance"]').val(balance.toFixed(2));
    }

    // Trigger calculation on any relevant input change
    $(document).on('input change', '.product-select, .quantity, .unit-price, .discount, input[name="round_off"], input[name="received"]', function () {
        updateSummary();
    });

    // On product change, set values and trigger full update
    $('#invoice-products').on('change', '.product-select', function () {
        const $row = $(this).closest('tr');
        const option = $(this).find('option:selected');

        $row.find('.unit').val(option.data('unit'));
        $row.find('.unit-price').val(option.data('price'));
        $row.find('.discount').val(option.data('discount') || 0);

        updateSummary();
    });

    // Remove row
    $('#invoice-products').on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        updateSummary();
    });

    // Add row
    $('#add-row').click(function () {
        const $lastRow = $('#invoice-products tbody tr:last');
        const $newRow = $lastRow.clone();

        $newRow.find('input').val('');
        $newRow.find('.discounted-amount').text('Discount: ₨ 0.00');
        $newRow.find('.product-select').prop('selectedIndex', 0);

        $('#invoice-products tbody').append($newRow);
        updateSummary();
    });

    // Initial calculation
    $('#invoice-products tbody tr').each(function () {
        calculateRow($(this));
    });
    updateSummary();
});

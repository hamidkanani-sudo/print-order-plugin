jQuery(document).ready(function ($) {
    console.log('Print Order: pricing.js loaded successfully');
    console.log('Print Order: jQuery version:', $.fn.jquery);
    console.log('Print Order: printOrderPricingData:', printOrderPricingData);

    const $rowsContainer = $('#pricing-rows');
    let rowCount = $rowsContainer.find('tr').length;
    console.log('Print Order: Initial row count:', rowCount);

    // Validate price input to allow only digits
    $rowsContainer.on('input', 'input[name*="[price]"]', function () {
        const $input = $(this);
        let value = $input.val();
        value = value.replace(/[^0-9]/g, '');
        $input.val(value);
    });

    // Add new row
    const $addRowButton = $('#add-pricing-row');
    if ($addRowButton.length) {
        console.log('Print Order: Add Row button found');
        $addRowButton.on('click', function () {
            const categories = printOrderPricingData.categories;
            const i18n = printOrderPricingData.i18n;

            if (!Object.keys(categories).length) {
                alert(i18n.no_categories || 'هیچ دسته‌بندی محصولی یافت نشد. لطفاً حداقل یک دسته‌بندی ایجاد کنید.');
                return;
            }

            const rowIndex = rowCount++;
            const categoryOptions = Object.entries(categories)
                .map(([id, name]) => `<option value="${id}">${name}</option>`)
                .join('');

            const newRow = `
                <tr style="display: none;">
                    <td data-label="${i18n.category}">
                        <select name="pricing[${rowIndex}][category_id]" class="border p-2 rounded-lg w-full">
                            ${categoryOptions}
                        </select>
                    </td>
                    <td data-label="${i18n.paper_type}">
                        <input type="text" name="pricing[${rowIndex}][paper_type]" value="" class="border p-2 rounded-lg w-full">
                    </td>
                    <td data-label="${i18n.paper_type_persian}">
                        <input type="text" name="pricing[${rowIndex}][paper_type_persian]" value="" class="border p-2 rounded-lg w-full">
                    </td>
                    <td data-label="${i18n.size}">
                        <input type="text" name="pricing[${rowIndex}][size]" value="" class="border p-2 rounded-lg w-full">
                    </td>
                    <td data-label="${i18n.quantity}">
                        <input type="number" name="pricing[${rowIndex}][quantity]" value="1" min="1" class="border p-2 rounded-lg w-full">
                    </td>
                    <td data-label="${i18n.sides}">
                        <select name="pricing[${rowIndex}][sides]" class="border p-2 rounded-lg w-full">
                            <option value="single">${i18n.single_sided}</option>
                            <option value="double">${i18n.double_sided}</option>
                        </select>
                    </td>
                    <td data-label="${i18n.price}">
                        <input type="number" name="pricing[${rowIndex}][price]" value="" min="0" step="1" class="border p-2 rounded-lg w-full">
                    </td>
                    <td data-label="${i18n.days}">
                        <input type="number" name="pricing[${rowIndex}][days]" value="0" min="0" class="border p-2 rounded-lg w-full">
                    </td>
                    <td class="actions">
                        <button type="button" class="duplicate-row bg-green-500 text-white p-1 rounded hover:bg-green-600" title="${i18n.copy}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                            </svg>
                        </button>
                        <button type="button" class="remove-row bg-red-500 text-white p-1 rounded hover:bg-red-600" title="${i18n.delete}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;

            const $noDataRow = $rowsContainer.find('tr td[colspan="9"]');
            if ($noDataRow.length) {
                $rowsContainer.empty().append(newRow);
            } else {
                $rowsContainer.append(newRow);
            }

            $rowsContainer.find('tr').last().fadeIn(300, function() {
                bindRowEvents();
            });
        });
    } else {
        console.log('Print Order: Add Row button not found');
    }

    // Duplicate row with animation and empty price
    function duplicateRow($row) {
        const $newRow = $row.clone();
        const rowIndex = rowCount++;
        $newRow.find('select, input').each(function () {
            const $input = $(this);
            const name = $input.attr('name');
            if (name) {
                const newName = name.replace(/\[\d+\]/, `[${rowIndex}]`);
                $input.attr('name', newName);
            }
            if ($input.attr('name').includes('[id]')) {
                $input.remove();
            }
            if ($input.attr('name').includes('[price]')) {
                $input.val(''); // Empty the price field
            }
        });
        $newRow.hide();
        $row.after($newRow);
        $newRow.fadeIn(300, function() {
            bindRowEvents();
        });
    }

    // Remove row with animation
    function removeRow($row) {
        const $rows = $rowsContainer.find('tr');
        if ($rows.length <= 1) {
            const i18n = printOrderPricingData.i18n;
            $rowsContainer.fadeOut(300, function () {
                $(this).html(`
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            ${i18n.no_data || 'هیچ داده قیمت‌گذاری موجود نیست. برای شروع روی "افزودن ردیف" کلیک کنید.'}
                        </td>
                    </tr>
                `).fadeIn(300);
            });
        } else {
            $row.fadeOut(300, function () {
                $(this).remove();
                bindRowEvents();
            });
        }
    }

    // Bind events to duplicate and remove buttons
    function bindRowEvents() {
        const $duplicateButtons = $rowsContainer.find('.duplicate-row');
        const $removeButtons = $rowsContainer.find('.remove-row');
        console.log('Print Order: Binding events - duplicate buttons:', $duplicateButtons.length);
        console.log('Print Order: Binding events - remove buttons:', $removeButtons.length);

        $duplicateButtons.off('click').on('click', function () {
            const $row = $(this).closest('tr');
            duplicateRow($row);
        });

        $removeButtons.off('click').on('click', function () {
            const $row = $(this).closest('tr');
            removeRow($row);
        });
    }

    // Initial binding of events
    bindRowEvents();

    // Sorting functionality for table headers
    const $headers = $('.print-order-table thead th[data-sort]');
    $headers.on('click', function () {
        const $header = $(this);
        const sortKey = $header.data('sort');
        const $tbody = $header.closest('table').find('tbody');
        const $rows = $tbody.find('tr').not(':has(td[colspan="9"])').get();

        const isAscending = $header.hasClass('sort-asc');
        $headers.removeClass('sort-asc sort-desc');
        $header.addClass(isAscending ? 'sort-desc' : 'sort-asc');

        $rows.sort((a, b) => {
            let aValue, bValue;
            const $aTd = $(a).find(`td[data-label="${$header.text()}"]`);
            const $bTd = $(b).find(`td[data-label="${$header.text()}"]`);

            if ($aTd.find('select').length) {
                aValue = $aTd.find('select').find('option:selected').text().trim();
                bValue = $bTd.find('select').find('option:selected').text().trim();
            } else {
                aValue = $aTd.find('input').val().trim();
                bValue = $bTd.find('input').val().trim();

                if (sortKey === 'quantity' || sortKey === 'price' || sortKey === 'days') {
                    aValue = parseFloat(aValue) || 0;
                    bValue = parseFloat(bValue) || 0;
                }
            }

            if (aValue < bValue) return isAscending ? 1 : -1;
            if (aValue > bValue) return isAscending ? -1 : 1;
            return 0;
        });

        $tbody.empty().append($rows);
        bindRowEvents();
    });
});
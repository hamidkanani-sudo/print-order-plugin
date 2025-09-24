(function ($) {
    console.log('PrintOrder: template-combinations.js loaded - v1.0.8');

    // بررسی وجود printOrderTemplateData
    if (typeof printOrderTemplateData === 'undefined') {
        console.error('PrintOrder: printOrderTemplateData is not defined. Script may not function correctly.');
        // ادامه اجرای تب‌ها و دکمه‌های غیروابسته
    } else {
        console.log('PrintOrder: printOrderTemplateData is defined:', printOrderTemplateData);
    }

    // انتظار برای بارگذاری کامل DOM
    $(document).ready(function () {
        console.log('PrintOrder: DOM fully loaded');

        // مدیریت تب‌ها
        function initializeTabs() {
            console.log('PrintOrder: Initializing tabs');
            const $tabLinks = $('.tab-link');
            const $tabPanes = $('.tab-pane');

            if ($tabLinks.length === 0 || $tabPanes.length === 0) {
                console.error('PrintOrder: Tab links or panes not found');
                return;
            }

            $tabLinks.off('click').on('click', function (e) {
                e.preventDefault();
                const $this = $(this);
                const target = $this.attr('href');

                console.log('PrintOrder: Tab clicked', target);

                // غیرفعال کردن تمام تب‌ها
                $tabLinks.removeClass('active bg-blue-600 text-white').addClass('text-gray-700');
                $tabPanes.addClass('hidden');

                // فعال کردن تب کلیک‌شده
                $this.addClass('active bg-blue-600 text-white');
                $(target).removeClass('hidden');

                console.log('PrintOrder: Showing pane', target);
            });

            // اطمینان از نمایش تب پیش‌فرض
            const $activeTab = $('.tab-link.active');
            if ($activeTab.length) {
                const targetPane = $activeTab.attr('href');
                $(targetPane).removeClass('hidden');
                console.log('PrintOrder: Default tab set', targetPane);
            } else {
                console.warn('PrintOrder: No active tab found, setting default');
                $tabLinks.first().addClass('active bg-blue-600 text-white');
                $tabPanes.first().removeClass('hidden');
            }
        }

        // مدیریت افزودن ردیف جدید
        function initializeAddRow() {
            console.log('PrintOrder: Initializing add row');
            const $addButton = $('#add-combination-row');
            if (!$addButton.length) {
                console.error('PrintOrder: Add combination row button not found');
                return;
            }

            $addButton.off('click').on('click', function (e) {
                e.preventDefault();
                console.log('PrintOrder: Add row clicked');
                const $tbody = $('#combination-rows');
                if (!$tbody.length) {
                    console.error('PrintOrder: Combination rows tbody not found');
                    return;
                }

                // اگر printOrderTemplateData وجود ندارد، ردیف خالی اضافه می‌شود
                const rowCount = $tbody.find('tr').length;
                let newRow = `
                    <tr class="animate-add-row">
                        <td data-label="دسته‌بندی">
                            <select name="combinations[${rowCount}][category_id]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                <option value="">انتخاب دسته‌بندی</option>
                            </select>
                        </td>
                        <td data-label="نوع کاغذ (فارسی)">
                            <select name="combinations[${rowCount}][paper_type_persian]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                <option value="">انتخاب نوع کاغذ</option>
                            </select>
                        </td>
                        <td data-label="شناسه شورت‌کد">
                            <input type="number" name="combinations[${rowCount}][shortcode_id]" value="" min="1" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                        </td>
                        <td class="actions flex gap-2">
                            <button type="button" class="duplicate-row flex w-10 h-10 items-center justify-center bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 hover:scale-105" title="کپی">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                    <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                                </svg>
                            </button>
                            <button type="button" class="remove-row flex w-10 h-10 items-center justify-center bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 hover:scale-105" title="حذف">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;

                // اگر printOrderTemplateData وجود دارد، گزینه‌ها را پر می‌کنیم
                if (typeof printOrderTemplateData !== 'undefined') {
                    newRow = `
                        <tr class="animate-add-row">
                            <td data-label="${printOrderTemplateData.i18n.category}">
                                <select name="combinations[${rowCount}][category_id]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                    ${Object.entries(printOrderTemplateData.categories)
                                        .map(([id, name]) => `<option value="${id}">${name}</option>`)
                                        .join('')}
                                </select>
                            </td>
                            <td data-label="${printOrderTemplateData.i18n.paper_type_persian}">
                                <select name="combinations[${rowCount}][paper_type_persian]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                    ${printOrderTemplateData.paper_types
                                        .map(type => `<option value="${type}">${type}</option>`)
                                        .join('')}
                                </select>
                            </td>
                            <td data-label="${printOrderTemplateData.i18n.shortcode_id}">
                                <input type="number" name="combinations[${rowCount}][shortcode_id]" value="" min="1" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="actions flex gap-2">
                                <button type="button" class="duplicate-row flex w-10 h-10 items-center justify-center bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 hover:scale-105" title="${printOrderTemplateData.i18n.copy}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                        <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                                    </svg>
                                </button>
                                <button type="button" class="remove-row flex w-10 h-10 items-center justify-center bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 hover:scale-105" title="${printOrderTemplateData.i18n.delete}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    `;
                }

                $tbody.append(newRow);
                console.log('PrintOrder: New row added, row count:', rowCount + 1);
                updateRowIndexes();
            });
        }

        // مدیریت حذف و کپی ردیف‌ها
        function initializeRowActions() {
            console.log('PrintOrder: Initializing row actions');
            const $tbody = $('#combination-rows');
            if (!$tbody.length) {
                console.error('PrintOrder: Combination rows tbody not found');
                return;
            }

            $tbody.off('click', '.duplicate-row, .remove-row').on('click', '.duplicate-row, .remove-row', function (e) {
                e.preventDefault();
                const $button = $(this);
                const $row = $button.closest('tr');

                if ($button.hasClass('remove-row')) {
                    console.log('PrintOrder: Remove row clicked');
                    if ($tbody.find('tr').length > 1) {
                        $row.addClass('animate-remove-row');
                        setTimeout(() => {
                            $row.remove();
                            console.log('PrintOrder: Row removed');
                            updateRowIndexes();
                        }, 300);
                    } else {
                        console.warn('PrintOrder: Cannot remove the last row');
                        alert('نمی‌توانید آخرین ردیف را حذف کنید.');
                    }
                } else if ($button.hasClass('duplicate-row')) {
                    console.log('PrintOrder: Duplicate row clicked');
                    const $newRow = $row.clone().addClass('animate-duplicate-row');
                    $newRow.find('input, select').val($row.find('input, select').val());
                    $tbody.append($newRow);
                    console.log('PrintOrder: Row duplicated');
                    updateRowIndexes();
                }
            });
        }

        // به‌روزرسانی اندیس‌های ردیف‌ها
        function updateRowIndexes() {
            console.log('PrintOrder: Updating row indexes');
            const $rows = $('#combination-rows tr');
            $rows.each(function (index) {
                const $row = $(this);
                $row.find('select[name*="[category_id]"]').attr('name', `combinations[${index}][category_id]`);
                $row.find('select[name*="[paper_type_persian]"]').attr('name', `combinations[${index}][paper_type_persian]`);
                $row.find('input[name*="[shortcode_id]"]').attr('name', `combinations[${index}][shortcode_id]`);
            });
            console.log('PrintOrder: Row indexes updated, total rows:', $rows.length);
        }

        // اجرای توابع اولیه
        console.log('PrintOrder: Initializing template combinations script');
        initializeTabs();
        initializeAddRow();
        initializeRowActions();
    });

})(jQuery.noConflict());
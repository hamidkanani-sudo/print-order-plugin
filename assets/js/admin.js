document.addEventListener('DOMContentLoaded', function() {
    console.log('PrintOrderAdmin: Script loaded - v1.0.36');

    // بررسی jQuery
    if (typeof jQuery === 'undefined') {
        console.error('PrintOrderAdmin: jQuery is not defined');
    } else {
        console.log('PrintOrderAdmin: jQuery is available, version:', jQuery.fn.jquery);
    }

    // مدیریت اضافه کردن ردیف جدید در صفحه Category Fields
    const addButtons = document.querySelectorAll('.add-field-row');
    console.log('PrintOrderAdmin: Found', addButtons.length, 'add-field-row buttons');

    addButtons.forEach(button => {
        console.log('PrintOrderAdmin: Binding click event for button with data-category =', button.getAttribute('data-category'));
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('PrintOrderAdmin: Add field button clicked');
            const categoryId = this.getAttribute('data-category');
            console.log('PrintOrderAdmin: Category ID =', categoryId);
            const tbody = document.getElementById('field-rows-' + categoryId);
            if (!tbody) {
                console.error('PrintOrderAdmin: Tbody not found for category ID =', categoryId);
                return;
            }
            const rowCount = tbody.children.length;
            console.log('PrintOrderAdmin: Adding row number', rowCount);
            const newRow = `
                <tr class="animate-fade-in">
                    <td data-label="Field Name">
                        <input type="text" name="fields[${categoryId}][${rowCount}][name]" value="" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                    </td>
                    <td data-label="Type">
                        <select name="fields[${categoryId}][${rowCount}][type]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </td>
                    <td data-label="Label">
                        <input type="text" name="fields[${categoryId}][${rowCount}][label]" value="" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                    </td>
                    <td class="actions">
                        <button type="button" class="remove-row" title="حذف">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', newRow);
            console.log('PrintOrderAdmin: New row added for category ID =', categoryId);
        });
    });

    // مدیریت حذف ردیف‌ها فقط برای جدول‌های category fields
    document.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.category-fields-table .remove-row');
        if (removeButton) {
            console.log('PrintOrderAdmin: Remove row button clicked in category fields table');
            const row = removeButton.closest('tr');
            row.classList.add('animate-slide-out');
            setTimeout(() => {
                row.remove();
                console.log('PrintOrderAdmin: Row removed');
            }, 300);
        }
    });

    // مدیریت تب‌ها با دیباگ
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    console.log('PrintOrderAdmin: Found', tabLinks.length, 'tab links');
    console.log('PrintOrderAdmin: Found', tabPanes.length, 'tab panes');

    if (tabLinks.length === 0 || tabPanes.length === 0) {
        console.error('PrintOrderAdmin: Tab links or panes not found');
        return;
    }

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('PrintOrderAdmin: Tab clicked, data-tab =', this.getAttribute('data-tab'));

            // حذف کلاس‌های active و استایل از همه تب‌ها
            tabLinks.forEach(l => {
                l.classList.remove('active', 'bg-gray-100');
                console.log('PrintOrderAdmin: Removed active from tab', l.getAttribute('data-tab'));
            });

            // مخفی کردن همه پنل‌ها
            tabPanes.forEach(p => {
                p.classList.add('hidden');
                console.log('PrintOrderAdmin: Hid pane', p.id);
            });

            // فعال کردن تب کلیک‌شده
            this.classList.add('active', 'bg-gray-100');
            console.log('PrintOrderAdmin: Activated tab', this.getAttribute('data-tab'));

            // نمایش پنل مربوطه
            const tabId = this.getAttribute('data-tab');
            const targetPane = document.getElementById(tabId);
            if (targetPane) {
                targetPane.classList.remove('hidden');
                console.log('PrintOrderAdmin: Showed pane', tabId);
            } else {
                console.error('PrintOrderAdmin: Pane not found for tab', tabId);
            }
        });
    });
});
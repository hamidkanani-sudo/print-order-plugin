jQuery(document).ready(function($) {
    // نمایش/مخفی کردن مدال افزودن/ویرایش
    $('#add-discount-btn').on('click', function() {
        $('#add-discount-modal').show();
        $('#add-discount-form')[0].reset();
        $('#discount-id').val('');
        $('#add-discount-form input[name="apply_to[]"]').prop('checked', false);
    });

    // بستن مدال
    $('.modal .close').on('click', function() {
        $(this).closest('.modal').hide();
    });

    // انتخاب همه چک‌باکس‌ها
    $('#select-all').on('change', function() {
        $('#discounts-form input[name="discount_ids[]"]').prop('checked', this.checked);
    });

    // ویرایش کد تخفیف
    $(document).on('click', '.discount-edit', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: printOrderDiscount.ajax_url,
            type: 'POST',
            data: {
                action: 'print_order_get_discount',
                nonce: printOrderDiscount.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#add-discount-modal').show();
                    $('#discount-id').val(data.id);
                    $('#discount-code').val(data.code);
                    $('#discount-type').val(data.discount_type);
                    $('#discount-value').val(data.discount_value);
                    $('#start-date').val(data.start_date);
                    $('#start-time').val(data.start_time);
                    $('#end-date').val(data.end_date);
                    $('#end-time').val(data.end_time);
                    $('#min-order-amount').val(data.min_order_amount || 0);
                    $('#usage-limit-per-user').val(data.usage_limit_per_user || 0);
                    $('#usage-limit-total').val(data.usage_limit_total || 0);
                    $('#discount-status').val(data.status);
                    $('#add-discount-form input[name="apply_to[]"]').prop('checked', false);
                    if (Array.isArray(data.apply_to)) {
                        data.apply_to.forEach(function(value) {
                            $('#add-discount-form input[name="apply_to[]"][value="' + value + '"]').prop('checked', true);
                        });
                    }
                } else {
                    $('#discount-notices').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                $('#discount-notices').html('<div class="notice notice-error"><p>خطا در دریافت اطلاعات: ' + error + '</p></div>');
            }
        });
    });

    // ارسال فرم افزودن/ویرایش
    $('#add-discount-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        if (!formData.some(item => item.name === 'apply_to[]')) {
            formData.push({ name: 'apply_to[]', value: '' });
        }
        console.log('Form data:', formData);
        var action = $('#discount-id').val() ? 'print_order_update_discount' : 'print_order_add_discount';
        formData.push({ name: 'action', value: action });
        formData.push({ name: 'nonce', value: printOrderDiscount.nonce });
        $.ajax({
            url: printOrderDiscount.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    $('#discount-notices').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                    $('#add-discount-modal').hide();
                    location.reload();
                } else {
                    $('#discount-notices').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', xhr, status, error);
                $('#discount-notices').html('<div class="notice notice-error"><p>خطا در ارسال درخواست: ' + error + '</p></div>');
            }
        });
    });

    // حذف کدهای تخفیف
    $('#delete-selected').on('click', function() {
        var selected = $('#discounts-form input[name="discount_ids[]"]:checked').map(function() {
            return this.value;
        }).get();
        if (!selected.length) {
            $('#discount-notices').html('<div class="notice notice-error"><p>هیچ کدی برای حذف انتخاب نشده است.</p></div>');
            return;
        }
        if (!confirm('آیا مطمئن هستید که می‌خواهید کدهای انتخاب‌شده را حذف کنید؟')) {
            return;
        }
        $.ajax({
            url: printOrderDiscount.ajax_url,
            type: 'POST',
            data: {
                action: 'print_order_delete_discounts',
                nonce: printOrderDiscount.nonce,
                discount_ids: selected
            },
            success: function(response) {
                if (response.success) {
                    $('#discount-notices').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                    location.reload();
                } else {
                    $('#discount-notices').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                $('#discount-notices').html('<div class="notice notice-error"><p>خطا در حذف: ' + error + '</p></div>');
            }
        });
    });

    // تغییر وضعیت
    $('#toggle-status').on('click', function() {
        var selected = $('#discounts-form input[name="discount_ids[]"]:checked').map(function() {
            return this.value;
        }).get();
        if (!selected.length) {
            $('#discount-notices').html('<div class="notice notice-error"><p>هیچ کدی برای تغییر وضعیت انتخاب نشده است.</p></div>');
            return;
        }
        var status = prompt('وضعیت جدید (active یا inactive):');
        if (!status || !['active', 'inactive'].includes(status)) {
            $('#discount-notices').html('<div class="notice notice-error"><p>وضعیت نامعتبر است.</p></div>');
            return;
        }
        $.ajax({
            url: printOrderDiscount.ajax_url,
            type: 'POST',
            data: {
                action: 'print_order_toggle_discount_status',
                nonce: printOrderDiscount.nonce,
                discount_ids: selected,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    $('#discount-notices').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                    location.reload();
                } else {
                    $('#discount-notices').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                $('#discount-notices').html('<div class="notice notice-error"><p>خطا در تغییر وضعیت: ' + error + '</p></div>');
            }
        });
    });
});
<?php
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Discount_Codes {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_print_order_add_discount', [$this, 'ajax_add_discount']);
        add_action('wp_ajax_print_order_update_discount', [$this, 'ajax_update_discount']);
        add_action('wp_ajax_print_order_delete_discounts', [$this, 'ajax_delete_discounts']);
        add_action('wp_ajax_print_order_toggle_discount_status', [$this, 'ajax_toggle_discount_status']);
        add_action('wp_ajax_print_order_get_discount', [$this, 'ajax_get_discount']);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'print-order-settings',
            'مدیریت کدهای تخفیف',
            'کدهای تخفیف',
            'manage_options',
            'print-order-discounts',
            [$this, 'render_discount_codes_page']
        );
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'print-order') === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Skipping enqueue for hook: ' . $hook);
            }
            return;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Enqueuing scripts for hook: ' . $hook);
            error_log('Print Order: Attempting to enqueue style from ' . PRINT_ORDER_URL . 'assets/css/class-discount-codes-tw.css');
            error_log('Print Order: Attempting to enqueue script from ' . PRINT_ORDER_URL . 'assets/js/discount-codes.js');
        }

        wp_enqueue_style(
            'print-order-discount-codes-tw',
            PRINT_ORDER_URL . 'assets/css/class-discount-codes-tw.css',
            [],
            file_exists(PRINT_ORDER_PATH . 'assets/css/class-discount-codes-tw.css') ? filemtime(PRINT_ORDER_PATH . 'assets/css/class-discount-codes-tw.css') : '1.0.4'
        );

        wp_enqueue_script('jquery-core');
        wp_enqueue_script('jquery-migrate');

        wp_enqueue_script(
            'print-order-discount-codes',
            PRINT_ORDER_URL . 'assets/js/discount-codes.js',
            ['jquery-core', 'jquery-migrate'],
            file_exists(PRINT_ORDER_PATH . 'assets/js/discount-codes.js') ? filemtime(PRINT_ORDER_PATH . 'assets/js/discount-codes.js') : '1.0.4',
            true
        );

        wp_localize_script('print-order-discount-codes', 'printOrderDiscount', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('print_order_discount_nonce'),
        ]);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $wp_styles = wp_styles();
            $wp_scripts = wp_scripts();
            error_log('Print Order: Enqueued style print-order-discount-codes-tw: ' . (isset($wp_styles->registered['print-order-discount-codes-tw']) ? 'success' : 'failed'));
            error_log('Print Order: Enqueued script jquery-core: ' . (isset($wp_scripts->registered['jquery-core']) ? 'success' : 'failed'));
            error_log('Print Order: Enqueued script jquery-migrate: ' . (isset($wp_scripts->registered['jquery-migrate']) ? 'success' : 'failed'));
            error_log('Print Order: Enqueued script print-order-discount-codes: ' . (isset($wp_scripts->registered['print-order-discount-codes']) ? 'success' : 'failed'));
        }
    }

    public function render_discount_codes_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';
        $per_page = 10;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

        $query = "SELECT * FROM $table_name WHERE 1=1";
        if ($search) {
            $query .= $wpdb->prepare(" AND code LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }
        if ($status_filter) {
            $query .= $wpdb->prepare(" AND status = %s", $status_filter);
        }
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM ($query) as total_query");
        $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $discounts = $wpdb->get_results($wpdb->prepare($query, $per_page, ($current_page - 1) * $per_page), ARRAY_A);
        $total_pages = ceil($total_items / $per_page);

        $print_order = new Print_Order();
        ?>
        <div class="wrap">
            <h1 class="text-2xl font-bold mb-4">مدیریت کدهای تخفیف</h1>
            <div id="discount-notices" class="mb-4"></div>
            <button class="button button-primary mb-4" id="add-discount-btn">افزودن کد تخفیف</button>
            <form method="get" class="search-form mb-4 flex gap-2">
                <input type="hidden" name="page" value="print-order-discounts">
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="جستجوی کد تخفیف" class="border rounded px-2 py-1">
                <select name="status" class="border rounded px-2 py-1">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" <?php selected($status_filter, 'active'); ?>>فعال</option>
                    <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>غیرفعال</option>
                    <option value="expired" <?php selected($status_filter, 'expired'); ?>>منقضی</option>
                </select>
                <button type="submit" class="button">فیلتر</button>
            </form>
            <form id="discounts-form" method="post">
                <table class="print-order-discount-table widefat striped">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2"><input type="checkbox" id="select-all"></th>
                            <th class="p-2">کد تخفیف</th>
                            <th class="p-2">نوع تخفیف</th>
                            <th class="p-2">مقدار</th>
                            <th class="p-2">تاریخ شروع</th>
                            <th class="p-2">تاریخ انقضا</th>
                            <th class="p-2">تعداد استفاده</th>
                            <th class="p-2">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $discount): ?>
                            <?php
                            $start_date = $print_order->convert_to_persian_date($discount['start_date']);
                            $end_date = $print_order->convert_to_persian_date($discount['end_date']);
                            $value = $discount['discount_type'] === 'percent' ? $discount['discount_value'] . '%' : number_format($discount['discount_value']) . ' تومان';
                            ?>
                            <tr data-id="<?php echo esc_attr($discount['id']); ?>">
                                <td class="p-2"><input type="checkbox" name="discount_ids[]" value="<?php echo esc_attr($discount['id']); ?>"></td>
                                <td class="p-2"><a href="#" class="discount-edit text-blue-600 hover:underline" data-id="<?php echo esc_attr($discount['id']); ?>"><?php echo esc_html($discount['code']); ?></a></td>
                                <td class="p-2"><?php echo $discount['discount_type'] === 'percent' ? 'درصدی' : 'مبلغ ثابت'; ?></td>
                                <td class="p-2"><?php echo esc_html($value); ?></td>
                                <td class="p-2"><?php echo esc_html($start_date['date']); ?> <?php echo esc_html($start_date['time']); ?></td>
                                <td class="p-2"><?php echo esc_html($end_date['date']); ?> <?php echo esc_html($end_date['time']); ?></td>
                                <td class="p-2"><?php echo esc_html($discount['usage_count']); ?></td>
                                <td class="p-2"><?php echo esc_html($discount['status'] === 'active' ? 'فعال' : ($discount['status'] === 'inactive' ? 'غیرفعال' : 'منقضی')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="tablenav bottom mt-4 flex justify-between items-center">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links([
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '?paged=%#%',
                            'total' => $total_pages,
                            'current' => $current_page,
                        ]);
                        ?>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="button action" id="delete-selected">حذف انتخاب‌شده‌ها</button>
                        <button type="button" class="button action" id="toggle-status">تغییر وضعیت</button>
                    </div>
                </div>
            </form>
            <div id="add-discount-modal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center" style="display:none;">
                <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-full max-w-md max-h-[80vh] overflow-y-auto">
                    <span class="close text-2xl cursor-pointer float-right">&times;</span>
                    <h2 class="text-xl font-bold mb-4">افزودن/ویرایش کد تخفیف</h2>
                    <form id="add-discount-form" class="space-y-4">
                        <input type="hidden" name="id" id="discount-id">
                        <div class="flex flex-col">
                            <label class="font-medium">کد تخفیف</label>
                            <input type="text" name="code" id="discount-code" required class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">نوع تخفیف</label>
                            <select name="discount_type" id="discount-type" class="border rounded px-2 py-1">
                                <option value="percent">درصدی</option>
                                <option value="fixed">مبلغ ثابت</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">مقدار تخفیف</label>
                            <input type="number" name="discount_value" id="discount-value" required class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">تاریخ شروع (اختیاری)</label>
                            <input type="date" name="start_date" id="start-date" class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">ساعت شروع (اختیاری)</label>
                            <input type="time" name="start_time" id="start-time" class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">تاریخ انقضا (اختیاری)</label>
                            <input type="date" name="end_date" id="end-date" class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">ساعت انقضا (اختیاری)</label>
                            <input type="time" name="end_time" id="end-time" class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">حداقل مبلغ سفارش</label>
                            <input type="number" name="min_order_amount" id="min-order-amount" value="0" class="border rounded px-2 py-1">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">محدودیت استفاده برای هر کاربر</label>
                            <input type="number" name="usage_limit_per_user" id="usage-limit-per-user" value="0" class="border rounded px-2 py-1">
                            <small class="text-gray-500">0 به معنی نامحدود است</small>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">محدودیت کل استفاده</label>
                            <input type="number" name="usage_limit_total" id="usage-limit-total" value="0" class="border rounded px-2 py-1">
                            <small class="text-gray-500">0 به معنی نامحدود است</small>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">اعمال بر</label>
                            <div class="space-y-2">
                                <label><input type="checkbox" name="apply_to[]" value="design_fee"> هزینه طرح</label>
                                <label><input type="checkbox" name="apply_to[]" value="print_fee"> هزینه چاپ</label>
                                <label><input type="checkbox" name="apply_to[]" value="design_service"> هزینه طراحی</label>
                                <label><input type="checkbox" name="apply_to[]" value="shipping_fee"> هزینه ارسال</label>
                                <label><input type="checkbox" name="apply_to[]" value="tax"> مالیات</label>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-medium">وضعیت</label>
                            <select name="status" id="discount-status" class="border rounded px-2 py-1">
                                <option value="active">فعال</option>
                                <option value="inactive">غیرفعال</option>
                            </select>
                        </div>
                        <button type="submit" class="button button-primary w-full mt-4 sticky bottom-0 bg-blue-500 text-white">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_add_discount() {
        check_ajax_referer('print_order_discount_nonce', 'nonce');
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: ajax_add_discount called with data: ' . print_r($_POST, true));
        }

        // اعتبارسنجی داده‌ها
        if (empty($_POST['code']) || empty($_POST['discount_type']) || empty($_POST['discount_value']) || empty($_POST['status'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Missing required fields in ajax_add_discount');
            }
            wp_send_json_error('لطفاً تمام فیلدهای الزامی را پر کنید.');
        }

        // مدیریت تاریخ‌های اختیاری
        $start_datetime = !empty($_POST['start_date']) && !empty($_POST['start_time'])
            ? sanitize_text_field($_POST['start_date'] . ' ' . $_POST['start_time'])
            : current_time('mysql');
        $end_datetime = !empty($_POST['end_date']) && !empty($_POST['end_time'])
            ? sanitize_text_field($_POST['end_date'] . ' ' . $_POST['end_time'])
            : date('Y-m-d H:i:s', strtotime('+1 year'));

        if (!strtotime($start_datetime) || !strtotime($end_datetime)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Invalid date format in ajax_add_discount');
            }
            wp_send_json_error('فرمت تاریخ یا ساعت نامعتبر است.');
        }

        $data = [
            'code' => sanitize_text_field($_POST['code']),
            'discount_type' => sanitize_text_field($_POST['discount_type']),
            'discount_value' => intval($_POST['discount_value']),
            'start_date' => date('Y-m-d H:i:s', strtotime($start_datetime)),
            'end_date' => date('Y-m-d H:i:s', strtotime($end_datetime)),
            'min_order_amount' => intval($_POST['min_order_amount'] ?? 0),
            'usage_limit_per_user' => intval($_POST['usage_limit_per_user'] ?? 0),
            'usage_limit_total' => intval($_POST['usage_limit_total'] ?? 0),
            'apply_to' => maybe_serialize(array_map('sanitize_text_field', $_POST['apply_to'] ?? [])),
            'status' => sanitize_text_field($_POST['status']),
            'created_at' => current_time('mysql')
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Prepared data for insertion: ' . print_r($data, true));
        }

        $existing_code = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE code = %s", $data['code']));
        if ($existing_code) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Duplicate discount code: ' . $data['code']);
            }
            wp_send_json_error('کد تخفیف قبلاً وجود دارد.');
        }

        $result = $wpdb->insert($table_name, $data);
        if ($result) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Discount code inserted successfully, ID: ' . $wpdb->insert_id);
            }
            wp_send_json_success('کد تخفیف با موفقیت اضافه شد.');
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Failed to insert discount code: ' . $wpdb->last_error);
            }
            wp_send_json_error('خطا در افزودن کد تخفیف: ' . $wpdb->last_error);
        }
    }

    public function ajax_update_discount() {
        check_ajax_referer('print_order_discount_nonce', 'nonce');
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: ajax_update_discount called with data: ' . print_r($_POST, true));
        }

        if (empty($_POST['id']) || empty($_POST['code']) || empty($_POST['discount_type']) || empty($_POST['discount_value']) || empty($_POST['status'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Missing required fields in ajax_update_discount');
            }
            wp_send_json_error('لطفاً تمام فیلدهای الزامی را پر کنید.');
        }

        $start_datetime = !empty($_POST['start_date']) && !empty($_POST['start_time'])
            ? sanitize_text_field($_POST['start_date'] . ' ' . $_POST['start_time'])
            : current_time('mysql');
        $end_datetime = !empty($_POST['end_date']) && !empty($_POST['end_time'])
            ? sanitize_text_field($_POST['end_date'] . ' ' . $_POST['end_time'])
            : date('Y-m-d H:i:s', strtotime('+1 year'));

        if (!strtotime($start_datetime) || !strtotime($end_datetime)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Invalid date format in ajax_update_discount');
            }
            wp_send_json_error('فرمت تاریخ یا ساعت نامعتبر است.');
        }

        $data = [
            'code' => sanitize_text_field($_POST['code']),
            'discount_type' => sanitize_text_field($_POST['discount_type']),
            'discount_value' => intval($_POST['discount_value']),
            'start_date' => date('Y-m-d H:i:s', strtotime($start_datetime)),
            'end_date' => date('Y-m-d H:i:s', strtotime($end_datetime)),
            'min_order_amount' => intval($_POST['min_order_amount'] ?? 0),
            'usage_limit_per_user' => intval($_POST['usage_limit_per_user'] ?? 0),
            'usage_limit_total' => intval($_POST['usage_limit_total'] ?? 0),
            'apply_to' => maybe_serialize(array_map('sanitize_text_field', $_POST['apply_to'] ?? [])),
            'status' => sanitize_text_field($_POST['status']),
        ];

        $existing_code = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE code = %s AND id != %d", $data['code'], intval($_POST['id'])));
        if ($existing_code) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Duplicate discount code: ' . $data['code']);
            }
            wp_send_json_error('کد تخفیف قبلاً وجود دارد.');
        }

        $result = $wpdb->update($table_name, $data, ['id' => intval($_POST['id'])]);
        if ($result !== false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Discount code updated successfully, ID: ' . intval($_POST['id']));
            }
            wp_send_json_success('کد تخفیف با موفقیت به‌روزرسانی شد.');
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Failed to update discount code: ' . $wpdb->last_error);
            }
            wp_send_json_error('خطا در به‌روزرسانی کد تخفیف: ' . $wpdb->last_error);
        }
    }

    public function ajax_delete_discounts() {
        check_ajax_referer('print_order_discount_nonce', 'nonce');
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';
        $ids = array_map('intval', $_POST['discount_ids'] ?? []);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: ajax_delete_discounts called with IDs: ' . print_r($ids, true));
        }

        if (empty($ids)) {
            wp_send_json_error('هیچ کدی برای حذف انتخاب نشده است.');
        }

        $result = $wpdb->query("DELETE FROM $table_name WHERE id IN (" . implode(',', $ids) . ")");
        if ($result) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Discount codes deleted successfully, IDs: ' . implode(',', $ids));
            }
            wp_send_json_success('کدهای تخفیف با موفقیت حذف شدند.');
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Failed to delete discount codes: ' . $wpdb->last_error);
            }
            wp_send_json_error('خطا در حذف کدهای تخفیف: ' . $wpdb->last_error);
        }
    }

    public function ajax_toggle_discount_status() {
        check_ajax_referer('print_order_discount_nonce', 'nonce');
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';
        $ids = array_map('intval', $_POST['discount_ids'] ?? []);
        $status = sanitize_text_field($_POST['status'] ?? '');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: ajax_toggle_discount_status called with IDs: ' . print_r($ids, true) . ', Status: ' . $status);
        }

        if (empty($ids)) {
            wp_send_json_error('هیچ کدی برای تغییر وضعیت انتخاب نشده است.');
        }

        if (!in_array($status, ['active', 'inactive'])) {
            wp_send_json_error('وضعیت نامعتبر است.');
        }

        $result = $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = %s WHERE id IN (" . implode(',', $ids) . ")", $status));
        if ($result) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Discount status toggled successfully, IDs: ' . implode(',', $ids));
            }
            wp_send_json_success('وضعیت کدهای تخفیف با موفقیت تغییر کرد.');
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Failed to toggle discount status: ' . $wpdb->last_error);
            }
            wp_send_json_error('خطا در تغییر وضعیت کدهای تخفیف: ' . $wpdb->last_error);
        }
    }

    public function ajax_get_discount() {
        check_ajax_referer('print_order_discount_nonce', 'nonce');
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';
        $id = intval($_POST['id'] ?? 0);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: ajax_get_discount called with ID: ' . $id);
        }

        if ($id <= 0) {
            wp_send_json_error('شناسه نامعتبر است.');
        }

        $discount = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        if ($discount) {
            try {
                $print_order = new Print_Order();
                $start_date = $print_order->convert_to_persian_date($discount['start_date']);
                $end_date = $print_order->convert_to_persian_date($discount['end_date']);
                $discount['start_date'] = $start_date['date'];
                $discount['start_time'] = $start_date['time'];
                $discount['end_date'] = $end_date['date'];
                $discount['end_time'] = $end_date['time'];
                $discount['apply_to'] = maybe_unserialize($discount['apply_to']) ?: [];
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Print Order: Discount data retrieved: ' . print_r($discount, true));
                }
                wp_send_json_success($discount);
            } catch (Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Print Order: Error in convert_to_persian_date: ' . $e->getMessage());
                }
                wp_send_json_error('خطا در تبدیل تاریخ: ' . $e->getMessage());
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Discount code not found for ID: ' . $id);
            }
            wp_send_json_error('کد تخفیف یافت نشد.');
        }
    }
}
?>
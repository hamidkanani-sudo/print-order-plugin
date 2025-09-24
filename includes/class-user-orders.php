<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_User_Orders {
    private $print_order_instance;

    public function __construct() {
        add_action('init', [$this, 'register_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_print_order_get_order_details', [$this, 'handle_details_request']);
        add_action('wp_ajax_print_order_update_order', [$this, 'handle_update_request']);
        add_action('wp_ajax_print_order_check_revision_status', [$this, 'handle_check_revision_status']);
        add_action('wp_ajax_print_order_user_upload_design', [$this, 'handle_user_upload_design']);
        add_action('wp_ajax_print_order_serve_private_image', [$this, 'handle_serve_private_image']);
        add_action('wp_ajax_print_order_download_private_file', [$this, 'handle_download_private_file']);
        add_action('wp_ajax_nopriv_print_order_get_order_details', [$this, 'handle_details_request']);
        add_action('wp_ajax_nopriv_print_order_update_order', [$this, 'handle_update_request']);
        add_action('wp_ajax_nopriv_print_order_check_revision_status', [$this, 'handle_check_revision_status']);
        add_action('wp_ajax_nopriv_print_order_user_upload_design', [$this, 'handle_user_upload_design']);
        add_action('wp_ajax_nopriv_print_order_serve_private_image', [$this, 'handle_serve_private_image']);
        add_action('wp_ajax_nopriv_print_order_download_private_file', [$this, 'handle_download_private_file']);
        // Initialize print_order instance
        $this->print_order_instance = new Print_Order();
    }

    public function register_scripts() {
        wp_register_script(
            'print-order-user-orders',
            PRINT_ORDER_URL . 'assets/js/user-orders.js',
            ['jquery'],
            filemtime(PRINT_ORDER_PATH . 'assets/js/user-orders.js'),
            true
        );
    }

    public function enqueue_scripts() {
        $current_slug = get_post_field('post_name', get_the_ID());
        if ($current_slug === 'order-details' || has_shortcode(get_post()->post_content ?? '', 'print_order_user_orders')) {
            wp_enqueue_script('print-order-user-orders');
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
            $order = $order_id ? wc_get_order($order_id) : null;
            $design_confirmed = $order ? ($order->get_meta('_print_order_design_confirmed') === 'yes' ? 'yes' : 'no') : 'no';
            $unread_messages = $order ? intval($order->get_meta('_print_order_unread_messages') ?? 0) : 0;

            // Get WooCommerce order statuses dynamically
            $wc_statuses = wc_get_order_statuses();
            $status_options = [];
            foreach ($wc_statuses as $status_slug => $status_name) {
                $status_options[str_replace('wc-', '', $status_slug)] = $status_name;
            }

            wp_localize_script('print-order-user-orders', 'printOrder', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('print_order_nonce'),
                'status_options' => $status_options,
                'design_confirmed' => $design_confirmed,
                'unread_messages' => $unread_messages,
                'order_id' => $order_id,
            ]);
        }
    }

    public function get_user_orders($user_id, $per_page = 10, $paged = 1, $status = '', $sort = 'date_desc') {
        $args = [
            'customer_id' => $user_id,
            'limit' => $per_page,
            'paged' => $paged,
            'type' => 'shop_order',
            'return' => 'objects',
        ];

        if ($status) {
            $args['status'] = $status;
        }

        switch ($sort) {
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'date_desc':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        return wc_get_orders($args);
    }

    public function get_order_status_steps($order) {
        $custom_status = $order->get_meta('_print_order_delivery_status') ?: '';
        return [
            'ثبت سفارش' => ['icon' => PRINT_ORDER_URL . 'assets/icons/order-registered.svg', 'status' => 'wc-order-registered'],
            'تکمیل پرداخت' => ['icon' => PRINT_ORDER_URL . 'assets/icons/payment-completed.svg', 'status' => 'wc-payment-completed'],
            'تأیید طرح' => ['icon' => PRINT_ORDER_URL . 'assets/icons/3.svg', 'status' => 'wc-design-approved'],
            'چاپ' => ['icon' => PRINT_ORDER_URL . 'assets/icons/4.svg', 'status' => 'wc-printing'],
            'ارسال' => ['icon' => PRINT_ORDER_URL . 'assets/icons/5.svg', 'status' => 'wc-shipping'],
            'تحویل‌شده' => ['icon' => PRINT_ORDER_URL . 'assets/icons/5.svg', 'status' => $custom_status === 'delivered' ? 'delivered' : 'wc-shipping'],
        ];
    }

    public function get_current_step($order) {
        $steps = $this->get_order_status_steps($order);
        $order_status = $order->get_status();
        $custom_status = $order->get_meta('_print_order_delivery_status') ?: '';
        $design_confirmed = $order->get_meta('_print_order_design_confirmed') === 'yes';

        foreach ($steps as $step_name => $step_data) {
            if ($step_data['status'] === $order_status || ($step_name === 'تحویل‌شده' && $custom_status === 'delivered')) {
                if ($step_name === 'چاپ' && !$design_confirmed && $order_status === 'printing') {
                    return 'تأیید طرح';
                }
                return $step_name;
            }
        }
        return array_key_first($steps);
    }

    public function get_order_details($order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $date_created = $order->get_date_created();
            $persian_date = $date_created ? $this->convert_date_to_persian($date_created) : ['date' => '-', 'time' => '-'];
            return [
                'number' => $order->get_order_number(),
                'date' => $persian_date['date'],
                'product' => get_the_title($order->get_meta('_print_order_wc_product_id')) ?: '-',
                'status' => $this->get_current_step($order),
                'total' => number_format($order->get_total()) . ' ' . __('تومان', 'print-order'),
            ];
        }
        return [];
    }

    public function get_design_history($order) {
        $history = $order->get_meta('_print_order_revision_history') ?: [];
        return is_array($history) ? $history : [];
    }

    public function handle_details_request() {
        check_ajax_referer('print_order_nonce', 'nonce');
        error_log('Ajax details request received with nonce: ' . ($_POST['nonce'] ?? 'not set') . ', order_id: ' . ($_POST['order_id'] ?? 'not set'));

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'details';
        $order = wc_get_order($order_id);

        error_log('Initial check - Order ID: ' . $order_id . ', Tab: ' . $tab . ', Order exists: ' . ($order ? 'yes' : 'no') . ', User ID: ' . get_current_user_id() . ', Customer ID: ' . ($order ? $order->get_customer_id() : 'N/A'));

        if (!$order) {
            error_log('Error: Order not found for order_id: ' . $order_id);
            wp_send_json_error(['message' => __('سفارش یافت نشد.', 'print-order')]);
            wp_die();
        }

        if ($order->get_customer_id() !== get_current_user_id()) {
            error_log('Error: Access denied for order_id: ' . $order_id . ', User ID: ' . get_current_user_id() . ', Customer ID: ' . $order->get_customer_id());
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        try {
            $product_name = get_the_title($order->get_meta('_print_order_wc_product_id')) ?: 'نامشخص';
            error_log('Product name retrieved: ' . $product_name);

            $html = print_order_user_orders_details($order_id, $tab);

            error_log('Raw HTML output length: ' . strlen($html));
            if (empty($html)) {
                error_log('Debug: Checking ob_get_level: ' . ob_get_level());
                error_log('Debug: Checking output buffer contents: ' . var_export(ob_get_contents(), true));
                error_log('Warning: HTML output is empty for order_id: ' . $order_id . ', Tab: ' . $tab);
                wp_send_json_error(['message' => __('خطا در تولید محتوای جزییات.', 'print-order')]);
            } else {
                error_log('HTML generated successfully with length: ' . strlen($html));
                wp_send_json_success(['html' => $html, 'product_name' => $product_name]);
            }
        } catch (Exception $e) {
            error_log('Exception in handle_details_request: ' . $e->getMessage());
            wp_send_json_error(['message' => __('خطای سرور: ' . $e->getMessage(), 'print-order')]);
        }

        wp_die();
    }

    public function handle_update_request() {
        check_ajax_referer('print_order_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        $order = wc_get_order($order_id);

        if ($order && $order->get_customer_id() === get_current_user_id()) {
            $options = get_option('print_order_options', []);
            $max_revisions = intval($options['max_design_revisions'] ?? 3);
            $extra_revisions = intval($order->get_meta('_print_order_extra_revisions') ?? 0);
            $history = $order->get_meta('_print_order_revision_history') ?: [];
            $revision_count = 0;
            foreach ($history as $entry) {
                if (isset($entry['note']) || isset($entry['user_file'])) {
                    $revision_count++;
                }
            }
            $remaining_revisions = max(0, $max_revisions + $extra_revisions - $revision_count);
            $design_confirmed = $order->get_meta('_print_order_design_confirmed') === 'yes';
            $unread_messages = intval($order->get_meta('_print_order_unread_messages') ?? 0);

            if ($action === 'revision_request') {
                $revision_note = isset($_POST['revision_note']) ? sanitize_textarea_field($_POST['revision_note']) : '';
                if (empty($revision_note)) {
                    wp_send_json_error(['message' => __('توضیحات درخواست نمی‌تواند خالی باشد.', 'print-order')]);
                    wp_die();
                }
                if ($remaining_revisions <= 0) {
                    wp_send_json_error(['message' => __('حداکثر تعداد درخواست‌های ویرایش پر شده است.', 'print-order')]);
                    wp_die();
                }
                $new_revision = [
                    'date' => current_time('Y-m-d H:i:s'),
                    'note' => $revision_note,
                ];
                $uploaded_file = $order->get_meta('_print_order_user_uploaded_file');
                if ($uploaded_file) {
                    $new_revision['user_file'] = $uploaded_file;
                    $order->delete_meta_data('_print_order_user_uploaded_file');
                }
                $history[] = $new_revision;
                $order->update_meta_data('_print_order_revision_history', $history);
                $order->update_meta_data('_print_order_last_revision_request', current_time('Y-m-d H:i:s'));
                $order->update_meta_data('_print_order_unread_messages', $unread_messages + 1);
                $order->add_order_note('درخواست اصلاح طرح توسط مشتری: ' . $revision_note . ($uploaded_file ? ' (فایل آپلود شد)' : ''), true);
                $order->save();
                wp_send_json_success([
                    'message' => __('درخواست اصلاح ثبت شد.', 'print-order'),
                    'notification' => true,
                    'remaining_revisions' => $remaining_revisions - 1,
                ]);
            } elseif ($action === 'confirm_design') {
                if ($design_confirmed) {
                    wp_send_json_error(['message' => __('طرح قبلاً تأیید شده است.', 'print-order')]);
                } else {
                    $order->update_meta_data('_print_order_design_confirmed', 'yes');
                    $order->update_status('design-approved', __('طرح توسط مشتری تأیید شد.', 'print-order'));
                    $order->add_order_note('طرح توسط مشتری تأیید شد.', true);
                    $order->save();
                    wp_send_json_success(['message' => __('طرح تأیید شد.', 'print-order')]);
                }
            } elseif ($action === 'shipping_update') {
                $shipping_method = isset($_POST['shipping_method']) ? sanitize_text_field($_POST['shipping_method']) : '';
                $tracking_code = isset($_POST['tracking_code']) ? sanitize_text_field($_POST['tracking_code']) : '';
                $shipping_date = isset($_POST['shipping_date']) ? sanitize_text_field($_POST['shipping_date']) : '';

                $order->update_meta_data('_print_order_shipping_method', $shipping_method);
                $order->update_meta_data('_print_order_tracking', $tracking_code);
                $order->update_meta_data('_print_order_shipping_date', $shipping_date);
                $order->update_meta_data('_print_order_delivery_status', 'shipping');
                $order->save();
                wp_send_json_success(['message' => __('اطلاعات ارسال به‌روزرسانی شد.', 'print-order')]);
            } elseif ($action === 'mark_messages_read') {
                $order->update_meta_data('_print_order_unread_messages', 0);
                $order->save();
                wp_send_json_success(['message' => __('پیام‌ها به عنوان خوانده‌شده علامت‌گذاری شدند.', 'print-order')]);
            } else {
                wp_send_json_error(['message' => __('اقدام نامعتبر.', 'print-order')]);
            }
        }

        wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
        wp_die();
    }

    public function handle_check_revision_status() {
        check_ajax_referer('print_order_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $revision_note = isset($_POST['revision_note']) ? sanitize_textarea_field($_POST['revision_note']) : '';
        $order = wc_get_order($order_id);

        if ($order && $order->get_customer_id() === get_current_user_id()) {
            $history = $order->get_meta('_print_order_revision_history') ?: [];
            $latest_revision = end($history);

            if ($latest_revision && $latest_revision['note'] === $revision_note) {
                wp_send_json_success(['status' => 'completed']);
            } else {
                wp_send_json_success(['status' => 'pending']);
            }
        }

        wp_send_json_error(['message' => __('وضعیت قابل بررسی نیست.', 'print-order')]);
        wp_die();
    }

    public function handle_user_upload_design() {
        check_ajax_referer('print_order_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $order = wc_get_order($order_id);

        if (!$order || $order->get_customer_id() !== get_current_user_id()) {
            error_log('Print Order: Access denied for user upload, order_id: ' . $order_id);
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        if (isset($_FILES['design_file']) && $_FILES['design_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['design_file'];
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($file['type'], $allowed_types)) {
                error_log('Print Order: Invalid file type for user upload: ' . $file['type']);
                wp_send_json_error(['message' => __('نوع فایل مجاز نیست. فقط JPEG، PNG و PDF پشتیبانی می‌شود.', 'print-order')]);
                wp_die();
            }

            $persian_date = $this->convert_date_to_persian(current_time('Y-m-d H:i:s'));
            $date_str = str_replace('/', '', $this->persian_to_english_digits($persian_date['date']));
            $private_upload_dir = WP_CONTENT_DIR . '/uploads/private/' . $date_str . '-' . $order_id . '/';
            if (!file_exists($private_upload_dir)) {
                wp_mkdir_p($private_upload_dir);
            }

            $filename = wp_unique_filename($private_upload_dir, $file['name']);
            $destination = $private_upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $order->update_meta_data('_print_order_user_uploaded_file', $destination);
                $order->save();
                wp_send_json_success([
                    'message' => __('فایل با موفقیت آپلود شد.', 'print-order'),
                    'file_url' => wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($destination)), 'download_nonce', 'nonce'),
                    'file_name' => $filename,
                ]);
            } else {
                error_log('Print Order: Failed to move uploaded file to ' . $destination);
                wp_send_json_error(['message' => __('خطا در آپلود فایل.', 'print-order')]);
            }
        } else {
            wp_send_json_error(['message' => __('هیچ فایلی انتخاب نشده است.', 'print-order')]);
        }

        wp_die();
    }

    public function handle_serve_private_image() {
        check_ajax_referer('print_order_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $file = isset($_GET['file']) ? urldecode($_GET['file']) : '';
        $order = wc_get_order($order_id);

        if (!$order || $order->get_customer_id() !== get_current_user_id()) {
            error_log('Print Order: Access denied for serving image, order_id: ' . $order_id);
            status_header(403);
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        if (empty($file) || !file_exists($file)) {
            error_log('Print Order: File not found for serving image: ' . $file);
            status_header(404);
            wp_send_json_error(['message' => __('فایل یافت نشد.', 'print-order')]);
            wp_die();
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        if (!in_array($extension, $allowed_types)) {
            error_log('Print Order: Invalid file type for serving image: ' . $extension);
            status_header(403);
            wp_send_json_error(['message' => __('نوع فایل غیرمجاز.', 'print-order')]);
            wp_die();
        }

        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
        ];

        $mime = $mime_types[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function handle_download_private_file() {
        check_ajax_referer('download_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $file = isset($_GET['file']) ? urldecode($_GET['file']) : '';
        $order = wc_get_order($order_id);

        if (!$order || $order->get_customer_id() !== get_current_user_id()) {
            error_log('Print Order: Access denied for downloading file, order_id: ' . $order_id);
            status_header(403);
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        if (empty($file) || !file_exists($file)) {
            error_log('Print Order: File not found for download: ' . $file);
            status_header(404);
            wp_send_json_error(['message' => __('فایل یافت نشد.', 'print-order')]);
            wp_die();
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf'];
        if (!in_array($extension, $allowed_types)) {
            error_log('Print Order: Invalid file type for download: ' . $extension);
            status_header(403);
            wp_send_json_error(['message' => __('نوع فایل غیرمجاز.', 'print-order')]);
            wp_die();
        }

        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'pdf' => 'application/pdf',
        ];

        $mime = $mime_types[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function convert_date_to_persian($datetime) {
        if (!$datetime) {
            return ['date' => '-', 'time' => '-'];
        }

        if ($datetime instanceof DateTime) {
            $datetime = $datetime->format('Y-m-d H:i:s');
        }

        $date = new DateTime($datetime);
        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/MM/dd'
        );
        $persian_date = $formatter->format($date);

        return ['date' => $persian_date, 'time' => $date->format('H:i:s')];
    }

    private function persian_to_english_digits($string) {
        $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian_digits, $english_digits, $string);
    }
}

if (!function_exists('print_order_user_orders_details')) {
    function print_order_user_orders_details($order_id = 0, $tab = 'details') {
        $user_orders = new Print_Order_User_Orders();
        $order = wc_get_order($order_id);
        $output = '';

        error_log('Entering print_order_user_orders_details - Order ID: ' . $order_id . ', Tab: ' . $tab . ', Order exists: ' . ($order ? 'yes' : 'no'));

        if (!$order) {
            error_log('Error: Order not found in details function for order_id: ' . $order_id);
            return '<p>' . __('سفارش یافت نشد.', 'print-order') . '</p>';
        }

        $current_tab = sanitize_text_field($tab);
        $is_initial_load = !isset($_POST['tab']);
        $user = wp_get_current_user();
        $user_display_name = $user->display_name;
        $admin_user = get_users(['role__in' => ['administrator'], 'number' => 1])[0] ?? null;
        $admin_display_name = $admin_user ? $admin_user->display_name : 'ادمین';
        $product_name = get_the_title($order->get_meta('_print_order_wc_product_id')) ?: 'نامشخص';
        $order_number = $order->get_order_number();
        error_log('Product meta retrieved: ' . ($order->get_meta('_print_order_wc_product_id') ?: 'not set') . ', Product name: ' . $product_name);
        $unread_messages = intval($order->get_meta('_print_order_unread_messages') ?? 0);

        $date_created = $order->get_date_created();
        error_log('Date created raw: ' . ($date_created ? $date_created->format('Y-m-d H:i:s') : 'null'));
        $persian_date_created = $date_created ? $user_orders->convert_date_to_persian($date_created) : ['date' => '-', 'time' => '-'];
        error_log('Persian date result: ' . json_encode($persian_date_created));

        // تعریف فرمت‌های تصویری
        $image_formats = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
        // تعریف آیکون‌های موجود
        $available_icons = ['ai.svg', 'file.svg', 'jpeg.svg', 'jpg.svg', 'pdf.svg', 'png.svg', 'psd.svg', 'order-registered.svg', 'payment-completed.svg'];

        // تابع برای گرفتن آیکون مناسب
        function get_file_icon($file_path, $available_icons) {
            $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            $icon_name = $extension . '.svg';
            $icon_path = PRINT_ORDER_URL . 'assets/icons/' . $icon_name;
            if (!in_array($icon_name, $available_icons)) {
                $icon_path = PRINT_ORDER_URL . 'assets/icons/file.svg';
            }
            return $icon_path;
        }

        ob_start();
        ?>
        <?php if ($is_initial_load): ?>
            <div class="order-details-content">
                <div class="order-steps mt-4">
                    <?php
                    $steps = $user_orders->get_order_status_steps($order);
                    $current_step = $user_orders->get_current_step($order);
                    $index = 0;
                    foreach ($steps as $step_name => $step_data) {
                        $is_completed = array_search($current_step, array_keys($steps)) > $index;
                        $is_active = $step_name === $current_step;
                        ?>
                        <div class="step-container">
                            <div class="step-circle <?php echo $is_active ? 'active' : ($is_completed ? 'completed' : ''); ?>">
                                <?php echo '<img src="' . esc_url($step_data['icon']) . '" alt="' . esc_attr($step_name) . '" class="step-icon">'; ?>
                            </div>
                            <div class="step-label <?php echo $is_active ? 'active' : ''; ?>"><?php echo esc_html($step_name); ?></div>
                        </div>
                        <?php
                        $index++;
                    }
                    ?>
                    <div class="step-line <?php echo array_search($current_step, array_keys($steps)) > 0 ? 'completed' : ''; ?>"></div>
                </div>
                <div class="tabs mt-4 flex gap-2">
                    <div class="tab <?php echo $current_tab === 'details' ? 'active' : ''; ?>" data-order-id="<?php echo esc_attr($order_id); ?>" data-tab="details"><?php _e('توضیحات سفارش', 'print-order'); ?></div>
                    <div class="tab <?php echo $current_tab === 'design' ? 'active' : ''; ?>" data-order-id="<?php echo esc_attr($order_id); ?>" data-tab="design">
                        <?php _e('تأیید طرح', 'print-order'); ?>
                        <?php if ($unread_messages > 0): ?>
                            <span class="unread-count bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"><?php echo esc_html($unread_messages); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="tab <?php echo $current_tab === 'shipping' ? 'active' : ''; ?>" data-order-id="<?php echo esc_attr($order_id); ?>" data-tab="shipping"><?php _e('ارسال', 'print-order'); ?></div>
                    <?php if ($order->needs_payment()): ?>
                        <button class="print-order-btn payment-button" onclick="window.location.href='<?php echo esc_url($order->get_checkout_payment_url()); ?>'"><?php _e('پرداخت', 'print-order'); ?></button>
                    <?php endif; ?>
                </div>
                <div id="tab-content-<?php echo esc_attr($order_id); ?>" class="tab-content-container">
        <?php endif; ?>
        <?php if ($current_tab === 'details'): ?>
            <div class="order-summary-card p-4 bg-white rounded-lg shadow-md mb-4">
                <h4 class="text-lg font-semibold mb-2"><?php _e('خلاصه سفارش', 'print-order'); ?></h4>
                <div class="summary-items flex flex-wrap gap-4">
                    <span><strong><?php _e('شماره سفارش:', 'print-order'); ?></strong> <?php echo esc_html($order_number); ?></span>
                    <span><strong><?php _e('نام محصول:', 'print-order'); ?></strong> <?php echo esc_html($product_name); ?></span>
                    <span><strong><?php _e('تاریخ:', 'print-order'); ?></strong> <?php echo esc_html($persian_date_created['date']); ?></span>
                    <span><strong><?php _e('وضعیت:', 'print-order'); ?></strong> <?php echo wc_get_order_status_name($order->get_status()); ?></span>
                    <span><strong><?php _e('مبلغ کل:', 'print-order'); ?></strong> <?php echo esc_html(number_format($order->get_total()) . ' ' . __('تومان', 'print-order')); ?></span>
                </div>
            </div>
            <div class="financial-details">
                <h4 class="text-lg font-semibold mb-2"><?php _e('جزییات مالی', 'print-order'); ?></h4>
                <table class="financial-table w-full border-collapse rounded-lg">
                    <thead>
                        <tr>
                            <th><?php _e('تاریخ تراکنش', 'print-order'); ?></th>
                            <th><?php _e('مبلغ', 'print-order'); ?></th>
                            <th><?php _e('روش پرداخت', 'print-order'); ?></th>
                            <th><?php _e('وضعیت', 'print-order'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        error_log('Debug: Checking transactions for order_id: ' . $order_id);
                        if (method_exists($order, 'get_transactions')) {
                            $payments = $order->get_transactions();
                            error_log('Debug: Number of transactions: ' . count($payments));
                            if (!empty($payments)) {
                                foreach ($payments as $payment) {
                                    $payment_date = $payment->get_date() ? $user_orders->convert_date_to_persian($payment->get_date()) : ['date' => '-', 'time' => '-'];
                                    $amount = number_format($payment->get_amount()) . ' ' . __('تومان', 'print-order');
                                    $method = $payment->get_payment_method_title() ?: '-';
                                    $status = $payment->get_status() === 'completed' ? 'پرداخت‌شده' : 'در انتظار';
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($payment_date['date']); ?></td>
                                        <td><?php echo esc_html($amount); ?></td>
                                        <td><?php echo esc_html($method); ?></td>
                                        <td class="payment-status <?php echo $status === 'پرداخت‌شده' ? 'paid' : 'pending'; ?>"><?php echo esc_html($status); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="4"><?php _e('هیچ تراکنشی ثبت نشده است.', 'print-order'); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4"><?php _e('اطلاعات تراکنش در دسترس نیست.', 'print-order'); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <p><strong><?php _e('شماره سفارش:', 'print-order'); ?></strong> <?php echo esc_html($order_number); ?></p>
            <p><strong><?php _e('نام محصول:', 'print-order'); ?></strong> <?php echo esc_html($product_name); ?></p>
            <p><strong><?php _e('جنس کاغذ:', 'print-order'); ?></strong> <?php echo esc_html($order->get_meta('_print_order_paper_type') ?: '-'); ?></p>
            <p><strong><?php _e('سایز:', 'print-order'); ?></strong> <?php echo esc_html($order->get_meta('_print_order_size') ?: '-'); ?></p>
            <p><strong><?php _e('تعداد:', 'print-order'); ?></strong> <?php echo esc_html($order->get_meta('_print_order_quantity') ?: '-'); ?></p>
            <p><strong><?php _e('تاریخ:', 'print-order'); ?></strong> <?php echo esc_html($persian_date_created['date']); ?></p>
            <p><strong><?php _e('مبلغ کل:', 'print-order'); ?></strong> <?php echo esc_html(number_format($order->get_total()) . ' ' . __('تومان', 'print-order')); ?></p>
        <?php endif; ?>
        <?php if ($current_tab === 'design'): ?>
            <?php
            $design_file = $order->get_meta('_print_order_design_file');
            $history = $user_orders->get_design_history($order);
            $revision_count = 0;
            foreach ($history as $entry) {
                if (isset($entry['note']) || isset($entry['user_file'])) {
                    $revision_count++;
                }
            }
            $options = get_option('print_order_options', []);
            $max_revisions = intval($options['max_design_revisions'] ?? 3);
            $extra_revisions = intval($order->get_meta('_print_order_extra_revisions') ?? 0);
            $remaining_revisions = max(0, $max_revisions + $extra_revisions - $revision_count);
            $default_message = esc_html($options['user_order_default_message'] ?? 'سفارش شما با موفقیت ثبت شد. در صورتی که نیاز به تایید طرح پیش از چاپ ندارید در پایین همین صفحه روی دکمه تایید طرح بزنید.');
            $edit_request_delay = intval($options['user_edit_request_delay'] ?? 1440);
            $edit_request_message = esc_html($options['user_edit_request_message'] ?? 'پیام شما دریافت شد. منتظر تأیید مدیر باشید.');
            $last_revision_request = $order->get_meta('_print_order_last_revision_request') ? strtotime($order->get_meta('_print_order_last_revision_request')) : 0;
            $current_time = current_time('timestamp');
            $time_diff_minutes = ($current_time - $last_revision_request) / 60;
            $show_edit_request_message = ($last_revision_request > 0 && $time_diff_minutes >= $edit_request_delay && !$order->get_meta('_print_order_admin_replied'));

            function get_user_friendly_time($timestamp, $user_orders) {
                if (!$timestamp) return '';
                $diff = current_time('timestamp') - $timestamp;
                if ($diff < 60) return sprintf(__('%d ثانیه پیش', 'print-order'), $diff);
                if ($diff < 3600) return sprintf(__('%d دقیقه پیش', 'print-order'), floor($diff / 60));
                if ($diff < 86400) return sprintf(__('%d ساعت پیش', 'print-order'), floor($diff / 3600));
                $date = new DateTime();
                $date->setTimestamp($timestamp);
                return $user_orders->convert_date_to_persian($date)['date'];
            }
            ?>
            <div class="chat-container" data-order-id="<?php echo esc_attr($order_id); ?>">
                <?php if ($design_file): ?>
                    <?php
                    $image_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_serve_private_image&order_id=' . $order_id . '&file=' . urlencode($design_file)), 'print_order_nonce', 'nonce');
                    $extension = strtolower(pathinfo($design_file, PATHINFO_EXTENSION));
                    $icon_url = get_file_icon($design_file, $available_icons);
                    $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($design_file)), 'download_nonce', 'nonce');
                    ?>
                    <div class="chat-message admin initial">
                        <span><?php echo esc_html($admin_display_name); ?>: <?php _e('فایل طراحی اولیه آپلود شد.', 'print-order'); ?></span>
                        <span class="chat-time"><?php echo get_user_friendly_time(strtotime($order->get_date_created()->date('Y-m-d H:i:s')), $user_orders); ?></span>
                        <div class="file-container">
                            <?php if (in_array($extension, $image_formats)): ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="" class="chat-thumbnail">
                            <?php endif; ?>
                            <div class="file-info">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="File Icon" class="file-icon">
                                <a href="<?php echo esc_url($download_url); ?>" class="chat-download"><?php _e('دانلود', 'print-order'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="chat-message admin system">
                    <span><?php echo esc_html($admin_display_name); ?>: <?php echo $default_message; ?></span>
                    <span class="chat-time"><?php echo get_user_friendly_time(strtotime($order->get_date_created()->date('Y-m-d H:i:s')), $user_orders); ?></span>
                </div>
                <?php if ($show_edit_request_message): ?>
                    <div class="chat-message admin">
                        <span><?php echo esc_html($admin_display_name); ?>: <?php echo $edit_request_message; ?></span>
                        <span class="chat-time"><?php echo get_user_friendly_time($current_time, $user_orders); ?></span>
                    </div>
                <?php endif; ?>
                <?php foreach ($history as $index => $revision): ?>
                    <?php if (!empty($revision['note'])): // فقط پیام‌های غیرخالی نمایش داده شوند ?>
                        <div class="chat-message user">
                            <span><?php echo esc_html($user_display_name); ?>: <?php echo esc_html($revision['note']); ?></span>
                            <span class="chat-time"><?php echo get_user_friendly_time(strtotime($revision['date']), $user_orders); ?></span>
                            <?php if (isset($revision['user_file'])): ?>
                                <?php
                                $image_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_serve_private_image&order_id=' . $order_id . '&file=' . urlencode($revision['user_file'])), 'print_order_nonce', 'nonce');
                                $extension = strtolower(pathinfo($revision['user_file'], PATHINFO_EXTENSION));
                                $icon_url = get_file_icon($revision['user_file'], $available_icons);
                                $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($revision['user_file']) . '&index=' . $index), 'download_nonce', 'nonce');
                                ?>
                                <div class="file-container">
                                    <?php if (in_array($extension, $image_formats)): ?>
                                        <img src="<?php echo esc_url($image_url); ?>" alt="" class="chat-thumbnail">
                                    <?php endif; ?>
                                    <div class="file-info">
                                        <img src="<?php echo esc_url($icon_url); ?>" alt="File Icon" class="file-icon">
                                        <a href="<?php echo esc_url($download_url); ?>" class="chat-download"><?php _e('دانلود', 'print-order'); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($revision['admin_response'])): ?>
                        <div class="chat-message admin">
                            <span><?php echo esc_html($admin_display_name); ?>: <?php echo esc_html($revision['admin_response']); ?></span>
                            <span class="chat-time"><?php echo get_user_friendly_time(strtotime($revision['response_date']), $user_orders); ?></span>
                            <?php if (isset($revision['new_design_file'])): ?>
                                <?php
                                $image_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_serve_private_image&order_id=' . $order_id . '&file=' . urlencode($revision['new_design_file'])), 'print_order_nonce', 'nonce');
                                $extension = strtolower(pathinfo($revision['new_design_file'], PATHINFO_EXTENSION));
                                $icon_url = get_file_icon($revision['new_design_file'], $available_icons);
                                $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($revision['new_design_file']) . '&index=' . $index), 'download_nonce', 'nonce');
                                ?>
                                <div class="file-container">
                                    <?php if (in_array($extension, $image_formats)): ?>
                                        <img src="<?php echo esc_url($image_url); ?>" alt="" class="chat-thumbnail">
                                    <?php endif; ?>
                                    <div class="file-info">
                                        <img src="<?php echo esc_url($icon_url); ?>" alt="File Icon" class="file-icon">
                                        <a href="<?php echo esc_url($download_url); ?>" class="chat-download"><?php _e('دانلود', 'print-order'); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="design-actions">
                <form class="order-action-form" method="post">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    <button type="button" class="print-order-btn print-order-confirm-btn" aria-label="تأیید طرح"><?php _e('تأیید طرح', 'print-order'); ?></button>
                    <?php if ($remaining_revisions > 0 && $order->get_meta('_print_order_design_confirmed') !== 'yes'): ?>
                        <button type="button" class="print-order-btn print-order-revision-btn" aria-label="درخواست ویرایش" data-remaining="<?php echo esc_attr($remaining_revisions); ?>">
                            <?php _e('درخواست ویرایش', 'print-order'); ?>
                            <span class="revision-remaining-circle"><?php echo esc_html($remaining_revisions); ?></span>
                        </button>
                    <?php elseif ($remaining_revisions <= 0): ?>
                        <p class="text-red-600"><?php _e('حداکثر تعداد درخواست‌های ویرایش تکمیل شده است.', 'print-order'); ?></p>
                    <?php endif; ?>
                </form>
                <form class="revision-form order-action-form hidden" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    <p class="revision-limit-warning text-red-600 mb-2"><?php printf(__('توجه: شما %d درخواست ویرایش باقی‌مانده دارید!', 'print-order'), $remaining_revisions); ?></p>
                    <label for="design_file" class="block text-gray-700 mb-2"><?php _e('آپلود فایل طراحی:', 'print-order'); ?></label>
                    <input type="file" id="design_file" name="design_file" accept=".jpg,.jpeg,.png,.pdf" class="border p-2 rounded-lg w-full mb-2">
                    <progress id="upload_progress" value="0" max="100" class="w-full mb-2 hidden"></progress>
                    <textarea name="revision_note" rows="3" placeholder="<?php _e('توضیحات درخواست ویرایش', 'print-order'); ?>" required></textarea>
                    <div class="revision-form-actions">
                        <button type="button" class="print-order-btn submit-revision" aria-label="ارسال درخواست ویرایش"><?php _e('ارسال', 'print-order'); ?></button>
                        <button type="button" class="print-order-btn cancel-revision" aria-label="لغو درخواست ویرایش"><?php _e('لغو', 'print-order'); ?></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        <?php if ($current_tab === 'shipping'): ?>
            <?php
            $shipping_method = $order->get_meta('_print_order_shipping_method') ?: '-';
            $tracking_code = $order->get_meta('_print_order_tracking') ?: '-';
            $shipping_date = $order->get_meta('_print_order_shipping_date') ? $user_orders->convert_date_to_persian($order->get_meta('_print_order_shipping_date'))['date'] : '-';
            $delivery_status = $order->get_meta('_print_order_delivery_status') ?: 'pending';
            ?>
            <div class="shipping-card">
                <h4 class="text-lg font-semibold mb-2"><?php _e('وضعیت ارسال', 'print-order'); ?></h4>
                <p><strong><?php _e('روش ارسال:', 'print-order'); ?></strong> <?php echo esc_html($shipping_method); ?></p>
                <p><strong><?php _e('کد رهگیری:', 'print-order'); ?></strong> <?php echo esc_html($tracking_code); ?>
                    <?php if ($tracking_code !== '-'): ?>
                        <button class="copy-btn" data-clipboard-text="<?php echo esc_attr($tracking_code); ?>"><?php _e('کپی', 'print-order'); ?></button>
                    <?php endif; ?>
                </p>
                <p><strong><?php _e('تاریخ ارسال:', 'print-order'); ?></strong> <?php echo esc_html($shipping_date); ?></p>
                <p><strong><?php _e('وضعیت:', 'print-order'); ?></strong> 
                    <span class="shipping-status <?php echo $delivery_status === 'delivered' ? 'delivered' : 'shipping'; ?>">
                        <?php echo $delivery_status === 'delivered' ? __('تحویل‌شده', 'print-order') : __('در حال ارسال', 'print-order'); ?>
                    </span>
                </p>
                <?php if ($tracking_code !== '-' && $shipping_method !== '-'): ?>
                    <p><a href="#" class="tracking-link"><?php _e('پیگیری سفارش', 'print-order'); ?></a></p>
                <?php endif; ?>
            </div>
            <?php if ($order->get_status() === 'printing' || $delivery_status === 'shipping'): ?>
                <form class="shipping-form order-action-form" method="post">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    <label for="shipping_method"><?php _e('روش ارسال:', 'print-order'); ?></label>
                    <select id="shipping_method" name="shipping_method" required>
                        <option value="" disabled <?php selected($shipping_method, ''); ?>><?php _e('انتخاب کنید', 'print-order'); ?></option>
                        <option value="post" <?php selected($shipping_method, 'post'); ?>><?php _e('پست', 'print-order'); ?></option>
                        <option value="courier" <?php selected($shipping_method, 'courier'); ?>><?php _e('پیک', 'print-order'); ?></option>
                    </select>
                    <label for="tracking_code"><?php _e('کد رهگیری:', 'print-order'); ?></label>
                    <input type="text" id="tracking_code" name="tracking_code" value="<?php echo esc_attr($tracking_code); ?>" required>
                    <label for="shipping_date"><?php _e('تاریخ ارسال:', 'print-order'); ?></label>
                    <input type="date" id="shipping_date" name="shipping_date" value="<?php echo esc_attr($order->get_meta('_print_order_shipping_date')); ?>" required>
                    <button type="button" class="print-order-btn shipping-submit"><?php _e('ثبت اطلاعات ارسال', 'print-order'); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($is_initial_load): ?>
                </div>
            </div>
        <?php endif; ?>
        <?php
        $output = ob_get_clean();
        return $output;
    }
}
?>
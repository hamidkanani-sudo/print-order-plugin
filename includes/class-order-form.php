<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Form {
    private $provinces_map = [
        'تهران' => 'THR',
        'البرز' => 'ABZ',
        'ایلام' => 'ILM',
        'بوشهر' => 'BHR',
        'اردبیل' => 'ADL',
        'اصفهان' => 'ESF',
        'آذربایجان شرقی' => 'EAZ',
        'آذربایجان غربی' => 'WAZ',
        'زنجان' => 'ZAN',
        'سمنان' => 'SMN',
        'سیستان و بلوچستان' => 'SBL',
        'فارس' => 'FRS',
        'قم' => 'QHM',
        'قزوین' => 'QZN',
        'گلستان' => 'GLS',
        'گیلان' => 'GIL',
        'مازندران' => 'MZN',
        'مرکزی' => 'MKZ',
        'هرمزگان' => 'HRZ',
        'همدان' => 'HMD',
        'کردستان' => 'KRD',
        'کرمانشاه' => 'KRH',
        'کرمان' => 'KRN',
        'کهگیلویه و بویراحمد' => 'KBD',
        'خوزستان' => 'KZT',
        'لرستان' => 'LRS',
        'خراسان شمالی' => 'KHS',
        'خراسان رضوی' => 'KJR',
        'خراسان جنوبی' => 'KJF',
        'چهارمحال و بختیاری' => 'CHB',
        'یزد' => 'YSD',
    ];

    public function __construct() {
        add_action('wp_ajax_get_product_info', [$this, 'get_product_info']);
        add_action('wp_ajax_nopriv_get_product_info', [$this, 'get_product_info']);
        add_action('wp_ajax_print_order_submit', [$this, 'submit_order']);
        add_action('wp_ajax_nopriv_print_order_submit', [$this, 'submit_order']);
        add_action('wp_ajax_print_order_get_stage_template', [$this, 'get_stage_template']);
        add_action('wp_ajax_nopriv_print_order_get_stage_template', [$this, 'get_stage_template']);
        add_action('wp_ajax_get_user_info', [$this, 'get_user_info']);
        add_action('wp_ajax_nopriv_get_user_info', [$this, 'get_user_info']);
        add_action('wp_autoload_options', [$this, 'get_provinces']);
        add_action('wp_ajax_get_provinces', [$this, 'get_provinces']);
        add_action('wp_ajax_nopriv_get_provinces', [$this, 'get_provinces']);
        add_action('wp_ajax_get_cities', [$this, 'get_cities']);
        add_action('wp_ajax_nopriv_get_cities', [$this, 'get_cities']);
        add_action('wp_ajax_update_user_profile', [$this, 'ajax_update_user_profile']);
        add_action('wp_ajax_nopriv_check_email_exists', [$this, 'check_email_exists']);
        add_action('wp_ajax_create_guest_user', [$this, 'create_guest_user']);
        add_action('wp_ajax_nopriv_create_guest_user', [$this, 'create_guest_user']);
        add_action('wp_ajax_print_order_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_nopriv_print_order_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_print_order_delete_file', [$this, 'delete_file']);
        add_action('wp_ajax_print_order_delete_temp_file', [$this, 'delete_temp_file']);
        add_action('wp_ajax_nopriv_print_order_delete_temp_file', [$this, 'delete_temp_file']);
        add_action('wp_ajax_print_order_generate_temp_id', [$this, 'generate_temp_id']);
        add_action('wp_ajax_nopriv_print_order_generate_temp_id', [$this, 'generate_temp_id']);
        add_action('wp_ajax_print_order_apply_discount', [$this, 'print_order_apply_discount']);
        add_action('wp_ajax_nopriv_print_order_apply_discount', [$this, 'print_order_apply_discount']);
        add_action('init', [$this, 'handle_zarinpal_callback']);
    }

    public function generate_temp_id() {
        if (!isset($_POST['nonce']) ||
            (!wp_verify_nonce($_POST['nonce'], 'print_order_nonce') &&
             !wp_verify_nonce($_POST['nonce'], 'print_order_public_nonce'))) {
            error_log('generate_temp_id: Invalid nonce');
            wp_send_json_error(['message' => 'خطای امنیتی: نانس نامعتبر است'], 403);
        }

        if (!WC()->session) {
            error_log('generate_temp_id: WooCommerce session not initialized');
            wp_send_json_error(['message' => 'خطای سرور: جلسه ووکامرس در دسترس نیست'], 500);
        }

        $temp_id = wp_generate_uuid4();
        WC()->session->set('print_order_temp_id', $temp_id);
        error_log('generate_temp_id: Generated and stored new temp_id: ' . $temp_id);

        wp_send_json_success(['temp_id' => $temp_id]);
    }

    public function convert_date_to_persian($datetime) {
        if (!$datetime) {
            return ['date' => '-', 'time' => '-'];
        }

        if (!extension_loaded('intl')) {
            return ['date' => 'افزونه intl غیرفعال است', 'time' => ''];
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
            'yyyyMMdd'
        );
        $persian_date = $formatter->format($date);

        return ['date' => $persian_date, 'time' => $date->format('H:i:s')];
    }

    private function persian_to_english_digits($string) {
        $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian_digits, $english_digits, $string);
    }

    public function upload_file() {
        if (!isset($_POST['nonce']) ||
            (!wp_verify_nonce($_POST['nonce'], 'print_order_nonce') &&
             !wp_verify_nonce($_POST['nonce'], 'print_order_public_nonce'))) {
            error_log('upload_file: Invalid nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'none'));
            wp_send_json_error(['message' => 'خطای امنیتی: نانس نامعتبر است'], 403);
        }

        if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
            error_log('upload_file: No file uploaded');
            wp_send_json_error(['message' => 'هیچ فایلی انتخاب نشده است'], 400);
        }

        if (!isset($_POST['temp_id']) || empty($_POST['temp_id'])) {
            error_log('upload_file: No temp_id provided');
            wp_send_json_error(['message' => 'شناسه موقت ارائه نشده است'], 400);
        }

        $temp_id = sanitize_text_field($_POST['temp_id']);
        $file = $_FILES['file'];
        $allowed_formats = ['psd', 'jpg', 'jpeg', 'pdf', 'png', 'ai', 'eps', 'cdr'];
        $max_file_size = 30 * 1024 * 1024; // 30MB
        $format = pathinfo($file['name'], PATHINFO_EXTENSION);
        $format = strtolower($format);

        error_log('upload_file: File details - Name: ' . $file['name'] . ', Size: ' . $file['size'] . ', Type: ' . $file['type'] . ', Format: ' . $format . ', Temp ID: ' . $temp_id);

        if (!in_array($format, $allowed_formats)) {
            error_log('upload_file: Invalid file format: ' . $format);
            wp_send_json_error(['message' => 'فرمت فایل غیرمجاز است'], 400);
        }

        if ($file['size'] > $max_file_size) {
            error_log('upload_file: File size exceeds limit: ' . $file['size']);
            wp_send_json_error(['message' => 'حجم فایل بیش از 30 مگابایت است'], 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed_mime_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/postscript', 'image/vnd.adobe.photoshop'];
        if (!in_array($mime_type, $allowed_mime_types)) {
            error_log('upload_file: Invalid MIME type: ' . $mime_type);
            wp_send_json_error(['message' => 'نوع فایل غیرمجاز است'], 400);
        }

        if (!WC()->session) {
            error_log('upload_file: WooCommerce session not initialized');
            wp_send_json_error(['message' => 'خطای سرور: جلسه ووکامرس در دسترس نیست'], 500);
        }

        $session_temp_id = WC()->session->get('print_order_temp_id');
        if (!$session_temp_id) {
            error_log('upload_file: No temp_id in session, generating new one');
            $session_temp_id = wp_generate_uuid4();
            WC()->session->set('print_order_temp_id', $session_temp_id);
            error_log('upload_file: Generated and stored new temp_id: ' . $session_temp_id);
        }

        if ($session_temp_id !== $temp_id) {
            error_log('upload_file: Temp ID mismatch - Sent: ' . $temp_id . ', Session: ' . $session_temp_id);
            WC()->session->set('print_order_temp_id', $temp_id);
            error_log('upload_file: Updated session temp_id to match sent temp_id: ' . $temp_id);
        }

        $uploads_dir = WP_CONTENT_DIR . '/Uploads/';
        if (!file_exists($uploads_dir) && !wp_mkdir_p($uploads_dir)) {
            error_log('upload_file: Failed to create uploads directory: ' . $uploads_dir);
            wp_send_json_error(['message' => 'خطا در ایجاد پوشه uploads: ' . $uploads_dir], 500);
        }
        if (!is_writable($uploads_dir)) {
            error_log('upload_file: uploads directory is not writable: ' . $uploads_dir);
            wp_send_json_error(['message' => 'پوشه uploads قابل نوشتن نیست: ' . $uploads_dir], 500);
        }

        $temp_dir = WP_CONTENT_DIR . '/Uploads/temp/' . $temp_id . '/';
        error_log('upload_file: Attempting to create temp directory: ' . $temp_dir);
        if (!file_exists($temp_dir) && !wp_mkdir_p($temp_dir)) {
            error_log('upload_file: Failed to create temp directory: ' . $temp_dir);
            wp_send_json_error(['message' => 'خطا در ایجاد پوشه موقت: ' . $temp_dir], 500);
        }

        if (!is_writable($temp_dir)) {
            error_log('upload_file: Temp directory is not writable: ' . $temp_dir);
            wp_send_json_error(['message' => 'پوشه موقت قابل نوشتن نیست: ' . $temp_dir], 500);
        }

        $file_name = sanitize_file_name($file['name']);
        $file_path = $temp_dir . $file_name;

        error_log('upload_file: Moving file to: ' . $file_path);
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            error_log('upload_file: Failed to move file to: ' . $file_path);
            wp_send_json_error(['message' => 'خطا در آپلود فایل به مسیر: ' . $file_path], 500);
        }

        if (!file_exists($file_path)) {
            error_log('upload_file: File does not exist after move: ' . $file_path);
            wp_send_json_error(['message' => 'فایل پس از انتقال یافت نشد: ' . $file_path], 500);
        }

        $temp_url = WP_CONTENT_URL . '/Uploads/temp/' . $temp_id . '/' . $file_name;

        $temp_files = WC()->session->get('print_order_temp_files', []);
        $temp_files[] = [
            'name' => $file_name,
            'temp_url' => $temp_url,
            'path' => $file_path,
            'format' => $format,
        ];
        WC()->session->set('print_order_temp_files', $temp_files);

        error_log('upload_file: File uploaded successfully - Temp URL: ' . $temp_url . ', Path: ' . $file_path . ', Format: ' . $format);

        wp_send_json_success([
            'temp_url' => $temp_url,
            'name' => $file_name,
            'format' => $format,
        ]);
    }

    public function delete_temp_file() {
        if (!isset($_POST['nonce']) ||
            (!wp_verify_nonce($_POST['nonce'], 'print_order_nonce') &&
             !wp_verify_nonce($_POST['nonce'], 'print_order_public_nonce'))) {
            error_log('delete_temp_file: Invalid nonce');
            wp_send_json_error(['message' => 'خطای امنیتی: نانس نامعتبر است'], 403);
        }
        if (!isset($_POST['temp_id']) || empty($_POST['temp_id'])) {
            error_log('delete_temp_file: No temp_id provided');
            wp_send_json_error(['message' => 'شناسه موقت ارائه نشده است'], 400);
        }
        if (!isset($_POST['file_name']) || empty($_POST['file_name'])) {
            error_log('delete_temp_file: No file_name provided');
            wp_send_json_error(['message' => 'نام فایل ارائه نشده است'], 400);
        }

        $temp_id = sanitize_text_field($_POST['temp_id']);
        $file_name = sanitize_file_name($_POST['file_name']);

        if (!WC()->session || WC()->session->get('print_order_temp_id') !== $temp_id) {
            error_log('delete_temp_file: Invalid temp_id: ' . $temp_id);
            wp_send_json_error(['message' => 'شناسه موقت نامعتبر است'], 403);
        }

        $file_path = WP_CONTENT_DIR . '/Uploads/temp/' . $temp_id . '/' . $file_name;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                error_log('delete_temp_file: File deleted successfully - Path: ' . $file_path);
                $temp_files = WC()->session->get('print_order_temp_files', []);
                $temp_files = array_filter($temp_files, function($file) use ($file_name) {
                    return $file['name'] !== $file_name;
                });
                WC()->session->set('print_order_temp_files', array_values($temp_files));
                wp_send_json_success(['message' => 'فایل با موفقیت حذف شد']);
            } else {
                error_log('delete_temp_file: Failed to delete file: ' . $file_path);
                wp_send_json_error(['message' => 'خطا در حذف فایل'], 500);
            }
        } else {
            error_log('delete_temp_file: File not found: ' . $file_path);
            wp_send_json_error(['message' => 'فایل یافت نشد'], 404);
        }
    }

    public function delete_file() {
        check_ajax_referer('print_order_nonce', 'nonce');
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        if (!$attachment_id) {
            error_log('delete_file: Invalid attachment_id');
            wp_send_json_error(['message' => 'شناسه فایل نامعتبر است'], 400);
        }

        $deleted = wp_delete_attachment($attachment_id, true);
        if ($deleted) {
            error_log('delete_file: Attachment deleted successfully - ID: ' . $attachment_id);
            wp_send_json_success(['message' => 'فایل با موفقیت حذف شد']);
        } else {
            error_log('delete_file: Failed to delete attachment - ID: ' . $attachment_id);
            wp_send_json_error(['message' => 'خطا در حذف فایل'], 500);
        }
    }

    public function check_email_exists() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'print_order_public_nonce')) {
            error_log('check_email_exists: Invalid nonce detected');
            wp_send_json_error(['message' => 'خطای امنیتی: نانس نامعتبر است'], 403);
        }
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        if (empty($email)) {
            error_log('check_email_exists: Email is empty');
            wp_send_json_error(['message' => 'ایمیل ارائه نشده است'], 400);
        }
        if (!is_email($email)) {
            error_log('check_email_exists: Invalid email format');
            wp_send_json_error(['message' => 'ایمیل نامعتبر است'], 400);
        }
        $email_exists = email_exists($email);
        error_log('check_email_exists: Email exists = ' . ($email_exists ? 'true' : 'false'));
        wp_send_json_success(['exists' => $email_exists]);
    }

    public function create_guest_user() {
        check_ajax_referer('print_order_public_nonce', 'nonce');
        $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
        if (empty($form_data)) {
            error_log('create_guest_user: Invalid form data');
            wp_send_json_error(['message' => 'داده‌های فرم نامعتبر است']);
        }

        $email = sanitize_email($form_data['customer_email']);
        $phone = sanitize_text_field($form_data['customer_phone']);
        $name = sanitize_text_field($form_data['customer_name']);
        $lastname = isset($form_data['customer_lastname']) ? sanitize_text_field($form_data['customer_lastname']) : '';
        $billing_state = sanitize_text_field($form_data['billing_state']);

        if (!is_email($email)) {
            error_log('create_guest_user: Invalid email');
            wp_send_json_error(['message' => 'ایمیل نامعتبر است']);
        }
        if (email_exists($email)) {
            error_log('create_guest_user: Email already exists: ' . $email);
            wp_send_json_error(['message' => 'ایمیل قبلاً ثبت شده است']);
        }
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            error_log('create_guest_user: Invalid phone number: ' . $phone);
            wp_send_json_error(['message' => 'شماره تماس باید 11 رقم و با 09 شروع شود']);
        }

        $billing_state_code = in_array($billing_state, array_values($this->provinces_map))
            ? $billing_state
            : (array_search($billing_state, $this->provinces_map) ?: $billing_state);
        error_log('create_guest_user: billing_state = ' . $billing_state . ', converted to = ' . $billing_state_code);

        if (!in_array($billing_state_code, array_values($this->provinces_map))) {
            error_log('create_guest_user: Invalid billing state: ' . $billing_state_code);
            wp_send_json_error(['message' => 'استان انتخاب‌شده معتبر نیست']);
        }

        $email_prefix = substr($email, 0, 4);
        $special_chars = ['!', '@', '#', '$', '%', '&', '*'];
        $special_char = $special_chars[array_rand($special_chars)];
        $phone_suffix = substr($phone, -6);
        $password = ucfirst(strtolower($email_prefix)) . $special_char . $phone_suffix;

        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            error_log('create_guest_user: Error creating user: ' . $user_id->get_error_message());
            wp_send_json_error(['message' => 'خطا در ایجاد حساب کاربری: ' . $user_id->get_error_message()]);
        }

        $user = new WP_User($user_id);
        $user->set_role('customer');

        update_user_meta($user_id, 'billing_first_name', $name);
        if ($lastname) {
            update_user_meta($user_id, 'billing_last_name', $lastname);
        }
        update_user_meta($user_id, 'billing_email', $email);
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'billing_state', $billing_state_code);
        update_user_meta($user_id, 'billing_city', sanitize_text_field($form_data['billing_city']));
        update_user_meta($user_id, 'billing_address_1', sanitize_textarea_field($form_data['billing_address']));
        update_user_meta($user_id, 'billing_postcode', sanitize_text_field($form_data['billing_postcode']));
        update_user_meta($user_id, 'billing_country', 'IR');

        $subject = 'خوش آمدید به ' . get_bloginfo('name');
        $message = "سلام {$name},\n\n";
        $message .= "حساب کاربری شما با موفقیت ایجاد شد.\n";
        $message .= "نام کاربری: {$email}\n";
        $message .= "رمز عبور: {$password}\n";
        $message .= "برای ورود به حساب خود از این لینک استفاده کنید: " . wp_login_url() . "\n\n";
        $message .= "با تشکر,\n" . get_bloginfo('name');
        wp_mail($email, $subject, $message);

        $options = get_option('print_order_options', []);
        $sms_template = $options['sms_welcome_template'] ? $options['sms_welcome_template'] : "حساب شما در {site_name} ایجاد شد. نام کاربری: {username}، رمز عبور: {password}";
        $sms_message = str_replace(
            ['{site_name}', '{username}', '{password}'],
            [get_bloginfo('name'), $email, $password],
            $sms_template
        );
        $this->send_sms($phone, $sms_message);

        wp_send_json_success([
            'user_id' => $user_id,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function print_order_apply_discount() {
        if (!isset($_POST['nonce']) ||
            (!wp_verify_nonce($_POST['nonce'], 'print_order_nonce') &&
             !wp_verify_nonce($_POST['nonce'], 'print_order_public_nonce'))) {
            error_log('print_order_apply_discount: Invalid nonce');
            wp_send_json_error(['message' => 'خطای امنیتی: نانس نامعتبر است'], 403);
        }

        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;

        error_log('print_order_apply_discount: Input - code: ' . $code . ', total_price: ' . $total_price . ', form_data: ' . print_r($form_data, true) . ', product_id: ' . $product_id);

        if (empty($code)) {
            error_log('print_order_apply_discount: No discount code provided');
            wp_send_json_error(['message' => 'کد تخفیف وارد نشده است'], 400);
        }

        if (!$product_id) {
            error_log('print_order_apply_discount: Invalid product_id');
            wp_send_json_error(['message' => 'شناسه محصول نامعتبر است'], 400);
        }

        if ($total_price <= 0) {
            error_log('print_order_apply_discount: Invalid total_price');
            wp_send_json_error(['message' => 'مبلغ کل نامعتبر است'], 400);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_discount_codes';
        $discount = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE code = %s", $code),
            ARRAY_A
        );

        if (!$discount) {
            error_log('print_order_apply_discount: Discount code not found: ' . $code);
            wp_send_json_error(['message' => 'کد تخفیف نامعتبر است'], 400);
        }

        // Check discount status
        if ($discount['status'] !== 'active') {
            error_log('print_order_apply_discount: Discount code is not active: ' . $code);
            wp_send_json_error(['message' => 'این کد تخفیف فعال نیست'], 400);
        }

        // Check start date
        if ($discount['start_date'] && strtotime($discount['start_date']) > time()) {
            error_log('print_order_apply_discount: Discount code not yet active: ' . $code);
            wp_send_json_error(['message' => 'این کد تخفیف هنوز فعال نشده است'], 400);
        }

        // Check minimum order amount
        if ($discount['min_order_amount'] > 0 && $total_price < $discount['min_order_amount']) {
            error_log('print_order_apply_discount: Total price (' . $total_price . ') is less than minimum order amount (' . $discount['min_order_amount'] . ')');
            wp_send_json_error(['message' => 'مبلغ سفارش کمتر از حداقل مبلغ مورد نیاز برای این کد تخفیف است'], 400);
        }

        // Check usage limit
        if ($discount['usage_limit_total'] > 0 && $discount['usage_count'] >= $discount['usage_limit_total']) {
            error_log('print_order_apply_discount: Discount code usage limit reached: ' . $code);
            wp_send_json_error(['message' => 'این کد تخفیف به حداکثر تعداد استفاده رسیده است'], 400);
        }

        // Check expiry date
        if ($discount['end_date'] && strtotime($discount['end_date']) < time()) {
            error_log('print_order_apply_discount: Discount code expired: ' . $code);
            wp_send_json_error(['message' => 'این کد تخفیف منقضی شده است'], 400);
        }

        // Calculate discount amount
        $discount_amount = 0;
        if ($discount['discount_type'] === 'fixed') {
            $discount_amount = floatval($discount['discount_value']);
        } elseif ($discount['discount_type'] === 'percentage') {
            $discount_amount = ($total_price * floatval($discount['discount_value'])) / 100;
        }

        // Ensure discount does not exceed total price
        $discount_amount = min($discount_amount, $total_price);

        // Update used count
        $wpdb->update(
            $table_name,
            ['usage_count' => $discount['usage_count'] + 1],
            ['code' => $code],
            ['%d'],
            ['%s']
        );

        error_log('print_order_apply_discount: Calculated discount_amount: ' . $discount_amount);

        wp_send_json_success([
            'message' => 'کد تخفیف با موفقیت اعمال شد',
            'discount_amount' => $discount_amount,
        ]);
    }

    public function get_product_info() {
        check_ajax_referer('print_order_nonce', 'nonce');
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if (!$product_id) {
            error_log('get_product_info: Invalid product_id');
            wp_send_json_error(['message' => 'شناسه محصول نامعتبر است']);
        }
        $product = wc_get_product($product_id);
        if (!$product) {
            error_log('get_product_info: Product not found for ID: ' . $product_id);
            wp_send_json_error(['message' => 'محصول یافت نشد']);
        }
        remove_all_filters('wp_get_post_terms');
        $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'all']);
        $category_id = 0;
        $category_name = '';
        $all_categories = [];
        $no_print = false;
        $design_price = 0;
        if (!is_wp_error($categories) && !empty($categories)) {
            $saved_settings = get_option('print_order_print_management', []);
            $saved_prices = get_option('print_order_design_prices', []);
            foreach ($categories as $cat) {
                $all_categories[] = [
                    'term_id' => $cat->term_id,
                    'name' => $cat->name,
                    'parent' => $cat->parent,
                ];
                if ($cat->parent == 0) {
                    $category_id = $cat->term_id;
                    $category_name = $cat->name;
                }
                if (isset($saved_settings[$cat->term_id])) {
                    $no_print = true;
                }
                if (isset($saved_prices[$cat->term_id]) && $saved_prices[$cat->term_id] > 0) {
                    $design_price = intval($saved_prices[$cat->term_id]);
                }
            }
            if (!$category_id && !empty($categories)) {
                $category_id = $categories[0]->term_id;
                $category_name = $categories[0]->name;
            }
        } else {
            error_log('get_product_info: No categories found for product_id: ' . $product_id);
            wp_send_json_error(['message' => 'دسته‌بندی محصول یافت نشد']);
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_pricing';
        $pricing = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE category_id = %d", $category_id),
            ARRAY_A
        );
        $response = [
            'id' => $product_id,
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'category_id' => $category_id,
            'category_name' => $category_name,
            'categories' => $all_categories,
            'pricing' => $pricing ?: [],
            'no_print' => $no_print,
            'design_price' => $design_price,
        ];
        wp_send_json_success($response);
    }

    public function get_stage_template() {
        check_ajax_referer('print_order_nonce', 'nonce');
        $stage = isset($_POST['stage']) ? sanitize_text_field($_POST['stage']) : '';
        if (!in_array($stage, ['stage_2', 'stage_3_shipping', 'stage_3_payment'])) {
            error_log('get_stage_template: Invalid stage: ' . $stage);
            wp_send_json_error(['message' => 'مرحله نامعتبر است']);
        }
        $options = get_option('print_order_template_options', []);
        $template_id_map = [
            'stage_2' => 'stage_2_template_id',
            'stage_3_shipping' => 'stage_3_shipping_template_id',
            'stage_3_payment' => 'stage_3_payment_template_id',
        ];
        $shortcode_id = isset($options[$template_id_map[$stage]]) ? absint($options[$template_id_map[$stage]]) : 0;
        $shortcode_content = '';
        if ($shortcode_id) {
            $template = get_post($shortcode_id);
            if ($template && $template->post_type === 'elementor_library' && $template->post_status === 'publish') {
                if (class_exists('\Elementor\Plugin')) {
                    $shortcode_content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($shortcode_id, true);
                    if (empty($shortcode_content)) {
                        $shortcode_content = '<p class="text-red-600 text-center">خطا: محتوای قالب المنتور بارگذاری نشد.</p>';
                    }
                } else {
                    $shortcode_content = '<p class="text-red-600 text-center">خطا: افزونه المنتور فعال نیست.</p>';
                }
            } else {
                $shortcode_content = '<p class="text-gray-600 text-center">خطا: قالب المنتور یافت نشد یا منتشر نشده است.</p>';
            }
        } else {
            $shortcode_content = '<p class="text-gray-600 text-center">هیچ قالبی برای این مرحله تعریف نشده است.</p>';
        }
        wp_send_json_success(['content' => $shortcode_content]);
    }

    public function get_user_info() {
        check_ajax_referer('print_order_nonce', 'nonce');
        $user_id = get_current_user_id();
        if ($user_id) {
            $user_info = [
                'user_id' => $user_id,
                'billing_first_name' => get_user_meta($user_id, 'billing_first_name', true),
                'billing_last_name' => get_user_meta($user_id, 'billing_last_name', true),
                'billing_email' => get_user_meta($user_id, 'billing_email', true),
                'billing_phone' => get_user_meta($user_id, 'billing_phone', true),
                'billing_state' => get_user_meta($user_id, 'billing_state', true),
                'billing_city' => get_user_meta($user_id, 'billing_city', true),
                'billing_address_1' => get_user_meta($user_id, 'billing_address_1', true),
                'billing_postcode' => get_user_meta($user_id, 'billing_postcode', true),
            ];
            wp_send_json_success($user_info);
        } else {
            error_log('get_user_info: No user logged in');
            wp_send_json_error(['message' => 'کاربر وارد نشده است']);
        }
    }

    public function get_provinces() {
        if (!isset($_POST['nonce']) ||
            (!wp_verify_nonce($_POST['nonce'], 'print_order_nonce') &&
             !wp_verify_nonce($_POST['nonce'], 'print_order_public_nonce'))) {
            error_log('get_provinces: Invalid nonce');
            wp_send_json_error(['message' => 'خطای امنیتی']);
        }
        $states = WC()->countries->get_states('IR');
        if ($states && is_array($states)) {
            wp_send_json_success($states);
        } else {
            error_log('get_provinces: No states found');
            wp_send_json_error(['message' => 'استان‌ها یافت نشدند']);
        }
    }

    public function get_cities() {
        check_ajax_referer('print_order_nonce', 'nonce');
        wp_send_json_success([]);
        return;
    }

    public function update_user_profile($user_id, $form_data) {
        $billing_state = isset($form_data['billing_state']) ? sanitize_text_field($form_data['billing_state']) : '';
        $billing_state_code = in_array($billing_state, array_values($this->provinces_map))
            ? $billing_state
            : (array_search($billing_state, $this->provinces_map) ?: $billing_state);
        error_log('update_user_profile: billing_state = ' . $billing_state . ', converted to = ' . $billing_state_code);

        $fields = [
            'billing_first_name' => $form_data['customer_name'] ? $form_data['customer_name'] : '',
            'billing_last_name' => $form_data['customer_lastname'] ? $form_data['customer_lastname'] : '',
            'billing_email' => $form_data['customer_email'] ? $form_data['customer_email'] : '',
            'billing_phone' => $form_data['customer_phone'] ? $form_data['customer_phone'] : '',
            'billing_state' => $billing_state_code,
            'billing_city' => $form_data['billing_city'] ? $form_data['billing_city'] : '',
            'billing_address_1' => $form_data['billing_address'] ? $form_data['billing_address'] : '',
            'billing_postcode' => $form_data['billing_postcode'] ? $form_data['billing_postcode'] : '',
        ];
        foreach ($fields as $key => $value) {
            if ($value) {
                update_user_meta($user_id, $key, sanitize_text_field($value));
            }
        }
    }

    public function ajax_update_user_profile() {
        check_ajax_referer('print_order_nonce', 'nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            error_log('ajax_update_user_profile: No user logged in');
            wp_send_json_error(['message' => 'کاربر وارد نشده است']);
        }
        $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
        if ($form_data) {
            $billing_state = isset($form_data['billing_state']) ? sanitize_text_field($form_data['billing_state']) : '';
            $billing_state_code = in_array($billing_state, array_values($this->provinces_map))
                ? $billing_state
                : (array_search($billing_state, $this->provinces_map) ?: $billing_state);
            if (!in_array($billing_state_code, array_values($this->provinces_map))) {
                error_log('ajax_update_user_profile: Invalid billing state: ' . $billing_state_code);
                wp_send_json_error(['message' => 'استان انتخاب‌شده معتبر نیست']);
            }
            $this->update_user_profile($user_id, $form_data);
            wp_send_json_success(['message' => 'پروفایل به‌روزرسانی شد']);
        } else {
            error_log('ajax_update_user_profile: Invalid form data');
            wp_send_json_error(['message' => 'داده‌های فرم نامعتبر است']);
        }
    }

    private function send_sms($phone, $message) {
        $options = get_option('print_order_options', []);
        $sms_api_key = $options['sms_api_key'] ? $options['sms_api_key'] : '';
        $sms_line_number = $options['sms_line_number'] ? $options['sms_line_number'] : '';
        if (empty($sms_api_key) || empty($sms_line_number)) {
            error_log('send_sms: Missing API key or line number');
            return false;
        }
        $api_url = 'https://api.kavenegar.com/v1/' . $sms_api_key . '/sms/send.json';
        $params = [
            'receptor' => $phone,
            'sender' => $sms_line_number,
            'message' => $message,
        ];
        $response = wp_remote_post($api_url, [
            'body' => $params,
            'timeout' => 10,
        ]);
        if (is_wp_error($response)) {
            error_log('send_sms: Request failed - ' . $response->get_error_message());
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        $success = isset($result['return']['status']) && $result['return']['status'] == 200;
        error_log('send_sms: Result - Success: ' . ($success ? 'true' : 'false') . ', Response: ' . print_r($result, true));
        return $success;
    }

    public function initiate_zarinpal_payment($order, $amount, $description, $callback_url, $customer_phone, $customer_email) {
        $options = get_option('print_order_options', []);
        if (empty($options['zarinpal_enabled']) || empty($options['zarinpal_merchant_id'])) {
            error_log('initiate_zarinpal_payment: Zarinpal not enabled or missing merchant ID');
            return false;
        }
        $merchant_id = $options['zarinpal_merchant_id'];
        $sandbox = !empty($options['zarinpal_sandbox']) ? true : false;
        $endpoint = $sandbox ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json' : 'https://api.zarinpal.com/pg/v4/payment/request.json';
        $args = [
            'merchant_id' => $merchant_id,
            'amount' => $amount * 10,
            'currency' => 'IRR',
            'description' => $description,
            'callback_url' => $callback_url,
            'metadata' => [
                'mobile' => $customer_phone,
                'email' => $customer_email,
            ],
        ];
        $response = wp_remote_post($endpoint, [
            'body' => wp_json_encode($args),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            error_log('initiate_zarinpal_payment: Request failed - ' . $response->get_error_message());
            if (!empty($options['zarinpal_debug_log'])) {
                wc_add_notice('خطا در ارتباط با درگاه زرین‌پال: ' . $response->get_error_message(), 'error');
            }
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        if (isset($result['data']['code']) && $result['data']['code'] == 100) {
            $authority = $result['data']['authority'];
            $payment_url = $sandbox
                ? 'https://sandbox.zarinpal.com/pg/StartPay/' . $authority
                : 'https://www.zarinpal.com/pg/StartPay/' . $authority;
            error_log('initiate_zarinpal_payment: Success - Authority: ' . $authority . ', Payment URL: ' . $payment_url);
            return ['authority' => $authority, 'payment_url' => $payment_url];
        }
        error_log('initiate_zarinpal_payment: Failed - Response: ' . print_r($result, true));
        if (!empty($options['zarinpal_debug_log'])) {
            wc_add_notice('خطا در شروع پرداخت زرین‌پال: ' . ($result['errors']['message'] ?? 'خطای ناشناخته'), 'error');
        }
        return false;
    }

    public function verify_zarinpal_payment($order, $authority) {
        $options = get_option('print_order_options', []);
        $merchant_id = $options['zarinpal_merchant_id'];
        $sandbox = !empty($options['zarinpal_sandbox']) ? true : false;
        $endpoint = $sandbox ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json' : 'https://api.zarinpal.com/pg/v4/payment/verify.json';
        $args = [
            'merchant_id' => $merchant_id,
            'authority' => $authority,
            'amount' => $order->get_total() * 10,
        ];
        $response = wp_remote_post($endpoint, [
            'body' => wp_json_encode($args),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            error_log('verify_zarinpal_payment: Request failed - ' . $response->get_error_message());
            if (!empty($options['zarinpal_debug_log'])) {
                wc_add_notice('خطا در تأیید پرداخت زرین‌پال: ' . $response->get_error_message(), 'error');
            }
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        if (isset($result['data']['code']) && in_array($result['data']['code'], [100, 101])) {
            $ref_id = $result['data']['ref_id'];
            $order->update_meta_data('_zarinpal_ref_id', $ref_id);
            // Ensure essential meta data is preserved
            $existing_meta = $order->get_meta('print_order_data');
            if ($existing_meta) {
                $order->update_meta_data('print_order_data', $existing_meta);
            }
            $existing_files = $order->get_meta('print_order_files');
            if ($existing_files) {
                $order->update_meta_data('print_order_files', $existing_files);
            }
            $existing_product_id = $order->get_meta('_print_order_wc_product_id');
            if ($existing_product_id) {
                $order->update_meta_data('_print_order_wc_product_id', $existing_product_id);
            }
            // Add WooCommerce meta for dashboard visibility
            $order->update_meta_data('_recorded_sales', 'yes');
            $order->update_meta_data('_order_stock_reduced', 'yes');
            // Update status using standard WooCommerce method
            $order->set_status('wc-payment-completed', 'پرداخت از طریق زرین‌پال با شماره ارجاع ' . $ref_id . ' تأیید شد.');
            $order->save();
            error_log('verify_zarinpal_payment: Success - Ref ID: ' . $ref_id . ', Order ID: ' . $order->get_id() . ', Status updated to wc-payment-completed');
            return true;
        }
        error_log('verify_zarinpal_payment: Failed - Response: ' . print_r($result, true));
        if (!empty($options['zarinpal_debug_log'])) {
            wc_add_notice('خطا در تأیید پرداخت زرین‌پال: ' . ($result['errors']['message'] ?? 'خطای ناشناخته'), 'error');
        }
        return false;
    }

    public function handle_zarinpal_callback() {
        if (!isset($_GET['Authority']) || !isset($_GET['Status'])) {
            return;
        }
        $authority = sanitize_text_field($_GET['Authority']);
        $status = sanitize_text_field($_GET['Status']);
        error_log('handle_zarinpal_callback: Processing - Authority: ' . $authority . ', Status: ' . $status);
        $order_id = get_transient('zarinpal_order_' . $authority);
        if (!$order_id) {
            error_log('handle_zarinpal_callback: No order found for Authority: ' . $authority);
            wp_redirect(home_url('/?payment=failed'));
            exit;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('handle_zarinpal_callback: Order not found for ID: ' . $order_id);
            wp_redirect(home_url('/?payment=failed'));
            exit;
        }
        $options = get_option('print_order_options', []);
        if ($status === 'OK' && $this->verify_zarinpal_payment($order, $authority)) {
            delete_transient('zarinpal_order_' . $authority);
            $redirect_url = !empty($options['zarinpal_success_url']) ? $options['zarinpal_success_url'] : $order->get_checkout_order_received_url();
            $success_message = !empty($options['zarinpal_success_message']) ? $options['zarinpal_success_message'] : 'پرداخت شما با موفقیت انجام شد!';
            wc_add_notice($success_message, 'success');
            error_log('handle_zarinpal_callback: Payment successful - Order ID: ' . $order_id);
            WC()->cart->empty_cart();
        } else {
            $order->update_status('failed', 'پرداخت زرین‌پال ناموفق بود.');
            $redirect_url = !empty($options['zarinpal_cancel_url']) ? $options['zarinpal_cancel_url'] : $order->get_checkout_payment_url();
            $error_message = !empty($options['zarinpal_error_message']) ? $options['zarinpal_error_message'] : 'پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.';
            wc_add_notice($error_message, 'error');
            error_log('handle_zarinpal_callback: Payment failed - Order ID: ' . $order_id . ', Status: ' . $status);
        }
        wp_redirect($redirect_url);
        exit;
    }

    public function submit_order() {
        check_ajax_referer('print_order_nonce', 'nonce');
        $product_id = isset($_POST['wc_product_id']) ? intval($_POST['wc_product_id']) : 0;
        $form_data = [];
        foreach ($_POST as $key => $value) {
            if ($key !== 'action' && $key !== 'nonce' && $key !== 'wc_product_id') {
                $form_data[$key] = $value;
            }
        }
        error_log('submit_order: Raw POST data: ' . print_r($_POST, true));
        error_log('submit_order: Filtered form_data: ' . print_r($form_data, true));
        error_log('submit_order: Raw form_data[files]: ' . print_r($form_data['files'] ?? 'Not set', true));
        error_log('submit_order: Type of form_data[files]: ' . gettype($form_data['files'] ?? 'Not set'));

        if (!$product_id || empty($form_data)) {
            error_log('submit_order: Invalid data - product_id: ' . $product_id . ', form_data: ' . print_r($form_data, true));
            wp_send_json_error(['message' => 'داده‌های نامعتبر']);
        }

        $billing_state = isset($form_data['billing_state']) ? sanitize_text_field($form_data['billing_state']) : '';
        $billing_state_code = in_array($billing_state, array_values($this->provinces_map))
            ? $billing_state
            : (array_search($billing_state, $this->provinces_map) ?: $billing_state);
        error_log('submit_order: billing_state = ' . $billing_state . ', converted to = ' . $billing_state_code);

        $shipping_state = isset($form_data['shipping_state']) ? sanitize_text_field($form_data['shipping_state']) : '';
        $shipping_state_code = in_array($shipping_state, array_values($this->provinces_map))
            ? $shipping_state
            : (array_search($shipping_state, $this->provinces_map) ?: $shipping_state);
        error_log('submit_order: shipping_state = ' . $shipping_state . ', converted to = ' . $shipping_state_code);

        if (!in_array($billing_state_code, array_values($this->provinces_map))) {
            error_log('submit_order: Invalid billing state: ' . $billing_state_code);
            wp_send_json_error(['message' => 'استان فاکتور معتبر نیست']);
        }
        if (isset($form_data['ship_to_different_address']) && $form_data['ship_to_different_address'] === 'true' && !in_array($shipping_state_code, array_values($this->provinces_map))) {
            error_log('submit_order: Invalid shipping state: ' . $shipping_state_code);
            wp_send_json_error(['message' => 'استان ارسال معتبر نیست']);
        }

        $sides_mapping = [
            'دورو' => 'Double',
            'یکرو' => 'Single',
        ];
        if (isset($form_data['sides']) && isset($sides_mapping[$form_data['sides']])) {
            $form_data['sides'] = $sides_mapping[$form_data['sides']];
        }

        $required_fields = [
            'customer_name' => 'نام',
            'customer_lastname' => 'نام خانوادگی',
            'customer_email' => 'ایمیل',
            'customer_phone' => 'شماره تماس',
            'billing_state' => 'استان',
            'billing_city' => 'شهر',
            'billing_address' => 'آدرس',
            'billing_postcode' => 'کد پستی',
        ];
        $errors = [];
        foreach ($required_fields as $field => $label) {
            if (empty($form_data[$field])) {
                $errors[] = "فیلد $label الزامی است";
            }
        }
        if (!empty($form_data['customer_email']) && !is_email($form_data['customer_email'])) {
            $errors[] = 'ایمیل وارد شده نامعتبر است';
        }
        if (!empty($form_data['customer_phone']) && !preg_match('/^09[0-9]{9}$/', $form_data['customer_phone'])) {
            $errors[] = 'شماره تماس باید 11 رقم و با 09 شروع شود';
        }
        if (!empty($form_data['billing_postcode']) && !preg_match('/^\d{10}$/', $form_data['billing_postcode'])) {
            $errors[] = 'کد پستی باید 10 رقم باشد';
        }
        if (isset($form_data['ship_to_different_address']) && $form_data['ship_to_different_address'] === 'true') {
            $shipping_required_fields = [
                'shipping_state' => 'استان ارسال',
                'shipping_city' => 'شهر ارسال',
                'shipping_address' => 'آدرس ارسال',
                'shipping_postcode' => 'کد پستی ارسال',
            ];
            foreach ($shipping_required_fields as $field => $label) {
                if (empty($form_data[$field])) {
                    $errors[] = "فیلد $label الزامی است";
                }
            }
            if (!empty($form_data['shipping_postcode']) && !preg_match('/^\d{10}$/', $form_data['shipping_postcode'])) {
                $errors[] = 'کد پستی ارسال باید 10 رقم باشد';
            }
            if (!empty($form_data['shipping_phone']) && !preg_match('/^09[0-9]{9}$/', $form_data['shipping_phone'])) {
                $errors[] = 'شماره تماس گیرنده باید 11 رقم و با 09 شروع شود';
            }
        }

        $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
        $category_id = !empty($categories) && !is_wp_error($categories) ? $categories[0] : 0;
        $saved_settings = get_option('print_order_print_management', []);
        $is_no_print_category = false;
        foreach ($categories as $cat_id) {
            if (isset($saved_settings[$cat_id])) {
                $is_no_print_category = true;
                break;
            }
        }

        $is_print_needed = !$is_no_print_category && (empty($form_data['no_print_needed']) || $form_data['no_print_needed'] === 'false');

        if ($is_print_needed) {
            $print_required_fields = [
                'paper_type_persian' => 'جنس کاغذ',
                'size' => 'سایز',
                'quantity' => 'تعداد',
                'sides' => 'نوع چاپ',
            ];
            foreach ($print_required_fields as $field => $label) {
                if (empty($form_data[$field])) {
                    $errors[] = "فیلد $label الزامی است";
                }
            }
        }

        if (!empty($errors)) {
            error_log('submit_order: Validation errors: ' . implode(', ', $errors));
            wp_send_json_error(['message' => implode('<br>', $errors)]);
        }

        if (!$category_id) {
            error_log('submit_order: No category found for product_id: ' . $product_id);
            wp_send_json_error(['message' => 'دسته‌بندی محصول یافت نشد']);
        }

        // Fetch category name for meta data
        $category_name = '';
        $category_terms = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'all']);
        foreach ($category_terms as $term) {
            if ($term->term_id == $category_id) {
                $category_name = $term->name;
                break;
            }
        }

        global $wpdb;
        $pricing = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}print_order_pricing", ARRAY_A);
        $options = get_option('print_order_options', ['design_fee' => 0, 'tax_rate' => 0, 'shipping_fee' => 0]);
        $saved_prices = get_option('print_order_design_prices', []);
        $design_fee = isset($saved_prices[$category_id]) && $saved_prices[$category_id] > 0 ? intval($saved_prices[$category_id]) : intval($options['design_fee']);
        $print_price = 0;
        
        if ($is_print_needed) {
            foreach ($pricing as $item) {
                if (
                    ($item['category_id'] == $category_id) &&
                    ($item['paper_type_persian'] === $form_data['paper_type_persian']) &&
                    ($item['size'] === $form_data['size']) &&
                    ((string)$item['quantity'] === (string)$form_data['quantity']) &&
                    ($item['sides'] === $form_data['sides'])
                ) {
                    $print_price = intval($item['price']);
                    break;
                }
            }
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            error_log('submit_order: Product not found for product_id: ' . $product_id);
            wp_send_json_error(['message' => 'محصول یافت نشد']);
        }
        $base_price = intval($product->get_price());
        $shipping_fee = intval($options['shipping_fee']);
        $tax_rate = intval($options['tax_rate']);
        $subtotal = $base_price + $print_price + $design_fee + $shipping_fee;
        $tax = $subtotal * ($tax_rate / 100);
        $discount_amount = isset($form_data['discount_amount']) ? floatval($form_data['discount_amount']) : 0;
        error_log('submit_order: Discount Amount: ' . $discount_amount);
        $total = $subtotal + $tax - $discount_amount;

        $user_id = isset($form_data['user_id']) && !empty($form_data['user_id']) ? intval($form_data['user_id']) : 0;
        if (!$user_id && !empty($form_data['customer_email'])) {
            $email = sanitize_email($form_data['customer_email']);
            if ($existing_user_id = email_exists($email)) {
                $user_id = $existing_user_id;
            } else {
                $temp_user_data = [
                    'customer_email' => $email,
                    'customer_phone' => $form_data['customer_phone'],
                    'customer_name' => $form_data['customer_name'],
                    'customer_lastname' => $form_data['customer_lastname'] ?? '',
                    'billing_state' => $billing_state_code,
                    'billing_city' => $form_data['billing_city'] ?? '',
                    'billing_address' => $form_data['billing_address'] ?? '',
                    'billing_postcode' => $form_data['billing_postcode'] ?? '',
                ];
                $guest_user = $this->create_guest_user_from_data($temp_user_data);
                if ($guest_user && !is_wp_error($guest_user)) {
                    $user_id = $guest_user['user_id'];
                }
            }
        }
        if (!$user_id) {
            error_log('submit_order: No valid user_id');
            wp_send_json_error(['message' => 'کاربر معتبر یافت نشد']);
        }

        $this->update_user_profile($user_id, $form_data);
        $order = wc_create_order(['status' => 'wc-order-registered']);
        $order->set_customer_id($user_id);
        $order->add_product($product, 1);
        $order->set_billing_first_name(sanitize_text_field($form_data['customer_name']));
        $order->set_billing_last_name(sanitize_text_field($form_data['customer_lastname']));
        $order->set_billing_email(sanitize_email($form_data['customer_email']));
        $order->set_billing_phone(sanitize_text_field($form_data['customer_phone']));
        $order->set_billing_country('IR');
        $order->set_billing_state($billing_state_code);
        $order->set_billing_city(sanitize_text_field($form_data['billing_city']));
        $order->set_billing_address_1(sanitize_textarea_field($form_data['billing_address']));
        $order->set_billing_postcode(sanitize_text_field($form_data['billing_postcode']));
        if (isset($form_data['ship_to_different_address']) && $form_data['ship_to_different_address'] === 'true') {
            $order->set_shipping_first_name(sanitize_text_field($form_data['customer_name']));
            $order->set_shipping_last_name(sanitize_text_field($form_data['customer_lastname']));
            $order->set_shipping_country('IR');
            $order->set_shipping_state($shipping_state_code);
            $order->set_shipping_city(sanitize_text_field($form_data['shipping_city']));
            $order->set_shipping_address_1(sanitize_textarea_field($form_data['shipping_address']));
            $order->set_shipping_postcode(sanitize_text_field($form_data['shipping_postcode']));
            if (!empty($form_data['shipping_phone'])) {
                $order->set_shipping_phone(sanitize_text_field($form_data['shipping_phone']));
            }
        }
        $order->calculate_totals();
        $order->set_total($total);
        // Add category meta data
        $order->update_meta_data('_print_order_category', $category_name);
        $order->update_meta_data('_print_order_category_id', $category_id);
        $order->update_meta_data('print_order_data', $form_data);
        $order->update_meta_data('print_price', $print_price);
        $order->update_meta_data('design_fee', $design_fee);
        $order->update_meta_data('shipping_fee', $shipping_fee);
        $order->update_meta_data('tax_amount', $tax);
        $order->update_meta_data('discount_amount', $discount_amount);
        $order->update_meta_data('no_print_needed', !$is_print_needed ? '1' : '0');
        $order->update_meta_data('_print_order_wc_product_id', $product_id); // Save product ID explicitly

        $temp_id = WC()->session->get('print_order_temp_id');
        $temp_files = WC()->session->get('print_order_temp_files', []);
        $attachment_ids = [];
        $stored_files = [];

        // Create private directory
        $private_dir = WP_CONTENT_DIR . '/Uploads/private/';
        if (!file_exists($private_dir) && !wp_mkdir_p($private_dir)) {
            error_log('submit_order: Failed to create private directory: ' . $private_dir);
            wp_send_json_error(['message' => 'خطا در ایجاد پوشه private: ' . $private_dir], 500);
        }
        if (!is_writable($private_dir)) {
            error_log('submit_order: private directory is not writable: ' . $private_dir);
            wp_send_json_error(['message' => 'پوشه private قابل نوشتن نیست: ' . $private_dir], 500);
        }

        // Generate folder name with Persian date and order ID
        $persian_date = $this->convert_date_to_persian(new DateTime());
        $order_id = $order->get_id();
        $folder_name = $this->persian_to_english_digits($persian_date['date']) . '-' . $order_id;
        $order_dir = $private_dir . $folder_name . '/';
        
        if (!file_exists($order_dir) && !wp_mkdir_p($order_dir)) {
            error_log('submit_order: Failed to create order directory: ' . $order_dir);
            wp_send_json_error(['message' => 'خطا در ایجاد پوشه سفارش: ' . $order_dir], 500);
        }
        if (!is_writable($order_dir)) {
            error_log('submit_order: Order directory is not writable: ' . $order_dir);
            wp_send_json_error(['message' => 'پوشه سفارش قابل نوشتن نیست: ' . $order_dir], 500);
        }

        foreach ($temp_files as $file) {
            $file_name = sanitize_file_name($file['name']);
            $temp_path = $file['path'];
            $new_path = $order_dir . $file_name;

            if (!file_exists($temp_path)) {
                error_log('submit_order: Temp file not found: ' . $temp_path);
                continue;
            }

            if (!copy($temp_path, $new_path)) {
                error_log('submit_order: Failed to copy file from ' . $temp_path . ' to ' . $new_path);
                continue;
            }

            if (!file_exists($new_path)) {
                error_log('submit_order: File not found after copy: ' . $new_path);
                continue;
            }

            // Delete the temporary file
            unlink($temp_path);

            // Store file information
            $new_url = WP_CONTENT_URL . '/Uploads/private/' . $folder_name . '/' . $file_name;
            $stored_files[] = [
                'name' => $file_name,
                'url' => $new_url,
                'path' => $new_path,
                'format' => $file['format'],
            ];

            // Add to WordPress Media Library
            $upload = wp_upload_bits($file_name, null, file_get_contents($new_path));
            if (!$upload['error']) {
                $attachment = [
                    'post_mime_type' => $upload['type'],
                    'post_title' => $file_name,
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];
                $attachment_id = wp_insert_attachment($attachment, $upload['file']);
                if (!is_wp_error($attachment_id)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);
                    $attachment_ids[] = $attachment_id;
                }
            }
        }

        if (!empty($stored_files)) {
            $order->update_meta_data('print_order_files', $stored_files);
        }

        WC()->session->set('print_order_temp_files', []);
        WC()->session->set('print_order_temp_id', '');

        $order->save();
        $payment_result = $this->initiate_zarinpal_payment(
            $order,
            $total,
            'پرداخت سفارش #' . $order->get_order_number(),
            add_query_arg(['order_id' => $order->get_id()], home_url('/zarinpal-callback')),
            sanitize_text_field($form_data['customer_phone']),
            sanitize_email($form_data['customer_email'])
        );
        if ($payment_result) {
            set_transient('zarinpal_order_' . $payment_result['authority'], $order->get_id(), 24 * HOUR_IN_SECONDS);
            wp_send_json_success(['redirect_url' => $payment_result['payment_url']]);
        } else {
            $order->update_status('failed', 'خطا در شروع پرداخت.');
            error_log('submit_order: Payment initiation failed for order ID: ' . $order->get_id());
            wp_send_json_error(['message' => 'خطا در شروع پرداخت']);
        }
    }

    private function create_guest_user_from_data($data) {
        $email = sanitize_email($data['customer_email']);
        $phone = sanitize_text_field($data['customer_phone']);
        $name = sanitize_text_field($data['customer_name']);
        $lastname = sanitize_text_field($data['customer_lastname']);
        $billing_state = sanitize_text_field($data['billing_state']);

        if (!is_email($email)) {
            return new WP_Error('invalid_email', 'ایمیل نامعتبر است');
        }
        if (email_exists($email)) {
            return new WP_Error('email_exists', 'ایمیل قبلاً ثبت شده است');
        }
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            return new WP_Error('invalid_phone', 'شماره تماس باید 11 رقم و با 09 شروع شود');
        }
        if (!in_array($billing_state, array_values($this->provinces_map))) {
            return new WP_Error('invalid_state', 'استان انتخاب‌شده معتبر نیست');
        }

        $email_prefix = substr($email, 0, 4);
        $special_chars = ['!', '@', '#', '$', '%', '&', '*'];
        $special_char = $special_chars[array_rand($special_chars)];
        $phone_suffix = substr($phone, -6);
        $password = ucfirst(strtolower($email_prefix)) . $special_char . $phone_suffix;

        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $user = new WP_User($user_id);
        $user->set_role('customer');

        update_user_meta($user_id, 'billing_first_name', $name);
        if ($lastname) {
            update_user_meta($user_id, 'billing_last_name', $lastname);
        }
        update_user_meta($user_id, 'billing_email', $email);
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'billing_state', $billing_state);
        update_user_meta($user_id, 'billing_city', sanitize_text_field($data['billing_city']));
        update_user_meta($user_id, 'billing_address_1', sanitize_textarea_field($data['billing_address']));
        update_user_meta($user_id, 'billing_postcode', sanitize_text_field($data['billing_postcode']));
        update_user_meta($user_id, 'billing_country', 'IR');

        $subject = 'خوش آمدید به ' . get_bloginfo('name');
        $message = "سلام {$name},\n\n";
        $message .= "حساب کاربری شما با موفقیت ایجاد شد.\n";
        $message .= "نام کاربری: {$email}\n";
        $message .= "رمز عبور: {$password}\n";
        $message .= "برای ورود به حساب خود از این لینک استفاده کنید: " . wp_login_url() . "\n\n";
        $message .= "با تشکر,\n" . get_bloginfo('name');
        wp_mail($email, $subject, $message);

        $options = get_option('print_order_options', []);
        $sms_template = $options['sms_welcome_template'] ? $options['sms_welcome_template'] : "حساب شما در {site_name} ایجاد شد. نام کاربری: {username}، رمز عبور: {password}";
        $sms_message = str_replace(
            ['{site_name}', '{username}', '{password}'],
            [get_bloginfo('name'), $email, $password],
            $sms_template
        );
        $this->send_sms($phone, $sms_message);

        return ['user_id' => $user_id, 'email' => $email, 'password' => $password];
    }
}
?>
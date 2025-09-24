<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Settings {
    public function __construct() {
        // Add settings menu
        add_action('admin_menu', [$this, 'add_settings_menu']);
        // Enqueue styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function enqueue_styles($hook) {
        // No styles enqueued here anymore, just logging for debugging
        error_log('Print Order: admin_enqueue_scripts hook fired for settings, current hook: ' . $hook);
    }

    public function add_settings_menu() {
        add_menu_page(
            __('تنظیمات سفارش چاپ', 'print-order'),
            __('سفارش چاپ', 'print-order'),
            'manage_options',
            'print-order-settings',
            [$this, 'settings_page'],
            'dashicons-admin-tools',
            20
        );
        add_submenu_page(
            'print-order-settings',
            __('تنظیمات', 'print-order'),
            __('تنظیمات', 'print-order'),
            'manage_options',
            'print-order-settings-main',
            [$this, 'settings_page']
        );
        remove_submenu_page('print-order-settings', 'print-order-settings');
    }

    public function settings_page() {
        if (isset($_POST['save_settings']) && check_admin_referer('save_settings_nonce')) {
            $options = [
                'form_page_url' => esc_url_raw($_POST['form_page_url'] ?? ''),
                'login_page_url' => esc_url_raw($_POST['login_page_url'] ?? wp_login_url()),
                'button_bg_color' => sanitize_hex_color($_POST['button_bg_color'] ?? '#2563EB'),
                'button_text_color' => sanitize_hex_color($_POST['button_text_color'] ?? '#ffffff'),
                'design_fee' => floatval($_POST['design_fee'] ?? 50000),
                'tax_rate' => floatval($_POST['tax_rate'] ?? 9),
                'shipping_fee' => floatval($_POST['shipping_fee'] ?? 0),
                'max_design_revisions' => intval($_POST['max_design_revisions'] ?? 3),
                'notification_message' => sanitize_textarea_field($_POST['notification_message'] ?? 'لطفاً اطلاعات سفارش را به‌دقت بررسی کنید. پس از تأیید، امکان تغییر سفارش وجود ندارد.'),
                'pdf_logo' => esc_url_raw($_POST['pdf_logo'] ?? ''),
                'pdf_footer' => sanitize_textarea_field($_POST['pdf_footer'] ?? ''),
                'pdf_title' => sanitize_text_field($_POST['pdf_title'] ?? 'فاکتور سفارش چاپ'),
                'pdf_header_color' => sanitize_hex_color($_POST['pdf_header_color'] ?? '#2563EB'),
                'pdf_table_border_color' => sanitize_hex_color($_POST['pdf_table_border_color'] ?? '#E5E7EB'),
                'sms_api_key' => sanitize_text_field($_POST['sms_api_key'] ?? ''),
                'sms_line_number' => sanitize_text_field($_POST['sms_line_number'] ?? ''),
                'sms_welcome_template' => sanitize_textarea_field($_POST['sms_welcome_template'] ?? 'حساب شما در {site_name} ایجاد شد. نام کاربری: {username}، رمز عبور: {password}'),
                'zarinpal_enabled' => isset($_POST['zarinpal_enabled']) ? 1 : 0,
                'zarinpal_merchant_id' => sanitize_text_field($_POST['zarinpal_merchant_id'] ?? ''),
                'zarinpal_sandbox' => isset($_POST['zarinpal_sandbox']) ? 1 : 0,
                'zarinpal_success_url' => esc_url_raw($_POST['zarinpal_success_url'] ?? ''),
                'zarinpal_cancel_url' => esc_url_raw($_POST['zarinpal_cancel_url'] ?? ''),
                'zarinpal_success_message' => sanitize_textarea_field($_POST['zarinpal_success_message'] ?? 'پرداخت شما با موفقیت انجام شد!'),
                'zarinpal_error_message' => sanitize_textarea_field($_POST['zarinpal_error_message'] ?? 'پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.'),
                'zarinpal_debug_log' => isset($_POST['zarinpal_debug_log']) ? 1 : 0,
                // تنظیمات جدید برای تب کاربر
                'user_order_default_message' => sanitize_textarea_field($_POST['user_order_default_message'] ?? 'سفارش شما با موفقیت ثبت شد. منتظر تأیید نهایی باشید.'),
                'user_edit_request_delay' => intval($_POST['user_edit_request_delay'] ?? 1440), // تغییر به دقیقه (24 ساعت = 1440 دقیقه)
                'user_edit_request_message' => sanitize_textarea_field($_POST['user_edit_request_message'] ?? 'پیام شما دریافت شد. منتظر تأیید مدیر باشید.'),
            ];
            update_option('print_order_options', $options);
            error_log('Print Order: Settings saved - Options: ' . json_encode($options));
            echo '<div class="notice notice-success is-dismissible"><p>' . __('تنظیمات با موفقیت ذخیره شد!', 'print-order') . '</p></div>';
        }

        $options = get_option('print_order_options', []);
        error_log('Print Order: Loaded settings - Options: ' . json_encode($options));
        $default_form_url = home_url('/order-form/');
        $default_success_url = wc_get_endpoint_url('order-received', '', wc_get_checkout_url());
        $default_cancel_url = home_url('/order-form/');

        // Enqueue the settings styles only in this callback
        wp_enqueue_style('class-settings-style', PRINT_ORDER_URL . 'assets/css/class-settings-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-settings-tw.css'));
        error_log('Print Order: Enqueued class-settings-tw.css from ' . PRINT_ORDER_URL . 'assets/css/class-settings-tw.css' . ' in settings_page');
        $wp_styles = wp_styles();
        if (isset($wp_styles->registered['class-settings-style'])) {
            error_log('Print Order: class-settings-style registered successfully in settings_page');
        } else {
            error_log('Print Order: class-settings-style failed to register in settings_page');
        }
        ?>
        <div class="wrap">
            <div class="max-w-full">
                <!-- هدر صفحه -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-800 text-right"><?php _e('مدیریت تنظیمات', 'print-order'); ?></h1>
                    <p class="text-gray-600 mt-2 text-right"><?php _e('تنظیمات افزونه سفارش چاپ را در این بخش مدیریت کنید.', 'print-order'); ?></p>
                </div>

                <!-- کارت اصلی -->
                <div class="settings-card bg-white shadow-md rounded-xl p-6">
                    <form method="post" action="" id="settings-form" class="rtl font-sans">
                        <?php wp_nonce_field('save_settings_nonce'); ?>

                        <!-- تب‌ها -->
                        <div class="mb-6">
                            <ul class="flex border-b border-gray-200 settings-tabs">
                                <li class="mr-1">
                                    <button type="button" class="tab-link active px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-lg" data-tab="general"><?php _e('تنظیمات عمومی', 'print-order'); ?></button>
                                </li>
                                <li class="mr-1">
                                    <button type="button" class="tab-link px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-t-lg" data-tab="appearance"><?php _e('تنظیمات ظاهری', 'print-order'); ?></button>
                                </li>
                                <li class="mr-1">
                                    <button type="button" class="tab-link px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-t-lg" data-tab="financial"><?php _e('تنظیمات مالی', 'print-order'); ?></button>
                                </li>
                                <li class="mr-1">
                                    <button type="button" class="tab-link px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-t-lg" data-tab="user"><?php _e('تنظیمات کاربر', 'print-order'); ?></button>
                                </li>
                                <li class="mr-1">
                                    <button type="button" class="tab-link px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-t-lg" data-tab="pdf"><?php _e('تنظیمات PDF', 'print-order'); ?></button>
                                </li>
                                <li class="mr-1">
                                    <button type="button" class="tab-link px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-t-lg" data-tab="sms"><?php _e('تنظیمات SMS', 'print-order'); ?></button>
                                </li>
                                <li class="mr-1">
                                    <button type="button" class="tab-link px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-t-lg" data-tab="zarinpal"><?php _e('تنظیمات درگاه زرین‌پال', 'print-order'); ?></button>
                                </li>
                            </ul>
                        </div>

                        <!-- محتوای تب‌ها -->
                        <div class="tab-content">
                            <!-- تنظیمات عمومی -->
                            <div id="general" class="tab-pane active">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات عمومی', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('تنظیمات اصلی افزونه را در این بخش وارد کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="form_page_url" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('آدرس صفحه فرم سفارش', 'print-order'); ?></label>
                                            <input type="url" name="form_page_url" id="form_page_url" value="<?php echo esc_attr($options['form_page_url'] ?? $default_form_url); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('آدرس صفحه‌ای که شورت‌کد [print_order_form] در آن قرار دارد را وارد کنید.', 'print-order'); ?></p>
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="form_page_url_error"><?php _e('لطفاً یک URL معتبر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="login_page_url" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('آدرس صفحه ورود', 'print-order'); ?></label>
                                            <input type="url" name="login_page_url" id="login_page_url" value="<?php echo esc_attr($options['login_page_url'] ?? wp_login_url()); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('آدرس صفحه ورود به حساب کاربری (برای لینک در اعتبارسنجی ایمیل).', 'print-order'); ?></p>
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="login_page_url_error"><?php _e('لطفاً یک URL معتبر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات ظاهری -->
                            <div id="appearance" class="tab-pane hidden">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات ظاهری', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('ظاهر دکمه‌ها را در این بخش تنظیم کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <label for="button_bg_color" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('رنگ پس‌زمینه دکمه', 'print-order'); ?></label>
                                                <input type="color" name="button_bg_color" id="button_bg_color" value="<?php echo esc_attr($options['button_bg_color'] ?? '#2563EB'); ?>" class="w-24 border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <span id="button_bg_color_value" class="text-gray-600 text-sm mr-2"><?php echo esc_attr($options['button_bg_color'] ?? '#2563EB'); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <label for="button_text_color" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('رنگ متن دکمه', 'print-order'); ?></label>
                                                <input type="color" name="button_text_color" id="button_text_color" value="<?php echo esc_attr($options['button_text_color'] ?? '#ffffff'); ?>" class="w-24 border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <span id="button_text_color_value" class="text-gray-600 text-sm mr-2"><?php echo esc_attr($options['button_text_color'] ?? '#ffffff'); ?></span>
                                        </div>
                                        <div class="col-span-2 flex justify-end">
                                            <label class="block text-gray-700 font-medium mb-2 text-right"><?php _e('پیش‌نمایش دکمه', 'print-order'); ?></label>
                                            <button type="button" id="button_preview" class="px-6 py-3 rounded-lg shadow-sm transition-all duration-200 mr-2"><?php _e('نمونه دکمه', 'print-order'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات مالی -->
                            <div id="financial" class="tab-pane hidden">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات مالی', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('هزینه‌ها و نرخ‌های مالی را در این بخش وارد کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="design_fee" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('هزینه طراحی (تومان)', 'print-order'); ?></label>
                                            <input type="number" name="design_fee" id="design_fee" value="<?php echo esc_attr($options['design_fee'] ?? 50000); ?>" step="1000" min="0" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="design_fee_error"><?php _e('لطفاً مقدار صفر یا بیشتر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="tax_rate" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('نرخ مالیات (%)', 'print-order'); ?></label>
                                            <input type="number" name="tax_rate" id="tax_rate" value="<?php echo esc_attr($options['tax_rate'] ?? 9); ?>" step="0.1" min="0" max="100" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="tax_rate_error"><?php _e('لطفاً مقدار بین 0 تا 100 وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="shipping_fee" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('هزینه حمل و نقل (تومان)', 'print-order'); ?></label>
                                            <input type="number" name="shipping_fee" id="shipping_fee" value="<?php echo esc_attr($options['shipping_fee'] ?? 0); ?>" step="1000" min="0" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="shipping_fee_error"><?php _e('لطفاً مقدار صفر یا بیشتر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات کاربر -->
                            <div id="user" class="tab-pane hidden">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات کاربر', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('تنظیمات مربوط به سفارشات را در این بخش وارد کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="max_design_revisions" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('حداکثر تعداد بازبینی طراحی', 'print-order'); ?></label>
                                            <input type="number" name="max_design_revisions" id="max_design_revisions" value="<?php echo esc_attr($options['max_design_revisions'] ?? 3); ?>" min="0" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="max_design_revisions_error"><?php _e('لطفاً مقدار صفر یا بیشتر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="notification_message" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('پیام اطلاع‌رسانی مرحله پرداخت', 'print-order'); ?></label>
                                            <textarea name="notification_message" id="notification_message" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['notification_message'] ?? 'لطفاً اطلاعات سفارش را به‌دقت بررسی کنید. پس از تأیید، امکان تغییر سفارش وجود ندارد.'); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('پیام نمایش داده شده در کارت اطلاع‌رسانی در مرحله پرداخت.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="user_order_default_message" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('پیام پیش‌فرض ثبت سفارش', 'print-order'); ?></label>
                                            <textarea name="user_order_default_message" id="user_order_default_message" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['user_order_default_message'] ?? 'سفارش شما با موفقیت ثبت شد. منتظر تأیید نهایی باشید.'); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('پیام پیش‌فرض نمایش داده شده پس از ثبت سفارش در تب تأیید طرح.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="user_edit_request_delay" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('مدت زمان تأخیر درخواست ویرایش (دقیقه)', 'print-order'); ?></label>
                                            <input type="number" name="user_edit_request_delay" id="user_edit_request_delay" value="<?php echo esc_attr($options['user_edit_request_delay'] ?? 1440); ?>" min="0" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('مدت زمان به دقیقه که پس از آن پیام تأییدیه نمایش داده شود.', 'print-order'); ?></p>
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="user_edit_request_delay_error"><?php _e('لطفاً مقدار صفر یا بیشتر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="user_edit_request_message" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('پیام تأییدیه درخواست ویرایش', 'print-order'); ?></label>
                                            <textarea name="user_edit_request_message" id="user_edit_request_message" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['user_edit_request_message'] ?? 'پیام شما دریافت شد. منتظر تأیید مدیر باشید.'); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('پیام نمایش داده شده پس از گذشت زمان تأخیر برای درخواست ویرایش.', 'print-order'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات PDF -->
                            <div id="pdf" class="tab-pane hidden">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات PDF', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V8a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('تنظیمات مربوط به فایل‌های PDF را در این بخش وارد کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="pdf_logo" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('آدرس لوگوی PDF', 'print-order'); ?></label>
                                            <input type="url" name="pdf_logo" id="pdf_logo" value="<?php echo esc_attr($options['pdf_logo'] ?? ''); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('آدرس تصویر لوگو برای نمایش در PDF.', 'print-order'); ?></p>
                                            <p class="error-message text-red-600 mt-1 hidden text-right text-sm" id="pdf_logo_error"><?php _e('لطفاً یک URL معتبر وارد کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="pdf_footer" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('متن فوتر PDF', 'print-order'); ?></label>
                                            <textarea name="pdf_footer" id="pdf_footer" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['pdf_footer'] ?? ''); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('متن نمایش داده شده در فوتر PDF.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="pdf_title" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('عنوان PDF', 'print-order'); ?></label>
                                            <input type="text" name="pdf_title" id="pdf_title" value="<?php echo esc_attr($options['pdf_title'] ?? 'فاکتور سفارش چاپ'); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('عنوان نمایش داده شده در PDF.', 'print-order'); ?></p>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <label for="pdf_header_color" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('رنگ سربرگ PDF', 'print-order'); ?></label>
                                                <input type="color" name="pdf_header_color" id="pdf_header_color" value="<?php echo esc_attr($options['pdf_header_color'] ?? '#2563EB'); ?>" class="w-24 border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <span id="pdf_header_color_value" class="text-gray-600 text-sm mr-2"><?php echo esc_attr($options['pdf_header_color'] ?? '#2563EB'); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <label for="pdf_table_border_color" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('رنگ حاشیه جدول PDF', 'print-order'); ?></label>
                                                <input type="color" name="pdf_table_border_color" id="pdf_table_border_color" value="<?php echo esc_attr($options['pdf_table_border_color'] ?? '#E5E7EB'); ?>" class="w-24 border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <span id="pdf_table_border_color_value" class="text-gray-600 text-sm mr-2"><?php echo esc_attr($options['pdf_table_border_color'] ?? '#E5E7EB'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات SMS -->
                            <div id="sms" class="tab-pane hidden">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات SMS', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('تنظیمات مربوط به ارسال پیامک را در این بخش وارد کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="sms_api_key" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('کلید API پیامک', 'print-order'); ?></label>
                                            <input type="text" name="sms_api_key" id="sms_api_key" value="<?php echo esc_attr($options['sms_api_key'] ?? ''); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('کلید API سرویس پیامک (مثل کاوه‌نگار).', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="sms_line_number" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('شماره خط پیامک', 'print-order'); ?></label>
                                            <input type="text" name="sms_line_number" id="sms_line_number" value="<?php echo esc_attr($options['sms_line_number'] ?? ''); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('شماره خطی که پیامک از آن ارسال می‌شود.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="sms_welcome_template" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('قالب پیامک خوش‌آمدگویی', 'print-order'); ?></label>
                                            <textarea name="sms_welcome_template" id="sms_welcome_template" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['sms_welcome_template'] ?? 'حساب شما در {site_name} ایجاد شد. نام کاربری: {username}، رمز عبور: {password}'); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('قالب پیامک خوش‌آمدگویی. متغیرها: {site_name}, {username}, {password}.', 'print-order'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات درگاه زرین‌پال -->
                            <div id="zarinpal" class="tab-pane hidden">
                                <div class="mb-12">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-end">
                                        <span><?php _e('تنظیمات درگاه زرین‌پال', 'print-order'); ?></span>
                                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </h2>
                                    <p class="text-gray-600 mb-4 text-right text-sm"><?php _e('تنظیمات درگاه پرداخت زرین‌پال را در این بخش وارد کنید.', 'print-order'); ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-right">
                                                <input type="checkbox" name="zarinpal_enabled" id="zarinpal_enabled" value="1" <?php checked($options['zarinpal_enabled'] ?? 0, 1); ?> class="ml-2">
                                                <?php _e('فعال کردن درگاه زرین‌پال', 'print-order'); ?>
                                            </label>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('برای استفاده از درگاه زرین‌پال این گزینه را فعال کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="zarinpal_merchant_id" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('شناسه تجاری زرین‌پال', 'print-order'); ?></label>
                                            <input type="text" name="zarinpal_merchant_id" id="zarinpal_merchant_id" value="<?php echo esc_attr($options['zarinpal_merchant_id'] ?? ''); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('شناسه تجاری (Merchant ID) دریافتی از زرین‌پال.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-right">
                                                <input type="checkbox" name="zarinpal_sandbox" id="zarinpal_sandbox" value="1" <?php checked($options['zarinpal_sandbox'] ?? 0, 1); ?> class="ml-2">
                                                <?php _e('فعال کردن حالت آزمایشی (سندباکس)', 'print-order'); ?>
                                            </label>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('برای تست پرداخت‌ها از حالت سندباکس زرین‌پال استفاده کنید.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="zarinpal_success_url" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('آدرس بازگشت موفق', 'print-order'); ?></label>
                                            <input type="url" name="zarinpal_success_url" id="zarinpal_success_url" value="<?php echo esc_attr($options['zarinpal_success_url'] ?? $default_success_url); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('آدرس صفحه‌ای که کاربر پس از پرداخت موفق به آن هدایت می‌شود.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="zarinpal_cancel_url" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('آدرس بازگشت ناموفق', 'print-order'); ?></label>
                                            <input type="url" name="zarinpal_cancel_url" id="zarinpal_cancel_url" value="<?php echo esc_attr($options['zarinpal_cancel_url'] ?? $default_cancel_url); ?>" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('آدرس صفحه‌ای که کاربر پس از پرداخت ناموفق به آن هدایت می‌شود.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="zarinpal_success_message" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('پیام پرداخت موفق', 'print-order'); ?></label>
                                            <textarea name="zarinpal_success_message" id="zarinpal_success_message" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['zarinpal_success_message'] ?? 'پرداخت شما با موفقیت انجام شد!'); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('پیامی که پس از پرداخت موفق به کاربر نمایش داده می‌شود.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label for="zarinpal_error_message" class="block text-gray-700 font-medium mb-2 text-right"><?php _e('پیام پرداخت ناموفق', 'print-order'); ?></label>
                                            <textarea name="zarinpal_error_message" id="zarinpal_error_message" rows="4" class="w-full max-w-md border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 text-right"><?php echo esc_textarea($options['zarinpal_error_message'] ?? 'پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.'); ?></textarea>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('پیامی که پس از پرداخت ناموفق به کاربر نمایش داده می‌شود.', 'print-order'); ?></p>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-right">
                                                <input type="checkbox" name="zarinpal_debug_log" id="zarinpal_debug_log" value="1" <?php checked($options['zarinpal_debug_log'] ?? 0, 1); ?> class="ml-2">
                                                <?php _e('فعال کردن لاگ دیباگ', 'print-order'); ?>
                                            </label>
                                            <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('برای ثبت خطاهای پرداخت در لاگ ووکامرس این گزینه را فعال کنید.', 'print-order'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- دکمه ذخیره -->
                        <div class="flex justify-end mt-4">
                            <p class="submit">
                                <input type="submit" name="save_settings" class="btn-primary" value="<?php _e('ذخیره تغییرات', 'print-order'); ?>">
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- جاوااسکریپت مدیریت تب‌ها -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                console.log('Print Order: Settings page loaded');
                const tabLinks = document.querySelectorAll('.tab-link');
                const tabPanes = document.querySelectorAll('.tab-pane');

                tabLinks.forEach(link => {
                    link.addEventListener('click', function () {
                        const tabId = this.getAttribute('data-tab');
                        console.log('Print Order: Switching to tab: ' + tabId);

                        // Remove active class from all tabs and panes
                        tabLinks.forEach(l => l.classList.remove('active', 'bg-gray-100'));
                        tabPanes.forEach(p => p.classList.add('hidden'));

                        // Add active class to clicked tab and show corresponding pane
                        this.classList.add('active', 'bg-gray-100');
                        const activePane = document.getElementById(tabId);
                        if (activePane) {
                            activePane.classList.remove('hidden');
                            console.log('Print Order: Showing pane: ' + tabId);
                        } else {
                            console.error('Print Order: Pane not found for tab: ' + tabId);
                        }
                    });
                });

                // Validate form before submission
                const form = document.getElementById('settings-form');
                form.addEventListener('submit', function (e) {
                    let hasError = false;

                    // Validate URL fields
                    const urlFields = ['form_page_url', 'login_page_url', 'pdf_logo', 'zarinpal_success_url', 'zarinpal_cancel_url'];
                    urlFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        const error = document.getElementById(fieldId + '_error');
                        if (field && field.value && !field.value.match(/^https?:\/\/[^\s$.?#].[^\s]*$/)) {
                            error.classList.remove('hidden');
                            field.classList.add('border-red-500');
                            hasError = true;
                        } else if (error && field) {
                            error.classList.add('hidden');
                            field.classList.remove('border-red-500');
                        }
                    });

                    // Validate number fields
                    const numberFields = [
                        { id: 'design_fee', min: 0, errorId: 'design_fee_error' },
                        { id: 'shipping_fee', min: 0, errorId: 'shipping_fee_error' },
                        { id: 'tax_rate', min: 0, max: 100, errorId: 'tax_rate_error' },
                        { id: 'max_design_revisions', min: 0, errorId: 'max_design_revisions_error' },
                        { id: 'user_edit_request_delay', min: 0, errorId: 'user_edit_request_delay_error' },
                    ];
                    numberFields.forEach(field => {
                        const input = document.getElementById(field.id);
                        const error = document.getElementById(field.errorId);
                        if (input) {
                            const value = parseFloat(input.value);
                            if (isNaN(value) || value < field.min || (field.max && value > field.max)) {
                                error.classList.remove('hidden');
                                input.classList.add('border-red-500');
                                hasError = true;
                            } else {
                                error.classList.add('hidden');
                                input.classList.remove('border-red-500');
                            }
                        }
                    });

                    if (hasError) {
                        e.preventDefault();
                        console.error('Print Order: Form validation failed');
                    }
                });

                // Update button preview
                const buttonPreview = document.getElementById('button_preview');
                const bgColorInput = document.getElementById('button_bg_color');
                const textColorInput = document.getElementById('button_text_color');
                const bgColorValue = document.getElementById('button_bg_color_value');
                const textColorValue = document.getElementById('button_text_color_value');

                function updateButtonPreview() {
                    if (buttonPreview && bgColorInput && textColorInput) {
                        buttonPreview.style.backgroundColor = bgColorInput.value;
                        buttonPreview.style.color = textColorInput.value;
                        bgColorValue.textContent = bgColorInput.value;
                        textColorValue.textContent = textColorInput.value;
                    }
                }

                if (bgColorInput && textColorInput) {
                    bgColorInput.addEventListener('input', updateButtonPreview);
                    textColorInput.addEventListener('input', updateButtonPreview);
                    updateButtonPreview();
                }
            });
        </script>
        <?php
    }
}
?>
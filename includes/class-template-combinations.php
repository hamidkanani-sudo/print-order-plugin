<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Template_Combinations {
    private static $page_rendered = false;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_template_combinations_page'], 11);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_print_order_get_template_shortcode', [$this, 'get_template_shortcode']);
        add_action('wp_ajax_nopriv_print_order_get_template_shortcode', [$this, 'get_template_shortcode']);
        add_action('admin_init', [$this, 'register_default_template_setting']);
    }

    public function register_default_template_setting() {
        register_setting('print-order-template-settings-group', 'print_order_template_options', [
            'sanitize_callback' => [$this, 'sanitize_options'],
        ]);

        add_settings_section(
            'print_order_template_section',
            __('تنظیمات قالب پیش‌فرض', 'print-order'),
            function() {
                echo '<p class="text-gray-600 text-right">' . __('تنظیمات مربوط به قالب‌های المنتور را در این بخش وارد کنید.', 'print-order') . '</p>';
            },
            'print-order-template-combinations-test'
        );

        add_settings_field(
            'default_template_id',
            __('شناسه قالب پیش‌فرض', 'print-order'),
            [$this, 'default_template_id_callback'],
            'print-order-template-combinations-test',
            'print_order_template_section'
        );

        add_settings_field(
            'stage_2_template_id',
            __('قالب مرحله دوم (اطلاعات طرح)', 'print-order'),
            [$this, 'stage_2_template_id_callback'],
            'print-order-template-combinations-test',
            'print_order_template_section'
        );

        add_settings_field(
            'stage_3_shipping_template_id',
            __('قالب مرحله سوم (آدرس ارسال)', 'print-order'),
            [$this, 'stage_3_shipping_template_id_callback'],
            'print-order-template-combinations-test',
            'print_order_template_section'
        );

        add_settings_field(
            'stage_3_payment_template_id',
            __('قالب مرحله چهارم  (پرداخت)', 'print-order'),
            [$this, 'stage_3_payment_template_id_callback'],
            'print-order-template-combinations-test',
            'print_order_template_section'
        );
    }

    public function sanitize_options($options) {
        if (isset($options['default_template_id'])) {
            $options['default_template_id'] = absint($options['default_template_id']);
        }
        if (isset($options['stage_2_template_id'])) {
            $options['stage_2_template_id'] = absint($options['stage_2_template_id']);
        }
        if (isset($options['stage_3_shipping_template_id'])) {
            $options['stage_3_shipping_template_id'] = absint($options['stage_3_shipping_template_id']);
        }
        if (isset($options['stage_3_payment_template_id'])) {
            $options['stage_3_payment_template_id'] = absint($options['stage_3_payment_template_id']);
        }
        return $options;
    }

    public function default_template_id_callback() {
        $options = get_option('print_order_template_options', []);
        $default_template_id = isset($options['default_template_id']) ? absint($options['default_template_id']) : '';
        ?>
        <input type="number" name="print_order_template_options[default_template_id]" value="<?php echo esc_attr($default_template_id); ?>" class="regular-text border p-2 rounded-lg text-right" />
        <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('شناسه قالب المنتور را وارد کنید تا به‌عنوان قالب پیش‌فرض برای ترکیب‌هایی که قالب خاصی ندارند یا قبل از انتخاب جنس کاغذ استفاده شود.', 'print-order'); ?></p>
        <?php
    }

    public function stage_2_template_id_callback() {
        $options = get_option('print_order_template_options', []);
        $stage_2_template_id = isset($options['stage_2_template_id']) ? absint($options['stage_2_template_id']) : '';
        ?>
        <input type="number" name="print_order_template_options[stage_2_template_id]" value="<?php echo esc_attr($stage_2_template_id); ?>" class="regular-text border p-2 rounded-lg text-right" />
        <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('شناسه قالب المنتور را وارد کنید تا در مرحله دوم فرم (اطلاعات طرح) نمایش داده شود.', 'print-order'); ?></p>
        <?php
    }

    public function stage_3_shipping_template_id_callback() {
        $options = get_option('print_order_template_options', []);
        $stage_3_shipping_template_id = isset($options['stage_3_shipping_template_id']) ? absint($options['stage_3_shipping_template_id']) : '';
        ?>
        <input type="number" name="print_order_template_options[stage_3_shipping_template_id]" value="<?php echo esc_attr($stage_3_shipping_template_id); ?>" class="regular-text border p-2 rounded-lg text-right" />
        <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('شناسه قالب المنتور را وارد کنید تا در مرحله سوم فرم (آدرس ارسال) نمایش داده شود.', 'print-order'); ?></p>
        <?php
    }

    public function stage_3_payment_template_id_callback() {
        $options = get_option('print_order_template_options', []);
        $stage_3_payment_template_id = isset($options['stage_3_payment_template_id']) ? absint($options['stage_3_payment_template_id']) : '';
        ?>
        <input type="number" name="print_order_template_options[stage_3_payment_template_id]" value="<?php echo esc_attr($stage_3_payment_template_id); ?>" class="regular-text border p-2 rounded-lg text-right" />
        <p class="description text-gray-600 mt-1 text-right text-sm"><?php _e('شناسه قالب المنتور را وارد کنید تا در مرحله سوم فرم (پرداخت) نمایش داده شود.', 'print-order'); ?></p>
        <?php
    }

    public function add_template_combinations_page() {
        error_log('Print Order: Adding Template Combinations submenu');
        error_log('Print Order: Current user roles: ' . json_encode(wp_get_current_user()->roles));
        $submenu = add_submenu_page(
            'print-order-settings',
            __('قالب راهنما', 'print-order'),
            __('قالب راهنما', 'print-order'),
            'manage_options',
            'print-order-template-combinations-test',
            [$this, 'template_combinations_page_callback']
        );
        error_log('Print Order: Template Combinations submenu added: ' . ($submenu ? $submenu : 'failed'));
    }

    public function enqueue_admin_scripts($hook) {
        // No styles enqueued here, moved to template_combinations_page_callback
        if (strpos($hook, 'print-order-template-combinations-test') === false) {
            error_log('Print Order: Hook does not match, scripts not enqueued: ' . $hook);
            return;
        }

        error_log('Print Order: Enqueuing scripts for template combinations page');
        wp_enqueue_script('jquery');
        wp_enqueue_script('print-order-template-combinations', PRINT_ORDER_URL . 'assets/js/template-combinations.js', ['jquery'], '1.0.8', true);

        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        $category_map = [];
        foreach ($categories as $cat) {
            $category_map[$cat->term_id] = $cat->name;
        }
        global $wpdb;
        $pricing_table = $wpdb->prefix . 'print_order_pricing';
        $paper_types = $wpdb->get_col("SELECT DISTINCT paper_type_persian FROM $pricing_table WHERE paper_type_persian != '' ORDER BY paper_type_persian");

        $localize_data = [
            'categories' => $category_map,
            'paper_types' => $paper_types,
            'i18n' => [
                'category' => __('دسته‌بندی', 'print-order'),
                'paper_type_persian' => __('نوع کاغذ (فارسی)', 'print-order'),
                'shortcode_id' => __('شناسه شورت‌کد', 'print-order'),
                'copy' => __('کپی', 'print-order'),
                'delete' => __('حذف', 'print-order'),
            ],
        ];

        wp_localize_script('print-order-template-combinations', 'printOrderTemplateData', $localize_data);
        error_log('Print Order: wp_localize_script called with data: ' . json_encode($localize_data));
    }

    public function template_combinations_page_callback() {
        if (self::$page_rendered) {
            error_log('Print Order: template_combinations_page_callback already rendered, skipping');
            return;
        }
        self::$page_rendered = true;

        error_log('Print Order: template_combinations_page_callback called');
        if (!current_user_can('manage_options')) {
            error_log('Print Order: User does not have manage_options capability');
            wp_die(__('با عرض پوزش، شما اجازهٔ دسترسی به این برگه را ندارید.', 'print-order'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_template_combinations';

        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        if (!$table_exists) {
            echo '<div class="error"><p>خطا: جدول ترکیب‌های قالب یافت نشد. لطفاً افزونه را غیرفعال و دوباره فعال کنید.</p></div>';
            error_log('Print Order: Template combinations table not found');
            return;
        }

        if (isset($_POST['print_order_template_combinations_nonce']) && wp_verify_nonce($_POST['print_order_template_combinations_nonce'], 'print_order_template_combinations')) {
            $combinations = isset($_POST['combinations']) ? array_values(array_filter($_POST['combinations'], function($item) {
                return !empty($item['category_id']) && !empty($item['paper_type_persian']) && !empty($item['shortcode_id']);
            })) : [];

            $has_duplicate = false;
            $seen_keys = [];
            $duplicates = [];

            foreach ($combinations as $index => $item) {
                $key = $item['category_id'] . '|' . $item['paper_type_persian'];
                if (isset($seen_keys[$key])) {
                    $has_duplicate = true;
                    $category_name = get_term($item['category_id'], 'product_cat')->name ?? 'نامشخص';
                    $duplicates[] = sprintf('دسته‌بندی: %s، جنس کاغذ: %s', $category_name, $item['paper_type_persian']);
                } else {
                    $seen_keys[$key] = true;
                }
            }

            if ($has_duplicate) {
                echo '<div class="error"><p>خطا: ترکیب‌های زیر در فرم فعلی تکراری هستند:<br>' . implode('<br>', $duplicates) . '</p></div>';
                error_log('Print Order: Duplicate combinations found in current form: ' . implode(', ', $duplicates));
            } else {
                $wpdb->query("TRUNCATE TABLE $table_name");
                foreach ($combinations as $item) {
                    $wpdb->insert($table_name, [
                        'category_id' => intval($item['category_id']),
                        'paper_type_persian' => sanitize_text_field($item['paper_type_persian']),
                        'shortcode_id' => intval($item['shortcode_id']),
                    ], ['%d', '%s', '%d']);
                }
                echo '<div class="updated"><p>' . __('ترکیب‌های قالب با موفقیت به‌روزرسانی شدند!', 'print-order') . '</p></div>';
                error_log('Print Order: Template combinations updated successfully');
            }
        }

        $combinations = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        $pricing_table = $wpdb->prefix . 'print_order_pricing';
        $paper_types = $wpdb->get_col("SELECT DISTINCT paper_type_persian FROM $pricing_table WHERE paper_type_persian != '' ORDER BY paper_type_persian");

        // Enqueue the template combinations styles only in this callback
        wp_enqueue_style('class-template-combinations-style', PRINT_ORDER_URL . 'assets/css/class-template-combinations-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-template-combinations.css'));
        wp_enqueue_script('jquery');
        wp_enqueue_script('print-order-template-combinations', PRINT_ORDER_URL . 'assets/js/template-combinations.js', ['jquery'], '1.0.8', true);

        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        $category_map = [];
        foreach ($categories as $cat) {
            $category_map[$cat->term_id] = $cat->name;
        }
        global $wpdb;
        $pricing_table = $wpdb->prefix . 'print_order_pricing';
        $paper_types = $wpdb->get_col("SELECT DISTINCT paper_type_persian FROM $pricing_table WHERE paper_type_persian != '' ORDER BY paper_type_persian");

        $localize_data = [
            'categories' => $category_map,
            'paper_types' => $paper_types,
            'i18n' => [
                'category' => __('دسته‌بندی', 'print-order'),
                'paper_type_persian' => __('نوع کاغذ (فارسی)', 'print-order'),
                'shortcode_id' => __('شناسه شورت‌کد', 'print-order'),
                'copy' => __('کپی', 'print-order'),
                'delete' => __('حذف', 'print-order'),
            ],
        ];

        wp_localize_script('print-order-template-combinations', 'printOrderTemplateData', $localize_data);

        if (empty($categories)) {
            echo '<div class="error"><p>هشدار: هیچ دسته‌بندی محصولی یافت نشد. لطفاً حداقل یک دسته‌بندی در ووکامرس ایجاد کنید.</p></div>';
            error_log('Print Order: No product categories found');
        }
        if (empty($paper_types)) {
            echo '<div class="error"><p>هشدار: هیچ جنس کاغذی در بخش قیمت‌گذاری تعریف نشده است. لطفاً ابتدا جنس کاغذها را در بخش قیمت‌گذاری تعریف کنید.</p></div>';
            error_log('Print Order: No paper types found in pricing table');
        }
        ?>
        <div class="wrap print-order-wrap" id="print-order-wrap">
            <h1 class="text-2xl font-bold mb-6"><?php _e('مدیریت قالب‌های راهنما', 'print-order'); ?></h1>
            
            <!-- Card containing tabs and content -->
            <div class="template-combinations-card bg-white rounded-xl shadow-md p-6">
                <!-- Tabs Navigation -->
                <div class="settings-tabs mb-6 flex border-b border-gray-200">
                    <a href="#tab-combinations" class="tab-link px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-t-lg transition-colors active bg-gray-100 text-blue-600 font-semibold"><?php _e('ترکیب‌ها', 'print-order'); ?></a>
                    <a href="#tab-settings" class="tab-link px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-t-lg transition-colors"><?php _e('تنظیمات قالب راهنما', 'print-order'); ?></a>
                </div>

                <!-- Tab Content -->
                <div id="tab-combinations" class="tab-pane">
                    <form method="post">
                        <?php wp_nonce_field('print_order_template_combinations', 'print_order_template_combinations_nonce'); ?>
                        <div class="table-container overflow-x-auto">
                            <table class="print-order-table w-full table-auto">
                                <thead class="sticky-header bg-blue-50">
                                    <tr>
                                        <th class="px-2 py-3 text-center text-gray-700 font-semibold"><?php _e('دسته‌بندی', 'print-order'); ?></th>
                                        <th class="px-2 py-3 text-center text-gray-700 font-semibold"><?php _e('نوع کاغذ (فارسی)', 'print-order'); ?></th>
                                        <th class="px-2 py-3 text-center text-gray-700 font-semibold"><?php _e('شناسه شورت‌کد', 'print-order'); ?></th>
                                        <th class="px-2 py-3 text-center text-gray-700 font-semibold"><?php _e('عملیات', 'print-order'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="combination-rows">
                                    <?php if (!empty($combinations)) : ?>
                                        <?php foreach ($combinations as $index => $item) : ?>
                                            <tr>
                                                <td data-label="<?php _e('دسته‌بندی', 'print-order'); ?>" class="px-2 py-3">
                                                    <select name="combinations[<?php echo $index; ?>][category_id]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                                        <?php
                                                        foreach ($categories as $cat) :
                                                            $prefix = $cat->parent == 0 ? '+ ' : '';
                                                            $style = $cat->parent == 0 ? 'style="background-color: #f3f4f6;"' : '';
                                                            $selected = $item['category_id'] == $cat->term_id ? 'selected' : '';
                                                            echo '<option value="' . esc_attr($cat->term_id) . '" ' . $style . ' ' . $selected . '>' . esc_html($prefix . $cat->name) . '</option>';
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                </td>
                                                <td data-label="<?php _e('نوع کاغذ (فارسی)', 'print-order'); ?>" class="px-2 py-3">
                                                    <select name="combinations[<?php echo $index; ?>][paper_type_persian]" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                                        <?php foreach ($paper_types as $type) : ?>
                                                            <option value="<?php echo esc_attr($type); ?>" <?php selected($item['paper_type_persian'], $type); ?>>
                                                                <?php echo esc_html($type); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td data-label="<?php _e('شناسه شورت‌کد', 'print-order'); ?>" class="px-2 py-3">
                                                    <input type="number" name="combinations[<?php echo $index; ?>][shortcode_id]" value="<?php echo esc_attr($item['shortcode_id']); ?>" min="1" class="w-full border p-2 rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                                                </td>
                                                <td class="actions flex gap-2 px-2 py-3">
                                                    <button type="button" class="duplicate-row flex w-10 h-10 items-center justify-center bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 hover:scale-105" title="<?php _e('کپی', 'print-order'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                                            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="remove-row flex w-10 h-10 items-center justify-center bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 hover:scale-105" title="<?php _e('حذف', 'print-order'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4"><?php _e('هیچ ترکیب قالبی موجود نیست. برای شروع روی "افزودن ردیف" کلیک کنید.', 'print-order'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="add-row-container flex justify-end mt-4">
                            <button type="button" id="add-combination-row" class="btn-primary inline-flex px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition-all duration-200 hover:scale-105"><?php _e('افزودن ردیف', 'print-order'); ?></button>
                        </div>
                        <p class="submit mt-4">
                            <input type="submit" name="submit" class="save-btn inline-flex px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200 hover:scale-105" value="<?php _e('ذخیره تغییرات', 'print-order'); ?>">
                        </p>
                    </form>
                </div>

                <div id="tab-settings" class="tab-pane hidden">
                    <h2 class="text-xl font-semibold mb-4"><?php _e('تنظیمات قالب راهنما', 'print-order'); ?></h2>
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('print-order-template-settings-group');
                        do_settings_sections('print-order-template-combinations-test');
                        submit_button(__('ذخیره تنظیمات', 'print-order'));
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_template_shortcode() {
        error_log('Print Order: get_template_shortcode called');
        check_ajax_referer('print_order_nonce', 'nonce');

        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $paper_type_persian = isset($_POST['paper_type_persian']) ? sanitize_text_field($_POST['paper_type_persian']) : '';
        error_log('Print Order: get_template_shortcode - category_id = ' . $category_id . ', paper_type_persian = ' . $paper_type_persian);

        $shortcode_content = '';
        $shortcode_id = 0;

        if ($category_id && $paper_type_persian) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'print_order_template_combinations';
            $combination = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT shortcode_id FROM $table_name WHERE category_id = %d AND paper_type_persian = %s",
                    $category_id,
                    $paper_type_persian
                ),
                ARRAY_A
            );
            error_log('Print Order: get_template_shortcode - Query result: ' . print_r($combination, true));

            if ($combination && !empty($combination['shortcode_id'])) {
                $shortcode_id = intval($combination['shortcode_id']);
                error_log('Print Order: get_template_shortcode - Found combination shortcode_id = ' . $shortcode_id);
            }
        }

        if (!$shortcode_id) {
            $options = get_option('print_order_template_options', []);
            $shortcode_id = isset($options['default_template_id']) ? absint($options['default_template_id']) : 0;
            error_log('Print Order: get_template_shortcode - Using default shortcode_id = ' . $shortcode_id);
        }

        if ($shortcode_id) {
            $template = get_post($shortcode_id);
            if ($template && $template->post_type === 'elementor_library' && $template->post_status === 'publish') {
                if (class_exists('\Elementor\Plugin')) {
                    $shortcode_content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($shortcode_id, true);
                    error_log('Print Order: get_template_shortcode - Content length: ' . strlen($shortcode_content));
                    if (empty($shortcode_content)) {
                        error_log('Print Order: get_template_shortcode - Elementor content is empty for ID: ' . $shortcode_id);
                        $shortcode_content = '<p class="text-red-600 text-center">خطا: محتوای قالب المنتور بارگذاری نشد.</p>';
                    }
                } else {
                    error_log('Print Order: get_template_shortcode - Elementor plugin not found');
                    $shortcode_content = '<p class="text-red-600 text-center">خطا: افزونه المنتور فعال نیست.</p>';
                }
            } else {
                error_log('Print Order: get_template_shortcode - Invalid or unpublished template ID: ' . $shortcode_id);
                $shortcode_content = '<p class="text-red-600 text-center">خطا: قالب المنتور یافت نشد یا منتشر نشده است.</p>';
            }
        } else {
            error_log('Print Order: get_template_shortcode - No shortcode_id provided');
            $shortcode_content = '<p class="text-gray-600 text-center">هیچ قالبی برای این ترکیب یا به‌صورت پیش‌فرض تعریف نشده است.</p>';
        }

        wp_send_json_success(['content' => $shortcode_content]);
    }
}
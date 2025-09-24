<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Pricing {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_pricing_page'], 11);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        // Prevent WooCommerce price formatting
        add_filter('woocommerce_price', [$this, 'prevent_price_manipulation'], 999, 1);
        add_filter('raw_woocommerce_price', [$this, 'prevent_price_manipulation'], 999, 1);
        add_filter('woocommerce_currency_converter', '__return_false', 999);
        // Disable wc_price formatting
        add_filter('wc_price', [$this, 'bypass_wc_price'], 999, 4);
    }

    public function prevent_price_manipulation($price) {
        // Log the price before any manipulation
        error_log('Print Order: Price before manipulation: ' . $price);
        // Ensure price is an integer
        $price = floor(floatval($price)); // Convert to float first, then to integer
        error_log('Print Order: Price after ensuring integer: ' . $price);
        return $price;
    }

    public function bypass_wc_price($formatted_price, $price, $args, $raw_price) {
        // Log the raw price
        error_log('Print Order: Raw price in wc_price: ' . $raw_price);
        // Ensure price is an integer
        $price = floor(floatval($raw_price)); // Convert to float first, then to integer
        error_log('Print Order: Price after ensuring integer in wc_price: ' . $price);
        return $price;
    }

    public function add_pricing_page() {
        global $menu, $submenu;

        // Log the current menu state for debugging
        error_log('Print Order: Checking menu for print-order-settings: ' . (isset($menu) ? print_r($menu, true) : 'not set'));
        error_log('Print Order: Checking submenu for print-order-settings: ' . (isset($submenu['print-order-settings']) ? print_r($submenu['print-order-settings'], true) : 'not set'));

        // Add pricing page as submenu
        $page_hook = add_submenu_page(
            'print-order-settings', // Parent slug
            __('قیمت‌گذاری', 'print-order'), // Page title
            __('قیمت‌گذاری', 'print-order'), // Menu title (use translation)
            'manage_options', // Capability
            'print-order-pricing', // Menu slug
            [$this, 'pricing_page_callback'] // Callback function
        );

        if ($page_hook) {
            error_log('Print Order: Added pricing submenu, hook: ' . $page_hook);
        } else {
            error_log('Print Order: Failed to add pricing submenu');
        }
    }

    public function enqueue_admin_scripts($hook) {
        // Log the current hook for debugging
        error_log('Print Order: admin_enqueue_scripts hook fired, current hook: ' . $hook);

        // Check if we're on the pricing page or any admin page for debugging
        $screen = get_current_screen();
        error_log('Print Order: Current screen ID: ' . ($screen ? $screen->id : 'not set'));
    }

    public function pricing_page_callback() {
        error_log('Print Order: pricing_page_callback called');
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'print_order_pricing';

        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        if (!$table_exists) {
            echo '<div class="notice notice-error"><p>خطا: جدول قیمت‌گذاری یافت نشد. لطفاً افزونه را غیرفعال و دوباره فعال کنید.</p></div>';
            return;
        }

        // Save pricing data
        if (isset($_POST['print_order_pricing_nonce']) && wp_verify_nonce($_POST['print_order_pricing_nonce'], 'print_order_pricing')) {
            $pricing = isset($_POST['pricing']) ? array_values(array_filter($_POST['pricing'], function($item) {
                return !empty($item['category_id']) && !empty($item['paper_type']) && !empty($item['paper_type_persian']) && !empty($item['quantity']) && !empty($item['price']);
            })) : [];

            // Get all existing IDs in the table
            $existing_ids = $wpdb->get_col("SELECT id FROM $table_name");
            $submitted_ids = [];

            foreach ($pricing as $item) {
                $price = isset($item['price']) ? str_replace(',', '', $item['price']) : 0; // Remove any commas
                $price = floor(floatval($price)); // Convert to float first, then to integer using floor
                // Log the raw price for debugging
                error_log('Print Order: Saving price - Raw value: ' . $item['price'] . ', Converted value: ' . $price);

                $data = [
                    'category_id' => intval($item['category_id']),
                    'paper_type' => sanitize_text_field($item['paper_type']),
                    'paper_type_persian' => sanitize_text_field($item['paper_type_persian']),
                    'size' => sanitize_text_field($item['size']),
                    'quantity' => intval($item['quantity']),
                    'sides' => sanitize_text_field($item['sides']),
                    'price' => $price,
                    'days' => intval($item['days']),
                ];

                // Check if this is an existing row (has ID)
                if (!empty($item['id']) && in_array($item['id'], $existing_ids)) {
                    // Update existing row
                    $wpdb->update(
                        $table_name,
                        $data,
                        ['id' => intval($item['id'])],
                        ['%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d'],
                        ['%d']
                    );
                    $submitted_ids[] = intval($item['id']);
                    error_log('Print Order: Updated row with ID: ' . $item['id'] . ', Price stored: ' . $price);
                } else {
                    // Insert new row
                    $insert_result = $wpdb->insert($table_name, $data, ['%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d']);
                    if ($insert_result) {
                        $new_id = $wpdb->insert_id;
                        $submitted_ids[] = $new_id;
                        error_log('Print Order: Inserted new row with ID: ' . $new_id . ', Price stored: ' . $price);
                    } else {
                        error_log('Print Order: Failed to insert new row');
                    }
                }

                // Fetch the stored value directly from the database to verify
                $stored_price = $wpdb->get_var($wpdb->prepare("SELECT price FROM $table_name WHERE price = %d ORDER BY id DESC LIMIT 1", $price));
                error_log('Print Order: Stored price in database: ' . $stored_price);
            }

            // Delete rows that were not submitted (i.e., removed by the user)
            $ids_to_delete = array_diff($existing_ids, $submitted_ids);
            if (!empty($ids_to_delete)) {
                $ids_to_delete = array_map('intval', $ids_to_delete);
                $wpdb->query("DELETE FROM $table_name WHERE id IN (" . implode(',', $ids_to_delete) . ")");
                error_log('Print Order: Deleted rows with IDs: ' . implode(',', $ids_to_delete));
            }

            echo '<div class="notice notice-success is-dismissible"><p>' . __('قیمت‌گذاری با موفقیت ذخیره شد!', 'print-order') . '</p></div>';
        }

        // Fetch pricing data from the database
        $pricing = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        // Log the raw prices from the database
        foreach ($pricing as $item) {
            error_log('Print Order: Raw price from database: ' . $item['price']);
        }

        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);

        // Add sample data if table is empty and categories exist
        if (empty($pricing) && !empty($categories)) {
            $wpdb->insert($table_name, [
                'category_id' => $categories[0]->term_id,
                'paper_type' => 'glossy',
                'paper_type_persian' => 'کاغذ براق',
                'size' => '4x8',
                'quantity' => 1000,
                'sides' => 'double',
                'price' => 2200000, // Ensure integer
                'days' => 7,
            ]);
            $pricing = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        }

        if (empty($categories)) {
            echo '<div class="notice notice-warning"><p>هشدار: هیچ دسته‌بندی محصولی یافت نشد. لطفاً حداقل یک دسته‌بندی در ووکامرس ایجاد کنید.</p></div>';
        }

        // Enqueue the pricing styles only in this callback
        wp_enqueue_style('class-pricing-style', PRINT_ORDER_URL . 'assets/css/class-pricing-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-pricing-tw.css'));
        error_log('Print Order: Enqueued class-pricing-tw.css from ' . PRINT_ORDER_URL . 'assets/css/class-pricing-tw.css' . ' in pricing_page_callback');
        $wp_styles = wp_styles();
        if (isset($wp_styles->registered['class-pricing-style'])) {
            error_log('Print Order: class-pricing-style registered successfully in pricing_page_callback');
        } else {
            error_log('Print Order: class-pricing-style failed to register in pricing_page_callback');
        }

        // Prepare data for JavaScript
        $categories_data = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        $category_map = [];
        foreach ($categories_data as $cat) {
            $category_map[$cat->term_id] = $cat->name;
        }
        $i18n_data = [
            'category' => __('دسته‌بندی', 'print-order'),
            'paper_type' => __('نوع کاغذ', 'print-order'),
            'paper_type_persian' => __('نوع کاغذ (فارسی)', 'print-order'),
            'size' => __('اندازه', 'print-order'),
            'quantity' => __('تعداد', 'print-order'),
            'sides' => __('چاپ', 'print-order'),
            'price' => __('قیمت', 'print-order'),
            'days' => __('روز', 'print-order'),
            'single_sided' => __('یک‌رو', 'print-order'),
            'double_sided' => __('دو‌رو', 'print-order'),
            'copy' => __('کپی', 'print-order'),
            'delete' => __('حذف', 'print-order'),
            'no_data' => __('هیچ داده قیمت‌گذاری موجود نیست. برای شروع روی "افزودن ردیف" کلیک کنید.', 'print-order'),
            'no_categories' => __('هیچ دسته‌بندی محصولی یافت نشد. لطفاً حداقل یک دسته‌بندی ایجاد کنید.', 'print-order'),
        ];
        $localized_data = json_encode(['categories' => $category_map, 'i18n' => $i18n_data]);
        ?>
        <div class="wrap">
            <h1><?php _e('مدیریت قیمت‌گذاری', 'print-order'); ?></h1>
            <div class="pricing-card">
                <form method="post">
                    <?php wp_nonce_field('print_order_pricing', 'print_order_pricing_nonce'); ?>
                    <div class="table-container">
                        <table class="wp-list-table widefat striped print-order-table">
                            <thead class="sticky-header">
                                <tr>
                                    <th data-sort="category"><?php _e('دسته‌بندی', 'print-order'); ?></th>
                                    <th data-sort="paper_type"><?php _e('نوع کاغذ', 'print-order'); ?></th>
                                    <th data-sort="paper_type_persian"><?php _e('نوع کاغذ (فارسی)', 'print-order'); ?></th>
                                    <th data-sort="size"><?php _e('اندازه', 'print-order'); ?></th>
                                    <th data-sort="quantity"><?php _e('تعداد', 'print-order'); ?></th>
                                    <th data-sort="sides"><?php _e('چاپ', 'print-order'); ?></th>
                                    <th data-sort="price"><?php _e('قیمت', 'print-order'); ?></th>
                                    <th data-sort="days"><?php _e('روز', 'print-order'); ?></th>
                                    <th><?php _e('عملیات', 'print-order'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="pricing-rows">
                                <?php if (!empty($pricing)) : ?>
                                    <?php foreach ($pricing as $index => $item) : ?>
                                        <tr>
                                            <td data-label="<?php _e('دسته‌بندی', 'print-order'); ?>">
                                                <input type="hidden" name="pricing[<?php echo $index; ?>][id]" value="<?php echo esc_attr($item['id']); ?>">
                                                <select name="pricing[<?php echo $index; ?>][category_id]" class="border p-2 rounded-lg w-full">
                                                    <?php foreach ($categories as $cat) : ?>
                                                        <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($item['category_id'], $cat->term_id); ?>>
                                                            <?php echo esc_html($cat->name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td data-label="<?php _e('نوع کاغذ', 'print-order'); ?>">
                                                <input type="text" name="pricing[<?php echo $index; ?>][paper_type]" value="<?php echo esc_attr($item['paper_type']); ?>" class="border p-2 rounded-lg w-full">
                                            </td>
                                            <td data-label="<?php _e('نوع کاغذ (فارسی)', 'print-order'); ?>">
                                                <input type="text" name="pricing[<?php echo $index; ?>][paper_type_persian]" value="<?php echo esc_attr($item['paper_type_persian']); ?>" class="border p-2 rounded-lg w-full">
                                            </td>
                                            <td data-label="<?php _e('اندازه', 'print-order'); ?>">
                                                <input type="text" name="pricing[<?php echo $index; ?>][size]" value="<?php echo esc_attr($item['size']); ?>" class="border p-2 rounded-lg w-full">
                                            </td>
                                            <td data-label="<?php _e('تعداد', 'print-order'); ?>">
                                                <input type="number" name="pricing[<?php echo $index; ?>][quantity]" value="<?php echo esc_attr($item['quantity']); ?>" min="1" class="border p-2 rounded-lg w-full">
                                            </td>
                                            <td data-label="<?php _e('چاپ', 'print-order'); ?>">
                                                <select name="pricing[<?php echo $index; ?>][sides]" class="border p-2 rounded-lg w-full">
                                                    <option value="single" <?php selected($item['sides'], 'single'); ?>><?php _e('یک‌رو', 'print-order'); ?></option>
                                                    <option value="double" <?php selected($item['sides'], 'double'); ?>><?php _e('دو‌رو', 'print-order'); ?></option>
                                                </select>
                                            </td>
                                            <td data-label="<?php _e('قیمت', 'print-order'); ?>">
                                                <input type="number" name="pricing[<?php echo $index; ?>][price]" value="<?php echo esc_attr($item['price']); ?>" min="0" step="1" class="border p-2 rounded-lg w-full">
                                            </td>
                                            <td data-label="<?php _e('روز', 'print-order'); ?>">
                                                <input type="number" name="pricing[<?php echo $index; ?>][days]" value="<?php echo esc_attr($item['days']); ?>" min="0" class="border p-2 rounded-lg w-full">
                                            </td>
                                            <td class="actions">
                                                <button type="button" class="duplicate-row bg-green-500 text-white p-1 rounded hover:bg-green-600" title="<?php _e('کپی', 'print-order'); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                                        <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="remove-row bg-red-500 text-white p-1 rounded hover:bg-red-600" title="<?php _e('حذف', 'print-order'); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <?php _e('هیچ داده قیمت‌گذاری موجود نیست. برای شروع روی "افزودن ردیف" کلیک کنید.', 'print-order'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="add-row-container">
                        <button type="button" id="add-pricing-row" class="btn-secondary"><?php _e('افزودن ردیف', 'print-order'); ?></button>
                    </div>
                    <hr class="separator">
                    <div class="flex justify-start mt-4">
                        <p class="submit">
                            <input type="submit" name="submit" class="btn-primary" value="<?php _e('ذخیره تغییرات', 'print-order'); ?>">
                        </p>
                    </div>
                </form>
            </div>
            <!-- Define localized data -->
            <script>
                var printOrderPricingData = <?php echo $localized_data; ?>;
            </script>
            <!-- Load pricing.js directly -->
            <script src="<?php echo esc_url(PRINT_ORDER_URL . 'assets/js/pricing.js'); ?>"></script>
        </div>
        <?php
    }
}
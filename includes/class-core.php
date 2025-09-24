<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Core {
    public function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(PRINT_ORDER_PATH . 'print-order.php', [$this, 'activate']);
        register_deactivation_hook(PRINT_ORDER_PATH . 'print-order.php', [$this, 'deactivate']);

        // WooCommerce hooks
        add_action('woocommerce_order_status_changed', [$this, 'on_order_status_changed'], 10, 4);
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', [$this, 'handle_custom_query'], 10, 2);

        // AJAX handlers
        add_action('wp_ajax_print_order_get_data', [$this, 'ajax_get_data']);
        add_action('wp_ajax_nopriv_print_order_get_data', [$this, 'ajax_get_data']);
    }

    public function activate() {
        // Create necessary pages
        $form_page = [
            'post_title' => __('فرم سفارش چاپ', 'print-order'),
            'post_content' => '[print_order_form]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'order-form',
        ];
        $dashboard_page = [
            'post_title' => __('داشبورد مشتری', 'print-order'),
            'post_content' => '[print_order_customer_dashboard]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'my-orders',
        ];
        if (!get_option('print_order_form_page_id')) {
            $form_page_id = wp_insert_post($form_page);
            update_option('print_order_form_page_id', $form_page_id);
        } else {
            $form_page_id = get_option('print_order_form_page_id');
            wp_update_post([
                'ID' => $form_page_id,
                'post_content' => '[print_order_form]',
            ]);
        }
        if (!get_option('print_order_dashboard_page_id')) {
            $dashboard_page_id = wp_insert_post($dashboard_page);
            update_option('print_order_dashboard_page_id', $dashboard_page_id);
        }

        // Set default options
        $default_options = [
            'form_page_url' => home_url('/order-form/'),
            'button_bg_color' => '#2563EB',
            'button_text_color' => '#ffffff',
            'design_fee' => 50000,
            'tax_rate' => 9,
            'shipping_fee' => 0,
            'max_design_revisions' => 3,
            'pdf_logo' => '',
            'pdf_footer' => 'تماس با ما: info@example.com | وب‌سایت: www.example.com',
            'pdf_title' => 'پیش‌فاکتور',
            'pdf_header_color' => '#f3f4f6',
            'pdf_table_border_color' => '#d1d5db',
        ];
        if (!get_option('print_order_options')) {
            update_option('print_order_options', $default_options);
        }
    }

    public function deactivate() {
        // Optional: Clean up options or pages if needed
    }

    public function on_order_status_changed($order_id, $old_status, $new_status, $order) {
        if ($new_status === 'completed') {
            $order->add_order_note(__('سفارش تکمیل شد.', 'print-order'), false);
        }
    }

    public function handle_custom_query($query, $query_vars) {
        if (!empty($query_vars['print_order_category'])) {
            $query['meta_query'][] = [
                'key' => '_print_order_category',
                'value' => $query_vars['print_order_category'],
            ];
        }
        return $query;
    }

    public function ajax_get_data() {
        check_ajax_referer('print_order_nonce', 'nonce');

        $response = ['success' => false];

        // Handle product info request
        if (isset($_POST['product_id'])) {
            $product_id = intval($_POST['product_id']);
            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
                    $response['data'] = [
                        'id' => $product->get_id(),
                        'name' => $product->get_name(),
                        'price' => $product->get_price(),
                        'category' => !empty($categories) ? $categories[0] : '',
                        'image' => wp_get_attachment_url($product->get_image_id()),
                    ];
                    $response['success'] = true;
                } else {
                    $response['message'] = 'محصول یافت نشد';
                }
            } else {
                $response['message'] = 'شناسه محصول نامعتبر است';
            }
        }

        // Handle pricing request
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $paper_type = isset($_POST['paper_type']) ? sanitize_text_field($_POST['paper_type']) : '';
        $size = isset($_POST['size']) ? sanitize_text_field($_POST['size']) : '';
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        $sides = isset($_POST['sides']) ? sanitize_text_field($_POST['sides']) : '';

        $pricing = get_option('print_order_pricing', []);
        $category_fields = get_option('print_order_category_fields', []);

        // Get paper types
        if ($category) {
            $paper_types = array_unique(array_column(array_filter($pricing, function($item) use ($category) {
                return $item['category'] === $category;
            }), 'paper_type_persian'));
            $response['paper_types'] = $paper_types;
            $response['success'] = true;
        }

        // Get sizes
        if ($category && $paper_type) {
            $sizes = array_unique(array_column(array_filter($pricing, function($item) use ($category, $paper_type) {
                return $item['category'] === $category && $item['paper_type_persian'] === $paper_type;
            }), 'size'));
            $response['sizes'] = $sizes;
            $response['success'] = true;
        }

        // Get quantities
        if ($category && $paper_type && $size) {
            $quantities = array_unique(array_column(array_filter($pricing, function($item) use ($category, $paper_type, $size) {
                return $item['category'] === $category && $item['paper_type_persian'] === $paper_type && $item['size'] === $size;
            }), 'quantity'));
            $response['quantities'] = $quantities;
            $response['success'] = true;
        }

        // Get sides
        if ($category && $paper_type && $size && $quantity) {
            $sides_options = array_unique(array_column(array_filter($pricing, function($item) use ($category, $paper_type, $size, $quantity) {
                return $item['category'] === $category && $item['paper_type_persian'] === $paper_type && $item['size'] === $size && $item['quantity'] === $quantity;
            }), 'sides'));
            $response['sides'] = $sides_options;
            $response['success'] = true;
        }

        // Get price
        if ($category && $paper_type && $size && $quantity && $sides) {
            $price = 0;
            foreach ($pricing as $item) {
                if (
                    $item['category'] === $category &&
                    $item['paper_type_persian'] === $paper_type &&
                    $item['size'] === $size &&
                    $item['quantity'] === $quantity &&
                    $item['sides'] === $sides
                ) {
                    $price = $item['price'];
                    break;
                }
            }
            $response['price'] = $price;
            $response['success'] = true;
        }

        // Get extra fields
        if ($category && isset($category_fields[$category])) {
            $response['extra_fields'] = $category_fields[$category];
            $response['success'] = true;
        }

        wp_send_json($response);
    }

    public function convert_persian_to_english($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $english, $string);
    }
}
?>
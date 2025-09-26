<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants for repeated strings
define('UNKNOWN_VALUE', 'نامشخص');
define('ORDER_STATUS_TEXT', 'Order status');

class Print_Order_WooCommerce {
    public function __construct() {
        // Remove WooCommerce add to cart buttons
        add_action('init', [$this, 'remove_add_to_cart_buttons']);
        // Add custom fees and meta to cart
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_custom_fees_to_cart'], 20);
        add_filter('woocommerce_get_item_data', [$this, 'display_custom_meta_in_cart'], 10, 2);
        // Prevent WooCommerce from adding additional taxes
        add_filter('woocommerce_calculate_totals', [$this, 'prevent_additional_taxes'], 20);
        // Register custom order statuses
        add_action('init', [$this, 'print_order_register_custom_statuses']);
        // Modify WooCommerce order statuses
        add_filter('wc_order_statuses', [$this, 'print_order_custom_order_statuses']);
        // Migrate old order statuses to new ones (one-time script)
        add_action('admin_init', [$this, 'migrate_old_order_statuses']);
        // Ensure orders are displayed in admin
        add_filter('woocommerce_admin_order_query_args', [$this, 'ensure_orders_display_in_admin'], 10, 1);
        // Enforce custom status on payment completion
        add_filter('woocommerce_payment_complete_order_status', [$this, 'custom_payment_complete_status'], 10, 0);
        // Track order status changes
        add_action('woocommerce_order_status_changed', [$this, 'track_order_status_changes'], 10, 2);
        // Correct non-standard statuses
        add_action('woocommerce_order_status_changed', [$this, 'correct_non_standard_status'], 1000, 3);
    }

    public function track_order_status_changes($order_id, $new_status) {
        error_log("Print Order: Order $order_id status changed to $new_status");
    }

    public function correct_non_standard_status($order_id, $old_status, $new_status) {
        if ($new_status === 'payment-completed') {
            $order = wc_get_order($order_id);
            $order->set_status('wc-payment-completed', 'وضعیت غیراستاندارد به wc-payment-completed اصلاح شد.');
            $order->save();
            error_log("Print Order: Order $order_id status corrected from payment-completed to wc-payment-completed");
        }
    }

    public function remove_add_to_cart_buttons() {
        // Remove from shop/archive pages
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        // Remove from single product page
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }

    public function add_custom_fees_to_cart($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        error_log('Print Order: add_custom_fees_to_cart called');
        $cart_items = $cart->get_cart();
        foreach ($cart_items as $cart_item) {
            if (isset($cart_item['_print_order_price']) && intval($cart_item['_print_order_price']) > 0) {
                $print_fee = intval($cart_item['_print_order_price']); // Changed from floatval to intval
                $cart->add_fee('هزینه چاپ', $print_fee);
                error_log('Print Order: Added print fee: ' . $print_fee);
            } else {
                error_log('Print Order: No print fee added, _print_order_price not set or zero: ' . (isset($cart_item['_print_order_price']) ? $cart_item['_print_order_price'] : 'not set'));
            }
            if (isset($cart_item['_print_order_design_fee']) && intval($cart_item['_print_order_design_fee']) > 0) {
                $design_fee = intval($cart_item['_print_order_design_fee']); // Changed from floatval to intval
                $cart->add_fee('هزینه طراحی', $design_fee);
                error_log('Print Order: Added design fee: ' . $design_fee);
            }
            if (isset($cart_item['_print_order_shipping_fee']) && intval($cart_item['_print_order_shipping_fee']) > 0) {
                $shipping_fee = intval($cart_item['_print_order_shipping_fee']); // Changed from floatval to intval
                $cart->add_fee('هزینه ارسال', $shipping_fee);
                error_log('Print Order: Added shipping fee: ' . $shipping_fee);
            }
            if (isset($cart_item['_print_order_tax']) && intval($cart_item['_print_order_tax']) > 0) {
                $tax_fee = intval($cart_item['_print_order_tax']); // Changed from floatval to intval
                $cart->add_fee('مالیات', $tax_fee);
                error_log('Print Order: Added tax: ' . $tax_fee);
            }
        }
    }

    public function display_custom_meta_in_cart($item_data, $cart_item) {
        error_log('Print Order: display_custom_meta_in_cart called');
        if (isset($cart_item['_print_order_data'])) {
            $order_data = $cart_item['_print_order_data'];
            $item_data[] = [
                'key' => 'جنس کاغذ',
                'value' => $order_data['paper_type_persian'] ?? UNKNOWN_VALUE,
            ];
            $item_data[] = [
                'key' => 'سایز',
                'value' => $order_data['size'] ?? UNKNOWN_VALUE,
            ];
            $item_data[] = [
                'key' => 'تعداد',
                'value' => $order_data['quantity'] ?? UNKNOWN_VALUE,
            ];
            $item_data[] = [
                'key' => 'نوع چاپ',
                'value' => $order_data['sides'] === 'single' ? 'تک‌رو' : 'دو رو',
            ];
        }
        if (isset($cart_item['_print_order_extra_data'])) {
            foreach ($cart_item['_print_order_extra_data'] as $key => $value) {
                $item_data[] = [
                    'key' => $key,
                    'value' => $value,
                ];
            }
        }
        if (isset($cart_item['_print_order_file']) && $cart_item['_print_order_file']) {
            $item_data[] = [
                'key' => 'فایل طرح',
                'value' => '<a href="' . esc_url($cart_item['_print_order_file']) . '" target="_blank">مشاهده فایل</a>',
            ];
        }
        error_log('Print Order: Cart item data added: ' . json_encode($item_data));
        return $item_data;
    }

    public function prevent_additional_taxes($cart) {
        // Prevent WooCommerce from adding additional taxes if our custom tax is set
        $cart_items = $cart->get_cart();
        foreach ($cart_items as $cart_item) {
            if (isset($cart_item['_print_order_tax']) && intval($cart_item['_print_order_tax']) > 0) {
                $cart->remove_taxes();
                error_log('Print Order: Removed WooCommerce taxes, using custom tax: ' . $cart_item['_print_order_tax']);
                break;
            }
        }
    }

    public function custom_payment_complete_status() {
        return 'wc-payment-completed';
    }

    public function print_order_register_custom_statuses() {
        register_post_status('wc-order-registered', array(
            'label'                     => _x('ثبت سفارش', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('ثبت سفارش <span class="count">(%s)</span>', 'ثبت سفارش <span class="count">(%s)</span>', 'print-order')
        ));

        register_post_status('wc-payment-completed', array(
            'label'                     => _x('تکمیل پرداخت', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('تکمیل پرداخت <span class="count">(%s)</span>', 'تکمیل پرداخت <span class="count">(%s)</span>', 'print-order')
        ));

        register_post_status('wc-designing', array(
            'label'                     => _x('طراحی', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('طراحی <span class="count">(%s)</span>', 'طراحی <span class="count">(%s)</span>', 'print-order')
        ));

        register_post_status('wc-design-approved', array(
            'label'                     => _x('تایید طرح', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('تایید طرح <span class="count">(%s)</span>', 'تایید طرح <span class="count">(%s)</span>', 'print-order')
        ));

        register_post_status('wc-printing', array(
            'label'                     => _x('در حال چاپ', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('در حال چاپ <span class="count">(%s)</span>', 'در حال چاپ <span class="count">(%s)</span>', 'print-order')
        ));

        register_post_status('wc-shipping', array(
            'label'                     => _x('در حال ارسال', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('در حال ارسال <span class="count">(%s)</span>', 'در حال ارسال <span class="count">(%s)</span>', 'print-order')
        ));

        register_post_status('wc-order-completed', array(
            'label'                     => _x('تکمیل سفارش', ORDER_STATUS_TEXT, 'print-order'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('تکمیل سفارش <span class="count">(%s)</span>', 'تکمیل سفارش <span class="count">(%s)</span>', 'print-order')
        ));
    }

    public function print_order_custom_order_statuses($order_statuses) {
        $new_statuses = array(
            'wc-order-registered'   => _x('ثبت سفارش', ORDER_STATUS_TEXT, 'print-order'),
            'wc-payment-completed'  => _x('تکمیل پرداخت', ORDER_STATUS_TEXT, 'print-order'),
            'wc-designing'          => _x('طراحی', ORDER_STATUS_TEXT, 'print-order'),
            'wc-design-approved'    => _x('تایید طرح', ORDER_STATUS_TEXT, 'print-order'),
            'wc-printing'           => _x('در حال چاپ', ORDER_STATUS_TEXT, 'print-order'),
            'wc-shipping'           => _x('در حال ارسال', ORDER_STATUS_TEXT, 'print-order'),
            'wc-order-completed'    => _x('تکمیل سفارش', ORDER_STATUS_TEXT, 'print-order'),
        );

        // تعریف متغیر جدید به‌جای تغییر مستقیم $order_statuses
        $merged_statuses = array_merge($new_statuses, array(
            'wc-cancelled' => _x('لغو شده', ORDER_STATUS_TEXT, 'woocommerce'),
            'wc-refunded'  => _x('بازپرداخت شده', ORDER_STATUS_TEXT, 'woocommerce'),
        ));

        return $merged_statuses;
    }

    public function migrate_old_order_statuses() {
        if (!current_user_can('manage_options') || get_option('print_order_status_migration_done', false)) {
            return;
        }

        global $wpdb;
        $status_mapping = array(
            'wc-pending'        => 'wc-order-registered',
            'wc-processing'     => 'wc-payment-completed',
            'wc-on-hold'        => 'wc-designing',
            'wc-completed'      => 'wc-order-completed',
        );

        foreach ($status_mapping as $old_status => $new_status) {
            $updated = $wpdb->update(
                $wpdb->posts,
                array('post_status' => $new_status),
                array('post_type' => 'shop_order', 'post_status' => $old_status),
                array('%s'),
                array('%s', '%s')
            );
            if ($updated !== false && defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Print Order: Migrated $updated orders from $old_status to $new_status");
            }
        }

        // Mark migration as done
        update_option('print_order_status_migration_done', true);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Order status migration completed');
        }
    }

    public function ensure_orders_display_in_admin($query_args) {
        // Ensure all custom statuses are included in admin order list
        $custom_statuses = array(
            'wc-order-registered',
            'wc-payment-completed',
            'wc-designing',
            'wc-design-approved',
            'wc-printing',
            'wc-shipping',
            'wc-order-completed',
            'wc-cancelled',
            'wc-refunded',
        );

        $query_args['post_status'] = array_merge(
            isset($query_args['post_status']) ? (array) $query_args['post_status'] : array(),
            $custom_statuses
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Modified admin order query args to include custom statuses: ' . json_encode($query_args['post_status']));
        }

        return $query_args;
    }
}
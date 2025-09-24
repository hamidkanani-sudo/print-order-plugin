<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_User_Dashboard_Transactions {
    private $print_order_instance;

    public function __construct() {
        // Register shortcodes
        add_shortcode('print_order_user_transactions', [$this, 'render_transactions_list']);
        add_shortcode('print_order_transaction_details', [$this, 'render_transaction_details']);
        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        // Register AJAX handler
        add_action('wp_ajax_load_transactions_page', [$this, 'load_transactions_page']);
        add_action('wp_ajax_nopriv_load_transactions_page', [$this, 'load_transactions_page']);
        // Initialize print_order instance
        $this->print_order_instance = new Print_Order();
    }

    public function enqueue_scripts() {
        global $wp_query;

        // Check if the page contains the user_transactions widget or shortcode
        $is_user_transactions_page = get_post() && (
            has_shortcode(get_post()->post_content ?? '', 'print_order_user_transactions') ||
            is_elementor_page_with_widget('user_transactions')
        );

        if (!$is_user_transactions_page) {
            return;
        }

        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'print-order-transactions',
            PRINT_ORDER_URL . 'assets/js/transactions.js',
            ['jquery'],
            filemtime(PRINT_ORDER_PATH . 'assets/js/transactions.js'),
            true
        );
        // Localize script for AJAX
        wp_localize_script('print-order-transactions', 'printOrder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('print_order_transactions_nonce'),
        ]);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Enqueued transactions.js for user transactions page');
        }
    }

    public function render_transactions_list($atts, $is_ajax = false) {
        if (!is_user_logged_in()) {
            if ($is_ajax) {
                wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            }
            return '<p>' . esc_html__('لطفاً وارد حساب کاربری خود شوید.', 'print-order') . '</p>';
        }

        $user_id = get_current_user_id();
        $atts = shortcode_atts([
            'per_page' => 10,
        ], $atts);

        // Filter and sort parameters
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date_desc';
        $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
        $posts_per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : intval($atts['per_page']);

        // Log parameters for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('render_transactions_list: user_id=' . $user_id . ', status=' . $status . ', sort=' . $sort . ', paged=' . $paged . ', per_page=' . $posts_per_page);
        }

        // Build query arguments
        $args = [
            'customer_id' => $user_id,
            'limit' => $posts_per_page,
            'paged' => $paged,
            'type' => 'shop_order',
            'return' => 'objects',
        ];

        if ($status) {
            $args['status'] = $status;
        }

        // Sorting
        switch ($sort) {
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'amount_asc':
                $args['meta_key'] = '_order_total';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'amount_desc':
                $args['meta_key'] = '_order_total';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default: // date_desc
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        $orders = wc_get_orders($args);

        // Log query result
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Orders count: ' . count($orders));
        }

        // Get total orders for pagination
        $total_args = [
            'customer_id' => $user_id,
            'type' => 'shop_order',
            'limit' => -1,
            'return' => 'ids',
        ];
        if ($status) {
            $total_args['status'] = $status;
        }
        $total_orders = count(wc_get_orders($total_args));
        $max_pages = ceil($total_orders / $posts_per_page);

        ob_start();
        ?>
        <div class="print-order-user-transactions">
            <h2 class="text-xl font-bold mb-4"><?php _e('تراکنش‌های شما', 'print-order'); ?></h2>

            <!-- فیلتر و مرتب‌سازی -->
            <div class="toolbar">
                <select name="status" class="filter-select" data-filter-type="status">
                    <option value="" <?php selected($status, ''); ?>><?php _e('همه وضعیت‌ها', 'print-order'); ?></option>
                    <option value="completed" <?php selected($status, 'completed'); ?>><?php _e('تکمیل شده', 'print-order'); ?></option>
                    <option value="processing" <?php selected($status, 'processing'); ?>><?php _e('در حال پردازش', 'print-order'); ?></option>
                    <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('در انتظار پرداخت', 'print-order'); ?></option>
                    <option value="on-hold" <?php selected($status, 'on-hold'); ?>><?php _e('در انتظار بررسی', 'print-order'); ?></option>
                </select>
                <select name="sort" class="filter-select" data-filter-type="sort">
                    <option value="date_desc" <?php selected($sort, 'date_desc'); ?>><?php _e('جدیدترین', 'print-order'); ?></option>
                    <option value="date_asc" <?php selected($sort, 'date_asc'); ?>><?php _e('قدیمی‌ترین', 'print-order'); ?></option>
                    <option value="amount_asc" <?php selected($sort, 'amount_asc'); ?>><?php _e('کمترین مبلغ', 'print-order'); ?></option>
                    <option value="amount_desc" <?php selected($sort, 'amount_desc'); ?>><?php _e('بیشترین مبلغ', 'print-order'); ?></option>
                </select>
            </div>

            <!-- جدول تراکنش‌ها -->
            <div class="card">
                <div class="table-container">
                    <div class="loading-overlay">
                        <div class="loading-spinner"></div>
                    </div>
                    <table class="print-order-table">
                        <thead>
                            <tr>
                                <th class="order-number"><?php _e('شماره سفارش', 'print-order'); ?></th>
                                <th class="date"><?php _e('تاریخ ثبت', 'print-order'); ?></th>
                                <th class="product"><?php _e('محصول', 'print-order'); ?></th>
                                <th class="amount"><?php _e('مبلغ', 'print-order'); ?></th>
                                <th class="status"><?php _e('وضعیت', 'print-order'); ?></th>
                                <th class="actions"><?php _e('اقدامات', 'print-order'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders) : ?>
                                <?php foreach ($orders as $order) : ?>
                                    <tr class="main-row" data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                                        <td class="order-number">
                                            <?php
                                            $order_number = esc_html($order->get_order_number());
                                            if (wp_is_mobile()) {
                                                echo '<span class="mobile-label">' . __('شماره سفارش:', 'print-order') . '</span> ' . $order_number;
                                            } else {
                                                echo $order_number;
                                            }
                                            ?>
                                        </td>
                                        <td class="date">
                                            <?php
                                            $date_created = $order->get_date_created();
                                            if ($date_created) {
                                                $date_data = $this->print_order_instance->convert_to_persian_date($date_created);
                                                echo esc_html($date_data['date']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="product">
                                            <?php
                                            $wc_product_id = $order->get_meta('_print_order_wc_product_id');
                                            echo $wc_product_id ? esc_html(get_the_title($wc_product_id)) : '-';
                                            ?>
                                        </td>
                                        <td class="amount">
                                            <?php
                                            $amount = number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order');
                                            if (wp_is_mobile()) {
                                                echo '<span class="mobile-label">' . __('مبلغ:', 'print-order') . '</span> ' . $amount;
                                            } else {
                                                echo $amount;
                                            }
                                            ?>
                                        </td>
                                        <td class="status">
                                            <span class="status-label <?php echo $order->get_status() === 'completed' ? 'text-green-600' : 'text-yellow-600'; ?>">
                                                <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button class="accordion-toggle" data-order-id="<?php echo esc_attr($order->get_id()); ?>"><?php _e('جزئیات', 'print-order'); ?></button>
                                            <?php if ($order->needs_payment()) : ?>
                                                <button class="payment-button" onclick="window.location.href='<?php echo esc_url($order->get_checkout_payment_url()); ?>'"><?php _e('پرداخت', 'print-order'); ?></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <div class="accordion-row hidden" data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                                        <div class="accordion-wrapper" id="accordion-<?php echo esc_attr($order->get_id()); ?>">
                                            <div class="transaction-details">
                                                <button class="close-accordion" title="<?php _e('بستن', 'print-order'); ?>">✕</button>
                                                <div class="accordion-grid">
                                                    <?php
                                                    $date_created = $order->get_date_created();
                                                    $date_data = $date_created ? $this->print_order_instance->convert_to_persian_date($date_created) : ['date' => '-', 'time' => '-'];
                                                    $date_paid = $order->get_date_paid();
                                                    $date_data_paid = $date_paid ? $this->print_order_instance->convert_to_persian_date($date_paid) : ['date' => '-', 'time' => '-'];
                                                    ?>
                                                    <div class="item">
                                                        <span class="label"><?php _e('تاریخ ثبت:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo esc_html($date_data['date']); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('تاریخ پرداخت:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo esc_html($date_data_paid['date']); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('جنس کاغذ:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo esc_html($order->get_meta('_print_order_paper_type') ?: '-'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('سایز:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo esc_html($order->get_meta('_print_order_size') ?: '-'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('تعداد:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo esc_html($order->get_meta('_print_order_quantity') ?: '-'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('چاپ:', 'print-order'); ?></span>
                                                        <span class="value">
                                                            <?php
                                                            $sides = $order->get_meta('_print_order_sides') ?: '-';
                                                            $sides_mapping = [
                                                                'double' => 'دورو',
                                                                'single' => 'یکرو',
                                                            ];
                                                            echo esc_html(isset($sides_mapping[$sides]) ? $sides_mapping[$sides] : $sides);
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('قیمت محصول:', 'print-order'); ?></span>
                                                        <span class="value">
                                                            <?php
                                                            $items = $order->get_items();
                                                            $product_price = 0;
                                                            $wc_product_id = $order->get_meta('_print_order_wc_product_id');
                                                            foreach ($items as $item) {
                                                                if ($item->get_product_id() == $wc_product_id) {
                                                                    $product_price = $item->get_subtotal() / max(1, $item->get_quantity());
                                                                    break;
                                                                }
                                                            }
                                                            echo number_format($product_price) . ' ' . esc_html__('تومان', 'print-order');
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('قیمت چاپ:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo number_format($order->get_meta('_print_order_price') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('هزینه طراحی:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo number_format($order->get_meta('_print_order_design_fee') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('هزینه ارسال:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo number_format($order->get_meta('_print_order_shipping_fee') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('مالیات:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo number_format($order->get_meta('_print_order_tax') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('مبلغ نهایی:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('شماره تراکنش:', 'print-order'); ?></span>
                                                        <span class="value"><?php echo esc_html($order->get_transaction_id() ?: '-'); ?></span>
                                                    </div>
                                                    <div class="item">
                                                        <span class="label"><?php _e('وضعیت پرداخت:', 'print-order'); ?></span>
                                                        <span class="value">
                                                            <?php
                                                            $payment_status = $order->get_meta('_payment_status') ?: $order->get_status();
                                                            $status_mapping = [
                                                                'completed' => 'پرداخت شده',
                                                                'pending' => 'در انتظار پرداخت',
                                                                'failed' => 'ناموفق',
                                                            ];
                                                            echo esc_html(isset($status_mapping[$payment_status]) ? $status_mapping[$payment_status] : $payment_status);
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php _e('هیچ تراکنشی یافت نشد.', 'print-order'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="error-message"></div>
                <!-- صفحه‌بندی -->
                <div class="pagination-container mt-6 flex justify-center" data-status="<?php echo esc_attr($status); ?>" data-sort="<?php echo esc_attr($sort); ?>" data-per-page="<?php echo esc_attr($posts_per_page); ?>">
                    <?php
                    // Custom pagination
                    if ($max_pages > 1) {
                        $current_page = max(1, $paged);
                        echo '<div class="pagination-links">';
                        // Previous link
                        if ($current_page > 1) {
                            echo '<a class="pagination-link" href="#" data-page="' . esc_attr($current_page - 1) . '">' . esc_html__('« قبلی', 'print-order') . '</a>';
                        }
                        // Page numbers
                        for ($i = 1; $i <= $max_pages; $i++) {
                            if ($i == $current_page) {
                                echo '<span class="pagination-link current" data-page="' . esc_attr($i) . '">' . esc_html($i) . '</span>';
                            } else {
                                echo '<a class="pagination-link" href="#" data-page="' . esc_attr($i) . '">' . esc_html($i) . '</a>';
                            }
                        }
                        // Next link
                        if ($current_page < $max_pages) {
                            echo '<a class="pagination-link" href="#" data-page="' . esc_attr($current_page + 1) . '">' . esc_html__('بعدی »', 'print-order') . '</a>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $output = ob_get_clean();

        if ($is_ajax) {
            wp_send_json_success([
                'html' => $output,
                'current_page' => $paged,
                'max_pages' => $max_pages,
            ]);
        }

        return $output;
    }

    public function load_transactions_page() {
        check_ajax_referer('print_order_transactions_nonce', 'nonce');

        // Log AJAX parameters for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('load_transactions_page: POST=' . print_r($_POST, true));
        }

        $atts = ['per_page' => isset($_POST['per_page']) ? intval($_POST['per_page']) : 10];
        $output = $this->render_transactions_list($atts, true);

        // Output is handled within render_transactions_list
        wp_die();
    }

    public function render_transaction_details($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('لطفاً وارد حساب کاربری خود شوید.', 'print-order') . '</p>';
        }

        $atts = shortcode_atts([
            'transaction_id' => isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0,
        ], $atts);

        $order_id = intval($atts['transaction_id']);
        $order = wc_get_order($order_id);

        if (!$order || $order->get_user_id() !== get_current_user_id()) {
            return '<p>' . esc_html__('تراکنش یافت نشد یا دسترسی ندارید.', 'print-order') . '</p>';
        }

        ob_start();
        ?>
        <div class="print-order-transaction-details">
            <h2 class="text-xl font-bold mb-4"><?php printf(esc_html__('جزئیات تراکنش #%s', 'print-order'), esc_html($order->get_order_number())); ?></h2>

            <div class="card mb-6">
                <h3 class="text-lg font-semibold mb-4"><?php _e('اطلاعات تراکنش', 'print-order'); ?></h3>
                <table class="print-order-table w-full">
                    <tbody>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('شماره سفارش:', 'print-order'); ?></td>
                            <td><?php echo esc_html($order->get_order_number()); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('تاریخ ثبت:', 'print-order'); ?></td>
                            <td>
                                <?php
                                $date_created = $order->get_date_created();
                                if ($date_created) {
                                    $date_data = $this->print_order_instance->convert_to_persian_date($date_created);
                                    echo esc_html($date_data['date']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('تاریخ پرداخت:', 'print-order'); ?></td>
                            <td>
                                <?php
                                $date_paid = $order->get_date_paid();
                                if ($date_paid) {
                                    $date_data = $this->print_order_instance->convert_to_persian_date($date_paid);
                                    echo esc_html($date_data['date']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('روش پرداخت:', 'print-order'); ?></td>
                            <td><?php echo esc_html($order->get_payment_method_title() ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('وضعیت:', 'print-order'); ?></td>
                            <td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('مبلغ کل:', 'print-order'); ?></td>
                            <td><?php echo number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card mb-6">
                <h3 class="text-lg font-semibold mb-4"><?php _e('محصولات', 'print-order'); ?></h3>
                <table class="print-order-table w-full">
                    <thead>
                        <tr>
                            <th class="text-right"><?php _e('محصول', 'print-order'); ?></th>
                            <th class="text-right"><?php _e('تعداد', 'print-order'); ?></th>
                            <th class="text-right"><?php _e('قیمت واحد', 'print-order'); ?></th>
                            <th class="text-right"><?php _e('جمع', 'print-order'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->get_items() as $item) : ?>
                            <tr>
                                <td class="p-2"><?php echo esc_html($item->get_name()); ?></td>
                                <td class="p-2"><?php echo esc_html($item->get_quantity()); ?></td>
                                <td class="p-2"><?php echo number_format($item->get_subtotal() / max(1, $item->get_quantity())) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                <td class="p-2"><?php echo number_format($item->get_subtotal()) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card mb-6">
                <h3 class="text-lg font-semibold mb-4"><?php _e('جزئیات چاپ', 'print-order'); ?></h3>
                <table class="print-order-table w-full">
                    <tbody>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('جنس کاغذ:', 'print-order'); ?></td>
                            <td><?php echo esc_html($order->get_meta('_print_order_paper_type') ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('سایز:', 'print-order'); ?></td>
                            <td><?php echo esc_html($order->get_meta('_print_order_size') ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('تعداد:', 'print-order'); ?></td>
                            <td><?php echo esc_html($order->get_meta('_print_order_quantity') ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('چاپ:', 'print-order'); ?></td>
                            <td>
                                <?php
                                $sides = $order->get_meta('_print_order_sides') ?: '-';
                                $sides_mapping = [
                                    'double' => 'دورو',
                                    'single' => 'یکرو',
                                ];
                                echo esc_html(isset($sides_mapping[$sides]) ? $sides_mapping[$sides] : $sides);
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card mb-6">
                <h3 class="text-lg font-semibold mb-4"><?php _e('فایل‌های طرح', 'print-order'); ?></h3>
                <?php
                $file_urls = $order->get_meta('_print_order_files') ?: [];
                if (!empty($file_urls) && is_array($file_urls)) {
                    echo '<ul class="list-disc pr-6 mb-4">';
                    foreach ($file_urls as $file) {
                        $file_name = isset($file['name']) ? sanitize_text_field($file['name']) : basename($file['url']);
                        echo '<li><a href="' . esc_url($file['url']) . '" download class="text-blue-600 hover:underline">' . esc_html($file_name) . '</a></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>' . esc_html__('هیچ فایلی آپلود نشده است.', 'print-order') . '</p>';
                }
                ?>
            </div>

            <div class="card">
                <h3 class="text-lg font-semibold mb-4"><?php _e('هزینه‌ها', 'print-order'); ?></h3>
                <table class="print-order-table w-full">
                    <tbody>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('قیمت محصول:', 'print-order'); ?></td>
                            <td>
                                <?php
                                $items = $order->get_items();
                                $product_price = 0;
                                $wc_product_id = $order->get_meta('_print_order_wc_product_id');
                                foreach ($items as $item) {
                                    if ($item->get_product_id() == $wc_product_id) {
                                        $product_price = $item->get_subtotal() / max(1, $item->get_quantity());
                                        break;
                                    }
                                }
                                echo number_format($product_price) . ' ' . esc_html__('تومان', 'print-order');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('قیمت چاپ:', 'print-order'); ?></td>
                            <td><?php echo number_format($order->get_meta('_print_order_price') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('هزینه طراحی:', 'print-order'); ?></td>
                            <td><?php echo number_format($order->get_meta('_print_order_design_fee') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('هزینه ارسال:', 'print-order'); ?></td>
                            <td><?php echo number_format($order->get_meta('_print_order_shipping_fee') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('مالیات:', 'print-order'); ?></td>
                            <td><?php echo number_format($order->get_meta('_print_order_tax') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-gray-700"><?php _e('مبلغ نهایی:', 'print-order'); ?></td>
                            <td><?php echo number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="<?php echo esc_url(home_url('/transactions')); ?>" class="bg-gray-200 text-gray-800 p-2 rounded-lg hover:bg-gray-300"><?php _e('بازگشت به لیست تراکنش‌ها', 'print-order'); ?></a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Register the class
new Print_Order_User_Dashboard_Transactions();
?>
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Orders {
    public function __construct() {
        // Add orders submenu
        add_action('admin_menu', [$this, 'add_orders_menu']);
        // Enqueue styles and scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        // AJAX handlers
        add_action('wp_ajax_print_order_admin_upload_design', [$this, 'handle_admin_upload_design']);
        add_action('wp_ajax_print_order_admin_respond_revision', [$this, 'handle_admin_respond_revision']);
        // Add mark messages read handler
        add_action('wp_ajax_print_order_mark_messages_read', [$this, 'handle_mark_messages_read']);
        // Add private file download handler
        add_action('wp_ajax_print_order_download_private_file', [$this, 'handle_private_file_download']);
        // Add increase revisions handler
        add_action('wp_ajax_print_order_increase_revisions', [$this, 'handle_increase_revisions']);
    }

    public function add_orders_menu() {
        add_submenu_page(
            'print-order-settings',
            __('سفارشات', 'print-order'),
            __('سفارشات', 'print-order'),
            'manage_options',
            'print-order-orders',
            [$this, 'orders_page']
        );
    }

    public function enqueue_scripts($hook) {
        // Log the hook for debugging
        error_log('Print Order Hook: ' . $hook);

        // Check if we are on the print-order-orders page (list or details)
        if (isset($_GET['page']) && $_GET['page'] === 'print-order-orders') {
            // Enqueue styles
            wp_enqueue_style(
                'class-orders-style',
                PRINT_ORDER_URL . 'assets/css/class-orders-tw.css',
                [],
                filemtime(PRINT_ORDER_PATH . 'assets/css/class-orders-tw.css')
            );
            // Enqueue scripts
            wp_enqueue_script(
                'admin-orders',
                PRINT_ORDER_URL . 'assets/js/admin-orders.js',
                ['jquery'],
                filemtime(PRINT_ORDER_PATH . 'assets/js/admin-orders.js'),
                true
            );
            // Localize script
            $admin_user = get_users(['role__in' => ['administrator'], 'number' => 1])[0] ?? null;
            $admin_display_name = $admin_user ? $admin_user->display_name : 'ادمین';
            wp_localize_script('admin-orders', 'printOrderAdmin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('print_order_admin_nonce'),
                'download_nonce' => wp_create_nonce('download_nonce'),
                'adminDisplayName' => esc_js($admin_display_name),
            ]);
        }
    }

    public function orders_page() {
        if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
            $this->order_details_page();
            return;
        }

        // Handle status update via AJAX
        if (isset($_POST['quick_update_status']) && check_admin_referer('quick_status_nonce')) {
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $new_status = isset($_POST['new_status']) ? sanitize_text_field($_POST['new_status']) : '';
            if ($order_id && !empty($new_status)) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $order->update_status($new_status, __('وضعیت توسط ادمین تغییر کرد.', 'print-order'));
                    $order->save();
                    echo '<div class="updated"><p>' . esc_html__('وضعیت به‌روزرسانی شد!', 'print-order') . '</p></div>';
                }
            }
        }

        // Filter and search parameters
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $posts_per_page = 20;

        echo $this->print_order_admin_orders_list($posts_per_page, $paged, $status, $search);
    }

    public function print_order_admin_orders_list($posts_per_page = 20, $paged = 1, $status = '', $search = '') {
        // Build query arguments
        $args = [
            'limit' => $posts_per_page,
            'paged' => $paged,
            'type' => 'shop_order',
            'return' => 'objects',
        ];
        if ($status) {
            $args['status'] = $status;
        }
        if ($search) {
            $args['s'] = $search;
        }

        $orders_query = new WC_Order_Query($args);
        $orders = $orders_query->get_orders();

        // Get total orders for pagination
        $total_args = [
            'type' => 'shop_order',
            'limit' => -1,
            'return' => 'ids',
        ];
        if ($status) {
            $total_args['status'] = $status;
        }
        if ($search) {
            $total_args['s'] = $search;
        }
        $total_orders = count(wc_get_orders($total_args));
        $max_pages = ceil($total_orders / $posts_per_page);

        ob_start();
        ?>
        <div class="wrap">
            <div class="max-w-full">
                <!-- هدر صفحه -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800"><?php _e('مدیریت سفارشات', 'print-order'); ?></h1>
                    <p class="text-gray-600 mt-2"><?php _e('لیست سفارشات ثبت‌شده را در این بخش مشاهده کنید.', 'print-order'); ?></p>
                </div>

                <!-- فرم فیلتر و جستجو -->
                <form method="get" class="toolbar">
                    <input type="hidden" name="page" value="print-order-orders">
                    <div class="flex items-center gap-4 flex-wrap">
                        <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('جستجوی شماره سفارش یا مشتری', 'print-order'); ?>" class="border p-2 rounded-lg">
                        <select name="status" class="border p-2 rounded-lg">
                            <option value=""><?php _e('همه وضعیت‌ها', 'print-order'); ?></option>
                            <?php
                            $wc_statuses = wc_get_order_statuses();
                            foreach ($wc_statuses as $status_slug => $status_name) {
                                ?>
                                <option value="<?php echo esc_attr(str_replace('wc-', '', $status_slug)); ?>" <?php selected($status, str_replace('wc-', '', $status_slug)); ?>>
                                    <?php echo esc_html($status_name); ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn-primary"><?php _e('فیلتر', 'print-order'); ?></button>
                    </div>
                </form>

                <!-- کارت اصلی -->
                <div class="orders-card">
                    <div class="table-container">
                        <table class="print-order-table">
                            <thead class="sticky-header">
                                <tr>
                                    <th><?php _e('شماره سفارش', 'print-order'); ?></th>
                                    <th><?php _e('مشتری', 'print-order'); ?></th>
                                    <th><?php _e('محصول ووکامرس', 'print-order'); ?></th>
                                    <th><?php _e('وضعیت', 'print-order'); ?></th>
                                    <th><?php _e('تاریخ', 'print-order'); ?></th>
                                    <th><?php _e('قیمت نهایی', 'print-order'); ?></th>
                                    <th><?php _e('فایل طرح', 'print-order'); ?></th>
                                    <th><?php _e('اقدامات', 'print-order'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order) { ?>
                                    <tr>
                                        <td data-label="<?php _e('شماره سفارش', 'print-order'); ?>"><?php echo esc_html($order->get_order_number()); ?></td>
                                        <td data-label="<?php _e('مشتری', 'print-order'); ?>">
                                            <?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?>
                                        </td>
                                        <td data-label="<?php _e('محصول ووکامرس', 'print-order'); ?>">
                                            <?php
                                            $wc_product_id = $order->get_meta('_print_order_wc_product_id');
                                            echo $wc_product_id ? esc_html(get_the_title($wc_product_id)) : '-';
                                            ?>
                                        </td>
                                        <td data-label="<?php _e('وضعیت', 'print-order'); ?>">
                                            <form method="post" class="inline-flex">
                                                <?php wp_nonce_field('quick_status_nonce'); ?>
                                                <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
                                                <select name="new_status" class="border p-1 rounded-lg" onchange="this.form.submit()">
                                                    <?php
                                                    foreach ($wc_statuses as $status_slug => $status_name) {
                                                        $short_status = str_replace('wc-', '', $status_slug);
                                                        ?>
                                                        <option value="<?php echo esc_attr($short_status); ?>" <?php selected($order->get_status(), $short_status); ?>>
                                                            <?php echo esc_html($status_name); ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                                <input type="hidden" name="quick_update_status" value="1">
                                            </form>
                                        </td>
                                        <td data-label="<?php _e('تاریخ', 'print-order'); ?>">
                                            <?php
                                            $date_created = $order->get_date_created();
                                            if ($date_created) {
                                                $date_data = $this->convert_to_persian_date($date_created);
                                                echo esc_html($date_data['date'] ?: '-');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td data-label="<?php _e('قیمت نهایی', 'print-order'); ?>">
                                            <?php echo number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order'); ?>
                                        </td>
                                        <td data-label="<?php _e('فایل طرح', 'print-order'); ?>">
                                            <?php
                                            $file_url = $order->get_meta('_print_order_file');
                                            $file_urls = $order->get_meta('print_order_files') ?: [];
                                            $attachment_ids = $order->get_meta('_print_order_attachment_ids') ?: [];
                                            if ($file_url || (!empty($file_urls) && is_array($file_urls)) || (!empty($attachment_ids) && is_array($attachment_ids))) {
                                                echo '<span class="text-blue-600" title="' . esc_attr__('دارای فایل یا پیوست', 'print-order') . '">📎</span>';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="actions">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=print-order-orders&order_id=' . $order->get_id())); ?>" class="btn-primary"><?php _e('مشاهده جزییات', 'print-order'); ?></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- صفحه‌بندی -->
                    <div class="pagination-container mt-6 flex justify-center">
                        <?php
                        echo paginate_links([
                            'base' => add_query_arg(['paged' => '%#%']),
                            'format' => '',
                            'current' => $paged,
                            'total' => $max_pages,
                            'prev_text' => __('« قبلی', 'print-order'),
                            'next_text' => __('بعدی »', 'print-order'),
                            'type' => 'plain',
                            'add_args' => [
                                'page' => 'print-order-orders',
                                'status' => $status,
                                's' => $search,
                            ],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function order_details_page() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        echo $this->print_order_admin_order_details($order_id);
    }

    public function print_order_admin_order_details($order_id = 0) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return '<div class="error"><p>' . esc_html__('سفارش یافت نشد.', 'print-order') . '</p></div>';
        }

        if (isset($_POST['save_changes']) && check_admin_referer('save_changes_nonce')) {
            if (isset($_POST['admin_note']) && !empty($_POST['admin_note'])) {
                $note = sanitize_textarea_field($_POST['admin_note']);
                wc_add_order_note($order_id, $note, false);
                $order->update_meta_data('_print_order_unread_messages', intval($order->get_meta('_print_order_unread_messages') ?? 0) + 1);
                $order->update_meta_data('_print_order_admin_replied', 'yes');
                $order->save();
            }
            if (isset($_POST['order_status']) && !empty($_POST['order_status'])) {
                $order->update_status(sanitize_text_field($_POST['order_status']), __('وضعیت توسط ادمین تغییر کرد.', 'print-order'));
                $order->save();
            }
        }

        // Calculate revision counts
        $history = $order->get_meta('_print_order_revision_history') ?: [];
        $revision_count = 0;
        foreach ($history as $entry) {
            if (isset($entry['note']) || isset($entry['user_file'])) {
                $revision_count++;
            }
        }
        $max_revisions = get_option('print_order_options')['max_design_revisions'] ?? 3;
        $extra_revisions = intval($order->get_meta('_print_order_extra_revisions') ?? 0);
        $remaining_revisions = max(0, $max_revisions + $extra_revisions - $revision_count);

        ob_start();
        ?>
        <div class="wrap print-order-details">
            <h1 class="text-2xl font-bold text-gray-800 mb-6"><?php printf(esc_html__('جزییات سفارش #%s', 'print-order'), esc_html($order->get_order_number())); ?></h1>

            <!-- کارت محصول ووکامرس -->
            <?php
            $wc_product_id = $order->get_meta('_print_order_wc_product_id');
            if ($wc_product_id) {
                $product = wc_get_product($wc_product_id);
                if ($product) {
                    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($wc_product_id), 'thumbnail');
                    $product_categories = wp_get_post_terms($wc_product_id, 'product_cat', ['fields' => 'names']);
                    ?>
                    <div class="orders-card mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('محصول انتخاب‌شده', 'print-order'); ?></h2>
                        <div class="flex items-center gap-4">
                            <?php if ($product_image) : ?>
                                <img src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="w-24 h-24 object-cover rounded-md">
                            <?php else : ?>
                                <div class="w-24 h-24 bg-gray-200 rounded-md flex items-center justify-center">
                                    <span class="text-gray-500"><?php _e('بدون تصویر', 'print-order'); ?></span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p><strong class="text-gray-700"><?php _e('نام محصول:', 'print-order'); ?></strong> <?php echo esc_html($product->get_name()); ?></p>
                                <p><strong class="text-gray-700"><?php _e('دسته‌بندی:', 'print-order'); ?></strong> <?php echo esc_html(implode(', ', $product_categories) ?: '-'); ?></p>
                                <p><a href="<?php echo esc_url(get_permalink($wc_product_id)); ?>" target="_blank" class="button"><?php _e('مشاهده محصول', 'print-order'); ?></a></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('save_changes_nonce'); ?>
                <!-- تب‌ها -->
                <div class="tabs">
                    <ul class="flex border-b border-gray-300 mb-4">
                        <li class="tab-link active" data-tab="order-info"><?php _e('اطلاعات سفارش', 'print-order'); ?></li>
                        <li class="tab-link" data-tab="address"><?php _e('آدرس', 'print-order'); ?></li>
                        <li class="tab-link" data-tab="design-info"><?php _e('اطلاعات طرح', 'print-order'); ?></li>
                        <li class="tab-link" data-tab="financial"><?php _e('مالی', 'print-order'); ?></li>
                        <li class="tab-link" data-tab="chat"><?php _e('تأیید طرح', 'print-order'); ?><?php
                            $unread_messages = intval($order->get_meta('_print_order_unread_messages') ?? 0);
                            if ($unread_messages > 0) {
                                echo '<span class="unread-count bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs ml-2">' . esc_html($unread_messages) . '</span>';
                            }
                        ?></li>
                    </ul>

                    <!-- تب اطلاعات سفارش -->
                    <div id="order-info" class="tab-content active">
                        <div class="orders-card">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('اطلاعات سفارش', 'print-order'); ?></h2>
                            <table class="print-order-table w-full">
                                <tbody>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('نام مشتری', 'print-order'); ?></td>
                                        <td><?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('شماره موبایل:', 'print-order'); ?></td>
                                        <td>
                                            <span class="copyable"><?php echo esc_html($order->get_billing_phone()); ?></span>
                                            <button type="button" class="copy-btn btn-copy" data-copy="<?php echo esc_attr($order->get_billing_phone()); ?>">📋</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('تاریخ سفارش:', 'print-order'); ?></td>
                                        <td>
                                            <?php 
                                            $date_created = $order->get_date_created();
                                            $date_data = $this->convert_to_persian_date($date_created);
                                            ?>
                                            <div class="flex flex-col">
                                                <span><?php echo esc_html($date_data['date'] ?: '-'); ?></span>
                                                <span class="text-gray-600 text-sm"><?php echo esc_html($date_data['time'] ?: '-'); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('جمع کل سفارش:', 'print-order'); ?></td>
                                        <td><?php echo number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><hr class="separator"></td>
                                    </tr>
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
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('تأیید طرح:', 'print-order'); ?></td>
                                        <td><?php echo $order->get_meta('_print_order_confirm_design') ? esc_html__('بله', 'print-order') : esc_html__('خیر', 'print-order'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تب آدرس -->
                    <div id="address" class="tab-content hidden">
                        <div class="orders-card">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('آدرس', 'print-order'); ?></h2>
                            <?php
                            // تعریف نگاشت استان‌ها
                            $provinces_map = [
                                'THR' => 'تهران',
                                'ABZ' => 'البرز',
                                'ILM' => 'ایلام',
                                'BHR' => 'بوشهر',
                                'ADL' => 'اردبیل',
                                'ESF' => 'اصفهان',
                                'EAZ' => 'آذربایجان شرقی',
                                'WAZ' => 'آذربایحان غربی',
                                'ZAN' => 'زنجان',
                                'SMN' => 'سمنان',
                                'SBL' => 'سیستان و بلوچستان',
                                'FRS' => 'فارس',
                                'QHM' => 'قم',
                                'QZN' => 'قزوین',
                                'GLS' => 'گلستان',
                                'GIL' => 'گیلان',
                                'MZN' => 'مازندران',
                                'MKZ' => 'مرکزی',
                                'HRZ' => 'هرمزگان',
                                'HMD' => 'همدان',
                                'KRD' => 'کردستان',
                                'KRH' => 'کرمانشاه',
                                'KRN' => 'کرمان',
                                'KBD' => 'کهگیلویه و بویراحمد',
                                'KZT' => 'خوزستان',
                                'LRS' => 'لرستان',
                                'KHS' => 'خراسان شمالی',
                                'KJR' => 'خراسان رضوی',
                                'KJF' => 'خراسان جنوبی',
                                'CHB' => 'چهارمحال و بختیاری',
                                'YSD' => 'یزد',
                            ];

                            // بررسی آدرس حمل‌ونقل یا صورت‌حساب
                            $address_label = __('آدرس پروفایل مشتری', 'print-order');
                            $address = '-';
                            $province = '';
                            $city = '';
                            $address_1 = '';
                            $postcode = '';

                            if ($order->get_shipping_address_1()) {
                                $address_label = __('ارسال به آدرس دیگر', 'print-order');
                                $province = $order->get_shipping_state();
                                $city = $order->get_shipping_city();
                                $address_1 = $order->get_shipping_address_1();
                                $postcode = $order->get_shipping_postcode();
                            } else {
                                $user_id = $order->get_user_id();
                                if ($user_id) {
                                    $province = get_user_meta($user_id, 'billing_state', true);
                                    $city = get_user_meta($user_id, 'billing_city', true);
                                    $address_1 = get_user_meta($user_id, 'billing_address_1', true);
                                    $postcode = get_user_meta($user_id, 'billing_postcode', true);
                                }
                            }

                            // تبدیل کد استان به نام فارسی
                            $province_name = !empty($province) && isset($provinces_map[$province]) ? $provinces_map[$province] : $province;

                            // ساخت آدرس با فرمت مورد نظر
                            if (!empty($province_name) || !empty($city) || !empty($address_1) || !empty($postcode)) {
                                $address_parts = [];
                                if ($province_name) {
                                    $address_parts[] = 'استان ' . esc_html($province_name);
                                }
                                if ($city) {
                                    $address_parts[] = 'شهر ' . esc_html($city);
                                }
                                if ($address_1) {
                                    $address_parts[] = esc_html($address_1);
                                }
                                if ($postcode) {
                                    $address_parts[] = 'کدپستی: ' . esc_html($postcode);
                                }
                                $address = implode(' / ', array_filter($address_parts));
                            }
                            ?>
                            <p class="text-gray-700 mb-2"><strong><?php echo esc_html($address_label); ?>:</strong></p>
                            <p><?php echo $address; ?></p>
                        </div>
                    </div>

                    <!-- تب اطلاعات طرح -->
                    <div id="design-info" class="tab-content hidden">
                        <div class="orders-card">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('اطلاعات طرح', 'print-order'); ?></h2>
                            <table class="print-order-table w-full">
                                <tbody>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('اطلاعات چاپ:', 'print-order'); ?></td>
                                        <td>
                                            <span class="copyable"><?php echo esc_html($order->get_meta('_print_order_print_info') ?: '-'); ?></span>
                                            <button type="button" class="copy-btn btn-copy" data-copy="<?php echo esc_attr($order->get_meta('_print_order_print_info') ?: '-'); ?>">📋</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('فایل‌های مشتری:', 'print-order'); ?></td>
                                        <td>
                                            <?php
                                            // نمایش فایل‌های اولیه آپلود شده توسط کاربر
                                            $file_urls = $order->get_meta('print_order_files') ?: [];
                                            $user_files = []; // آرایه برای جمع‌آوری تمام فایل‌های کاربر
                                            if (!empty($file_urls) && is_array($file_urls)) {
                                                foreach ($file_urls as $index => $file) {
                                                    $user_files[] = $file;
                                                }
                                            }

                                            // نمایش فایل‌های آپلود شده توسط کاربر در تاریخچه بازبینی
                                            $history = $order->get_meta('_print_order_revision_history') ?: [];
                                            foreach ($history as $index => $entry) {
                                                if (isset($entry['user_file']) && !empty($entry['user_file'])) {
                                                    $user_files[] = ['url' => $entry['user_file'], 'name' => basename($entry['user_file'])];
                                                }
                                            }

                                            if (!empty($user_files)) {
                                                echo '<div class="uploaded-files flex flex-col gap-2">';
                                                foreach ($user_files as $index => $file) {
                                                    // Use the 'name' field for display if available
                                                    $file_name = isset($file['name']) ? sanitize_file_name($file['name']) : basename($file['url']);
                                                    $format = pathinfo($file_name, PATHINFO_EXTENSION);
                                                    $format = strtolower($format);

                                                    // Truncate file name if needed
                                                    $max_length = 30;
                                                    if (mb_strlen($file_name) > $max_length) {
                                                        $ext = $format;
                                                        $name_without_ext = pathinfo($file_name, PATHINFO_FILENAME);
                                                        $chars_to_show = floor(($max_length - strlen($ext) - 3) / 2);
                                                        $file_name_display = mb_substr($name_without_ext, 0, $chars_to_show) . '...' . 
                                                                             mb_substr($name_without_ext, -$chars_to_show) . '.' . $ext;
                                                    } else {
                                                        $file_name_display = $file_name;
                                                    }

                                                    // Get file size from actual file
                                                    $file_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $file['url']);
                                                    $file_size = file_exists($file_path) ? number_format(filesize($file_path) / 1024 / 1024, 2) : '-';

                                                    // Define icon and format classes
                                                    $icon_map = [
                                                        'psd' => '/wp-content/plugins/print-order/assets/icons/psd.svg',
                                                        'jpg' => '/wp-content/plugins/print-order/assets/icons/jpg.svg',
                                                        'jpeg' => '/wp-content/plugins/print-order/assets/icons/jpeg.svg',
                                                        'pdf' => '/wp-content/plugins/print-order/assets/icons/pdf.svg',
                                                        'png' => '/wp-content/plugins/print-order/assets/icons/png.svg',
                                                        'ai' => '/wp-content/plugins/print-order/assets/icons/ai.svg',
                                                        'eps' => '/wp-content/plugins/print-order/assets/icons/eps.svg',
                                                        'cdr' => '/wp-content/plugins/print-order/assets/icons/cdr.svg',
                                                    ];
                                                    $icon_url = isset($icon_map[$format]) ? $icon_map[$format] : '/wp-content/plugins/print-order/assets/icons/file.svg';

                                                    $class_map = [
                                                        'psd' => 'bg-psd',
                                                        'jpg' => 'bg-jpg',
                                                        'jpeg' => 'bg-jpeg',
                                                        'pdf' => 'bg-pdf',
                                                        'png' => 'bg-png',
                                                        'ai' => 'bg-ai',
                                                        'eps' => 'bg-eps',
                                                        'cdr' => 'bg-cdr',
                                                    ];
                                                    $format_class = isset($class_map[$format]) ? $class_map[$format] : '';

                                                    // Generate secure download URL
                                                    $download_url = wp_nonce_url(
                                                        admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($file['url'])),
                                                        'download_nonce',
                                                        'nonce'
                                                    );
                                                    ?>
                                                    <div class="file-item inline-flex items-center p-2 bg-gray-50 border border-gray-200 rounded-md">
                                                        <div class="icon-wrapper rounded-full p-1 <?php echo esc_attr($format_class); ?>">
                                                            <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($format); ?>" class="w-5 h-5 format-icon <?php echo esc_attr($format); ?>" />
                                                        </div>
                                                        <a href="<?php echo esc_url($download_url); ?>" class="text-xs text-blue-600 hover:underline truncate flex-1 mx-2">
                                                            <?php echo esc_html($file_name_display); ?> (<?php echo esc_html($file_size); ?> مگابایت)
                                                        </a>
                                                    </div>
                                                    <?php
                                                }
                                                echo '</div>';
                                            } else {
                                                echo esc_html__('ندارد', 'print-order');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $extra_data = $order->get_meta('print_order_data');
                                    if (!empty($extra_data) && is_array($extra_data)) {
                                        $category_fields = get_option('print_order_category_fields', []);
                                        $category_id = $order->get_meta('_print_order_category_id') ?: '';
                                        $fields = isset($category_fields[$category_id]) ? $category_fields[$category_id] : [];
                                        foreach ($extra_data as $key => $value) {
                                            // Skip fields that are already displayed elsewhere
                                            if (in_array($key, ['temp_id', 'customer_name', 'customer_lastname', 'customer_email', 'customer_phone', 
                                                                'billing_state', 'billing_city', 'billing_address', 'billing_postcode', 'billing_country',
                                                                'ship_to_different_address', 'shipping_state', 'shipping_country', 'no_print_needed', 'user_id', 'files'])) {
                                                continue;
                                            }
                                            $label = $key;
                                            foreach ($fields as $field) {
                                                if ($field['name'] === $key) {
                                                    $label = $field['label'];
                                                    break;
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td class="font-semibold text-gray-700"><?php echo esc_html($label); ?>:</td>
                                                <td>
                                                    <span class="copyable"><?php echo esc_html($value); ?></span>
                                                    <button type="button" class="copy-btn btn-copy" data-copy="<?php echo esc_attr($value); ?>">📋</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تب مالی -->
                    <div id="financial" class="tab-content hidden">
                        <div class="orders-card">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('اطلاعات مالی', 'print-order'); ?></h2>
                            <table class="print-order-table w-full">
                                <thead>
                                    <tr>
                                        <th class="text-right"><?php _e('مورد', 'print-order'); ?></th>
                                        <th class="text-right"><?php _e('مقدار', 'print-order'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($wc_product_id && $product = wc_get_product($wc_product_id)) : ?>
                                        <tr>
                                            <td class="font-semibold text-gray-700"><?php _e('قیمت محصول:', 'print-order'); ?></td>
                                            <td><?php echo number_format($product->get_price()) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('قیمت چاپ:', 'print-order'); ?></td>
                                        <td><?php echo number_format($order->get_meta('print_price') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('هزینه طراحی:', 'print-order'); ?></td>
                                        <td><?php echo number_format($order->get_meta('design_fee') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('هزینه ارسال:', 'print-order'); ?></td>
                                        <td><?php echo number_format($order->get_meta('shipping_fee') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('مالیات:', 'print-order'); ?></td>
                                        <td><?php echo number_format($order->get_meta('tax_amount') ?: 0) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-gray-700"><?php _e('قیمت نهایی:', 'print-order'); ?></td>
                                        <td><?php echo number_format($order->get_total()) . ' ' . esc_html__('تومان', 'print-order'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تب گفت‌وگو -->
                    <div id="chat" class="tab-content hidden">
                        <div class="orders-card">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('تأیید طرح', 'print-order'); ?></h2>
                            <!-- Revision status -->
                            <div class="revision-status mb-4" style="background-color: #f3f4f6; padding: 1rem; border-radius: 0.5rem;">
                                <p class="text-gray-700"><strong><?php _e('تعداد درخواست‌های ویرایش ثبت‌شده:', 'print-order'); ?></strong> <span id="revision-count"><?php echo esc_html($revision_count); ?></span></p>
                                <p class="text-gray-700"><strong><?php _e('تعداد درخواست‌های باقی‌مانده:', 'print-order'); ?></strong> <span id="remaining-revisions"><?php echo esc_html($remaining_revisions); ?></span></p>
                                <button type="button" class="btn-primary mt-2 increase-revisions-btn" data-order-id="<?php echo esc_attr($order_id); ?>"><?php _e('افزایش تعداد درخواست‌های مجاز', 'print-order'); ?></button>
                            </div>
                            <div class="chat-container" data-order-id="<?php echo esc_attr($order_id); ?>">
                                <?php
                                $user = wp_get_current_user();
                                $user_display_name = $user->display_name;
                                $history = $order->get_meta('_print_order_revision_history') ?: [];
                                $design_file = $order->get_meta('_print_order_design_file');
                                $default_message = get_option('print_order_options')['admin_order_default_message'] ?? 'سفارش شما ثبت شد. منتظر تأیید نهایی باشید.';
                                $default_message = str_replace(['%s', '%d'], '', $default_message);
                                $default_message = esc_html($default_message);

                                // نمایش پیام فایل طراحی اولیه اگر وجود داشته باشد
                                if ($design_file) {
                                    echo '<div class="chat-message admin initial">';
                                    echo '<span>' . esc_html__('ادمین') . ': ' . __('فایل طراحی اولیه آپلود شد.', 'print-order') . '</span>';
                                    echo '<span class="chat-time">' . esc_html($this->convert_to_persian_date($order->get_date_created())['date']) . '</span>';
                                    $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($design_file)), 'download_nonce', 'nonce');
                                    echo '<img src="' . esc_url(str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, $design_file)) . '" alt="Design Thumbnail" class="chat-thumbnail">';
                                    echo '<a href="' . esc_url($download_url) . '" class="chat-download">' . __('دانلود', 'print-order') . '</a>';
                                    echo '</div>';
                                }

                                // نمایش پیام پیش‌فرض سیستم
                                echo '<div class="chat-message admin system">';
                                echo '<span>' . esc_html($user_display_name) . ': ' . $default_message . '</span>';
                                echo '<span class="chat-time">' . esc_html($this->convert_to_persian_date($order->get_date_created())['date']) . '</span>';
                                echo '</div>';

                                // نمایش تاریخچه درخواست‌های ویرایش
                                foreach ($history as $index => $entry) {
                                    if (isset($entry['note'])) {
                                        echo '<div class="chat-message user">';
                                        echo '<span>' . esc_html($user_display_name) . ': ' . esc_html($entry['note']) . '</span>';
                                        echo '<span class="chat-time">' . esc_html($this->convert_to_persian_date($entry['date'])['date']) . '</span>';
                                        if (isset($entry['user_file']) && !empty($entry['user_file'])) {
                                            $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($entry['user_file']) . '&index=' . $index), 'download_nonce', 'nonce');
                                            $extension = strtolower(pathinfo($entry['user_file'], PATHINFO_EXTENSION));
                                            $icon_map = [
                                                'psd' => '/wp-content/plugins/print-order/assets/icons/psd.svg',
                                                'jpg' => '/wp-content/plugins/print-order/assets/icons/jpg.svg',
                                                'jpeg' => '/wp-content/plugins/print-order/assets/icons/jpeg.svg',
                                                'pdf' => '/wp-content/plugins/print-order/assets/icons/pdf.svg',
                                                'png' => '/wp-content/plugins/print-order/assets/icons/png.svg',
                                                'ai' => '/wp-content/plugins/print-order/assets/icons/ai.svg',
                                                'eps' => '/wp-content/plugins/print-order/assets/icons/eps.svg',
                                                'cdr' => '/wp-content/plugins/print-order/assets/icons/cdr.svg',
                                            ];
                                            $icon_url = isset($icon_map[$extension]) ? $icon_map[$extension] : '/wp-content/plugins/print-order/assets/icons/file.svg';
                                            echo '<div class="file-container">';
                                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                                                echo '<img src="' . esc_url(str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, $entry['user_file'])) . '" alt="User File Thumbnail" class="chat-thumbnail">';
                                            }
                                            echo '<div class="file-info">';
                                            echo '<img src="' . esc_url($icon_url) . '" alt="File Icon" class="file-icon">';
                                            echo '<a href="' . esc_url($download_url) . '" class="chat-download">' . __('دانلود', 'print-order') . '</a>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    if (isset($entry['admin_response'])) {
                                        echo '<div class="chat-message admin">';
                                        echo '<span>' . esc_html__('ادمین') . ': ' . esc_html($entry['admin_response']) . '</span>';
                                        echo '<span class="chat-time">' . esc_html($this->convert_to_persian_date($entry['response_date'])['date']) . '</span>';
                                        if (isset($entry['new_design_file'])) {
                                            $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=print_order_download_private_file&order_id=' . $order_id . '&file=' . urlencode($entry['new_design_file']) . '&index=' . $index), 'download_nonce', 'nonce');
                                            echo '<img src="' . esc_url(str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, $entry['new_design_file'])) . '" alt="Design Thumbnail" class="chat-thumbnail">';
                                            echo '<a href="' . esc_url($download_url) . '" class="chat-download">' . __('دانلود', 'print-order') . '</a>';
                                        }
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="revision-form mt-4">
                                <label for="design_file" class="block text-gray-700 mb-2"><?php _e('آپلود فایل طراحی جدید:', 'print-order'); ?></label>
                                <input type="file" id="design_file" name="design_file" accept=".jpg,.jpeg,.png,.pdf" class="border p-2 rounded-lg w-full mb-2">
                                <progress id="upload_progress" value="0" max="100" class="w-full mb-2 hidden"></progress>
                                <textarea name="admin_response" placeholder="<?php esc_attr_e('پاسخ خود را به درخواست کاربر بنویسید...', 'print-order'); ?>" class="w-full rounded-lg border-gray-300 p-2" rows="3"></textarea>
                                <button type="button" class="btn-primary mt-2 upload-design-btn" data-order-id="<?php echo esc_attr($order_id); ?>"><?php _e('ارسال فایل و پاسخ', 'print-order'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- کارت اقدامات -->
                <div class="orders-card">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('اقدامات', 'print-order'); ?></h2>
                    <div class="flex flex-wrap gap-4 items-center">
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $order->get_id() . '&action=edit')); ?>" class="button button-primary"><?php echo esc_html__('ویرایش در ووکامرس', 'print-order'); ?></a>
                        <a href="<?php echo esc_url(home_url('/my-orders')); ?>" target="_blank" class="button"><?php echo esc_html__('مشاهده داشبورد مشتری', 'print-order'); ?></a>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_order_generate_pdf&order_id=' . $order->get_id()), 'pdf_nonce')); ?>" class="button button-primary"><?php echo esc_html__('دانلود PDF', 'print-order'); ?></a>
                        <select name="order_status" class="border p-2 rounded-lg">
                            <?php
                            $wc_statuses = wc_get_order_statuses();
                            foreach ($wc_statuses as $status_slug => $status_name) {
                                $short_status = str_replace('wc-', '', $status_slug);
                                ?>
                                <option value="<?php echo esc_attr($short_status); ?>" <?php selected($order->get_status(), $short_status); ?>>
                                    <?php echo esc_html($status_name); ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-4 flex-wrap gap-2 mt-6">
                    <input type="submit" name="save_changes" class="button button-primary" value="<?php esc_attr_e('ذخیره تغییرات', 'print-order'); ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=print-order-orders')); ?>" class="button" style="background-color: #f3f4f6; color: #333;"><?php esc_html_e('بازگشت به سفارشات', 'print-order'); ?></a>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_admin_upload_design() {
        check_ajax_referer('print_order_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $response_text = isset($_POST['response_text']) ? sanitize_textarea_field($_POST['response_text']) : '';
        $order = wc_get_order($order_id);

        if (!$order) {
            error_log('Print Order: Order not found for ID ' . $order_id);
            wp_send_json_error(['message' => __('سفارش یافت نشد.', 'print-order')]);
            wp_die();
        }

        $new_design_file = '';
        if (isset($_FILES['design_file']) && $_FILES['design_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['design_file'];
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($file['type'], $allowed_types)) {
                error_log('Print Order: Invalid file type for upload: ' . $file['type']);
                wp_send_json_error(['message' => __('نوع فایل مجاز نیست. فقط JPEG، PNG و PDF پشتیبانی می‌شود.', 'print-order')]);
                wp_die();
            }

            // Create private uploads directory with Persian date and order ID
            $persian_date = $this->convert_to_persian_date(current_time('Y-m-d H:i:s'));
            $date_str = str_replace('/', '', $this->persian_to_english_digits($persian_date['date']));
            $private_upload_dir = WP_CONTENT_DIR . '/uploads/private/' . $date_str . '-' . $order_id . '/';
            if (!file_exists($private_upload_dir)) {
                wp_mkdir_p($private_upload_dir);
            }

            // Generate unique filename to avoid conflicts
            $filename = wp_unique_filename($private_upload_dir, $file['name']);
            $destination = $private_upload_dir . $filename;

            // Move uploaded file to private directory
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $new_design_file = $destination;
                $order->update_meta_data('_print_order_design_file', $new_design_file);
            } else {
                error_log('Print Order: Failed to move uploaded file to ' . $destination);
                wp_send_json_error(['message' => __('خطا در آپلود فایل.', 'print-order')]);
                wp_die();
            }
        }

        if (empty($response_text) && empty($new_design_file)) {
            wp_send_json_error(['message' => __('پاسخ یا فایل نمی‌تواند خالی باشد.', 'print-order')]);
            wp_die();
        }

        $history = $order->get_meta('_print_order_revision_history') ?: [];
        $new_entry = [
            'admin_response' => $response_text,
            'response_date' => current_time('Y-m-d H:i:s'),
        ];
        if ($new_design_file) {
            $new_entry['new_design_file'] = $new_design_file;
        }
        $history[] = $new_entry;
        $order->update_meta_data('_print_order_revision_history', $history);
        $order->update_meta_data('_print_order_admin_replied', 'yes');
        $order->update_meta_data('_print_order_unread_messages', intval($order->get_meta('_print_order_unread_messages') ?? 0) + 1);
        $order->add_order_note(__('پاسخ ادمین: ', 'print-order') . $response_text . ($new_design_file ? ' (فایل جدید آپلود شد)' : ''), true);
        $order->save();

        wp_send_json_success([
            'message' => __('پاسخ و فایل با موفقیت ثبت شد.', 'print-order'),
            'new_design_file' => $new_design_file ? str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, $new_design_file) : '',
            'response_text' => $response_text,
            'response_date' => $this->convert_to_persian_date(current_time('Y-m-d H:i:s'))['date'],
        ]);
        wp_die();
    }

    public function handle_admin_respond_revision() {
        check_ajax_referer('print_order_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $response_text = isset($_POST['response_text']) ? sanitize_textarea_field($_POST['response_text']) : '';
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(['message' => __('سفارش یافت نشد.', 'print-order')]);
            wp_die();
        }

        if (empty($response_text)) {
            wp_send_json_error(['message' => __('پاسخ نمی‌تواند خالی باشد.', 'print-order')]);
            wp_die();
        }

        $history = $order->get_meta('_print_order_revision_history') ?: [];
        $new_entry = [
            'admin_response' => $response_text,
            'response_date' => current_time('Y-m-d H:i:s'),
        ];
        $history[] = $new_entry;
        $order->update_meta_data('_print_order_revision_history', $history);
        $order->update_meta_data('_print_order_admin_replied', 'yes');
        $order->update_meta_data('_print_order_unread_messages', intval($order->get_meta('_print_order_unread_messages') ?? 0) + 1);
        $order->add_order_note(__('پاسخ ادمین: ', 'print-order') . $response_text, true);
        $order->save();

        wp_send_json_success([
            'message' => __('پاسخ با موفقیت ثبت شد.', 'print-order'),
            'response_text' => $response_text,
            'response_date' => $this->convert_to_persian_date(current_time('Y-m-d H:i:s'))['date'],
        ]);
        wp_die();
    }

    public function handle_mark_messages_read() {
        check_ajax_referer('print_order_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(['message' => __('سفارش یافت نشد.', 'print-order')]);
            wp_die();
        }

        $order->update_meta_data('_print_order_unread_messages', 0);
        $order->save();
        wp_send_json_success(['message' => __('پیام‌ها به‌عنوان خوانده‌شده علامت‌گذاری شدند.', 'print-order')]);
        wp_die();
    }

    public function handle_increase_revisions() {
        check_ajax_referer('print_order_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز.', 'print-order')]);
            wp_die();
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(['message' => __('سفارش یافت نشد.', 'print-order')]);
            wp_die();
        }

        $extra_revisions = intval($order->get_meta('_print_order_extra_revisions') ?? 0);
        $extra_revisions += 1;
        $order->update_meta_data('_print_order_extra_revisions', $extra_revisions);
        $order->save();

        // Recalculate remaining revisions
        $history = $order->get_meta('_print_order_revision_history') ?: [];
        $revision_count = 0;
        foreach ($history as $entry) {
            if (isset($entry['note']) || isset($entry['user_file'])) {
                $revision_count++;
            }
        }
        $max_revisions = get_option('print_order_options')['max_design_revisions'] ?? 3;
        $remaining_revisions = max(0, $max_revisions + $extra_revisions - $revision_count);

        wp_send_json_success([
            'message' => __('تعداد درخواست‌های مجاز با موفقیت افزایش یافت.', 'print-order'),
            'remaining_revisions' => $remaining_revisions,
        ]);
        wp_die();
    }

    public function handle_private_file_download() {
        check_ajax_referer('download_nonce', 'nonce');
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $file_url = isset($_GET['file']) ? urldecode($_GET['file']) : '';
        $index = isset($_GET['index']) ? intval($_GET['index']) : -1;

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(__('سفارش یافت نشد.', 'print-order'));
        }

        $current_user = wp_get_current_user();
        if (!$current_user->exists() || ($current_user->ID != $order->get_user_id() && !current_user_can('manage_woocommerce'))) {
            wp_die(__('دسترسی غیرمجاز.', 'print-order'));
        }

        if (empty($file_url)) {
            wp_die(__('فایل مشخص نشده است.', 'print-order'));
        }

        // Convert URL to file path
        $file_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $file_url);

        if (!file_exists($file_path)) {
            wp_die(__('فایل یافت نشد.', 'print-order'));
        }

        // Verify file belongs to the order
        $history = $order->get_meta('_print_order_revision_history') ?: [];
        $design_file = $order->get_meta('_print_order_design_file');
        $file_urls = $order->get_meta('print_order_files') ?: [];
        $file_valid = false;

        // Check if file is in _print_order_design_file
        if ($file_path === $design_file) {
            $file_valid = true;
        }

        // Check if file is in _print_order_revision_history
        if ($index >= 0 && isset($history[$index])) {
            if ((isset($history[$index]['new_design_file']) && $file_path === $history[$index]['new_design_file']) ||
                (isset($history[$index]['user_file']) && $file_path === $history[$index]['user_file'])) {
                $file_valid = true;
            }
        }

        // Check if file is in print_order_files
        foreach ($file_urls as $file) {
            if (isset($file['url']) && $file_url === $file['url']) {
                $file_valid = true;
                break;
            }
        }

        if (!$file_valid) {
            wp_die(__('فایل نامعتبر است.', 'print-order'));
        }

        $file_name = basename($file_path);
        header('Content-Type: ' . mime_content_type($file_path));
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }

    private function convert_to_persian_date($datetime) {
        if (!$datetime) {
            return ['date' => '-', 'time' => '-'];
        }

        if (!extension_loaded('intl')) {
            return ['date' => 'افزونه intl غیرفعال است', 'time' => ''];
        }

        $date = new DateTime($datetime);
        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/MM/dd'
        );
        $persian_date = $formatter->format($date);
        $time = $date->format('H:i:s');

        return ['date' => $persian_date, 'time' => $time];
    }

    private function persian_to_english_digits($string) {
        $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian_digits, $english_digits, $string);
    }
}
?>
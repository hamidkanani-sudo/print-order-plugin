<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Shortcodes {
    public function __construct() {
        // Register shortcodes
        add_shortcode('print_order_customer_dashboard', [$this, 'customer_dashboard_shortcode']);
        add_shortcode('print_order_user_orders', [$this, 'user_orders_shortcode']);
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        // Log page content and query vars for debugging
        error_log('Page content for shortcode check: ' . (get_post()->post_content ?? 'No content'));
        error_log('Current page ID: ' . get_the_ID());
        global $wp_query;
        error_log('Query vars: ' . print_r($wp_query->query_vars, true));

        // Enqueue scripts and styles for shortcodes
        if (has_shortcode(get_post()->post_content ?? '', 'print_order_customer_dashboard') || 
            has_shortcode(get_post()->post_content ?? '', 'print_order_user_orders')) {
            wp_enqueue_style('class-shortcodes-style', PRINT_ORDER_URL . 'assets/css/class-shortcodes-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-shortcodes.css'));
            wp_enqueue_script('print-order-react', includes_url('js/dist/vendor/react.min.js'), [], '18.3.1', true);
            wp_enqueue_script('print-order-react-dom', includes_url('js/dist/vendor/react-dom.min.js'), ['print-order-react'], '18.3.1', true);
            error_log('Enqueued common scripts and styles for shortcodes');
        }

        // Load user-orders.js and styles for print_order_user_orders only on order-details page
        $current_slug = get_post_field('post_name', get_the_ID());
        if ($current_slug === 'order-details' || has_shortcode(get_post()->post_content ?? '', 'print_order_user_orders')) {
            $script_handle = 'print-order-user-orders';
            wp_enqueue_script($script_handle, PRINT_ORDER_URL . 'assets/js/user-orders.js', ['print-order-react', 'print-order-react-dom', 'jquery'], filemtime(PRINT_ORDER_PATH . 'assets/js/user-orders.js'), true);
            error_log('Enqueuing print-order-user-orders.js with dependencies: print-order-react, print-order-react-dom, jquery');

            // Verify if script is registered before localizing
            if (wp_script_is($script_handle, 'enqueued')) {
                $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
                $order = $order_id ? wc_get_order($order_id) : null;
                $design_confirmed = $order ? ($order->get_meta('_print_order_design_confirmed') === 'yes' ? 'yes' : 'no') : 'no';
                $unread_messages = $order ? intval($order->get_meta('_print_order_unread_messages') ?? 0) : 0;

                wp_localize_script($script_handle, 'printOrderUser', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('print_order_nonce'),
                    'status_options' => [
                        'pending' => __('ثبت', 'print-order'),
                        'processing' => __('پرداخت', 'print-order'),
                        'on-hold' => __('تأیید', 'print-order'),
                        'completed' => __('چاپ', 'print-order'),
                        'shipping' => __('ارسال', 'print-order'),
                        'delivered' => __('تحویل‌شده', 'print-order'),
                    ],
                    'design_confirmed' => $design_confirmed,
                    'unread_messages' => $unread_messages,
                    'order_id' => $order_id,
                ]);
                error_log('Localized printOrderUser for print-order-user-orders.js');
            } else {
                error_log('Error: print-order-user-orders.js not enqueued properly');
            }
        }
    }

    public function customer_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p class="text-red-600">' . __('لطفاً برای مشاهده داشبورد وارد شوید.', 'print-order') . '</p>';
        }

        $user_id = get_current_user_id();
        $orders = wc_get_orders(['customer' => $user_id, 'limit' => -1]);
        $options = get_option('print_order_options', []);
        $max_revisions = intval($options['max_design_revisions'] ?? 3);

        // Handle revision request submission
        if (isset($_POST['submit_revision']) && check_admin_referer('revision_nonce')) {
            $order_id = intval($_POST['order_id']);
            $revision_note = sanitize_textarea_field($_POST['revision_note']);
            $order = wc_get_order($order_id);

            if ($order && $order->get_customer_id() === $user_id) {
                $revisions = intval($order->get_meta('_print_order_design_revisions') ?? 0);
                if ($revisions < $max_revisions) {
                    $order->update_meta_data('_print_order_design_revisions', $revisions + 1);
                    $order->add_order_note('درخواست اصلاح طرح: ' . $revision_note, 1);
                    $order->save();
                    echo '<div class="updated p-4 bg-green-100 text-green-700 rounded-md mb-6">درخواست اصلاح با موفقیت ثبت شد!</div>';
                } else {
                    echo '<div class="error p-4 bg-red-100 text-red-700 rounded-md mb-6">شما به حداکثر تعداد اصلاحات مجاز رسیده‌اید.</div>';
                }
            }
        }

        ob_start();
        ?>
        <div class="print-order-customer-dashboard max-w-4xl mx-auto p-6 rtl">
            <h1 class="text-2xl font-bold mb-6"><?php _e('My Orders', 'print-order'); ?></h1>
            <?php if (empty($orders)) : ?>
                <p class="text-gray-500"><?php _e('You have no orders yet.', 'print-order'); ?></p>
            <?php else : ?>
                <div class="orders-grid space-y-6">
                    <?php foreach ($orders as $order) :
                        $design_file = $order->get_meta('_print_order_design_file');
                        $revisions = intval($order->get_meta('_print_order_design_revisions') ?? 0);
                        ?>
                        <div class="order-card bg-white p-6 rounded-lg shadow-md">
                            <h2 class="text-lg font-semibold mb-4"><?php _e('Order #', 'print-order'); ?><?php echo $order->get_order_number(); ?></h2>
                            <p><strong><?php _e('Product:', 'print-order'); ?></strong> <?php echo get_the_title($order->get_meta('_print_order_wc_product_id')); ?></p>
                            <p><strong><?php _e('Status:', 'print-order'); ?></strong> <?php echo wc_get_order_status_name($order->get_status()); ?></p>
                            <p><strong><?php _e('Date:', 'print-order'); ?></strong> <?php echo $order->get_date_created()->date('Y-m-d'); ?></p>
                            <p><strong><?php _e('Total:', 'print-order'); ?></strong> <?php echo number_format($order->get_total()); ?> تومان</p>

                            <p class="mt-4">
                                <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=print_order_generate_pdf&order_id=' . $order->get_id()), 'pdf_nonce'); ?>" class="bg-green-500 text-white py-2 px-4 rounded-full hover:bg-green-600 transition-all"><?php _e('Download Invoice', 'print-order'); ?></a>
                            </p>

                            <?php if ($design_file) : ?>
                                <div class="design-preview mt-4">
                                    <h3 class="text-md font-medium mb-2"><?php _e('Final Design', 'print-order'); ?></h3>
                                    <a href="<?php echo esc_url($design_file); ?>" target="_blank">
                                        <img src="<?php echo esc_url($design_file); ?>" alt="Design Preview" class="max-w-full h-auto rounded-md cursor-zoom-in" style="max-height: 200px;">
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="revisions mt-4">
                                <p><strong><?php _e('Revisions Used:', 'print-order'); ?></strong> <?php echo $revisions; ?> / <?php echo $max_revisions; ?></p>
                                <?php if ($revisions < $max_revisions && $design_file) : ?>
                                    <form method="post" class="revision-form mt-2">
                                        <?php wp_nonce_field('revision_nonce'); ?>
                                        <input type="hidden" name="order_id" value="<?php echo $order->get_id(); ?>">
                                        <label for="revision_note_<?php echo $order->get_id(); ?>" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Revision Request', 'print-order'); ?></label>
                                        <textarea id="revision_note_<?php echo $order->get_id(); ?>" name="revision_note" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="توضیح دهید چه چیزی در طرح نیاز به تغییر دارد..."></textarea>
                                        <button type="submit" name="submit_revision" class="mt-2 bg-blue-500 text-white py-2 px-4 rounded-full hover:bg-blue-600 transition-all"><?php _e('Submit Revision Request', 'print-order'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="order-notes mt-4">
                                <h3 class="text-md font-medium mb-2"><?php _e('Order Notes', 'print-order'); ?></h3>
                                <?php
                                $notes = wc_get_order_notes(['order_id' => $order->get_id(), 'type' => 'customer']);
                                if ($notes) :
                                    ?>
                                    <ul class="list-disc pr-5">
                                        <?php foreach ($notes as $note) : ?>
                                            <li><?php echo esc_html($note->content); ?> <span class="text-gray-500 text-sm">(<?php echo $note->date_created->date('Y-m-d H:i'); ?>)</span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p class="text-gray-500"><?php _e('No notes available.', 'print-order') . '</p>';
                                ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function user_orders_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p class="text-red-600">' . __('لطفاً برای مشاهده سفارشات وارد شوید.', 'print-order') . '</p>';
        }

        // Get order_id from URL
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $order = wc_get_order($order_id);

        if (!$order || $order->get_customer_id() !== get_current_user_id()) {
            return '<p class="text-red-600">' . __('سفارش یافت نشد یا دسترسی غیرمجاز است.', 'print-order') . '</p>';
        }

        ob_start();
        ?>
        <div class="print-order-user-orders max-w-4xl mx-auto p-6 rtl">
            <?php echo print_order_user_orders_details($order_id, 'details'); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
new Print_Order_Shortcodes();
?>
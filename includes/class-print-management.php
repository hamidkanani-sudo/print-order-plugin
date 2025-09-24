<?php
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Print_Management {
    private $page_hook;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_save_print_management', [$this, 'save_settings']);
    }

    public function add_admin_menu() {
        $this->page_hook = add_submenu_page(
            'print-order-settings',
            'مدیریت چاپ',
            'مدیریت چاپ',
            'manage_options',
            'print-order-print-management',
            [$this, 'render_page']
        );
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function render_page() {
        $categories = get_terms('product_cat', ['hide_empty' => false, 'parent' => 0]);
        $saved_settings = get_option('print_order_print_management', []);
        $saved_prices = get_option('print_order_design_prices', []);
        ?>
        <div class="wrap">
            <h1 class="text-2xl font-bold mb-6">مدیریت چاپ</h1>
            <div class="pricing-card">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="save_print_management">
                    <?php wp_nonce_field('print_order_print_management_nonce'); ?>
                    <div class="table-container">
                        <table class="wp-list-table widefat striped print-order-table">
                            <thead class="sticky-header">
                                <tr>
                                    <th data-sort="checkbox">بدون چاپ</th>
                                    <th data-sort="category">نام دسته</th>
                                    <th data-sort="price">قیمت طراحی</th>
                                </tr>
                            </thead>
                            <tbody id="management-rows">
                                <?php foreach ($categories as $category): ?>
                                    <tr class="category-parent-row">
                                        <td data-label="بدون چاپ">
                                            <input type="checkbox" name="print_management[<?php echo $category->term_id; ?>]" 
                                                <?php checked(isset($saved_settings[$category->term_id])); ?> 
                                                class="category-parent h-5 w-5 text-blue-600">
                                        </td>
                                        <td data-label="نام دسته">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-gray-200 text-gray-600 mr-2 text-base">
                                                    +
                                                </span>
                                                <label class="font-bold"><?php echo esc_html($category->name); ?></label>
                                            </div>
                                        </td>
                                        <td data-label="قیمت طراحی">
                                            <input type="number" 
                                                name="design_price[<?php echo $category->term_id; ?>]" 
                                                value="<?php echo esc_attr($saved_prices[$category->term_id] ?? ''); ?>" 
                                                placeholder="قیمت طراحی" 
                                                min="0" 
                                                class="w-full p-2 border border-gray-400 rounded-md placeholder-gray-300">
                                        </td>
                                    </tr>
                                    <?php
                                    $subcats = get_terms('product_cat', ['hide_empty' => false, 'parent' => $category->term_id]);
                                    foreach ($subcats as $subcat):
                                    ?>
                                        <tr class="sub-category">
                                            <td data-label="بدون چاپ">
                                                <input type="checkbox" 
                                                    name="print_management[<?php echo $subcat->term_id; ?>]" 
                                                    <?php checked(isset($saved_settings[$subcat->term_id])); ?> 
                                                    class="category-child h-5 w-5 text-blue-600">
                                            </td>
                                            <td data-label="نام دسته">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-gray-200 text-gray-600 mr-2 text-base">
                                                        -
                                                    </span>
                                                    <label><?php echo esc_html($subcat->name); ?></label>
                                                </div>
                                            </td>
                                            <td data-label="قیمت طراحی">
                                                <input type="number" 
                                                    name="design_price[<?php echo $subcat->term_id; ?>]" 
                                                    value="<?php echo esc_attr($saved_prices[$subcat->term_id] ?? ''); ?>" 
                                                    placeholder="قیمت طراحی" 
                                                    min="0" 
                                                    class="w-full p-2 border border-gray-400 rounded-md placeholder-gray-300">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <hr class="separator">
                    <div class="flex justify-start mt-4">
                        <p class="submit">
                            <input type="submit" name="submit" class="btn-primary" value="ذخیره تغییرات">
                        </p>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function save_settings() {
        check_admin_referer('print_order_print_management_nonce');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }

        $settings = $_POST['print_management'] ?? [];
        $prices = $_POST['design_price'] ?? [];

        update_option('print_order_print_management', $settings);
        update_option('print_order_design_prices', $prices);

        wp_redirect(admin_url('admin.php?page=print-order-print-management'));
        exit;
    }

    public function enqueue_assets($hook) {
        if ($hook !== $this->page_hook) {
            return;
        }

        wp_enqueue_style('print-order-print-management-tw', PRINT_ORDER_URL . 'assets/css/class-print-management-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-print-management-tw.css'));
        wp_enqueue_script('print-order-print-management', PRINT_ORDER_URL . 'assets/js/print-management.js', ['jquery'], filemtime(PRINT_ORDER_PATH . 'assets/js/print-management.js'), true);
    }
}
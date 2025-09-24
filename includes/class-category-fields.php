<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Category_Fields {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_category_fields_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function enqueue_styles($hook) {
        // No styles enqueued here, moved to category_fields_page_callback
    }

    public function add_category_fields_page() {
        add_submenu_page(
            'print-order-settings',
            __('فیلدهای سفارش', 'print-order'),
            __('فیلدهای سفارش', 'print-order'),
            'manage_options',
            'print-order-category-fields',
            [$this, 'category_fields_page_callback']
        );
    }

    public function category_fields_page_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        $category_fields = get_option('print_order_category_fields', []);

        // Save fields
        if (isset($_POST['print_order_category_fields_nonce']) && wp_verify_nonce($_POST['print_order_category_fields_nonce'], 'print_order_category_fields')) {
            $new_fields = [];
            if (isset($_POST['fields']) && is_array($_POST['fields'])) {
                foreach ($_POST['fields'] as $category_id => $fields) {
                    $new_fields[$category_id] = [];
                    foreach ($fields as $field) {
                        if (!empty($field['name']) && !empty($field['label'])) {
                            $new_fields[$category_id][] = [
                                'name' => sanitize_key($field['name']),
                                'label' => sanitize_text_field($field['label']),
                                'type' => sanitize_text_field($field['type']),
                                'required' => isset($field['required']) ? 1 : 0,
                            ];
                        }
                    }
                }
            }
            update_option('print_order_category_fields', $new_fields);
            // Reload category_fields to reflect the updated data
            $category_fields = get_option('print_order_category_fields', []);
            echo '<div class="notice notice-success is-dismissible"><p>' . __('فیلدها با موفقیت ذخیره شدند!', 'print-order') . '</p></div>';
        }

        // Enqueue the category fields styles only in this callback
        wp_enqueue_style('class-category-fields-style', PRINT_ORDER_URL . 'assets/css/class-category-fields-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-category-fields-tw.css'));
        ?>
        <div class="wrap">
            <div class="max-w-6xl mx-auto">
                <!-- هدر صفحه -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-800 text-right"><?php _e('مدیریت فیلدهای دسته‌بندی', 'print-order'); ?></h1>
                    <p class="text-gray-600 mt-2 text-right"><?php _e('فیلدهای سفارشی را برای هر دسته‌بندی محصول تعریف کنید.', 'print-order'); ?></p>
                </div>

                <?php if (empty($categories)) : ?>
                    <div class="notice notice-warning"><p><?php _e('هیچ دسته‌بندی محصولی یافت نشد. لطفاً حداقل یک دسته‌بندی در ووکامرس ایجاد کنید.', 'print-order'); ?></p></div>
                <?php else : ?>
                    <!-- کارت اصلی -->
                    <div class="category-fields-card bg-white shadow-md rounded-lg p-8">
                        <form method="post">
                            <?php wp_nonce_field('print_order_category_fields', 'print_order_category_fields_nonce'); ?>
                            <div class="accordion">
                                <?php foreach ($categories as $category) : ?>
                                    <?php
                                    $fields = isset($category_fields[$category->term_id]) ? $category_fields[$category->term_id] : [];
                                    $is_parent = $category->parent == 0;
                                    $accordion_classes = $is_parent ? 'parent-category bg-blue-50' : 'child-category bg-gray-50';
                                    $button_classes = $is_parent ? 'font-bold bg-blue-100' : 'font-normal pr-8';
                                    ?>
                                    <div class="accordion-item border-b border-gray-200 <?php echo esc_attr($accordion_classes); ?>">
                                        <h2 class="accordion-header">
                                            <button type="button" class="accordion-button flex justify-between items-center w-full px-4 py-3 text-right text-gray-800 font-semibold hover:bg-gray-100 transition-all <?php echo esc_attr($button_classes); ?>">
                                                <span class="flex items-center">
                                                    <?php echo esc_html($category->name); ?>
                                                    <?php if (count($fields) > 0) : ?>
                                                        <span class="field-count bg-gray-200 text-gray-700 rounded-full px-2 py-1 text-xs mr-2"><?php echo count($fields); ?></span>
                                                    <?php endif; ?>
                                                </span>
                                                <svg class="w-5 h-5 text-gray-500 transform transition-transform accordion-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </h2>
                                        <div class="accordion-content hidden">
                                            <table class="category-fields-table w-full border-collapse">
                                                <thead>
                                                    <tr class="bg-blue-50">
                                                        <th class="px-4 py-3 text-right text-gray-700 font-semibold"><?php _e('Name', 'print-order'); ?></th>
                                                        <th class="px-4 py-3 text-right text-gray-700 font-semibold"><?php _e('Label', 'print-order'); ?></th>
                                                        <th class="px-4 py-3 text-right text-gray-700 font-semibold"><?php _e('Type', 'print-order'); ?></th>
                                                        <th class="px-4 py-3 text-right text-gray-700 font-semibold"><?php _e('Required', 'print-order'); ?></th>
                                                        <th class="px-4 py-3 text-right text-gray-700 font-semibold"><?php _e('Actions', 'print-order'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fields-rows" data-category-id="<?php echo esc_attr($category->term_id); ?>">
                                                    <?php
                                                    if (!empty($fields)) :
                                                        foreach ($fields as $index => $field) :
                                                    ?>
                                                            <tr class="bg-white border-b border-gray-200">
                                                                <td data-label="<?php _e('Name', 'print-order'); ?>" class="px-4 py-3">
                                                                    <input type="text" name="fields[<?php echo esc_attr($category->term_id); ?>][<?php echo $index; ?>][name]" value="<?php echo esc_attr($field['name']); ?>" class="border p-2 rounded-lg w-full border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                                                </td>
                                                                <td data-label="<?php _e('Label', 'print-order'); ?>" class="px-4 py-3">
                                                                    <input type="text" name="fields[<?php echo esc_attr($category->term_id); ?>][<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" class="border p-2 rounded-lg w-full border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                                                </td>
                                                                <td data-label="<?php _e('Type', 'print-order'); ?>" class="px-4 py-3">
                                                                    <select name="fields[<?php echo esc_attr($category->term_id); ?>][<?php echo $index; ?>][type]" class="border p-2 rounded-lg w-full border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                                                        <option value="text" <?php selected($field['type'], 'text'); ?>><?php _e('Text', 'print-order'); ?></option>
                                                                        <option value="textarea" <?php selected($field['type'], 'textarea'); ?>><?php _e('Textarea', 'print-order'); ?></option>
                                                                        <option value="select" <?php selected($field['type'], 'select'); ?>><?php _e('Select', 'print-order'); ?></option>
                                                                    </select>
                                                                </td>
                                                                <td data-label="<?php _e('Required', 'print-order'); ?>" class="px-4 py-3 text-center">
                                                                    <input type="checkbox" name="fields[<?php echo esc_attr($category->term_id); ?>][<?php echo $index; ?>][required]" value="1" <?php checked($field['required'], 1); ?> class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                                </td>
                                                                <td data-label="<?php _e('Actions', 'print-order'); ?>" class="px-4 py-3">
                                                                    <button type="button" class="delete-field-btn flex w-10 h-10 items-center justify-center bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-200 hover:scale-105">
                                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                        </svg>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                            <button type="button" class="add-field-btn inline-flex px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600 transition-all duration-200 hover:scale-105 mt-4"><?php _e('Add Field', 'print-order'); ?></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="flex justify-end mt-6">
                                <input type="submit" class="btn-primary px-6 py-3 rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200" value="<?php esc_attr_e('Save Fields', 'print-order'); ?>">
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Accordion functionality
                document.querySelectorAll('.accordion-button').forEach(button => {
                    button.addEventListener('click', () => {
                        const content = button.parentElement.nextElementSibling;
                        const icon = button.querySelector('.accordion-icon');
                        content.classList.toggle('hidden');
                        icon.classList.toggle('rotate-180');
                    });
                });

                // Add field
                document.querySelectorAll('.add-field-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const table = this.previousElementSibling.querySelector('.fields-rows');
                        const categoryId = table.dataset.categoryId;
                        const index = table.querySelectorAll('tr').length;
                        const row = document.createElement('tr');
                        row.className = 'bg-white border-b border-gray-200';
                        row.innerHTML = `
                            <td data-label="<?php _e('Name', 'print-order'); ?>" class="px-4 py-3">
                                <input type="text" name="fields[${categoryId}][${index}][name]" class="border p-2 rounded-lg w-full border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                            </td>
                            <td data-label="<?php _e('Label', 'print-order'); ?>" class="px-4 py-3">
                                <input type="text" name="fields[${categoryId}][${index}][label]" class="border p-2 rounded-lg w-full border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                            </td>
                            <td data-label="<?php _e('Type', 'print-order'); ?>" class="px-4 py-3">
                                <select name="fields[${categoryId}][${index}][type]" class="border p-2 rounded-lg w-full border-gray-300 focus:ring-2 focus:ring-blue-500 text-right">
                                    <option value="text"><?php _e('Text', 'print-order'); ?></option>
                                    <option value="textarea"><?php _e('Textarea', 'print-order'); ?></option>
                                    <option value="select"><?php _e('Select', 'print-order'); ?></option>
                                </select>
                            </td>
                            <td data-label="<?php _e('Required', 'print-order'); ?>" class="px-4 py-3 text-center">
                                <input type="checkbox" name="fields[${categoryId}][${index}][required]" value="1" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            </td>
                            <td data-label="<?php _e('Actions', 'print-order'); ?>" class="px-4 py-3">
                                <button type="button" class="delete-field-btn flex w-10 h-10 items-center justify-center bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-200 hover:scale-105">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </td>
                        `;
                        table.appendChild(row);
                    });
                });

                // Delete field
                document.addEventListener('click', function (e) {
                    if (e.target.classList.contains('delete-field-btn') || e.target.closest('.delete-field-btn')) {
                        e.target.closest('tr').remove();
                    }
                });
            });
        </script>
        <?php
    }
}
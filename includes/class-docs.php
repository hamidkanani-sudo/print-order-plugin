<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Docs {
    public function __construct() {
        // Add docs submenu
        add_action('admin_menu', [$this, 'add_docs_menu']);
    }

    public function add_docs_menu() {
        add_submenu_page(
            'print-order-settings',
            __('مستندات', 'print-order'),
            __('مستندات', 'print-order'),
            'manage_options',
            'print-order-docs',
            [$this, 'docs_page']
        );
    }

    public function docs_page() {
        // Enqueue the docs styles only in this callback
        wp_enqueue_style('class-docs-style', PRINT_ORDER_URL . 'assets/css/class-docs-tw.css', [], filemtime(PRINT_ORDER_PATH . 'assets/css/class-docs-tw.css'));
        ?>
        <div class="wrap">
            <div class="max-w-full">
                <!-- هدر صفحه -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800"><?php _e('مستندات افزونه سفارش چاپ', 'print-order'); ?></h1>
                    <p class="text-gray-600 mt-2"><?php _e('راهنمای استفاده از افزونه سفارش چاپ را در این بخش مطالعه کنید.', 'print-order'); ?></p>
                </div>
                <!-- کارت معرفی -->
                <div class="docs-card bg-white shadow-md rounded-lg p-8 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('معرفی افزونه', 'print-order'); ?></h2>
                    <p class="text-gray-600 mb-4"><?php _e('این افزونه به شما امکان می‌دهد سفارشات چاپ را با ووکامرس و المنتور مدیریت کنید. قابلیت‌های اصلی شامل فرم سفارش سفارشی، مدیریت قیمت‌گذاری، تولید فاکتور PDF، و داشبورد مشتری است.', 'print-order'); ?></p>
                </div>
                <!-- کارت شورت‌کدها -->
                <div class="docs-card bg-white shadow-md rounded-lg p-8 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('شورت‌کدها', 'print-order'); ?></h2>
                    <p class="text-gray-600 mb-4"><?php _e('لیست شورت‌کدهای موجود برای استفاده در صفحات جت‌انجین یا المنتور:', 'print-order'); ?></p>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-right text-gray-700 font-semibold"><?php _e('شورت‌کد', 'print-order'); ?></th>
                                    <th class="px-4 py-2 text-right text-gray-700 font-semibold"><?php _e('توضیح', 'print-order'); ?></th>
                                    <th class="px-4 py-2 text-right text-gray-700 font-semibold"><?php _e('پارامترها', 'print-order'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-gray-600">[print_order_form]</td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('نمایش فرم سفارش برای مشتریان جهت ثبت سفارش چاپ.', 'print-order'); ?></td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('ندارد', 'print-order'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-gray-600">[print_order_customer_orders]</td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('نمایش لیست سفارشات مشتری با امکان مشاهده جزئیات، چت ویرایش، و دانلود فاکتور.', 'print-order'); ?></td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('order_id (اختیاری): برای نمایش جزئیات یک سفارش خاص.', 'print-order'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-gray-600">[print_order_user_transactions]</td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('نمایش لیست تراکنش‌های مشتری با فیلترهای وضعیت، مرتب‌سازی، و صفحه‌بندی.', 'print-order'); ?></td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('per_page (اختیاری): تعداد تراکنش‌ها در هر صفحه (پیش‌فرض: 10).', 'print-order'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-gray-600">[print_order_transaction_details]</td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('نمایش جزئیات یک تراکنش خاص شامل اطلاعات سفارش، محصولات، و هزینه‌ها.', 'print-order'); ?></td>
                                    <td class="px-4 py-2 text-gray-600"><?php _e('transaction_id (اختیاری): شناسه تراکنش برای نمایش جزئیات (پیش‌فرض: از URL گرفته می‌شود).', 'print-order'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- کارت نحوه استفاده -->
                <div class="docs-card bg-white shadow-md rounded-lg p-8 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('نحوه استفاده', 'print-order'); ?></h2>
                    <ul class="list-disc pr-6 text-gray-600">
                        <li><?php _e('تنظیمات افزونه را از منوی "تنظیمات" پیکربندی کنید.', 'print-order'); ?></li>
                        <li><?php _e('قیمت‌های چاپ را در بخش "قیمت‌گذاری" وارد کنید یا از فایل CSV استفاده کنید.', 'print-order'); ?></li>
                        <li><?php _e('فیلدهای اضافی برای دسته‌بندی‌ها را در بخش "فیلدهای دسته‌بندی" تنظیم کنید.', 'print-order'); ?></li>
                        <li><?php _e('شورت‌کدهای بالا را در صفحات جت‌انجین یا المنتور قرار دهید.', 'print-order'); ?></li>
                        <li><?php _e('سفارشات را از منوی "سفارشات" مدیریت کنید و فاکتورهای PDF تولید کنید.', 'print-order'); ?></li>
                    </ul>
                </div>
                <!-- کارت نکات مهم -->
                <div class="docs-card bg-white shadow-md rounded-lg p-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('نکات مهم', 'print-order'); ?></h2>
                    <ul class="list-disc pr-6 text-gray-600">
                        <li><?php _e('برای تولید فاکتورهای PDF، کتابخانه TCPDF باید در مسیر vendor/tcpdf نصب باشد.', 'print-order'); ?></li>
                        <li><?php _e('اطمینان حاصل کنید که افزونه‌های ووکامرس و المنتور فعال باشند.', 'print-order'); ?></li>
                        <li><?php _e('برای استفاده از قابلیت SMS، کلید API کاوه‌نگار و شماره خط را در تنظیمات افزونه وارد کنید.', 'print-order'); ?></li>
                        <li><?php _e('برای بهبود عملکرد، کش سفارشات با Transient API فعال است.', 'print-order'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}
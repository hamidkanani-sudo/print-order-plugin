<?php
/**
 * Plugin Name: Print Order
 * Description: A custom WooCommerce plugin for print services with Elementor integration.
 * Version: 1.0.65
 * Author: Arvand Graphic
 * Text Domain: print-order
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PRINT_ORDER_PATH', plugin_dir_path(__FILE__));
define('PRINT_ORDER_URL', plugin_dir_url(__FILE__));
define('PRINT_ORDER_TEMP_DIR', WP_CONTENT_DIR . '/Uploads/temp/');

// Function to create database tables
function print_order_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create pricing table
    $table_name_pricing = $wpdb->prefix . 'print_order_pricing';
    $sql_pricing = "CREATE TABLE $table_name_pricing (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        category_id BIGINT(20) UNSIGNED NOT NULL,
        paper_type VARCHAR(100) NOT NULL,
        paper_type_persian VARCHAR(100) NOT NULL,
        size VARCHAR(50) NOT NULL,
        quantity INT NOT NULL,
        sides VARCHAR(50) NOT NULL,
        price BIGINT NOT NULL,
        days INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_pricing (category_id, paper_type_persian, size, quantity, sides)
    ) $charset_collate;";

    // Create template combinations table
    $table_name_template = $wpdb->prefix . 'print_order_template_combinations';
    $sql_template = "CREATE TABLE $table_name_template (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        category_id bigint(20) NOT NULL,
        paper_type_persian varchar(100) NOT NULL,
        shortcode_id bigint(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_combination (category_id, paper_type_persian)
    ) $charset_collate;";

    // Create discount codes table
    $table_name_discount = $wpdb->prefix . 'print_order_discount_codes';
    $sql_discount = "CREATE TABLE $table_name_discount (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        code varchar(50) NOT NULL,
        discount_type enum('percent', 'fixed') NOT NULL,
        discount_value bigint NOT NULL,
        start_date datetime NOT NULL,
        end_date datetime NOT NULL,
        usage_count bigint DEFAULT 0,
        usage_limit_per_user int DEFAULT 0,
        usage_limit_total int DEFAULT 0,
        apply_to text NOT NULL,
        status enum('active', 'inactive', 'expired') DEFAULT 'active',
        min_order_amount bigint DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_code (code)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result_pricing = dbDelta($sql_pricing);
    $result_template = dbDelta($sql_template);
    $result_discount = dbDelta($sql_discount);

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Print Order: Table creation attempted for ' . $table_name_pricing . ', Result: ' . print_r($result_pricing, true));
        error_log('Print Order: Table creation attempted for ' . $table_name_template . ', Result: ' . print_r($result_template, true));
        error_log('Print Order: Table creation attempted for ' . $table_name_discount . ', Result: ' . print_r($result_discount, true));
        if ($wpdb->last_error) {
            error_log('Print Order: Database error during table creation: ' . $wpdb->last_error);
        }
    }

    // Update existing prices to integers
    $wpdb->query("ALTER TABLE $table_name_pricing MODIFY price BIGINT NOT NULL");
    $wpdb->query("UPDATE $table_name_pricing SET price = FLOOR(price)");
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Print Order: Updated pricing table to use BIGINT and converted prices to integers');
    }
}

// Create tables on plugin activation
register_activation_hook(__FILE__, 'print_order_create_tables');

// Schedule daily cleanup of temporary folders
add_action('wp', function() {
    if (!wp_next_scheduled('print_order_clean_temp_folders')) {
        wp_schedule_event(time(), 'daily', 'print_order_clean_temp_folders');
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Scheduled daily cleanup cron job for temporary folders');
        }
    }
});

// Function to clean up temporary folders older than 24 hours
add_action('print_order_clean_temp_folders', function() {
    $temp_dir = PRINT_ORDER_TEMP_DIR;
    if (!file_exists($temp_dir)) {
        return;
    }

    $dirs = glob($temp_dir . '*', GLOB_ONLYDIR);
    $now = time();
    $expiration = 24 * 60 * 60; // 24 hours in seconds

    foreach ($dirs as $dir) {
        if (is_dir($dir) && ($now - filemtime($dir)) > $expiration) {
            // Recursively delete directory and its contents
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($dir);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Deleted temporary folder ' . $dir);
            }
        }
    }
});

// Load includes
require_once PRINT_ORDER_PATH . 'includes/class-elementor-widgets.php';
require_once PRINT_ORDER_PATH . 'includes/class-woocommerce.php';
require_once PRINT_ORDER_PATH . 'includes/class-settings.php';
require_once PRINT_ORDER_PATH . 'includes/class-orders.php';
require_once PRINT_ORDER_PATH . 'includes/class-pricing.php';
require_once PRINT_ORDER_PATH . 'includes/class-category-fields.php';
require_once PRINT_ORDER_PATH . 'includes/class-docs.php';
require_once PRINT_ORDER_PATH . 'includes/class-shortcodes.php';
require_once PRINT_ORDER_PATH . 'includes/class-pdf-generator.php';
require_once PRINT_ORDER_PATH . 'includes/class-core.php';
require_once PRINT_ORDER_PATH . 'includes/class-order-form.php';
require_once PRINT_ORDER_PATH . 'includes/class-template-combinations.php';
require_once PRINT_ORDER_PATH . 'includes/class-user-orders.php';
require_once PRINT_ORDER_PATH . 'includes/class-user-dashboard-transactions.php';
require_once PRINT_ORDER_PATH . 'includes/class-print-management.php';
require_once PRINT_ORDER_PATH . 'includes/class-discount-codes.php';

// Initialize plugin classes
class Print_Order {
    private $order_form;
    private $pricing;
    private $template_combinations;

    public function __construct() {
        $this->order_form = new Print_Order_Form();
        // $this->pricing = new Print_Order_Pricing(); // کامنت شده
        // $this->template_combinations = new Print_Order_Template_Combinations(); // کامنت شده
        $this->register_scripts();
    }

    public function register_scripts() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_styles'], 10);
    }

    public function enqueue_scripts() {
        global $wp_query, $wpdb;

        // بررسی دقیق‌تر برای بارگذاری اسکریپت‌ها و استایل‌ها
        $is_order_form = (isset($wp_query->query['pagename']) && $wp_query->query['pagename'] === 'order-form') ||
                        (get_post() && has_shortcode(get_post()->post_content ?? '', 'print_order_form')) ||
                        (isset($_GET['product_id']) && !empty($_GET['product_id']));
        $is_guide_widget = get_post() && has_shortcode(get_post()->post_content ?? '', 'print_order_guide');
        $is_user_orders_page = get_post() && (
            has_shortcode(get_post()->post_content ?? '', 'user_orders_table') ||
            has_shortcode(get_post()->post_content ?? '', 'user_orders_details') ||
            is_elementor_page_with_widget('user_orders_table') ||
            is_elementor_page_with_widget('user_orders_details')
        );
        $is_user_transactions_page = get_post() && (
            has_shortcode(get_post()->post_content ?? '', 'print_order_user_transactions') ||
            has_shortcode(get_post()->post_content ?? '', 'print_order_transaction_details') ||
            is_elementor_page_with_widget('user_transactions')
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: wp_enqueue_scripts hook fired');
            error_log('Print Order: Current page URL: ' . esc_url_raw(home_url($wp_query->request)));
            error_log('Print Order: is_order_form = ' . ($is_order_form ? 'true' : 'false'));
            error_log('Print Order: is_guide_widget = ' . ($is_guide_widget ? 'true' : 'false'));
            error_log('Print Order: is_user_orders_page = ' . ($is_user_orders_page ? 'true' : 'false'));
            error_log('Print Order: is_user_transactions_page = ' . ($is_user_transactions_page ? 'true' : 'false'));
        }

        // Enqueue styles for user orders pages
        if ($is_user_orders_page) {
            $orders_style_path = PRINT_ORDER_PATH . 'assets/css/class-user-orders-tw.css';
            if (file_exists($orders_style_path)) {
                wp_enqueue_style(
                    'class-user-orders-tw',
                    PRINT_ORDER_URL . 'assets/css/class-user-orders-tw.css',
                    [],
                    filemtime($orders_style_path) . '-' . time() // Force cache busting
                );
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Print Order: Enqueued class-user-orders-tw.css with version ' . filemtime($orders_style_path) . '-' . time());
                }
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Print Order: Error - class-user-orders-tw.css not found at ' . $orders_style_path);
                }
            }
        }

        // Enqueue styles for user transactions pages
        if ($is_user_transactions_page) {
            $transactions_style_path = PRINT_ORDER_PATH . 'assets/css/class-user-dashboard-transactions-tw.css';
            if (file_exists($transactions_style_path)) {
                wp_enqueue_style(
                    'class-user-dashboard-transactions-style',
                    PRINT_ORDER_URL . 'assets/css/class-user-dashboard-transactions-tw.css',
                    [],
                    filemtime($transactions_style_path) . '-' . time() // Force cache busting
                );
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Print Order: Enqueued class-user-dashboard-transactions-tw.css with version ' . filemtime($transactions_style_path) . '-' . time());
                }
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Print Order: Error - class-user-dashboard-transactions-tw.css not found at ' . $transactions_style_path);
                }
            }
        }

        if ($is_order_form || $is_guide_widget) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Loading scripts for form or guide widget page');
            }

            // استفاده از نسخه React پیش‌فرض وردپرس با مسیر صریح
            wp_enqueue_script('react', includes_url('js/dist/vendor/react.min.js'), [], '18.3.1', true);
            wp_enqueue_script('react-dom', includes_url('js/dist/vendor/react-dom.min.js'), ['react'], '18.3.1', true);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Enqueued React version 18.3.1 from ' . includes_url('js/dist/vendor/react.min.js'));
                error_log('Print Order: Enqueued ReactDOM version 18.3.1 from ' . includes_url('js/dist/vendor/react-dom.min.js'));
            }
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');

            // فایل‌های جاوااسکریپت مورد نیاز
            $js_files = [];
            if ($is_order_form) {
                $js_files = [
                    'utils' => 'utils.js',
                    'custom-fields' => 'custom-fields.js',
                    'form-pricing' => 'form-pricing.js',
                    'user-address' => 'user-address.js',
                    'step-one' => 'step-one.js',
                    'step-two' => 'step-two.js',
                    'step-three' => 'step-three.js',
                    'step-four' => 'step-four.js',
                    'form-state' => 'form-state.js',
                    'data-fetching' => 'data-fetching.js',
                    'step-navigation' => 'step-navigation.js',
                    'ui-rendering' => 'ui-rendering.js',
                    'event-handlers' => 'event-handlers.js',
                    'order-form' => 'order-form.js',
                    'order-form-widget' => 'order-form-widget.js',
                ];
            }
            if ($is_guide_widget) {
                $js_files['data-fetching'] = 'data-fetching.js';
                $js_files['guide-widget'] = 'guide-widget.js';
            }

            $dependent_handles = ['react', 'react-dom', 'jquery'];
            foreach ($js_files as $handle => $file) {
                wp_enqueue_script(
                    "print-order-$handle",
                    PRINT_ORDER_URL . "assets/js/$file",
                    $dependent_handles,
                    filemtime(PRINT_ORDER_PATH . "assets/js/$file"),
                    true
                );
                $dependent_handles[] = "print-order-$handle";
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Print Order: Enqueued $file with version " . filemtime(PRINT_ORDER_PATH . "assets/js/$file"));
                }
            }

            // Generate unique temp_id for the session
            $temp_id = wp_generate_uuid4();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Generated temp_id: ' . $temp_id);
            }

            // Fetch and organize pricing data by category
            $raw_pricing = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}print_order_pricing", ARRAY_A);
            $pricing_by_category = [];
            foreach ($raw_pricing as $item) {
                $category_id = $item['category_id'];
                if (!isset($pricing_by_category[$category_id])) {
                    $pricing_by_category[$category_id] = [];
                }
                $pricing_by_category[$category_id][] = $item;
            }

            if (empty($pricing_by_category) && defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Warning - No pricing data found in wp_print_order_pricing');
            }

            // Get all settings including button colors
            $options = get_option('print_order_options', [
                'design_fee' => 0,
                'tax_rate' => 0,
                'shipping_fee' => 0,
                'button_bg_color' => '#2563EB',
                'button_text_color' => '#ffffff',
                'login_page_url' => wp_login_url()
            ]);

            // Add inline CSS for button colors
            $inline_css = ":root { --button-bg-color: {$options['button_bg_color']}; --button-text-color: {$options['button_text_color']}; }";
            wp_add_inline_style('font-awesome', $inline_css);

            // Use category fields from database instead of test data
            $category_fields = get_option('print_order_category_fields', []);

            wp_localize_script(
                'print-order-order-form',
                'printOrder',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('print_order_nonce'),
                    'public_nonce' => wp_create_nonce('print_order_public_nonce'),
                    'pricing' => $pricing_by_category,
                    'category_fields' => $category_fields,
                    'options' => $options,
                    'temp_id' => $temp_id
                ]
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Localized printOrder object with ajax_url: ' . admin_url('admin-ajax.php'));
            }
        }
    }

    public function admin_enqueue_styles($hook) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Admin styles enqueued for hook: ' . $hook);
        }

        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');
    }

    public function convert_to_persian_date($datetime) {
        try {
            $gregorian_date = new DateTime($datetime);
            $jd = gregoriantojd(
                $gregorian_date->format('m'),
                $gregorian_date->format('d'),
                $gregorian_date->format('Y')
            );
            $persian_date = jdtogregorian($jd);
            $persian_parts = explode('/', $persian_date);
            $persian_year = $persian_parts[2];
            $persian_month = str_pad($persian_parts[0], 2, '0', STR_PAD_LEFT);
            $persian_day = str_pad($persian_parts[1], 2, '0', STR_PAD_LEFT);
            $time = $gregorian_date->format('H:i');
            return ['date' => "$persian_year/$persian_month/$persian_day", 'time' => $time];
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Print Order: Error in convert_to_persian_date: ' . $e->getMessage());
            }
            return ['date' => $datetime, 'time' => ''];
        }
    }
}

// Helper function to check if the page contains specific Elementor widgets
if (!function_exists('is_elementor_page_with_widget')) {
    function is_elementor_page_with_widget($widget_name) {
        if (!class_exists('Elementor\Plugin')) {
            return false;
        }
        $post_id = get_the_ID();
        if (!$post_id) {
            return false;
        }
        $document = \Elementor\Plugin::$instance->documents->get($post_id);
        if (!$document || !$document->is_built_with_elementor()) {
            return false;
        }
        $data = $document->get_elements_data();
        $has_widget = false;

        $check_widgets = function($elements) use ($widget_name, &$has_widget, &$check_widgets) {
            foreach ($elements as $element) {
                if (isset($element['widgetType']) && $element['widgetType'] === $widget_name) {
                    $has_widget = true;
                    return;
                }
                if (!empty($element['elements'])) {
                    $check_widgets($element['elements']);
                }
            }
        };
        $check_widgets($data);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Checked for widget ' . $widget_name . ', found: ' . ($has_widget ? 'true' : 'false'));
        }
        return $has_widget;
    }
}

// Check for TCPDF
$tcpdf_path = PRINT_ORDER_PATH . 'vendor/tcpdf/tcpdf.php';
if (file_exists($tcpdf_path)) {
    require_once $tcpdf_path;
} else {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>خطا: کتابخانه TCPDF یافت نشد. لطفاً TCPDF را در مسیر <code>vendor/tcpdf/</code> نصب کنید.</p></div>';
    });
}

// Initialize plugin
if (!function_exists('print_order_init')) {
    function print_order_init() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Print Order: Plugin loaded');
        }
        $print_order = new Print_Order();
        new Print_Order_Elementor_Widgets();
        new Print_Order_WooCommerce();
        new Print_Order_Settings();
        new Print_Order_Orders();
        new Print_Order_Pricing();
        new Print_Order_Category_Fields();
        new Print_Order_Docs();
        new Print_Order_Shortcodes();
        new Print_Order_PDF_Generator();
        new Print_Order_Core();
        new Print_Order_Form();
        new Print_Order_Template_Combinations();
        new Print_Order_User_Orders();
        new Print_Order_Print_Management();
        new Print_Order_Discount_Codes();
    }
    add_action('plugins_loaded', 'print_order_init', 10);
}

// Manual table creation action for debugging
add_action('admin_init', function() {
    if (isset($_GET['print_order_create_tables']) && current_user_can('manage_options')) {
        print_order_create_tables();
        wp_die('Table creation attempted. Check debug.log for details.');
    }
});
?>
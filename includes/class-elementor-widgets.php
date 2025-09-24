<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Print_Order_Elementor_Widgets {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function register_category($elements_manager) {
        $elements_manager->add_category(
            'print-order',
            [
                'title' => __('Print Order', 'print-order'),
                'icon' => 'eicon-apps',
            ]
        );
    }

    public function register_widgets($widgets_manager) {
        require_once PRINT_ORDER_PATH . 'widgets/order-button.php';
        require_once PRINT_ORDER_PATH . 'widgets/user-orders-table.php';
        require_once PRINT_ORDER_PATH . 'widgets/user-orders-details.php';
        require_once PRINT_ORDER_PATH . 'widgets/user-transactions.php';
        require_once PRINT_ORDER_PATH . 'widgets/user-profile-settings.php';
        require_once PRINT_ORDER_PATH . 'widgets/order-form-widget.php';
        require_once PRINT_ORDER_PATH . 'widgets/guide-widget.php';
        require_once PRINT_ORDER_PATH . 'widgets/user-orders-progress-bar.php';

        $widgets_manager->register(new Print_Order_Button_Widget());
        $widgets_manager->register(new User_Orders_Table_Widget());
        $widgets_manager->register(new User_Orders_Details_Widget());
        $widgets_manager->register(new User_Transactions_Widget());
        $widgets_manager->register(new User_Profile_Settings_Widget());
        $widgets_manager->register(new \PrintOrder\Widgets\Order_Form_Widget());
        $widgets_manager->register(new \PrintOrder\Widgets\Guide_Widget());
        $widgets_manager->register(new User_Orders_Progress_Bar_Widget());
    }

    public function enqueue_scripts() {
        $version = defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0';

        wp_register_script('print-order-react', includes_url('js/dist/vendor/react.min.js'), [], '18.3.1', true);
        wp_register_script('print-order-react-dom', includes_url('js/dist/vendor/react-dom.min.js'), ['print-order-react'], '18.3.1', true);
        wp_register_script('print-order-data-fetching', PRINT_ORDER_URL . 'assets/js/data-fetching.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-step-one', PRINT_ORDER_URL . 'assets/js/step-one.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-step-two', PRINT_ORDER_URL . 'assets/js/step-two.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-step-three', PRINT_ORDER_URL . 'assets/js/step-three.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-step-four', PRINT_ORDER_URL . 'assets/js/step-four.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-step-navigation', PRINT_ORDER_URL . 'assets/js/step-navigation.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-form-state', PRINT_ORDER_URL . 'assets/js/form-state.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-ui-rendering', PRINT_ORDER_URL . 'assets/js/ui-rendering.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-event-handlers', PRINT_ORDER_URL . 'assets/js/event-handlers.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-custom-fields', PRINT_ORDER_URL . 'assets/js/custom-fields.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-form-pricing', PRINT_ORDER_URL . 'assets/js/form-pricing.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-user-address', PRINT_ORDER_URL . 'assets/js/user-address.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-utils', PRINT_ORDER_URL . 'assets/js/utils.js', ['print-order-react', 'print-order-react-dom'], $version, true);
        wp_register_script('print-order-order-form', PRINT_ORDER_URL . 'assets/js/order-form.js', [
            'print-order-react',
            'print-order-react-dom',
            'print-order-step-one',
            'print-order-step-two',
            'print-order-step-three',
            'print-order-step-four',
            'print-order-step-navigation',
            'print-order-form-state',
            'print-order-ui-rendering',
            'print-order-event-handlers',
            'print-order-custom-fields',
            'print-order-form-pricing',
            'print-order-user-address',
            'print-order-utils'
        ], $version, true);
        wp_register_script('print-order-widget', PRINT_ORDER_URL . 'assets/js/order-form-widget.js', [
            'print-order-react',
            'print-order-react-dom',
            'print-order-order-form'
        ], $version, true);
        wp_register_script('print-order-guide-widget', PRINT_ORDER_URL . 'assets/js/guide-widget.js', [
            'print-order-react',
            'print-order-react-dom',
            'print-order-data-fetching'
        ], $version, true);
        wp_register_script('print-order-progress-bar', PRINT_ORDER_URL . 'assets/js/user-orders-progress-bar.js', ['jquery'], $version, true);

        wp_enqueue_style('print-order-progress-bar', PRINT_ORDER_URL . 'assets/css/class-user-orders-progress-bar-tw.css', [], $version, 'all');

        global $post;
        if ($post && \Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID)) {
            wp_enqueue_script('print-order-widget');
            wp_enqueue_script('print-order-guide-widget');
            wp_enqueue_script('print-order-progress-bar');
            error_log('PrintOrderWidgets: Enqueued print-order-widget, print-order-guide-widget, and print-order-progress-bar scripts for post ID ' . $post->ID);
        }

        $tailwind_path = PRINT_ORDER_PATH . 'assets/css/tailwind.min.css';
        if (file_exists($tailwind_path)) {
            wp_enqueue_style('print-order-tailwind', PRINT_ORDER_URL . 'assets/css/tailwind.min.css', [], $version, 'all');
        } else {
            error_log('PrintOrderWidget: Tailwind CSS file not found at ' . $tailwind_path);
        }
    }
}
?>
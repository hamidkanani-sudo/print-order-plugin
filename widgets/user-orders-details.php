<?php
if (!defined('ABSPATH')) {
    exit;
}

class User_Orders_Details_Widget extends \Elementor\Widget_Base {
    private $user_orders;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->user_orders = new Print_Order_User_Orders();
    }

    public function get_name() {
        return 'user_orders_details';
    }

    public function get_title() {
        return __('User Orders Details', 'print-order');
    }

    public function get_icon() {
        return 'eicon-document-file';
    }

    public function get_categories() {
        return ['print-order'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'order_id',
            [
                'label' => __('Order ID (Preview Only)', 'print-order'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'description' => __('Enter the order ID for preview in Elementor editor. In live mode, the order ID is read from the URL.', 'print-order'),
            ]
        );

        $this->add_control(
            'base_url',
            [
                'label' => __('Base URL', 'print-order'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'https://psdiha.ir/order-details/?order_id=',
                'description' => __('Enter the base URL for the order details page (e.g., https://psdiha.ir/order-details/?order_id=). This is used for JavaScript navigation.', 'print-order'),
            ]
        );

        $this->end_controls_section();

        // Style Section: Container
        $this->start_controls_section(
            'style_container_section',
            [
                'label' => __('Container Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'container_width',
            [
                'label' => __('Container Width', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1200,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 50,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'vw' => [
                        'min' => 50,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 960,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-user-orders' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 24,
                    'right' => 24,
                    'bottom' => 24,
                    'left' => 24,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-details-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 'auto',
                    'bottom' => 0,
                    'left' => 'auto',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-details-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .order-details-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-details-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Order Steps
        $this->start_controls_section(
            'style_steps_section',
            [
                'label' => __('Order Steps Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'step_circle_background_color',
            [
                'label' => __('Step Circle Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e0e0e0',
                'selectors' => [
                    '{{WRAPPER}} .step-circle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'step_circle_active_background_color',
            [
                'label' => __('Active Step Circle Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .step-circle.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'step_circle_completed_background_color',
            [
                'label' => __('Completed Step Circle Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#28a745',
                'selectors' => [
                    '{{WRAPPER}} .step-circle.completed' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'step_icon_color',
            [
                'label' => __('Step Icon Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .step-icon' => 'filter: brightness(0) invert(1);',
                ],
            ]
        );

        $this->add_control(
            'step_line_color',
            [
                'label' => __('Step Line Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e0e0e0',
                'selectors' => [
                    '{{WRAPPER}} .step-line' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'step_line_completed_color',
            [
                'label' => __('Completed Step Line Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#28a745',
                'selectors' => [
                    '{{WRAPPER}} .step-line.completed' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'step_label_typography',
                'label' => __('Step Label Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .step-label',
                'default' => [
                    'font_size' => [
                        'size' => 12,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_control(
            'step_label_color',
            [
                'label' => __('Step Label Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .step-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'step_label_active_color',
            [
                'label' => __('Active Step Label Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .step-label.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Tabs
        $this->start_controls_section(
            'style_tabs_section',
            [
                'label' => __('Tabs Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tabs_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .tabs .tab' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_active_background_color',
            [
                'label' => __('Active Tab Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .tabs .tab.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .tabs .tab' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_active_text_color',
            [
                'label' => __('Active Tab Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tabs .tab.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tabs_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .tabs .tab',
                'default' => [
                    'font_size' => [
                        'size' => 16,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8,
                    'right' => 16,
                    'bottom' => 8,
                    'left' => 16,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tabs .tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_spacing',
            [
                'label' => __('Spacing Between Tabs', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tabs' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Summary Card
        $this->start_controls_section(
            'style_summary_card_section',
            [
                'label' => __('Summary Card Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'summary_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .order-summary-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'summary_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dddddd',
                'selectors' => [
                    '{{WRAPPER}} .order-summary-card' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'summary_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 16,
                    'right' => 16,
                    'bottom' => 16,
                    'left' => 16,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-summary-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'summary_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .order-summary-card .summary-items span',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Financial Table
        $this->start_controls_section(
            'style_table_section',
            [
                'label' => __('Financial Table Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dddddd',
                'selectors' => [
                    '{{WRAPPER}} .financial-table th, {{WRAPPER}} .financial-table td' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_bg_color',
            [
                'label' => __('Header Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .financial-table th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_text_color',
            [
                'label' => __('Header Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .financial-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'table_cell_padding',
            [
                'label' => __('Cell Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .financial-table th, {{WRAPPER}} .financial-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Buttons
        $this->start_controls_section(
            'style_buttons_section',
            [
                'label' => __('Buttons Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .print-order-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Hover Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#005f8a',
                'selectors' => [
                    '{{WRAPPER}} .print-order-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Hover Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .print-order-btn',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8,
                    'right' => 16,
                    'bottom' => 8,
                    'left' => 16,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 4,
                    'right' => 4,
                    'bottom' => 4,
                    'left' => 4,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Chat Container
        $this->start_controls_section(
            'style_chat_section',
            [
                'label' => __('Chat Container Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'chat_user_background_color',
            [
                'label' => __('User Message Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e6f3ff',
                'selectors' => [
                    '{{WRAPPER}} .chat-message.user' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'chat_admin_background_color',
            [
                'label' => __('Admin Message Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .chat-message.admin' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'chat_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .chat-message' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'chat_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .chat-message span',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'chat_message_padding',
            [
                'label' => __('Message Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .chat-message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'chat_message_spacing',
            [
                'label' => __('Spacing Between Messages', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .chat-message' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $is_preview = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $order_id = $is_preview ? intval($settings['order_id']) : (isset($_GET['order_id']) ? intval($_GET['order_id']) : 0);
        $user_id = get_current_user_id();

        if (!is_user_logged_in()) {
            echo '<p class="text-red-600">' . esc_html__('لطفاً برای مشاهده سفارشات وارد شوید.', 'print-order') . '</p>';
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order || $order->get_customer_id() !== $user_id) {
            if ($is_preview) {
                echo '<p class="text-red-600">' . esc_html__('لطفاً یک Order ID معتبر وارد کنید یا با حساب کاربری مالک سفارش وارد شوید.', 'print-order') . '</p>';
            } else {
                echo '<p class="text-red-600">' . esc_html__('سفارش یافت نشد یا دسترسی غیرمجاز است.', 'print-order') . '</p>';
            }
            return;
        }

        // ثبت و بارگذاری استایل
        $orders_style_path = PRINT_ORDER_PATH . 'assets/css/class-user-orders-tw.css';
        if (file_exists($orders_style_path)) {
            wp_enqueue_style(
                'class-user-orders-tw',
                PRINT_ORDER_URL . 'assets/css/class-user-orders-tw.css',
                [],
                filemtime($orders_style_path) . '-' . time() // Force cache busting
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('User Orders Details Widget: Enqueued class-user-orders-tw.css with version ' . filemtime($orders_style_path) . '-' . time());
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('User Orders Details Widget: Error - class-user-orders-tw.css not found at ' . $orders_style_path);
            }
        }

        // Enqueue scripts
        wp_enqueue_script('print-order-user-orders');
        wp_localize_script('print-order-user-orders', 'printOrderUser', [
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
            'design_confirmed' => $order->get_meta('_print_order_design_confirmed') === 'yes' ? 'yes' : 'no',
            'unread_messages' => intval($order->get_meta('_print_order_unread_messages') ?? 0),
            'order_id' => $order_id,
            'base_url' => esc_url($settings['base_url']),
        ]);

        // Render the details
        echo '<div class="print-order-user-orders max-w-4xl mx-auto">';
        echo print_order_user_orders_details($order_id, 'details');
        echo '</div>';
    }
}
<?php
if (!defined('ABSPATH')) {
    exit;
}

class User_Transactions_Widget extends \Elementor\Widget_Base {
    private $transactions;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->transactions = new Print_Order_User_Dashboard_Transactions();
    }

    public function get_name() {
        return 'user_transactions';
    }

    public function get_title() {
        return __('User Transactions', 'print-order');
    }

    public function get_icon() {
        return 'eicon-table';
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
            'per_page',
            [
                'label' => __('Transactions Per Page', 'print-order'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
                'step' => 1,
                'description' => __('Number of transactions to display per page.', 'print-order'),
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
                    '{{WRAPPER}} .print-order-user-transactions' => 'max-width: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .print-order-user-transactions' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .print-order-user-transactions' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .print-order-user-transactions' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .print-order-user-transactions' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Table
        $this->start_controls_section(
            'style_table_section',
            [
                'label' => __('Table Style', 'print-order'),
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
                    '{{WRAPPER}} .print-order-table th, {{WRAPPER}} .print-order-table td' => 'border-color: {{VALUE}};',
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
                    '{{WRAPPER}} .print-order-table th' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .print-order-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .print-order-table td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'table_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .print-order-table th, {{WRAPPER}} .print-order-table td',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
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
                    '{{WRAPPER}} .print-order-table th, {{WRAPPER}} .print-order-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Toolbar
        $this->start_controls_section(
            'style_toolbar_section',
            [
                'label' => __('Toolbar Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'toolbar_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .toolbar' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toolbar_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dddddd',
                'selectors' => [
                    '{{WRAPPER}} .filter-select' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'toolbar_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .filter-select',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'toolbar_padding',
            [
                'label' => __('Padding', 'print-order'),
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
                    '{{WRAPPER}} .toolbar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toolbar_spacing',
            [
                'label' => __('Spacing Between Filters', 'print-order'),
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
                    '{{WRAPPER}} .toolbar' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Pagination
        $this->start_controls_section(
            'style_pagination_section',
            [
                'label' => __('Pagination Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'pagination_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .pagination-links' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .pagination-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_current_background_color',
            [
                'label' => __('Current Page Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .pagination-link.current' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_current_text_color',
            [
                'label' => __('Current Page Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .pagination-link.current' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'pagination_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .pagination-link',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'pagination_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8,
                    'right' => 12,
                    'bottom' => 8,
                    'left' => 12,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pagination-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'pagination_spacing',
            [
                'label' => __('Spacing Between Links', 'print-order'),
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
                    'size' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pagination-links' => 'gap: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .accordion-toggle, {{WRAPPER}} .payment-button' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .accordion-toggle, {{WRAPPER}} .payment-button' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .accordion-toggle:hover, {{WRAPPER}} .payment-button:hover' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .accordion-toggle:hover, {{WRAPPER}} .payment-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .accordion-toggle, {{WRAPPER}} .payment-button',
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
                    '{{WRAPPER}} .accordion-toggle, {{WRAPPER}} .payment-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .accordion-toggle, {{WRAPPER}} .payment-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Accordion
        $this->start_controls_section(
            'style_accordion_section',
            [
                'label' => __('Accordion Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'accordion_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .accordion-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'accordion_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dddddd',
                'selectors' => [
                    '{{WRAPPER}} .accordion-wrapper' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'accordion_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .accordion-grid .item .label, {{WRAPPER}} .accordion-grid .item .value',
                'default' => [
                    'font_size' => [
                        'size' => 14,
                        'unit' => 'px',
                    ],
                ],
            ]
        );

        $this->add_control(
            'accordion_label_color',
            [
                'label' => __('Label Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .accordion-grid .item .label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'accordion_value_color',
            [
                'label' => __('Value Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .accordion-grid .item .value' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'accordion_padding',
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
                    '{{WRAPPER}} .accordion-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'close_button_color',
            [
                'label' => __('Close Button Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .close-accordion' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'close_button_hover_color',
            [
                'label' => __('Close Button Hover Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e74c3c',
                'selectors' => [
                    '{{WRAPPER}} .close-accordion:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $atts = [
            'per_page' => isset($settings['per_page']) ? intval($settings['per_page']) : 10,
        ];

        // ثبت و بارگذاری استایل
        $transactions_style_path = PRINT_ORDER_PATH . 'assets/css/class-user-dashboard-transactions-tw.css';
        if (file_exists($transactions_style_path)) {
            wp_enqueue_style(
                'class-user-dashboard-transactions-style',
                PRINT_ORDER_URL . 'assets/css/class-user-dashboard-transactions-tw.css',
                [],
                filemtime($transactions_style_path) . '-' . time() // Force cache busting
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('User Transactions Widget: Enqueued class-user-dashboard-transactions-tw.css with version ' . filemtime($transactions_style_path) . '-' . time());
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('User Transactions Widget: Error - class-user-dashboard-transactions-tw.css not found at ' . $transactions_style_path);
            }
        }

        // Enqueue scripts
        wp_enqueue_script('print-order-transactions');

        // Localize script
        wp_localize_script('print-order-transactions', 'printOrder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('print_order_transactions_nonce'),
        ]);

        // Render transactions list
        echo $this->transactions->render_transactions_list($atts);
    }
}
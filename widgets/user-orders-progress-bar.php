<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class User_Orders_Progress_Bar_Widget extends \Elementor\Widget_Base {
    private $user_orders;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->user_orders = new Print_Order_User_Orders();
    }

    public function get_name() {
        return 'user-orders-progress-bar';
    }

    public function get_title() {
        return __('Order Progress Bar', 'print-order');
    }

    public function get_icon() {
        return 'eicon-progress-tracker';
    }

    public function get_categories() {
        return ['print-order'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'max_orders',
            [
                'label' => __('Maximum Orders', 'print-order'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 20,
                'step' => 1,
            ]
        );

        $this->add_control(
            'orders_page_slug',
            [
                'label' => __('Orders Page Slug', 'print-order'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'user-dashboard/orders',
                'placeholder' => __('e.g., user-dashboard/orders', 'print-order'),
            ]
        );

        $this->add_control(
            'details_page_slug',
            [
                'label' => __('Order Details Page Slug', 'print-order'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'user-dashboard/order-details',
                'placeholder' => __('e.g., user-dashboard/order-details', 'print-order'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Card Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_background_color',
            [
                'label' => __('Card Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .order-progress-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_color',
            [
                'label' => __('Card Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e5e7eb',
                'selectors' => [
                    '{{WRAPPER}} .order-progress-card' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_width',
            [
                'label' => __('Card Border Width', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-progress-card' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
                ],
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => __('Card Border Radius', 'print-order'),
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
                    '{{WRAPPER}} .order-progress-card' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_box_shadow',
            [
                'label' => __('Card Box Shadow', 'print-order'),
                'type' => \Elementor\Controls_Manager::BOX_SHADOW,
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 4,
                    'blur' => 6,
                    'spread' => 0,
                    'color' => 'rgba(0, 0, 0, 0.1)',
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-progress-card' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'progress_style_section',
            [
                'label' => __('Progress Bar Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 16,
                        'max' => 48,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .step-circle' => 'width: calc({{SIZE}}{{UNIT}} + 12px); height: calc({{SIZE}}{{UNIT}} + 12px);',
                ],
            ]
        );

        $this->add_control(
            'active_step_heading',
            [
                'label' => __('Active Step', 'print-order'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'active_step_background_color',
            [
                'label' => __('Active Step Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .step-circle.active' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                    '{{WRAPPER}} .step-line.completed' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'active_step_icon_color',
            [
                'label' => __('Active Step Icon Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .step-circle.active .step-icon' => 'filter: brightness(0) invert(1);',
                ],
            ]
        );

        $this->add_control(
            'active_step_text_color',
            [
                'label' => __('Active Step Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .step-label.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'active_step_text_size',
            [
                'label' => __('Active Step Text Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-label.active' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'active_step_text_weight',
            [
                'label' => __('Active Step Text Weight', 'print-order'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '600',
                'options' => [
                    '400' => __('Normal', 'print-order'),
                    '600' => __('Semi-Bold', 'print-order'),
                    '700' => __('Bold', 'print-order'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-label.active' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'completed_step_heading',
            [
                'label' => __('Completed Steps', 'print-order'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'completed_step_background_color',
            [
                'label' => __('Completed Step Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e0f2fe',
                'selectors' => [
                    '{{WRAPPER}} .step-circle.completed' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'completed_step_icon_color',
            [
                'label' => __('Completed Step Icon Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .step-circle.completed .step-icon' => 'filter: none;',
                ],
            ]
        );

        $this->add_control(
            'completed_step_text_color',
            [
                'label' => __('Completed Step Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1f2937',
                'selectors' => [
                    '{{WRAPPER}} .step-label.completed' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'completed_step_text_size',
            [
                'label' => __('Completed Step Text Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-label.completed' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'completed_step_text_weight',
            [
                'label' => __('Completed Step Text Weight', 'print-order'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '400',
                'options' => [
                    '400' => __('Normal', 'print-order'),
                    '600' => __('Semi-Bold', 'print-order'),
                    '700' => __('Bold', 'print-order'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-label.completed' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'remaining_step_heading',
            [
                'label' => __('Remaining Steps', 'print-order'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'remaining_step_background_color',
            [
                'label' => __('Remaining Step Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f3f4f6',
                'selectors' => [
                    '{{WRAPPER}} .step-circle:not(.active):not(.completed)' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'remaining_step_icon_color',
            [
                'label' => __('Remaining Step Icon Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => [
                    '{{WRAPPER}} .step-circle:not(.active):not(.completed) .step-icon' => 'filter: none;',
                ],
            ]
        );

        $this->add_control(
            'remaining_step_text_color',
            [
                'label' => __('Remaining Step Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => [
                    '{{WRAPPER}} .step-label:not(.active):not(.completed)' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'remaining_step_text_size',
            [
                'label' => __('Remaining Step Text Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-label:not(.active):not(.completed)' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'remaining_step_text_weight',
            [
                'label' => __('Remaining Step Text Weight', 'print-order'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '400',
                'options' => [
                    '400' => __('Normal', 'print-order'),
                    '600' => __('Semi-Bold', 'print-order'),
                    '700' => __('Bold', 'print-order'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .step-label:not(.active):not(.completed)' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Details Button Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'print-order'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('نمایش جزئیات', 'print-order'),
                'placeholder' => __('Enter button text', 'print-order'),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Button Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Button Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_color',
            [
                'label' => __('Button Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_width',
            [
                'label' => __('Button Border Width', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Button Border Radius', 'print-order'),
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
                    '{{WRAPPER}} .details-button' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_size',
            [
                'label' => __('Button Text Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 14,
                ],
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_weight',
            [
                'label' => __('Button Text Weight', 'print-order'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '600',
                'options' => [
                    '400' => __('Normal', 'print-order'),
                    '600' => __('Semi-Bold', 'print-order'),
                    '700' => __('Bold', 'print-order'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Button Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8,
                    'right' => 16,
                    'bottom' => 8,
                    'left' => 16,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .details-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'text_style_section',
            [
                'label' => __('Order Info Text Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1f2937',
                'selectors' => [
                    '{{WRAPPER}} .order-info p' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .order-info strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_size',
            [
                'label' => __('Text Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 14,
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-info p' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'text_weight',
            [
                'label' => __('Text Weight', 'print-order'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '400',
                'options' => [
                    '400' => __('Normal', 'print-order'),
                    '600' => __('Semi-Bold', 'print-order'),
                    '700' => __('Bold', 'print-order'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .order-info p' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if (!is_user_logged_in()) {
            echo '<p class="text-red-600">' . __('لطفاً وارد حساب کاربری خود شوید.', 'print-order') . '</p>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $max_orders = $settings['max_orders'] ?: 5;
        $orders_page_slug = $settings['orders_page_slug'] ?: 'user-dashboard/orders';
        $details_page_slug = $settings['details_page_slug'] ?: 'user-dashboard/order-details';
        $button_text = $settings['button_text'] ?: __('نمایش جزئیات', 'print-order');
        $user_id = get_current_user_id();
        $orders = $this->user_orders->get_user_orders($user_id, $max_orders + 1, 1, ['order-registered', 'payment-completed', 'design-approved', 'printing', 'shipping', 'delivered']);
        $filtered_orders = [];
        $current_time = current_time('timestamp');

        foreach ($orders as $order) {
            if ($order->get_status() === 'delivered' && ($current_time - strtotime($order->get_date_modified())) > 10 * 24 * 60 * 60) {
                continue;
            }
            $filtered_orders[] = $order;
        }

        if (empty($filtered_orders)) {
            echo '<p class="text-gray-600">' . __('هیچ سفارش جاری یافت نشد.', 'print-order') . '</p>';
            return;
        }

        $orders_to_display = array_slice($filtered_orders, 0, $max_orders);
        $has_more_orders = count($filtered_orders) > $max_orders;
        ?>
        <div class="order-progress-widget">
            <?php foreach ($orders_to_display as $order) : 
                $order_id = $order->get_id();
                $order_details = $this->user_orders->get_order_details($order_id);
                $steps = $this->user_orders->get_order_status_steps($order);
                $current_step = $this->user_orders->get_current_step($order);
                $details_url = home_url($details_page_slug . '/?order_id=' . $order_id);
            ?>
                <div class="order-progress-card flex flex-col sm:flex-row mb-4 p-4">
                    <div class="order-info w-full sm:w-1/5 mb-4 sm:mb-0">
                        <p><strong><?php _e('شماره سفارش:', 'print-order'); ?></strong> <a href="<?php echo esc_url($details_url); ?>" class="text-blue-600"><?php echo esc_html($order_details['number']); ?></a></p>
                        <p><strong><?php _e('نام محصول:', 'print-order'); ?></strong> <?php echo esc_html($order_details['product']); ?></p>
                        <p><strong><?php _e('تاریخ:', 'print-order'); ?></strong> <?php echo esc_html($order_details['date']); ?></p>
                        <p><strong><?php _e('مبلغ کل:', 'print-order'); ?></strong> <?php echo esc_html($order_details['total']); ?></p>
                        <a href="<?php echo esc_url($details_url); ?>" class="details-button inline-block mt-2"><?php echo esc_html($button_text); ?></a>
                    </div>
                    <div class="order-steps w-full sm:w-4/5 flex overflow-x-auto">
                        <?php
                        $index = 0;
                        foreach ($steps as $step_name => $step_data) :
                            $is_completed = array_search($current_step, array_keys($steps)) > $index;
                            $is_active = $step_name === $current_step;
                            $step_class = $is_active ? 'active' : ($is_completed ? 'completed' : '');
                        ?>
                            <div class="step-container flex-shrink-0">
                                <div class="step-circle <?php echo esc_attr($step_class); ?>">
                                    <img src="<?php echo esc_url($step_data['icon']); ?>" alt="<?php echo esc_attr($step_name); ?>" class="step-icon">
                                </div>
                                <div class="step-label <?php echo esc_attr($step_class); ?>"><?php echo esc_html($step_name); ?></div>
                            </div>
                        <?php
                            $index++;
                        endforeach;
                        ?>
                        <div class="step-line <?php echo array_search($current_step, array_keys($steps)) > 0 ? 'completed' : ''; ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($has_more_orders) : ?>
                <p class="more-orders text-gray-600 mt-4">
                    <?php printf(
                        __('برای مشاهده سفارش‌های بیشتر، به <a href="%s" class="text-blue-600">صفحه سفارش‌ها</a> مراجعه کنید.', 'print-order'),
                        esc_url(home_url($orders_page_slug))
                    ); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>
<?php
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_Button_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'print_order_button';
    }

    public function get_title() {
        return __('Order Button', 'print-order');
    }

    public function get_icon() {
        return 'eicon-button';
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
            'button_text',
            [
                'label' => __('Button Text', 'print-order'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => ['active' => true],
                'default' => __('شروع سفارش', 'print-order'),
                'placeholder' => __('Enter button text', 'print-order'),
            ]
        );

        $this->add_control(
            'button_link',
            [
                'label' => __('Button Link', 'print-order'),
                'type' => \Elementor\Controls_Manager::URL,
                'dynamic' => ['active' => true],
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'placeholder' => __('Leave blank to use form URL from settings', 'print-order'),
            ]
        );

        $this->add_control(
            'button_icon',
            [
                'label' => __('Icon', 'print-order'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-arrow-left',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'label' => __('Icon Position', 'print-order'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'before',
                'options' => [
                    'before' => __('Before Text', 'print-order'),
                    'after' => __('After Text', 'print-order'),
                ],
                'condition' => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'icon_spacing',
            [
                'label' => __('Icon Spacing', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50, 'step' => 1],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-button .elementor-icon-before' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .print-order-button .elementor-icon-after' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .print-order-button',
            ]
        );

        $this->start_controls_tabs('style_tabs');

        // Normal State
        $this->start_controls_tab(
            'normal_tab',
            [
                'label' => __('Normal', 'print-order'),
            ]
        );

        $this->add_control(
            'bg_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'background-color: {{VALUE}};',
                ],
                'default' => get_option('print_order_options')['button_bg_color'] ?? '#2563EB',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'color: {{VALUE}};',
                ],
                'default' => get_option('print_order_options')['button_text_color'] ?? '#ffffff',
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'hover_tab',
            [
                'label' => __('Hover', 'print-order'),
            ]
        );

        $this->add_control(
            'bg_color_hover',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color_hover',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'border_color_hover',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'border_width',
            [
                'label' => __('Border Width', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 10, 'step' => 1],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
                ],
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50, 'step' => 1],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'full_width',
            [
                'label' => __('Full Width', 'print-order'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'no',
                'selectors' => [
                    '{{WRAPPER}} .print-order-button' => 'width: 100%; display: block;',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_shadow',
                'selector' => '{{WRAPPER}} .print-order-button',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $options = get_option('print_order_options', []);

        // Try getting form page URL from settings
        $form_page_url = !empty($options['form_page_url']) ? $options['form_page_url'] : '';

        // Fallback to page with shortcode [print_order_form]
        if (empty($form_page_url)) {
            $form_page_id = get_option('print_order_form_page_id');
            $form_page_url = $form_page_id ? get_permalink($form_page_id) : home_url('/order-form/');
        }

        $base_link = $settings['button_link']['url'] ?: $form_page_url;
        $product_id = get_the_ID();

        if (!is_product() || !$product_id) {
            ?>
            <div class="print-order-button-error text-red-600 p-4 bg-red-100 rounded-md">
                <?php _e('This button is only available on product pages. Please place it on a WooCommerce product page.', 'print-order'); ?>
            </div>
            <?php
            return;
        }

        $link = esc_url(add_query_arg('product_id', $product_id, $base_link));
        $icon_class = $settings['icon_position'] === 'before' ? 'elementor-icon-before' : 'elementor-icon-after';

        ?>
        <a href="<?php echo $link; ?>" class="print-order-button-link">
            <button class="print-order-button">
                <?php if (!empty($settings['button_icon']['value']) && $settings['icon_position'] === 'before') : ?>
                    <i class="<?php echo esc_attr($settings['button_icon']['value']); ?> <?php echo $icon_class; ?>"></i>
                <?php endif; ?>
                <?php echo esc_html($settings['button_text']); ?>
                <?php if (!empty($settings['button_icon']['value']) && $settings['icon_position'] === 'after') : ?>
                    <i class="<?php echo esc_attr($settings['button_icon']['value']); ?> <?php echo $icon_class; ?>"></i>
                <?php endif; ?>
            </button>
        </a>
        <?php
    }
}
?>
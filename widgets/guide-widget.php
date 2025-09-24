<?php
namespace PrintOrder\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Guide_Widget extends Widget_Base {
    public function get_name() {
        return 'print-order-guide';
    }

    public function get_title() {
        return __('Print Order Guide', 'print-order');
    }

    public function get_icon() {
        return 'eicon-info-circle';
    }

    public function get_categories() {
        return ['print-order'];
    }

    protected function register_controls() {
        // Loading Settings Section
        $this->start_controls_section(
            'loading_settings',
            [
                'label' => __('Loading Settings', 'print-order'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'loading_color',
            [
                'label' => __('Loading Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4B5563',
                'selectors' => [
                    '{{WRAPPER}} .loading-spinner div' => 'border-top-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'loading_size',
            [
                'label' => __('Loading Size', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 16,
                        'max' => 64,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 32,
                ],
                'selectors' => [
                    '{{WRAPPER}} .loading-spinner div' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'loading_delay',
            [
                'label' => __('Loading Delay (ms)', 'print-order'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 5000,
                'step' => 100,
                'default' => 500,
                'description' => __('Delay before hiding loading spinner (in milliseconds)', 'print-order'),
            ]
        );

        $this->end_controls_section();

        // Mobile Button Settings Section
        $this->start_controls_section(
            'mobile_button_settings',
            [
                'label' => __('Mobile Button Settings', 'print-order'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'show_mobile_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_mobile_button',
            [
                'label' => __('Show Button in Mobile', 'print-order'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'print-order'),
                'type' => Controls_Manager::TEXT,
                'default' => __('نمایش راهنما', 'print-order'),
                'placeholder' => __('Enter button text', 'print-order'),
            ]
        );

        $this->add_control(
            'button_icon',
            [
                'label' => __('Button Icon', 'print-order'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-info-circle',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'label' => __('Icon Position', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Left', 'print-order'),
                    'right' => __('Right', 'print-order'),
                ],
                'condition' => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_position',
            [
                'label' => __('Button Position', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => 'bottom-right',
                'options' => [
                    'top-left' => __('Top Left', 'print-order'),
                    'top-right' => __('Top Right', 'print-order'),
                    'bottom-left' => __('Bottom Left', 'print-order'),
                    'bottom-right' => __('Bottom Right', 'print-order'),
                ],
            ]
        );

        $this->add_control(
            'button_offset_horizontal',
            [
                'label' => __('Horizontal Offset (%)', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 2,
                ],
            ]
        );

        $this->add_control(
            'button_offset_vertical',
            [
                'label' => __('Vertical Offset (%)', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 2,
                ],
            ]
        );

        $this->add_control(
            'button_sticky',
            [
                'label' => __('Sticky Button', 'print-order'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'yes',
                'description' => __('Enable to keep button fixed on screen while scrolling', 'print-order'),
            ]
        );

        $this->add_control(
            'slide_width',
            [
                'label' => __('Slide Width (% of screen)', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 50,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 80,
                ],
            ]
        );

        $this->add_control(
            'slide_direction',
            [
                'label' => __('Slide Direction', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => 'right',
                'options' => [
                    'right' => __('Right to Left', 'print-order'),
                    'left' => __('Left to Right', 'print-order'),
                ],
            ]
        );

        $this->add_control(
            'close_method',
            [
                'label' => __('Close Method', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => 'both',
                'options' => [
                    'button' => __('Close Button Only', 'print-order'),
                    'outside' => __('Outside Click Only', 'print-order'),
                    'both' => __('Both Button and Outside Click', 'print-order'),
                ],
            ]
        );

        $this->end_controls_section();

        // Button Style Section
        $this->start_controls_section(
            'button_style',
            [
                'label' => __('Button Style', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_mobile_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_icon_color',
            [
                'label' => __('Icon Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button i' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_font_size',
            [
                'label' => __('Font Size', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 12,
                        'max' => 24,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_font_weight',
            [
                'label' => __('Font Weight', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => '500',
                'options' => [
                    '100' => __('Thin', 'print-order'),
                    '300' => __('Light', 'print-order'),
                    '400' => __('Normal', 'print-order'),
                    '500' => __('Medium', 'print-order'),
                    '700' => __('Bold', 'print-order'),
                    '900' => __('Extra Bold', 'print-order'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_icon_size',
            [
                'label' => __('Icon Size', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 12,
                        'max' => 24,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_icon_spacing',
            [
                'label' => __('Icon Spacing', 'print-order'),
                'type' => Controls_Manager::SLIDER,
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
                    '{{WRAPPER}} .guide-toggle-button i.icon-left' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .guide-toggle-button i.icon-right' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
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
                    '{{WRAPPER}} .guide-toggle-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_box_shadow',
            [
                'label' => __('Box Shadow', 'print-order'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_border_width',
            [
                'label' => __('Border Width', 'print-order'),
                'type' => Controls_Manager::SLIDER,
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
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'button_border_width[size]!' => 0,
                ],
            ]
        );

        $this->add_control(
            'button_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 12,
                    'right' => 20,
                    'bottom' => 12,
                    'left' => 20,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-toggle-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Button Animation Section
        $this->start_controls_section(
            'button_animation',
            [
                'label' => __('Button Animation', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_animation',
            [
                'label' => __('Animation Type', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('None', 'print-order'),
                    'scale' => __('Scale', 'print-order'),
                    'shake' => __('Shake', 'print-order'),
                    'pulse' => __('Pulse', 'print-order'),
                ],
            ]
        );

        $this->add_control(
            'animation_interval',
            [
                'label' => __('Animation Interval (seconds)', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 1,
                        'max' => 10,
                        'step' => 0.5,
                    ],
                ],
                'default' => [
                    'unit' => 's',
                    'size' => 3,
                ],
                'condition' => [
                    'button_animation!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        // Slide Style Section
        $this->start_controls_section(
            'slide_style',
            [
                'label' => __('Slide Style', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_mobile_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'slide_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .guide-slide' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'slide_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-slide' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'slide_box_shadow',
            [
                'label' => __('Box Shadow', 'print-order'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Layout Settings Section
        $this->start_controls_section(
            'layout_settings',
            [
                'label' => __('Layout Settings', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'alignment',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .guide-widget' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 16,
                    'right' => 16,
                    'bottom' => 16,
                    'left' => 16,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .guide-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('print_order_nonce');
        $button_icon = !empty($settings['button_icon']['value']) ? esc_attr($settings['button_icon']['value']) : '';
        $animation_interval = isset($settings['animation_interval']['size']) ? esc_attr($settings['animation_interval']['size']) : '3';
        $button_margin = isset($settings['button_margin']) && is_array($settings['button_margin']) ? $settings['button_margin'] : ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0];
        $button_padding = isset($settings['button_padding']) && is_array($settings['button_padding']) ? $settings['button_padding'] : ['top' => 12, 'right' => 20, 'bottom' => 12, 'left' => 20];
        $button_border_radius = isset($settings['button_border_radius']) && is_array($settings['button_border_radius']) ? $settings['button_border_radius'] : ['top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8];
        
        error_log('PrintOrderGuideWidget: Rendering widget with ID ' . $this->get_id());
        ?>
        <div id="print-order-guide-<?php echo esc_attr($this->get_id()); ?>" 
             class="print-order-guide" 
             data-ajax-url="<?php echo esc_url($ajax_url); ?>" 
             data-nonce="<?php echo esc_attr($nonce); ?>"
             data-loading-delay="<?php echo esc_attr($settings['loading_delay'] ?? 500); ?>"
             data-button-text="<?php echo esc_attr($settings['button_text'] ?? 'نمایش راهنما'); ?>"
             data-button-icon="<?php echo $button_icon; ?>"
             data-icon-position="<?php echo esc_attr($settings['icon_position'] ?? 'left'); ?>"
             data-button-position="<?php echo esc_attr($settings['button_position'] ?? 'bottom-right'); ?>"
             data-slide-width="<?php echo esc_attr($settings['slide_width']['size'] ?? 80); ?>"
             data-slide-direction="<?php echo esc_attr($settings['slide_direction'] ?? 'right'); ?>"
             data-close-method="<?php echo esc_attr($settings['close_method'] ?? 'both'); ?>"
             data-button-offset-horizontal="<?php echo esc_attr($settings['button_offset_horizontal']['size'] ?? 2); ?>"
             data-button-offset-vertical="<?php echo esc_attr($settings['button_offset_vertical']['size'] ?? 2); ?>"
             data-button-sticky="<?php echo isset($settings['button_sticky']) && $settings['button_sticky'] === 'yes' ? 'yes' : 'no'; ?>"
             data-button-bg-color="<?php echo esc_attr($settings['button_background_color'] ?? '#2563eb'); ?>"
             data-button-text-color="<?php echo esc_attr($settings['button_text_color'] ?? '#ffffff'); ?>"
             data-button-font-size="<?php echo esc_attr($settings['button_font_size']['size'] ?? 16); ?>"
             data-button-font-weight="<?php echo esc_attr($settings['button_font_weight'] ?? '500'); ?>"
             data-button-icon-size="<?php echo esc_attr($settings['button_icon_size']['size'] ?? 16); ?>"
             data-button-icon-spacing="<?php echo esc_attr($settings['button_icon_spacing']['size'] ?? 8); ?>"
             data-button-icon-color="<?php echo esc_attr($settings['button_icon_color'] ?? '#ffffff'); ?>"
             data-button-border-radius-top="<?php echo esc_attr($button_border_radius['top'] ?? 8); ?>"
             data-button-border-radius-right="<?php echo esc_attr($button_border_radius['right'] ?? 8); ?>"
             data-button-border-radius-bottom="<?php echo esc_attr($button_border_radius['bottom'] ?? 8); ?>"
             data-button-border-radius-left="<?php echo esc_attr($button_border_radius['left'] ?? 8); ?>"
             data-button-box-shadow="<?php echo isset($settings['button_box_shadow']) && $settings['button_box_shadow'] === 'yes' ? 'yes' : 'no'; ?>"
             data-button-border-width="<?php echo esc_attr($settings['button_border_width']['size'] ?? 0); ?>"
             data-button-border-color="<?php echo esc_attr($settings['button_border_color'] ?? '#2563eb'); ?>"
             data-button-margin-top="<?php echo esc_attr($button_margin['top'] ?? 0); ?>"
             data-button-margin-right="<?php echo esc_attr($button_margin['right'] ?? 0); ?>"
             data-button-margin-bottom="<?php echo esc_attr($button_margin['bottom'] ?? 0); ?>"
             data-button-margin-left="<?php echo esc_attr($button_margin['left'] ?? 0); ?>"
             data-button-padding-top="<?php echo esc_attr($button_padding['top'] ?? 12); ?>"
             data-button-padding-right="<?php echo esc_attr($button_padding['right'] ?? 20); ?>"
             data-button-padding-bottom="<?php echo esc_attr($button_padding['bottom'] ?? 12); ?>"
             data-button-padding-left="<?php echo esc_attr($button_padding['left'] ?? 20); ?>"
             data-button-animation="<?php echo esc_attr($settings['button_animation'] ?? ''); ?>"
             data-animation-interval="<?php echo $animation_interval; ?>"
             data-slide-bg-color="<?php echo esc_attr($settings['slide_background_color'] ?? '#ffffff'); ?>"
             data-slide-border-radius="<?php echo esc_attr($settings['slide_border_radius']['size'] ?? 0); ?>"
             data-slide-box-shadow="<?php echo isset($settings['slide_box_shadow']) && $settings['slide_box_shadow'] === 'yes' ? 'yes' : 'no'; ?>">
            <div class="guide-widget-placeholder">در حال بارگذاری ویجت راهنما...</div>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var animation_interval = settings.animation_interval && settings.animation_interval.size ? settings.animation_interval.size : '3';
        var button_margin = settings.button_margin && typeof settings.button_margin === 'object' ? settings.button_margin : { top: 0, right: 0, bottom: 0, left: 0 };
        var button_padding = settings.button_padding && typeof settings.button_padding === 'object' ? settings.button_padding : { top: 12, right: 20, bottom: 12, left: 20 };
        var button_border_radius = settings.button_border_radius && typeof settings.button_border_radius === 'object' ? settings.button_border_radius : { top: 8, right: 8, bottom: 8, left: 8 };
        #>
        <div id="print-order-guide-{{{ settings._element_id }}}" 
             class="print-order-guide" 
             data-ajax-url="{{{ settings.ajax_url || '' }}}" 
             data-nonce="{{{ settings.nonce || '' }}}" 
             data-loading-delay="{{{ settings.loading_delay || 500 }}}"
             data-button-text="{{{ settings.button_text || 'نمایش راهنما' }}}"
             data-button-icon="{{{ settings.button_icon && settings.button_icon.value ? settings.button_icon.value : '' }}}"
             data-icon-position="{{{ settings.icon_position || 'left' }}}"
             data-button-position="{{{ settings.button_position || 'bottom-right' }}}"
             data-slide-width="{{{ settings.slide_width && settings.slide_width.size ? settings.slide_width.size : 80 }}}"
             data-slide-direction="{{{ settings.slide_direction || 'right' }}}"
             data-close-method="{{{ settings.close_method || 'both' }}}"
             data-button-offset-horizontal="{{{ settings.button_offset_horizontal && settings.button_offset_horizontal.size ? settings.button_offset_horizontal.size : 2 }}}"
             data-button-offset-vertical="{{{ settings.button_offset_vertical && settings.button_offset_vertical.size ? settings.button_offset_vertical.size : 2 }}}"
             data-button-sticky="{{{ settings.button_sticky || 'yes' }}}"
             data-button-bg-color="{{{ settings.button_background_color || '#2563eb' }}}"
             data-button-text-color="{{{ settings.button_text_color || '#ffffff' }}}"
             data-button-font-size="{{{ settings.button_font_size && settings.button_font_size.size ? settings.button_font_size.size : 16 }}}"
             data-button-font-weight="{{{ settings.button_font_weight || '500' }}}"
             data-button-icon-size="{{{ settings.button_icon_size && settings.button_icon_size.size ? settings.button_icon_size.size : 16 }}}"
             data-button-icon-spacing="{{{ settings.button_icon_spacing && settings.button_icon_spacing.size ? settings.button_icon_spacing.size : 8 }}}"
             data-button-icon-color="{{{ settings.button_icon_color || '#ffffff' }}}"
             data-button-border-radius-top="{{{ button_border_radius.top || 8 }}}"
             data-button-border-radius-right="{{{ button_border_radius.right || 8 }}}"
             data-button-border-radius-bottom="{{{ button_border_radius.bottom || 8 }}}"
             data-button-border-radius-left="{{{ button_border_radius.left || 8 }}}"
             data-button-box-shadow="{{{ settings.button_box_shadow || 'yes' }}}"
             data-button-border-width="{{{ settings.button_border_width && settings.button_border_width.size ? settings.button_border_width.size : 0 }}}"
             data-button-border-color="{{{ settings.button_border_color || '#2563eb' }}}"
             data-button-margin-top="{{{ button_margin.top || 0 }}}"
             data-button-margin-right="{{{ button_margin.right || 0 }}}"
             data-button-margin-bottom="{{{ button_margin.bottom || 0 }}}"
             data-button-margin-left="{{{ button_margin.left || 0 }}}"
             data-button-padding-top="{{{ button_padding.top || 12 }}}"
             data-button-padding-right="{{{ button_padding.right || 20 }}}"
             data-button-padding-bottom="{{{ button_padding.bottom || 12 }}}"
             data-button-padding-left="{{{ button_padding.left || 20 }}}"
             data-button-animation="{{{ settings.button_animation || '' }}}"
             data-animation-interval="{{{ animation_interval }}}"
             data-slide-bg-color="{{{ settings.slide_background_color || '#ffffff' }}}"
             data-slide-border-radius="{{{ settings.slide_border_radius && settings.slide_border_radius.size ? settings.slide_border_radius.size : 0 }}}"
             data-slide-box-shadow="{{{ settings.slide_box_shadow || 'yes' }}}">
            <div class="guide-widget-placeholder">در حال بارگذاری ویجت راهنما...</div>
        </div>
        <?php
    }

    public function __construct($data = [], $args = []) {
        parent::__construct($data, $args);
        // Enqueue FontAwesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            [],
            '5.15.4'
        );
        // Enqueue CSS with version bump
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/class-guide-widget.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'print-order-guide-widget',
                plugin_dir_url(__FILE__) . 'assets/css/class-guide-widget.css',
                [],
                '1.0.4' // Updated version to ensure cache refresh
            );
        } else {
            error_log('PrintOrderGuideWidget: CSS file not found at ' . $css_file);
        }
    }
}
<?php
namespace PrintOrder\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Order_Form_Widget extends Widget_Base {
    public function get_name() {
        return 'print-order-form';
    }

    public function get_title() {
        return __('Print Order Form', 'print-order');
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    public function get_categories() {
        return ['print-order'];
    }

    public function get_keywords() {
        return ['print', 'order', 'form', 'print-order'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content Settings', 'print-order'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product ID', 'print-order'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Enter Product ID for preview', 'print-order'),
                'description' => __('Enter a WooCommerce Product ID for preview in Elementor editor. In frontend, Product ID is fetched from URL parameter "product_id".', 'print-order'),
            ]
        );

        $this->add_control(
            'preview_step',
            [
                'label' => __('Preview Step', 'print-order'),
                'type' => Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => __('Step 1: Print Type', 'print-order'),
                    '2' => __('Step 2: Design Info', 'print-order'),
                    '3' => __('Step 3: Address', 'print-order'),
                    '4' => __('Step 4: Payment', 'print-order'),
                ],
                'description' => __('Select the form step to preview in Elementor editor.', 'print-order'),
                'condition' => [
                    'product_id!' => '', // Only show if product_id is set
                ],
            ]
        );

        $this->end_controls_section();

        // General Style Section
        $this->start_controls_section(
            'general_style_section',
            [
                'label' => __('General Form Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_background_color',
            [
                'label' => __('Form Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_align',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'form_border',
                'selector' => '{{WRAPPER}} .print-order-form',
            ]
        );

        $this->add_control(
            'form_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'form_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form',
            ]
        );

        $this->end_controls_section();

        // Progress Bar Style Section
        $this->start_controls_section(
            'progress_bar_style_section',
            [
                'label' => __('Progress Bar Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'progress_bar_circle_inactive_color',
            [
                'label' => __('Inactive Circle Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#e5e7eb',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar .circle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'progress_bar_circle_active_color',
            [
                'label' => __('Active Circle Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#3b82f6',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar .step.active .circle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'progress_bar_circle_completed_color',
            [
                'label' => __('Completed Circle Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar .step.completed .circle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'progress_bar_circle_text_color',
            [
                'label' => __('Circle Text Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar .circle' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'progress_bar_line_color',
            [
                'label' => __('Progress Line Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar .progress-line' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'progress_bar_label_color',
            [
                'label' => __('Label Text Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4b5563',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar .label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_bar_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_bar_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_bar_align',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .progress-bar' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'progress_bar_label_typography',
                'label' => __('Label Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .print-order-form .progress-bar .label',
            ]
        );

        $this->end_controls_section();

        // Product Section Styles
        $this->start_controls_section(
            'product_style_section',
            [
                'label' => __('Product Section Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'product_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .flex.items-start' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'product_title_color',
            [
                'label' => __('Title Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#1f2937',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card h2' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'product_category_color',
            [
                'label' => __('Category Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4b5563',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .text-gray-600' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_category_margin',
            [
                'label' => __('Category Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .text-gray-600' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_category_padding',
            [
                'label' => __('Category Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .text-gray-600' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_category_align',
            [
                'label' => __('Category Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .text-gray-600' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_image_size',
            [
                'label' => __('Image Size', 'print-order'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 200,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 96,
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card img.w-24' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .flex.items-start' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .flex.items-start' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'product_border',
                'selector' => '{{WRAPPER}} .print-order-form .card .flex.items-start',
            ]
        );

        $this->add_control(
            'product_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card .flex.items-start' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'product_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form .card .flex.items-start',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'product_title_typography',
                'label' => __('Title Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .print-order-form .card h2',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'product_category_typography',
                'label' => __('Category Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .print-order-form .card .text-gray-600',
            ]
        );

        $this->end_controls_section();

        // Pricing Cards Style Section
        $this->start_controls_section(
            'pricing_cards_style_section',
            [
                'label' => __('Pricing Cards Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'pricing_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f9fafb',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .price-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pricing_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#1f2937',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .price-item' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'pricing_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .price-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'pricing_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .price-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'pricing_border',
                'selector' => '{{WRAPPER}} .print-order-form .price-item',
            ]
        );

        $this->add_control(
            'pricing_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 9999,
                    'right' => 9999,
                    'bottom' => 9999,
                    'left' => 9999,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .price-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pricing_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form .price-item',
            ]
        );

        $this->end_controls_section();

        // Step 1 Style Section
        $this->start_controls_section(
            'step_1_style_section',
            [
                'label' => __('Step 1: Print Type Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'step_1_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'background-color: {{VALUE}};',
                    'condition' => [
                        'preview_step' => '1',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'step_1_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_1_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_1_align',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'step_1_border',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->add_control(
            'step_1_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'step_1_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->end_controls_section();

        // Step 2 Style Section
        $this->start_controls_section(
            'step_2_style_section',
            [
                'label' => __('Step 2: Design Info Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'step_2_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'background-color: {{VALUE}};',
                    'condition' => [
                        'preview_step' => '2',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'step_2_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_2_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_2_align',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'step_2_border',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->add_control(
            'step_2_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'step_2_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->end_controls_section();

        // Step 3 Style Section
        $this->start_controls_section(
            'step_3_style_section',
            [
                'label' => __('Step 3: Address Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'step_3_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'background-color: {{VALUE}};',
                    'condition' => [
                        'preview_step' => '3',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'step_3_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_3_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_3_align',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'step_3_border',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->add_control(
            'step_3_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'step_3_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->end_controls_section();

        // Step 4 Style Section
        $this->start_controls_section(
            'step_4_style_section',
            [
                'label' => __('Step 4: Payment Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'step_4_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'background-color: {{VALUE}};',
                    'condition' => [
                        'preview_step' => '4',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'step_4_margin',
            [
                'label' => __('Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_4_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'step_4_align',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'right' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'left' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'step_4_border',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->add_control(
            'step_4_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'step_4_box_shadow',
                'selector' => '{{WRAPPER}} .print-order-form .card',
            ]
        );

        $this->end_controls_section();

        // Buttons Style Section
        $this->start_controls_section(
            'buttons_style_section',
            [
                'label' => __('Buttons Styles', 'print-order'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('button_styles');

        // Normal State
        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'print-order'),
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2563eb',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form button.next-stage' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.submit-order' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .print-order-form button.next-stage' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.submit-order' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .print-order-form button.next-stage, {{WRAPPER}} .print-order-form button.submit-order, {{WRAPPER}} .print-order-form button.prev-stage',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form button.next-stage' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .print-order-form button.submit-order' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form button.next-stage' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .print-order-form button.submit-order' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .print-order-form button.next-stage, {{WRAPPER}} .print-order-form button.submit-order, {{WRAPPER}} .print-order-form button.prev-stage',
            ]
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'print-order'),
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#1e40af',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form button.next-stage:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.submit-order:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form button.next-stage:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.submit-order:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .print-order-form button.next-stage:hover' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.submit-order:hover' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .print-order-form button.prev-stage:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'buttons_margin',
            [
                'label' => __('Buttons Container Margin', 'print-order'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .form-actions .buttons' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'buttons_align',
            [
                'label' => __('Buttons Alignment', 'print-order'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .print-order-form .form-actions .buttons' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        // In frontend, product_id is fetched from URL only
        $product_id = isset($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : '';

        // Localize script with necessary data
        global $wpdb;
        $pricing = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}print_order_pricing", ARRAY_A);
        $pricing_by_category = [];
        foreach ($pricing as $item) {
            $pricing_by_category[$item['category_id']][] = $item;
        }
        wp_localize_script('print-order-order-form', 'printOrder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('print_order_nonce'),
            'public_nonce' => wp_create_nonce('print_order_public_nonce'),
            'temp_id' => wp_generate_uuid4(),
            'pricing' => $pricing_by_category,
            'category_fields' => get_option('print_order_category_fields', []),
            'options' => [
                'tax_rate' => get_option('print_order_tax_rate', 9),
                'login_page_url' => wp_login_url(),
            ],
        ]);

        wp_localize_script('print-order-order-form', 'printOrderWidget', [
            'product_id' => $settings['product_id'] ?: '',
            'preview_step' => $settings['preview_step'] ?: '1',
            'is_editor' => \Elementor\Plugin::$instance->editor->is_edit_mode(),
        ]);

        // Enqueue styles
        wp_enqueue_style('print-order-form', plugin_dir_url(__FILE__) . '../assets/css/class-order-form.css', [], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0');
        wp_enqueue_style('print-order-form-tw', plugin_dir_url(__FILE__) . '../assets/css/class-order-form-tw.css', [], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0');

        // Enqueue scripts with dependencies
        wp_enqueue_script('print-order-react', plugin_dir_url(__FILE__) . '../assets/js/react.min.js', [], '18.3.1', true);
        wp_enqueue_script('print-order-react-dom', plugin_dir_url(__FILE__) . '../assets/js/react-dom.min.js', ['print-order-react'], '18.3.1', true);
        wp_enqueue_script('print-order-data-fetching', plugin_dir_url(__FILE__) . '../assets/js/data-fetching.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-step-one', plugin_dir_url(__FILE__) . '../assets/js/step-one.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-step-two', plugin_dir_url(__FILE__) . '../assets/js/step-two.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-step-three', plugin_dir_url(__FILE__) . '../assets/js/step-three.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-step-four', plugin_dir_url(__FILE__) . '../assets/js/step-four.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-step-navigation', plugin_dir_url(__FILE__) . '../assets/js/step-navigation.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-form-state', plugin_dir_url(__FILE__) . '../assets/js/form-state.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-ui-rendering', plugin_dir_url(__FILE__) . '../assets/js/ui-rendering.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-event-handlers', plugin_dir_url(__FILE__) . '../assets/js/event-handlers.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-custom-fields', plugin_dir_url(__FILE__) . '../assets/js/custom-fields.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-form-pricing', plugin_dir_url(__FILE__) . '../assets/js/form-pricing.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-user-address', plugin_dir_url(__FILE__) . '../assets/js/user-address.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-utils', plugin_dir_url(__FILE__) . '../assets/js/utils.js', ['print-order-react'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-order-form', plugin_dir_url(__FILE__) . '../assets/js/order-form.js', [
            'print-order-react',
            'print-order-react-dom',
            'print-order-data-fetching',
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
        ], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);
        wp_enqueue_script('print-order-widget', plugin_dir_url(__FILE__) . '../assets/js/order-form-widget.js', ['print-order-order-form'], defined('PRINT_ORDER_VERSION') ? PRINT_ORDER_VERSION : '1.0.0', true);

        // Initialize session and temp_id
        if (function_exists('WC') && !WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
            $temp_id = wp_generate_uuid4();
            WC()->session->set('print_order_temp_id', $temp_id);
        } else {
            $temp_id = wp_generate_uuid4();
        }

        ?>
        <div id="print-order-form" class="print-order-form"></div>
        <?php
    }

    protected function content_template() {
        ?>
        <div id="print-order-form" class="print-order-form"></div>
        <?php
    }
}
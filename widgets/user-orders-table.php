<?php
if (!defined('ABSPATH')) {
    exit;
}

class User_Orders_Table_Widget extends \Elementor\Widget_Base {
    private $user_orders;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->user_orders = new Print_Order_User_Orders();
    }

    public function get_name() {
        return 'user_orders_table';
    }

    public function get_title() {
        return __('User Orders Table', 'print-order');
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
                'label' => __('Orders per Page', 'print-order'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 1,
                'max' => 50,
            ]
        );

        $this->add_control(
            'details_page_slug',
            [
                'label' => __('Details Page Slug', 'print-order'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'order-details',
                'description' => __('Enter the slug of the page where the User Orders Details widget is placed (e.g., order-details).', 'print-order'),
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
                    '{{WRAPPER}} .user-orders-table th, {{WRAPPER}} .user-orders-table td' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_bg_color',
            [
                'label' => __('Header Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_text_color',
            [
                'label' => __('Header Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_bg_color_even',
            [
                'label' => __('Even Row Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f9f9f9',
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table tbody tr:nth-child(even)' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_bg_color_odd',
            [
                'label' => __('Odd Row Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table tbody tr:nth-child(odd)' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'cell_padding',
            [
                'label' => __('Cell Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
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
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table th, {{WRAPPER}} .user-orders-table td' => 'padding: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Button
        $this->start_controls_section(
            'style_button_section',
            [
                'label' => __('Details Button Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Button Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table .details-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Button Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table .details-button' => 'color: {{VALUE}};',
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
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .user-orders-table .details-button' => 'border-radius: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .user-orders-table .details-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'pagination_alignment',
            [
                'label' => __('Alignment', 'print-order'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Left', 'print-order'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'print-order'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Right', 'print-order'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'space-between' => [
                        'title' => __('Justify', 'print-order'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .pagination' => 'display: flex; justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_link_color',
            [
                'label' => __('Link Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_bg_color',
            [
                'label' => __('Active/Hover Background', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link.active' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pagination .page-link:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_text_color',
            [
                'label' => __('Active/Hover Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link.active' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pagination .page-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dddddd',
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_font_size',
            [
                'label' => __('Font Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 30,
                        'step' => 1,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 14,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
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
                    'size' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_link_padding',
            [
                'label' => __('Link Padding', 'print-order'),
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
                    '{{WRAPPER}} .pagination .page-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
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
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pagination .page-link' => 'margin: 0 {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $user_id = get_current_user_id();
        if (!$user_id) {
            echo '<p class="no-orders">' . esc_html__('لطفاً وارد حساب کاربری خود شوید.', 'print-order') . '</p>';
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
                error_log('User Orders Table Widget: Enqueued class-user-orders-tw.css with version ' . filemtime($orders_style_path) . '-' . time());
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('User Orders Table Widget: Error - class-user-orders-tw.css not found at ' . $orders_style_path);
            }
        }

        // بارگذاری اسکریپت
        wp_enqueue_script('print-order-user-orders');
        wp_localize_script('print-order-user-orders', 'printOrder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('print_order_nonce'),
        ]);

        $paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));
        $per_page = $settings['per_page'];
        $details_page_slug = !empty($settings['details_page_slug']) ? $settings['details_page_slug'] : 'order-details';

        global $wpdb;
        $total_orders = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM {$wpdb->posts} p
                 JOIN {$wpdb->postmeta} pm_user ON p.ID = pm_user.post_id
                 WHERE p.post_type = 'shop_order'
                 AND pm_user.meta_key = '_customer_user'
                 AND pm_user.meta_value = %d",
                $user_id
            )
        );

        $orders = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_date, pm.meta_value AS order_total
                 FROM {$wpdb->posts} p
                 JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 JOIN {$wpdb->postmeta} pm_user ON p.ID = pm_user.post_id
                 WHERE p.post_type = 'shop_order'
                 AND pm.meta_key = '_order_total'
                 AND pm_user.meta_key = '_customer_user'
                 AND pm_user.meta_value = %d
                 ORDER BY p.post_date DESC
                 LIMIT %d OFFSET %d",
                $user_id,
                $per_page,
                ($paged - 1) * $per_page
            )
        );

        if (empty($orders)) {
            echo '<p class="no-orders">' . esc_html__('سفارشی یافت نشد.', 'print-order') . '</p>';
            return;
        }

        ?>
        <table class="user-orders-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('شماره سفارش', 'print-order'); ?></th>
                    <th><?php esc_html_e('تاریخ', 'print-order'); ?></th>
                    <th><?php esc_html_e('نام محصول', 'print-order'); ?></th>
                    <th><?php esc_html_e('وضعیت سفارش', 'print-order'); ?></th>
                    <th><?php esc_html_e('پیام جدید', 'print-order'); ?></th>
                    <th><?php esc_html_e('جزئیات', 'print-order'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order_data) :
                    $order = wc_get_order($order_data->ID);
                    if (!$order) continue;

                    $product_name = get_the_title($order->get_meta('_print_order_wc_product_id')) ?: esc_html__('نامشخص', 'print-order');
                    $persian_date = $this->user_orders->convert_date_to_persian($order_data->post_date);
                    $status = $this->user_orders->get_current_step($order);
                    $unread_messages = intval($order->get_meta('_print_order_unread_messages') ?? 0);
                ?>
                <tr>
                    <td><?php echo esc_html($order->get_order_number()); ?></td>
                    <td><?php echo esc_html($persian_date['date']); ?></td>
                    <td><?php echo esc_html($product_name); ?></td>
                    <td><?php echo esc_html($status); ?></td>
                    <td>
                        <?php if ($unread_messages > 0) : ?>
                            <span class="unread-count bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"><?php echo esc_html($unread_messages); ?></span>
                        <?php else : ?>
                            <?php esc_html_e('-', 'print-order'); ?>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo esc_url(home_url(trailingslashit($details_page_slug) . '?order_id=' . $order_data->ID)); ?>" class="details-button"><?php esc_html_e('مشاهده جزئیات', 'print-order'); ?></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_orders > $per_page) : ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($total_orders / $per_page);
                for ($i = 1; $i <= $total_pages; $i++) :
                    $link = add_query_arg('paged', $i, home_url(add_query_arg([])));
                ?>
                    <a href="<?php echo esc_url($link); ?>" class="page-link<?php echo $paged == $i ? ' active' : ''; ?>"><?php echo esc_html($i); ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        <?php
    }
}
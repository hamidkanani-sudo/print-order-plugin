<?php
if (!defined('ABSPATH')) {
    exit;
}

class User_Profile_Settings_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'user_profile_settings';
    }

    public function get_title() {
        return __('User Profile Settings', 'print-order');
    }

    public function get_icon() {
        return 'eicon-person';
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
            'show_profile_picture',
            [
                'label' => __('Show Profile Picture', 'print-order'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_password_change',
            [
                'label' => __('Show Password Change', 'print-order'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'print-order'),
                'label_off' => __('No', 'print-order'),
                'default' => 'yes',
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
                    'px' => ['min' => 300, 'max' => 1200, 'step' => 10],
                    '%' => ['min' => 50, 'max' => 100, 'step' => 1],
                    'vw' => ['min' => 50, 'max' => 100, 'step' => 1],
                ],
                'default' => ['unit' => 'px', 'size' => 960],
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-container' => 'max-width: {{SIZE}}{{UNIT}};',
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
                    'top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24,
                    'unit' => 'px', 'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .profile-settings-container' => 'background-color: {{VALUE}};',
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
                    'top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8,
                    'unit' => 'px', 'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Profile Picture
        $this->start_controls_section(
            'style_profile_picture_section',
            [
                'label' => __('Profile Picture Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => ['show_profile_picture' => 'yes'],
            ]
        );

        $this->add_responsive_control(
            'profile_picture_size',
            [
                'label' => __('Picture Size', 'print-order'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 50, 'max' => 200, 'step' => 1],
                ],
                'default' => ['unit' => 'px', 'size' => 100],
                'selectors' => [
                    '{{WRAPPER}} .profile-picture img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'profile_picture_border_radius',
            [
                'label' => __('Border Radius', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50,
                    'unit' => '%', 'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .profile-picture img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section: Form Fields
        $this->start_controls_section(
            'style_form_fields_section',
            [
                'label' => __('Form Fields Style', 'print-order'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'field_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .profile-settings-form input, {{WRAPPER}} .profile-settings-form select, {{WRAPPER}} .profile-settings-form textarea',
                'default' => [
                    'font_size' => ['size' => 14, 'unit' => 'px'],
                ],
            ]
        );

        $this->add_control(
            'field_background_color',
            [
                'label' => __('Background Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f4f4f4',
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-form input, {{WRAPPER}} .profile-settings-form select, {{WRAPPER}} .profile-settings-form textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'field_text_color',
            [
                'label' => __('Text Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-form input, {{WRAPPER}} .profile-settings-form select, {{WRAPPER}} .profile-settings-form textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_padding',
            [
                'label' => __('Padding', 'print-order'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8, 'right' => 12, 'bottom' => 8, 'left' => 12,
                    'unit' => 'px', 'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-form input, {{WRAPPER}} .profile-settings-form select, {{WRAPPER}} .profile-settings-form textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'field_border_color',
            [
                'label' => __('Border Color', 'print-order'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dddddd',
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-form input, {{WRAPPER}} .profile-settings-form select, {{WRAPPER}} .profile-settings-form textarea' => 'border-color: {{VALUE}};',
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
                    '{{WRAPPER}} .profile-settings-btn' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .profile-settings-btn' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .profile-settings-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'print-order'),
                'selector' => '{{WRAPPER}} .profile-settings-btn',
                'default' => [
                    'font_size' => ['size' => 14, 'unit' => 'px'],
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
                    'top' => 8, 'right' => 16, 'bottom' => 8, 'left' => 16,
                    'unit' => 'px', 'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4,
                    'unit' => 'px', 'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .profile-settings-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $is_preview = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $user = wp_get_current_user();

        if (!is_user_logged_in()) {
            echo '<p class="text-red-600">' . esc_html__('لطفاً برای ویرایش پروفایل وارد شوید.', 'print-order') . '</p>';
            return;
        }

        // Get user data
        $user_id = $user->ID;
        $first_name = get_user_meta($user_id, 'first_name', true) ?: '';
        $last_name = get_user_meta($user_id, 'last_name', true) ?: '';
        $email = $user->user_email;
        $billing_phone = get_user_meta($user_id, 'billing_phone', true) ?: '';
        $billing_address_1 = get_user_meta($user_id, 'billing_address_1', true) ?: '';
        $billing_city = get_user_meta($user_id, 'billing_city', true) ?: '';
        $billing_postcode = get_user_meta($user_id, 'billing_postcode', true) ?: '';
        $profile_picture_id = get_user_meta($user_id, 'profile_picture', true);
        $profile_picture_url = $profile_picture_id ? wp_get_attachment_url($profile_picture_id) : get_avatar_url($user_id);

        // Enqueue scripts
        wp_enqueue_script('print-order-profile-settings', PRINT_ORDER_URL . 'assets/js/user-profile-settings.js', ['jquery'], '1.0.0', true);
        wp_localize_script('print-order-profile-settings', 'printOrderProfile', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('profile_settings_nonce'),
        ]);

        // Render form
        ?>
        <div class="profile-settings-container max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <form id="profile-settings-form" class="profile-settings-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                <?php if ($settings['show_profile_picture'] === 'yes'): ?>
                    <div class="profile-picture mb-4">
                        <label class="block text-gray-700 mb-2"><?php _e('عکس پروفایل', 'print-order'); ?></label>
                        <img src="<?php echo esc_url($profile_picture_url); ?>" alt="Profile Picture" class="rounded-full mb-2">
                        <input type="file" name="profile_picture" accept="image/*" class="border p-2 rounded-lg w-full">
                    </div>
                <?php endif; ?>
                <div class="mb-4">
                    <label for="first_name" class="block text-gray-700 mb-2"><?php _e('نام', 'print-order'); ?></label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($first_name); ?>" class="border p-2 rounded-lg w-full">
                </div>
                <div class="mb-4">
                    <label for="last_name" class="block text-gray-700 mb-2"><?php _e('نام خانوادگی', 'print-order'); ?></label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($last_name); ?>" class="border p-2 rounded-lg w-full">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2"><?php _e('ایمیل', 'print-order'); ?></label>
                    <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" class="border p-2 rounded-lg w-full" required>
                </div>
                <div class="mb-4">
                    <label for="billing_phone" class="block text-gray-700 mb-2"><?php _e('شماره موبایل', 'print-order'); ?></label>
                    <input type="text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr($billing_phone); ?>" class="border p-2 rounded-lg w-full">
                </div>
                <div class="mb-4">
                    <label for="billing_address_1" class="block text-gray-700 mb-2"><?php _e('آدرس', 'print-order'); ?></label>
                    <textarea name="billing_address_1" id="billing_address_1" class="border p-2 rounded-lg w-full"><?php echo esc_textarea($billing_address_1); ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="billing_city" class="block text-gray-700 mb-2"><?php _e('شهر', 'print-order'); ?></label>
                    <input type="text" name="billing_city" id="billing_city" value="<?php echo esc_attr($billing_city); ?>" class="border p-2 rounded-lg w-full">
                </div>
                <div class="mb-4">
                    <label for="billing_postcode" class="block text-gray-700 mb-2"><?php _e('کد پستی', 'print-order'); ?></label>
                    <input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo esc_attr($billing_postcode); ?>" class="border p-2 rounded-lg w-full">
                </div>
                <?php if ($settings['show_password_change'] === 'yes'): ?>
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold mb-2"><?php _e('تغییر رمز عبور', 'print-order'); ?></h4>
                        <label for="current_password" class="block text-gray-700 mb-2"><?php _e('رمز عبور فعلی', 'print-order'); ?></label>
                        <input type="password" name="current_password" id="current_password" class="border p-2 rounded-lg w-full">
                        <label for="new_password" class="block text-gray-700 mb-2 mt-2"><?php _e('رمز عبور جدید', 'print-order'); ?></label>
                        <input type="password" name="new_password" id="new_password" class="border p-2 rounded-lg w-full">
                        <label for="confirm_password" class="block text-gray-700 mb-2 mt-2"><?php _e('تأیید رمز عبور جدید', 'print-order'); ?></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="border p-2 rounded-lg w-full">
                    </div>
                <?php endif; ?>
                <button type="button" class="profile-settings-btn save-profile"><?php _e('ذخیره تغییرات', 'print-order'); ?></button>
            </form>
            <div id="profile-message" class="mt-4"></div>
        </div>
        <style>
            .profile-settings-container {
                border: 1px solid #dddddd;
            }
            .profile-settings-form input,
            .profile-settings-form select,
            .profile-settings-form textarea {
                border: 1px solid #dddddd;
                border-radius: 4px;
            }
            .profile-settings-btn {
                display: inline-block;
                text-decoration: none;
                transition: all 0.3s;
            }
            .error-message {
                color: #e74c3c;
            }
            .success-message {
                color: #28a745;
            }
            @media (max-width: 640px) {
                .profile-settings-container {
                    padding: 16px;
                }
            }
        </style>
        <?php
    }

    public static function handle_profile_update() {
        check_ajax_referer('profile_settings_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('لطفاً وارد حساب کاربری خود شوید.', 'print-order')]);
            wp_die();
        }

        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'billing_phone' => sanitize_text_field($_POST['billing_phone'] ?? ''),
            'billing_address_1' => sanitize_textarea_field($_POST['billing_address_1'] ?? ''),
            'billing_city' => sanitize_text_field($_POST['billing_city'] ?? ''),
            'billing_postcode' => sanitize_text_field($_POST['billing_postcode'] ?? ''),
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
        ];

        // Validate email
        if (empty($data['email']) || !is_email($data['email'])) {
            wp_send_json_error(['message' => __('ایمیل معتبر وارد کنید.', 'print-order')]);
            wp_die();
        }

        // Check if email is already used
        if ($data['email'] !== $user->user_email && email_exists($data['email'])) {
            wp_send_json_error(['message' => __('این ایمیل قبلاً استفاده شده است.', 'print-order')]);
            wp_die();
        }

        // Update user data
        $user_data = [
            'ID' => $user_id,
            'user_email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ];
        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('خطا در به‌روزرسانی پروفایل: ', 'print-order') . $result->get_error_message()]);
            wp_die();
        }

        // Update user meta
        update_user_meta($user_id, 'first_name', $data['first_name']);
        update_user_meta($user_id, 'last_name', $data['last_name']);
        update_user_meta($user_id, 'billing_phone', $data['billing_phone']);
        update_user_meta($user_id, 'billing_address_1', $data['billing_address_1']);
        update_user_meta($user_id, 'billing_city', $data['billing_city']);
        update_user_meta($user_id, 'billing_postcode', $data['billing_postcode']);

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('profile_picture', 0);
            if (is_wp_error($attachment_id)) {
                wp_send_json_error(['message' => __('خطا در آپلود عکس پروفایل: ', 'print-order') . $attachment_id->get_error_message()]);
                wp_die();
            }
            update_user_meta($user_id, 'profile_picture', $attachment_id);
        }

        // Handle password change
        if (!empty($data['current_password']) && !empty($data['new_password']) && !empty($data['confirm_password'])) {
            if (!wp_check_password($data['current_password'], $user->user_pass, $user_id)) {
                wp_send_json_error(['message' => __('رمز عبور فعلی نادرست است.', 'print-order')]);
                wp_die();
            }
            if ($data['new_password'] !== $data['confirm_password']) {
                wp_send_json_error(['message' => __('رمز عبور جدید و تأیید آن مطابقت ندارند.', 'print-order')]);
                wp_die();
            }
            wp_set_password($data['new_password'], $user_id);
            wp_send_json_success(['message' => __('پروفایل و رمز عبور با موفقیت به‌روزرسانی شدند.', 'print-order')]);
            wp_die();
        }

        wp_send_json_success(['message' => __('پروفایل با موفقیت به‌روزرسانی شد.', 'print-order')]);
        wp_die();
    }
}

// Register AJAX handler
add_action('wp_ajax_print_order_profile_update', ['User_Profile_Settings_Widget', 'handle_profile_update']);
?>
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Print_Order_PDF_Generator {
    public function __construct() {
        // AJAX handler for PDF generation
        add_action('wp_ajax_print_order_generate_pdf', [$this, 'generate_pdf']);
    }

    public function generate_pdf() {
        check_ajax_referer('pdf_nonce', 'nonce');

        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die('سفارش یافت نشد.');
        }

        if (!class_exists('TCPDF')) {
            wp_die('کتابخانه TCPDF یافت نشد.');
        }

        $options = get_option('print_order_options', []);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(get_bloginfo('name'));
        $pdf->SetTitle($options['pdf_title'] ?? 'پیش‌فاکتور');
        $pdf->SetSubject('فاکتور سفارش');
        $pdf->SetKeywords('فاکتور, سفارش, چاپ');

        // Set header data
        if (!empty($options['pdf_logo'])) {
            $pdf->Image($options['pdf_logo'], 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $pdf->SetHeaderData('', 0, $options['pdf_title'] ?? 'پیش‌فاکتور', get_bloginfo('name'));

        // Set fonts
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        // Header background
        $pdf->SetFillColor(243, 244, 246); // Default: #f3f4f6
        if (!empty($options['pdf_header_color'])) {
            $rgb = $this->hex2rgb($options['pdf_header_color']);
            $pdf->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        }
        $pdf->Rect(0, 0, $pdf->getPageWidth(), 30, 'F');

        // Order details
        $html = '<h1 style="text-align: right;">' . esc_html($options['pdf_title'] ?? 'پیش‌فاکتور') . ' #' . $order->get_order_number() . '</h1>';
        $html .= '<table border="0" cellpadding="5" style="direction: rtl;">';
        $html .= '<tr><td><strong>' . __('مشتری:', 'print-order') . '</strong></td><td>' . esc_html($order->get_billing_first_name()) . '</td></tr>';
        $html .= '<tr><td><strong>' . __('تاریخ:', 'print-order') . '</strong></td><td>' . $order->get_date_created()->date('Y-m-d') . '</td></tr>';
        $html .= '<tr><td><strong>' . __('محصول:', 'print-order') . '</strong></td><td>' . ($order->get_meta('_print_order_wc_product_id') ? get_the_title($order->get_meta('_print_order_wc_product_id')) : '-') . '</td></tr>';
        $html .= '<tr><td><strong>' . __('جنس کاغذ:', 'print-order') . '</strong></td><td>' . esc_html($order->get_meta('_print_order_paper_type')) . '</td></tr>';
        $html .= '<tr><td><strong>' . __('تعداد:', 'print-order') . '</strong></td><td>' . esc_html($order->get_meta('_print_order_quantity')) . '</td></tr>';
        $html .= '<tr><td><strong>' . __('چاپ:', 'print-order') . '</strong></td><td>' . esc_html($order->get_meta('_print_order_sides')) . '</td></tr>';
        $html .= '<tr><td><strong>' . __('قیمت چاپ:', 'print-order') . '</strong></td><td>' . number_format($order->get_meta('_print_order_price')) . ' تومان</td></tr>';
        $html .= '<tr><td><strong>' . __('هزینه طراحی:', 'print-order') . '</strong></td><td>' . number_format($order->get_meta('_print_order_design_fee')) . ' تومان</td></tr>';
        $html .= '<tr><td><strong>' . __('هزینه ارسال:', 'print-order') . '</strong></td><td>' . number_format($order->get_meta('_print_order_shipping_fee')) . ' تومان</td></tr>';
        $html .= '<tr><td><strong>' . __('مالیات:', 'print-order') . '</strong></td><td>' . number_format($order->get_meta('_print_order_tax')) . ' تومان</td></tr>';
        $html .= '<tr><td><strong>' . __('قیمت نهایی:', 'print-order') . '</strong></td><td>' . number_format($order->get_total()) . ' تومان</td></tr>';
        $html .= '</table>';

        // Extra fields
        $extra_data = $order->get_meta('_print_order_extra_data');
        if (!empty($extra_data) && is_array($extra_data)) {
            $html .= '<h2 style="text-align: right;">' . __('جزئیات اضافی', 'print-order') . '</h2>';
            $html .= '<table border="0" cellpadding="5" style="direction: rtl;">';
            $category_fields = get_option('print_order_category_fields', []);
            $category = $order->get_meta('_print_order_category');
            $fields = $category_fields[$category] ?? [];
            foreach ($extra_data as $key => $value) {
                $label = $key;
                foreach ($fields as $field) {
                    if ($field['name'] === $key) {
                        $label = $field['label'];
                        break;
                    }
                }
                $html .= '<tr><td><strong>' . esc_html($label) . ':</strong></td><td>' . esc_html($value) . '</td></tr>';
            }
            $html .= '</table>';
        }

        // Footer
        if (!empty($options['pdf_footer'])) {
            $html .= '<p style="text-align: right; margin-top: 20px;">' . esc_html($options['pdf_footer']) . '</p>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('invoice-' . $order->get_order_number() . '.pdf', 'D');
        wp_die();
    }

    private function hex2rgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }
}
?>
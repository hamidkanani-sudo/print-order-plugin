jQuery(document).ready(function($) {
    // Tab switching
    $('.tab-link').on('click', function(e) {
        e.preventDefault();
        const tabId = $(this).data('tab');

        $('.tab-link').removeClass('active');
        $('.tab-content').addClass('hidden');

        $(this).addClass('active');
        $('#' + tabId).removeClass('hidden');

        console.log('Tab clicked: ' + tabId);

        // Mark messages as read when chat tab is opened
        if (tabId === 'chat') {
            const orderId = $('.chat-container').data('order-id');
            $.ajax({
                url: printOrderAdmin.ajax_url,
                method: 'POST',
                data: {
                    action: 'print_order_mark_messages_read',
                    nonce: printOrderAdmin.nonce,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        $('.unread-count').remove();
                    } else {
                        console.error('Failed to mark messages as read:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error marking messages as read:', error);
                }
            });
        }
    });

    // Copy buttons
    $('.btn-copy').on('click', function() {
        const text = $(this).data('copy');
        navigator.clipboard.writeText(text).then(() => {
            alert('متن کپی شد!');
        }).catch(err => {
            console.error('Failed to copy text:', err);
        });
    });

    // Upload design file and response
    $('.upload-design-btn').on('click', function() {
        const orderId = $(this).data('order-id');
        const $form = $(this).closest('.revision-form');
        const $fileInput = $form.find('#design_file');
        const $responseText = $form.find('textarea[name="admin_response"]');
        const $progressBar = $form.find('#upload_progress');
        const formData = new FormData();

        formData.append('action', 'print_order_admin_upload_design');
        formData.append('nonce', printOrderAdmin.nonce);
        formData.append('order_id', orderId);
        formData.append('response_text', $responseText.val());

        if ($fileInput[0].files.length > 0) {
            formData.append('design_file', $fileInput[0].files[0]);
        }

        // Show progress bar
        $progressBar.removeClass('hidden').val(0);

        $.ajax({
            url: printOrderAdmin.ajax_url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function() {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        $progressBar.val(percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $progressBar.addClass('hidden');
                if (response.success) {
                    const $chatContainer = $('.chat-container');
                    const adminName = printOrderAdmin.adminDisplayName || 'ادمین';
                    // Generate secure download URL
                    const downloadUrl = response.data.new_design_file ? 
                        `${printOrderAdmin.ajax_url}?action=print_order_download_private_file&order_id=${orderId}&file=${encodeURIComponent(response.data.new_design_file)}&nonce=${printOrderAdmin.download_nonce}` : '';
                    const messageHtml = `
                        <div class="chat-message admin">
                            <span>${adminName}: ${response.data.response_text}</span>
                            <span class="chat-time">${response.data.response_date}</span>
                            ${response.data.new_design_file ? `
                                <img src="${response.data.new_design_file}" alt="Design Thumbnail" class="chat-thumbnail">
                                <a href="${downloadUrl}" class="chat-download">دانلود</a>
                            ` : ''}
                        </div>
                    `;
                    $chatContainer.append(messageHtml);
                    $responseText.val('');
                    $fileInput.val('');
                    alert(response.data.message);
                    // Update revision count
                    const revisionCount = parseInt($('#revision-count').text()) + (response.data.response_text || response.data.new_design_file ? 1 : 0);
                    $('#revision-count').text(revisionCount);
                    $('#remaining-revisions').text(Math.max(0, parseInt($('#remaining-revisions').text()) - (response.data.response_text || response.data.new_design_file ? 1 : 0)));
                } else {
                    console.error('خطا در ارسال:', response.data.message);
                    alert('خطا در ارسال پاسخ. لطفاً دوباره تلاش کنید.');
                }
            },
            error: function(xhr, status, error) {
                $progressBar.addClass('hidden');
                console.error('خطا در ارسال:', error);
                alert('خطا در ارسال پاسخ. لطفاً دوباره تلاش کنید.');
            }
        });
    });

    // Increase revisions button
    $('.increase-revisions-btn').on('click', function() {
        const orderId = $(this).data('order-id');
        $.ajax({
            url: printOrderAdmin.ajax_url,
            method: 'POST',
            data: {
                action: 'print_order_increase_revisions',
                nonce: printOrderAdmin.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    $('#remaining-revisions').text(response.data.remaining_revisions);
                    alert(response.data.message);
                } else {
                    console.error('Failed to increase revisions:', response.data.message);
                    alert('خطا در افزایش تعداد درخواست‌های مجاز. لطفاً دوباره تلاش کنید.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error increasing revisions:', error);
                alert('خطا در افزایش تعداد درخواست‌های مجاز. لطفاً دوباره تلاش کنید.');
            }
        });
    });
});
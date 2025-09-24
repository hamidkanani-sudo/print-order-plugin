jQuery(document).ready(function ($) {
    // Initialize tabs for the order details
    function initializeTabs(orderId) {
        $('.tab').on('click', function () {
            var $this = $(this);
            $this.addClass('active').siblings().removeClass('active');
            loadTabContent(orderId, $this.data('tab'));
            if ($this.data('tab') === 'design' && printOrderUser.unread_messages > 0) {
                $.ajax({
                    url: printOrderUser.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'print_order_update_order',
                        nonce: printOrderUser.nonce,
                        order_id: orderId,
                        action_type: 'mark_messages_read'
                    },
                    success: function (response) {
                        if (response.success) {
                            printOrderUser.unread_messages = 0;
                            updateUnreadCount(orderId);
                        }
                    },
                    error: function (xhr) {
                        console.log('Error marking messages as read:', xhr.responseText);
                    }
                });
            }
        });
    }

    // Load content for a specific tab
    function loadTabContent(orderId, tab) {
        var content = $('#tab-content-' + orderId);
        var $errorMessage = $('.print-order-user-orders').find('.error-message');
        content.addClass('loading-overlay active').html('در حال بارگذاری ...');
        $errorMessage.addClass('hidden');

        $.ajax({
            url: printOrderUser.ajax_url,
            type: 'POST',
            data: {
                action: 'print_order_get_order_details',
                nonce: printOrderUser.nonce,
                order_id: orderId,
                tab: tab
            },
            success: function (response) {
                console.log('Tab response:', response);
                if (response.success) {
                    content.html(response.data.html).removeClass('loading-overlay active');
                    if (tab === 'design') {
                        initializeDesignTab(orderId);
                        var $chatContainer = $('.chat-container');
                        if ($chatContainer.length > 0 && $chatContainer[0]) {
                            $chatContainer.scrollTop($chatContainer[0].scrollHeight);
                        }
                    } else if (tab === 'shipping') {
                        initializeShippingTab(orderId);
                    }
                } else {
                    console.log('Tab load error:', response.data);
                    content.removeClass('loading-overlay active');
                    $errorMessage.text(response.data.message || 'خطا در بارگذاری محتوای تب.').removeClass('hidden');
                }
            },
            error: function (xhr) {
                console.log('Error loading tab - Status:', xhr.status, 'Response:', xhr.responseText);
                content.removeClass('loading-overlay active');
                $errorMessage.text('خطا در ارتباط با سرور: ' + xhr.status).removeClass('hidden');
            }
        });
    }

    // Initialize steps for order progress
    function initializeSteps(orderId) {
        var $steps = $('.order-steps');
        var activeStep = $steps.find('.step-circle.active').index();
        if (activeStep >= 0) {
            $steps[0].scrollLeft = activeStep * 70;
            if (window.innerWidth <= 640) {
                var containerWidth = $steps.width();
                var stepWidth = $steps.find('.step-container').outerWidth();
                $steps[0].scrollLeft = (activeStep * stepWidth) - (containerWidth / 2) + (stepWidth / 2);
            }
        }
    }

    // Initialize accordion for financial details on mobile
    function initializeAccordion() {
        if (window.innerWidth <= 640) {
            $('.financial-details').each(function () {
                var $details = $(this).find('.financial-table tr');
                if ($details.length > 1) {
                    var $accordion = $('<div class="accordion"><div class="accordion-summary">جزییات مالی</div><div class="accordion-details"></div></div>');
                    var $accordionTable = $('<table class="accordion-table"></table>');
                    $details.each(function () {
                        var $row = $(this).clone();
                        $accordionTable.append($row);
                    });
                    $accordion.find('.accordion-details').append($accordionTable);
                    $(this).html($accordion);
                }
            });

            $('.accordion-summary').on('click', function () {
                $(this).parent().toggleClass('active');
            });
        }
    }

    // Initialize design tab interactions
    function initializeDesignTab(orderId) {
        var $designTab = $('#tab-content-' + orderId).find('.design-actions');
        var designConfirmed = printOrderUser.design_confirmed;

        if (designConfirmed === 'yes') {
            $designTab.find('.print-order-revision-btn').remove();
        }

        $('.print-order-confirm-btn').on('click', function () {
            var $btn = $(this);
            $btn.addClass('loading').prop('disabled', true).html('تأیید طرح <span class="spinner"></span>');
            var $form = $btn.closest('form');
            $.ajax({
                url: printOrderUser.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=print_order_update_order&action_type=confirm_design&order_id=' + orderId + '&nonce=' + printOrderUser.nonce,
                timeout: 20000,
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        window.location.href = printOrderUser.base_url + orderId; // Use base_url for reload
                    } else {
                        alert(response.data.message || 'خطا در تأیید طرح.');
                    }
                },
                error: function (xhr, status) {
                    alert('خطا در تأیید طرح: ' + (status === 'timeout' ? 'زمان انتظار به پایان رسید' : xhr.responseText));
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false).html('تأیید طرح');
                }
            });
        });

        $('.print-order-revision-btn').on('click', function () {
            $(this).closest('.design-actions').find('.revision-form').removeClass('hidden').addClass('active');
        });

        $('.cancel-revision').on('click', function () {
            $(this).closest('.revision-form').removeClass('active').addClass('hidden');
        });

        $('.submit-revision').on('click', function () {
            var $form = $(this).closest('form');
            var $fileInput = $form.find('input[type="file"]');
            var revisionNote = $form.find('textarea').val();
            var $chatContainer = $form.closest('.tab-content-container').find('.chat-container');
            var $progress = $form.find('#upload_progress');
            var $errorMessage = $('.print-order-user-orders').find('.error-message');

            if (!revisionNote) {
                alert('لطفاً توضیحات درخواست ویرایش را وارد کنید.');
                return;
            }

            var $message = $('<div class="chat-message user new-message"><span>' + $('<div>').text(revisionNote).html() + '</span><span class="chat-time loading" data-order-id="' + orderId + '" data-revision-note="' + encodeURIComponent(revisionNote) + '">در حال ارسال...</span></div>');
            $chatContainer.append($message);
            if ($chatContainer.length > 0 && $chatContainer[0]) {
                $chatContainer.scrollTop($chatContainer[0].scrollHeight);
            }

            // Handle file upload if a file is selected
            if ($fileInput[0].files.length > 0) {
                var formData = new FormData();
                formData.append('action', 'print_order_user_upload_design');
                formData.append('nonce', printOrderUser.nonce);
                formData.append('order_id', orderId);
                formData.append('design_file', $fileInput[0].files[0]);

                $progress.removeClass('hidden').val(0);
                $.ajax({
                    url: printOrderUser.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function (e) {
                            if (e.lengthComputable) {
                                var percent = Math.round((e.loaded / e.total) * 100);
                                $progress.val(percent);
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (response) {
                        if (response.success) {
                            $progress.addClass('hidden');
                            var fileExtension = response.data.file_name.split('.').pop().toLowerCase();
                            var iconUrl = printOrderUser.ajax_url.replace('admin-ajax.php', '') + 'wp-content/plugins/print-order/assets/icons/' + (['jpg', 'jpeg', 'png', 'pdf'].includes(fileExtension) ? fileExtension : 'file') + '.svg';
                            $message.append('<div class="file-container"><img src="' + response.data.file_url + '" alt="User Uploaded File" class="chat-thumbnail"><div class="file-info"><img src="' + iconUrl + '" alt="File Icon" class="file-icon"><a href="' + response.data.file_url + '" class="chat-download">دانلود</a></div></div>');
                            sendRevision($form, $message, $chatContainer, orderId, revisionNote);
                        } else {
                            $progress.addClass('hidden');
                            $message.find('.chat-time').removeClass('loading').html('خطا در آپلود فایل: ' + response.data.message + ' <a href="#" class="retry-link">تلاش دوباره</a>');
                            $errorMessage.text(response.data.message || 'خطا در آپلود فایل.').removeClass('hidden');
                            $message.find('.retry-link').on('click', function (e) {
                                e.preventDefault();
                                $message.find('.chat-time').addClass('loading').text('در حال ارسال...');
                                $('.submit-revision').trigger('click');
                            });
                        }
                    },
                    error: function (xhr, status) {
                        $progress.addClass('hidden');
                        $message.find('.chat-time').removeClass('loading').html('خطا در آپلود فایل: ' + (status === 'timeout' ? 'زمان انتظار به پایان رسید' : xhr.responseText) + ' <a href="#" class="retry-link">تلاش دوباره</a>');
                        $errorMessage.text('خطا در ارتباط با سرور: ' + xhr.status).removeClass('hidden');
                        $message.find('.retry-link').on('click', function (e) {
                            e.preventDefault();
                            $message.find('.chat-time').addClass('loading').text('در حال ارسال...');
                            $('.submit-revision').trigger('click');
                        });
                    }
                });
            } else {
                sendRevision($form, $message, $chatContainer, orderId, revisionNote);
            }
        });

        function sendRevision($form, $message, $chatContainer, orderId, revisionNote) {
            var $errorMessage = $('.print-order-user-orders').find('.error-message');
            $.ajax({
                url: printOrderUser.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=print_order_update_order&action_type=revision_request&order_id=' + orderId + '&revision_note=' + encodeURIComponent(revisionNote) + '&nonce=' + printOrderUser.nonce,
                timeout: 20000,
                success: function (response) {
                    if (response.success) {
                        var $timeSpan = $message.find('.chat-time');
                        $timeSpan.removeClass('loading').text('✓ ' + getFriendlyTime(Date.now() / 1000));
                        $form.removeClass('active').addClass('hidden');
                        $form.find('textarea').val('');
                        $form.find('input[type="file"]').val('');
                        alert(response.data.message);
                        if (response.data.notification) {
                            var remaining = response.data.remaining_revisions;
                            $('.revision-remaining-circle').text(remaining);
                            $('.revision-limit-warning').text('توجه: شما تنها ' + remaining + ' درخواست ویرایش باقی‌مانده دارید!');
                            if (remaining <= 0) {
                                $('.print-order-revision-btn').remove();
                                $('.revision-form').before('<p class="text-red-600">حداکثر تعداد درخواست‌ها تکمیل شده است.</p>');
                            }
                        }
                        if ($chatContainer.length > 0 && $chatContainer[0]) {
                            $chatContainer.scrollTop($chatContainer[0].scrollHeight);
                        }
                    } else {
                        $message.find('.chat-time').removeClass('loading').html('خطا: ' + response.data.message + ' <a href="#" class="retry-link">تلاش دوباره</a>');
                        $errorMessage.text(response.data.message || 'خطا در ارسال درخواست ویرایش.').removeClass('hidden');
                        $message.find('.retry-link').on('click', function (e) {
                            e.preventDefault();
                            $message.find('.chat-time').addClass('loading').text('در حال ارسال...');
                            sendRevision($form, $message, $chatContainer, orderId, revisionNote);
                        });
                    }
                },
                error: function (xhr, status) {
                    $message.find('.chat-time').removeClass('loading').html('خطا: ' + (status === 'timeout' ? 'زمان انتظار به پایان رسید' : xhr.responseText) + ' <a href="#" class="retry-link">تلاش دوباره</a>');
                    $errorMessage.text('خطا در ارتباط با سرور: ' + xhr.status).removeClass('hidden');
                    $message.find('.retry-link').on('click', function (e) {
                        e.preventDefault();
                        $message.find('.chat-time').addClass('loading').text('در حال ارسال...');
                        sendRevision($form, $message, $chatContainer, orderId, revisionNote);
                    });
                }
            });
        }

        function getFriendlyTime(timestamp) {
            var diff = Math.floor((Date.now() / 1000) - timestamp);
            if (diff < 60) return diff + ' ثانیه پیش';
            if (diff < 3600) return Math.floor(diff / 60) + ' دقیقه پیش';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ساعت پیش';
            var date = new Date(timestamp * 1000);
            return date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + ('0' + date.getDate()).slice(-2);
        }
    }

    // Initialize shipping tab interactions
    function initializeShippingTab(orderId) {
        $('.shipping-submit').on('click', function () {
            var $btn = $(this);
            var $form = $btn.closest('form');
            var $errorMessage = $('.print-order-user-orders').find('.error-message');
            $btn.addClass('loading').prop('disabled', true).html('ثبت اطلاعات ارسال <span class="spinner"></span>');
            $errorMessage.addClass('hidden');
            $.ajax({
                url: printOrderUser.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=print_order_update_order&action_type=shipping_update&order_id=' + orderId + '&nonce=' + printOrderUser.nonce,
                timeout: 20000,
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        loadTabContent(orderId, 'shipping');
                    } else {
                        $errorMessage.text(response.data.message || 'خطا در ثبت اطلاعات ارسال.').removeClass('hidden');
                    }
                },
                error: function (xhr, status) {
                    $errorMessage.text('خطا در ارتباط با سرور: ' + xhr.status).removeClass('hidden');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false).html('ثبت اطلاعات ارسال');
                }
            });
        });

        $('.copy-btn').on('click', function () {
            var text = $(this).data('clipboard-text');
            navigator.clipboard.writeText(text).then(function () {
                alert('کد رهگیری کپی شد!');
            }, function () {
                alert('خطا در کپی کد رهگیری.');
            });
        });
    }

    // Update unread messages count
    function updateUnreadCount(orderId) {
        var $tab = $('.tab[data-tab="design"][data-order-id="' + orderId + '"]');
        $tab.find('.unread-count').remove();
    }

    // Handle browser history navigation
    window.addEventListener('popstate', function (event) {
        var urlParams = new URLSearchParams(window.location.search);
        var orderId = urlParams.get('order_id');
        if (orderId && window.location.href.includes(printOrderUser.base_url)) {
            initializeTabs(orderId);
            initializeSteps(orderId);
            initializeAccordion();
            initializeDesignTab(orderId);
            initializeShippingTab(orderId);
            var $chatContainer = $('.chat-container');
            if ($chatContainer.length > 0 && $chatContainer[0]) {
                $chatContainer.scrollTop($chatContainer[0].scrollHeight);
            }
        } else {
            $('.print-order-user-orders').find('.error-message').text('شناسه سفارش نامعتبر است یا آدرس صفحه نادرست است.').removeClass('hidden');
        }
    });

    // Initial setup
    if (typeof printOrderUser === 'undefined') {
        console.error('printOrderUser is not defined. Ensure the script is enqueued and localized properly.');
        $('.print-order-user-orders').find('.error-message').text('خطا: تنظیمات اسکریپت بارگذاری نشده است. لطفاً صفحه را رفرش کنید یا با پشتیبانی تماس بگیرید.').removeClass('hidden');
        return;
    }

    var urlParams = new URLSearchParams(window.location.search);
    var orderId = urlParams.get('order_id') || printOrderUser.order_id;
    if (orderId && window.location.href.includes(printOrderUser.base_url)) {
        initializeTabs(orderId);
        initializeSteps(orderId);
        initializeAccordion();
        initializeDesignTab(orderId);
        initializeShippingTab(orderId);
        var $chatContainer = $('.chat-container');
        if ($chatContainer.length > 0 && $chatContainer[0]) {
            $chatContainer.scrollTop($chatContainer[0].scrollHeight);
        }
        if (printOrderUser.unread_messages > 0) {
            var activeTab = $('.tab.active').data('tab') || 'details';
            if (activeTab === 'design') {
                $.ajax({
                    url: printOrderUser.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'print_order_update_order',
                        nonce: printOrderUser.nonce,
                        order_id: orderId,
                        action_type: 'mark_messages_read'
                    },
                    success: function (response) {
                        if (response.success) {
                            printOrderUser.unread_messages = 0;
                            updateUnreadCount(orderId);
                        }
                    },
                    error: function (xhr) {
                        console.log('Error marking messages as read:', xhr.responseText);
                    }
                });
            }
        }
    } else {
        $('.print-order-user-orders').find('.error-message').text('شناسه سفارش یافت نشد یا آدرس صفحه نادرست است.').removeClass('hidden');
    }
});
jQuery(document).ready(function($) {
    $('.profile-settings-btn.save-profile').on('click', function(e) {
        e.preventDefault();
        var form = $('#profile-settings-form');
        var formData = new FormData(form[0]);
        formData.append('action', 'print_order_profile_update');
        formData.append('nonce', printOrderProfile.nonce);

        $.ajax({
            url: printOrderProfile.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var messageDiv = $('#profile-message');
                if (response.success) {
                    messageDiv.html('<p class="success-message">' + response.data.message + '</p>');
                } else {
                    messageDiv.html('<p class="error-message">' + response.data.message + '</p>');
                }
                setTimeout(function() {
                    messageDiv.html('');
                }, 5000);
            },
            error: function() {
                $('#profile-message').html('<p class="error-message"><?php _e('خطای سرور رخ داد.', 'print-order'); ?></p>');
                setTimeout(function() {
                    $('#profile-message').html('');
                }, 5000);
            }
        });
    });
});
jQuery(document).ready(function ($) {
    // Initialize accordions as closed
    function initializeAccordions() {
        $('.accordion-wrapper').removeClass('active');
        $('.accordion-row').removeClass('active');
    }

    // Function to load transactions
    function loadTransactions(page, status, sort, perPage) {
        var container = $('.print-order-user-transactions');
        container.find('.loading-overlay').addClass('active');
        container.find('.loading-spinner').show();
        container.find('.error-message').hide();

        $.ajax({
            url: printOrder.ajax_url,
            type: 'POST',
            data: {
                action: 'load_transactions_page',
                nonce: printOrder.nonce,
                paged: page,
                status: status,
                sort: sort,
                per_page: perPage
            },
            success: function (response) {
                if (response.success) {
                    container.html(response.data.html);
                    initializeAccordions(); // Re-initialize accordions after AJAX load
                } else {
                    container.find('.error-message').text(response.data.message || 'خطایی رخ داد.').show();
                }
            },
            error: function (xhr, status, error) {
                container.find('.error-message').text('خطایی در ارتباط با سرور رخ داد: ' + error).show();
            },
            complete: function () {
                container.find('.loading-overlay').removeClass('active');
                container.find('.loading-spinner').hide();
            }
        });
    }

    // Handle pagination clicks
    $(document).on('click', '.pagination-link', function (e) {
        e.preventDefault();
        var $this = $(this);
        var page = $this.data('page');
        var paginationContainer = $('.pagination-container');
        var status = paginationContainer.data('status') || '';
        var sort = paginationContainer.data('sort') || 'date_desc';
        var perPage = paginationContainer.data('per-page') || 10;

        loadTransactions(page, status, sort, perPage);
    });

    // Handle filter changes
    $(document).on('change', '.filter-select', function () {
        var $this = $(this);
        var filterType = $this.data('filter-type');
        var value = $this.val();
        var paginationContainer = $('.pagination-container');
        var status = paginationContainer.data('status') || '';
        var sort = paginationContainer.data('sort') || 'date_desc';
        var perPage = paginationContainer.data('per-page') || 10;

        // Update the appropriate filter
        if (filterType === 'status') {
            status = value;
        } else if (filterType === 'sort') {
            sort = value;
        }

        // Reset to page 1 when filters change
        loadTransactions(1, status, sort, perPage);

        // Update data attributes to reflect new filter values
        paginationContainer.data('status', status).attr('data-status', status);
        paginationContainer.data('sort', sort).attr('data-sort', sort);
    });

    // Handle accordion toggle
    $(document).on('click', '.accordion-toggle', function () {
        var $this = $(this);
        var orderId = $this.data('order-id');
        var $accordion = $('#accordion-' + orderId);
        var $accordionRow = $('.accordion-row[data-order-id="' + orderId + '"]');

        if ($accordion.length && $accordionRow.length) {
            console.log('Toggling accordion for orderId:', orderId);
            // Toggle active class
            if ($accordion.hasClass('active')) {
                $accordion.removeClass('active');
                $accordionRow.removeClass('active');
            } else {
                // Close other open accordions
                $('.accordion-wrapper').removeClass('active');
                $('.accordion-row').removeClass('active');
                $accordion.addClass('active');
                $accordionRow.addClass('active');
            }
        } else {
            console.log('Element not found: accordion-' + orderId + ' or accordion-row-' + orderId);
        }
    });

    // Handle accordion close
    $(document).on('click', '.close-accordion', function () {
        var $accordion = $(this).closest('.accordion-wrapper');
        var $accordionRow = $accordion.closest('.accordion-row');
        $accordion.removeClass('active');
        $accordionRow.removeClass('active');
    });

    // Initialize on page load
    initializeAccordions();
});
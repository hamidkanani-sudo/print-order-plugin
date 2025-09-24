jQuery(document).ready(function ($) {
    function initializeProgressBars() {
        $('.order-progress-card .order-steps').each(function () {
            var $steps = $(this);
            var activeStep = $steps.find('.step-circle.active').index();
            if (activeStep >= 0) {
                var stepWidth = $steps.find('.step-container').outerWidth() || 70;
                var containerWidth = $steps.width();
                $steps[0].scrollLeft = activeStep * stepWidth;
                if (window.innerWidth <= 640) {
                    $steps[0].scrollLeft = (activeStep * stepWidth) - (containerWidth / 2) + (stepWidth / 2);
                }
                console.log('Initialized progress bar: activeStep=' + activeStep + ', stepWidth=' + stepWidth);
            } else {
                console.warn('No active step found in progress bar');
            }
        });
    }

    initializeProgressBars();
});
(function (global, React) {
    'use strict';

    global.PrintOrderForm = global.PrintOrderForm || {};
    global.PrintOrderForm.eventHandlers = global.PrintOrderForm.eventHandlers || {};

    global.PrintOrderForm.eventHandlers.setupEventHandlers = function (currentStep, stepError) {
        React.useEffect(() => {
            const inputs = document.querySelectorAll('.order-form input, .order-form select, .order-form textarea');
            const buttons = document.querySelectorAll('.next-stage, .prev-stage, .submit-order, #guide-button, #close-drawer');
            const handleFocus = (e) => e.target.parentElement.classList.add('focused');
            const handleBlur = (e) => e.target.parentElement.classList.remove('focused');
            const handleButtonClick = (e) => {
                if (!e.target.closest('.file-item button')) {
                    e.target.classList.add('clicked');
                    setTimeout(() => e.target.classList.remove('clicked'), 300);
                }
            };
            inputs.forEach(input => {
                input.addEventListener('focus', handleFocus);
                input.addEventListener('blur', handleBlur);
            });
            buttons.forEach(button => {
                button.addEventListener('click', handleButtonClick);
            });
            return () => {
                inputs.forEach(input => {
                    input.removeEventListener('focus', handleFocus);
                    input.removeEventListener('blur', handleBlur);
                });
                buttons.forEach(button => {
                    button.removeEventListener('click', handleButtonClick);
                });
            };
        }, [currentStep]);

        React.useEffect(() => {
            if (stepError) {
                const inputs = document.querySelectorAll('.order-form input[required], .order-form select[required], .order-form textarea[required]');
                inputs.forEach(input => {
                    if (!input.value) {
                        input.classList.add('error', 'animate-shake');
                        setTimeout(() => input.classList.remove('animate-shake'), 300);
                    }
                });
            }
        }, [stepError]);

        React.useEffect(() => {
            const progressLine = document.querySelector('.progress-line');
            if (progressLine) {
                const progressWidth = ((currentStep - 1) / 3) * 100;
                progressLine.style.width = `${progressWidth}%`;
                if (currentStep > 1) {
                    progressLine.classList.add('active');
                } else {
                    progressLine.classList.remove('active');
                }
            }
        }, [currentStep]);
    };
})(window, window.React);
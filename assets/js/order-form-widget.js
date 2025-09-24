(function () {
    // بررسی زودهنگام وجود عنصر #print-order-form
    if (!document.getElementById('print-order-form')) {
        console.error('PrintOrderWidget: No #print-order-form element found, skipping initialization');
        return;
    }

    // پرچم برای جلوگیری از اجرای چندباره
    let isInitialized = false;

    // Function to initialize the widget
    function initializeWidget() {
        if (isInitialized) {
            return;
        }
        isInitialized = true;

        if (!window.React || !window.ReactDOM) {
            console.error('PrintOrderWidget: React or ReactDOM not loaded');
            return;
        }

        if (!window.printOrderWidget) {
            console.error('PrintOrderWidget: printOrderWidget is not defined');
            return;
        }

        // Ensure PrintOrderForm is loaded
        if (!window.PrintOrderForm || !window.PrintOrderFormLoaded || !window.PrintOrderForm.PrintOrderForm) {
            console.error('PrintOrderWidget: PrintOrderForm or PrintOrderForm.PrintOrderForm not loaded');
            return;
        }

        // Override setupGuideDrawer to disable mobile guide drawer
        window.PrintOrderForm.guideDrawer = window.PrintOrderForm.guideDrawer || {};
        window.PrintOrderForm.guideDrawer.setupGuideDrawer = () => {};

        // Set product_id for editor preview
        if (window.printOrderWidget.is_editor && window.printOrderWidget.product_id) {
            const url = new URL(window.location.href);
            url.searchParams.set('product_id', window.printOrderWidget.product_id);
            window.history.replaceState({}, '', url.toString());
        }
    }

    // Function to attempt initialization with retries
    function attemptInitialization(attempts = 5, delay = 500) {
        if (isInitialized) {
            return;
        }
        if (attempts <= 0) {
            console.error('PrintOrderWidget: Failed to initialize after multiple attempts');
            return;
        }
        setTimeout(() => {
            if (document.getElementById('print-order-form') && window.PrintOrderForm?.PrintOrderForm && window.printOrderWidget) {
                initializeWidget();
            } else {
                console.warn(`PrintOrderWidget: DOM, PrintOrderForm, or printOrderWidget not ready, retrying (${attempts - 1} attempts left)`);
                attemptInitialization(attempts - 1, delay * 1.2);
            }
        }, delay);
    }

    // Run initialization only when DOM is fully loaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        attemptInitialization();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            attemptInitialization();
        });
    }
})();
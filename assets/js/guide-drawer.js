(function (global, React) {
    'use strict';

    global.PrintOrderForm = global.PrintOrderForm || {};
    global.PrintOrderForm.guideDrawer = global.PrintOrderForm.guideDrawer || {};

    global.PrintOrderForm.guideDrawer.setupGuideDrawer = function (isGuideOpen, setIsGuideOpen, shortcodeContent, loadingTemplate, product, fetchTemplateShortcode, formData, currentStep) {
        React.useEffect(() => {
            const shortcodeContainerMobile = document.getElementById('print-order-shortcode-mobile');
            const shortcodeContainer = document.getElementById('print-order-shortcode');
            if (shortcodeContainerMobile && shortcodeContainer && isGuideOpen) {
                shortcodeContainerMobile.innerHTML = '';
                if (loadingTemplate) {
                    shortcodeContainerMobile.innerHTML = `
                        <div class="flex justify-center items-center h-full">
                            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 animate-spin" style="border-top-color: #3498db;"></div>
                            <span class="mr-3 text-gray-600">در حال بارگذاری...</span>
                        </div>
                    `;
                } else if (shortcodeContainer.innerHTML && shortcodeContainer.innerHTML.length > 0) {
                    shortcodeContainerMobile.innerHTML = shortcodeContainer.innerHTML;
                } else {
                    shortcodeContainerMobile.innerHTML = '<p class="text-gray-600 text-center">محتوای راهنما در دسترس نیست.</p>';
                }
            } else if (shortcodeContainerMobile && !isGuideOpen) {
                shortcodeContainerMobile.innerHTML = '';
            }
        }, [shortcodeContent, loadingTemplate, isGuideOpen]);

        React.useEffect(() => {
            const setupEventListeners = () => {
                const guideButton = document.getElementById('guide-button');
                const closeButton = document.getElementById('close-drawer');
                const guideDrawer = document.getElementById('guide-drawer');
                if (!guideButton || !closeButton || !guideDrawer) return;

                const toggleGuide = () => {
                    if (isGuideOpen) {
                        setIsGuideOpen(false);
                        guideDrawer.classList.remove('open');
                        guideButton.classList.remove('open');
                    } else {
                        setIsGuideOpen(true);
                        guideDrawer.classList.add('open');
                        guideButton.classList.add('open');
                        if (shortcodeContainer && shortcodeContainer.innerHTML.length === 0 && !loadingTemplate && product && product.category_id) {
                            fetchTemplateShortcode(product.category_id, formData.paper_type_persian);
                        }
                    }
                };

                const closeGuide = () => {
                    setIsGuideOpen(false);
                    guideDrawer.classList.remove('open');
                    guideButton.classList.remove('open');
                };

                guideButton.addEventListener('click', toggleGuide);
                guideButton.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    toggleGuide();
                });

                closeButton.addEventListener('click', closeGuide);
                closeButton.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    closeGuide();
                });

                guideDrawer.addEventListener('click', (e) => {
                    if (e.target === guideDrawer) closeGuide();
                });

                return () => {
                    guideButton.removeEventListener('click', toggleGuide);
                    guideButton.removeEventListener('touchstart', toggleGuide);
                    closeButton.removeEventListener('click', closeGuide);
                    closeButton.removeEventListener('touchstart', closeGuide);
                    guideDrawer.removeEventListener('click', closeGuide);
                };
            };

            setupEventListeners();
        }, [isGuideOpen, setIsGuideOpen, product, fetchTemplateShortcode, formData]);
    };
})(window, window.React);
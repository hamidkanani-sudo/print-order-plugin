(function (globalThis, React, ReactDOM) {
    'use strict';

    if (!globalThis.PrintOrderForm) {
        globalThis.PrintOrderForm = {};
    }
    globalThis.PrintOrderForm.guideWidget = globalThis.PrintOrderForm.guideWidget || {};

    // بررسی زودهنگام وجود عنصر .print-order-guide
    if (!document.querySelector('.print-order-guide')) {
        return;
    }

    if (!React || !ReactDOM) {
        console.error('GuideWidget: React or ReactDOM not loaded');
        return;
    }

    if (!globalThis.PrintOrderForm.dataFetching) {
        console.error('GuideWidget: dataFetching module not loaded');
        return;
    }

    const GuideWidget = ({
        ajax_url,
        nonce,
        loading_delay,
        button_text,
        button_icon,
        icon_position,
        button_position,
        slide_width,
        slide_direction,
        button_styles,
        slide_styles,
        close_method,
        button_offset_horizontal,
        button_offset_vertical,
        button_margin,
        button_padding,
        button_animation,
        animation_interval
    }) => {
        const [currentStep, setCurrentStep] = React.useState(1);
        const [paperTypePersian, setPaperTypePersian] = React.useState('');
        const [categoryId, setCategoryId] = React.useState(null);
        const [shortcodeContent, setShortcodeContent] = React.useState('<p class="text-gray-600 text-center">در حال انتظار برای انتخاب محصول...</p>');
        const [loadingTemplate, setLoadingTemplate] = React.useState(false);
        const [isSlideOpen, setIsSlideOpen] = React.useState(false);
        const isMobile = globalThis.innerWidth < 768;

        // Handle form state changes via CustomEvent
        React.useEffect(() => {
            const handleFormStateChange = (event) => {
                const { currentStep, formData, product } = event.detail || {};
                setCurrentStep(currentStep || 1);
                setPaperTypePersian(formData?.paper_type_persian || '');
                setCategoryId(product?.category_id || null);
            };

            globalThis.addEventListener('printOrderFormStateChange', handleFormStateChange);
            return () => globalThis.removeEventListener('printOrderFormStateChange', handleFormStateChange);
        }, []);

        // Load template based on step and paper_type_persian
        React.useEffect(() => {
            if (!categoryId) {
                setShortcodeContent('<p class="text-gray-600 text-center">لطفاً محصول را انتخاب کنید</p>');
                return;
            }

            const loadTemplate = async () => {
                setLoadingTemplate(true);
                try {
                    if (currentStep === 1) {
                        await globalThis.PrintOrderForm.dataFetching.fetchTemplateShortcode(
                            categoryId,
                            paperTypePersian,
                            ajax_url,
                            nonce,
                            setShortcodeContent,
                            setLoadingTemplate
                        );
                    } else {
                        const stage = currentStep === 2 ? 'stage_2' : currentStep === 3 ? 'stage_3_shipping' : 'stage_3_payment';
                        await globalThis.PrintOrderForm.dataFetching.fetchStageTemplate(
                            stage,
                            ajax_url,
                            nonce,
                            setShortcodeContent,
                            setLoadingTemplate
                        );
                    }
                } catch (error) {
                    console.error('GuideWidget: Error fetching template:', error);
                    setShortcodeContent('<p class="text-red-600 text-center">خطا در بارگذاری راهنما: ' + error.message + '</p>');
                }
            };

            loadTemplate();
        }, [currentStep, paperTypePersian, categoryId]);

        const toggleSlide = () => {
            setIsSlideOpen(!isSlideOpen);
        };

        const handleOutsideClick = (e) => {
            if ((close_method === 'outside' || close_method === 'both') && isSlideOpen) {
                if (!e.target.closest('.guide-slide') && !e.target.closest('.guide-toggle-button')) {
                    setIsSlideOpen(false);
                }
            }
        };

        const handleCloseButtonClick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleSlide();
        };

        // Apply dynamic styles
        const buttonStyle = {
            backgroundColor: button_styles.background_color || '#2563eb',
            color: button_styles.text_color || '#ffffff',
            borderRadius: button_styles.border_radius ? `${button_styles.border_radius}px` : '8px',
            boxShadow: button_styles.box_shadow ? `0 4px 6px rgba(0, 0, 0, 0.1)` : 'none',
            border: button_styles.border_width ? `${button_styles.border_width}px solid ${button_styles.border_color || '#2563eb'}` : 'none',
            fontSize: button_styles.font_size ? `${button_styles.font_size}px` : '16px',
            fontWeight: button_styles.font_weight || '500',
            margin: `${button_margin.top || 0}px ${button_margin.right || 0}px ${button_margin.bottom || 0}px ${button_margin.left || 0}px`,
            padding: `${button_padding.top || 12}px ${button_padding.right || 20}px ${button_padding.bottom || 12}px ${button_padding.left || 20}px`,
            animation: button_animation && animation_interval ? `${button_animation} ${animation_interval}s infinite` : 'none',
            [button_position.includes('left') ? 'left' : 'right']: button_offset_horizontal ? `${button_offset_horizontal}px` : '20px',
            [button_position.includes('top') ? 'top' : 'bottom']: button_offset_vertical ? `${button_offset_vertical}px` : '20px',
        };

        const slideStyle = {
            width: isMobile ? `${slide_width || 80}%` : '100%',
            backgroundColor: slide_styles.background_color || '#ffffff',
            borderRadius: slide_styles.border_radius ? `${slide_styles.border_radius}px` : '0px',
            boxShadow: slide_styles.box_shadow ? `0 4px 6px rgba(0, 0, 0, 0.2)` : 'none',
            transform: isMobile && !isSlideOpen ? (slide_direction === 'right' ? 'translateX(100%)' : 'translateX(-100%)') : 'translateX(0)',
            transition: 'transform 0.3s ease-in-out',
        };

        const iconStyle = {
            fontSize: button_styles.icon_size ? `${button_styles.icon_size}px` : '16px',
            marginRight: icon_position === 'right' ? (button_styles.icon_spacing ? `${button_styles.icon_spacing}px` : '8px') : '0',
            marginLeft: icon_position === 'left' ? (button_styles.icon_spacing ? `${button_styles.icon_spacing}px` : '8px') : '0',
            color: button_styles.icon_color || '#ffffff',
        };

        if (isMobile) {
            return React.createElement(
                'div',
                { className: 'guide-widget-mobile', onClick: handleOutsideClick },
                [
                    React.createElement(
                        'button',
                        {
                            className: `guide-toggle-button fixed ${button_position} flex items-center justify-center transition-transform duration-100 hover:scale-95`,
                            style: buttonStyle,
                            onClick: toggleSlide,
                        },
                        [
                            button_icon && icon_position === 'left' && React.createElement('i', { className: `fas ${button_icon} icon-left`, style: iconStyle }),
                            button_text,
                            button_icon && icon_position === 'right' && React.createElement('i', { className: `fas ${button_icon} icon-right`, style: iconStyle }),
                        ]
                    ),
                    isSlideOpen && React.createElement(
                        'div',
                        { className: 'guide-slide-overlay fixed inset-0 bg-black bg-opacity-50 z-40' },
                        null
                    ),
                    React.createElement(
                        'div',
                        {
                            className: `guide-slide fixed top-0 ${slide_direction === 'right' ? 'right-0' : 'left-0'} h-full overflow-y-auto z-50`,
                            style: slideStyle,
                        },
                        [
                            (close_method === 'button' || close_method === 'both') && React.createElement(
                                'button',
                                {
                                    className: 'close-slide absolute top-4 right-4 text-gray-600 hover:text-gray-800 focus:outline-none pointer-events-auto',
                                    onClick: handleCloseButtonClick,
                                    'aria-label': 'Close guide slide',
                                },
                                React.createElement('i', { className: 'fas fa-times' })
                            ),
                            loadingTemplate
                                ? React.createElement(
                                      'div',
                                      { className: 'loading-spinner flex justify-center items-center py-4' },
                                      React.createElement('div', {
                                          className: 'animate-spin rounded-full border-t-2 border-b-2',
                                          style: { borderColor: button_styles.border_color || '#2563eb', width: '32px', height: '32px' },
                                      })
                                  )
                                : React.createElement('div', {
                                      className: 'guide-content text-right p-4',
                                      dangerouslySetInnerHTML: { __html: shortcodeContent },
                                  }),
                        ]
                    ),
                ]
            );
        }

        return React.createElement(
            'div',
            { className: 'guide-widget w-full bg-white rounded-lg shadow-md p-4' },
            loadingTemplate
                ? React.createElement(
                      'div',
                      { className: 'loading-spinner flex justify-center items-center py-4' },
                      React.createElement('div', {
                          className: 'animate-spin rounded-full border-t-2 border-b-2',
                          style: { borderColor: button_styles.border_color || '#2563eb', width: '32px', height: '32px' },
                      })
                  )
                : React.createElement('div', {
                      className: 'guide-content text-right',
                      dangerouslySetInnerHTML: { __html: shortcodeContent },
                  })
        );
    };

    globalThis.PrintOrderForm.guideWidget.renderGuideWidget = () => {
        const guideElements = document.querySelectorAll('.print-order-guide');
        if (guideElements.length === 0) {
            return;
        }
        for (const element of guideElements) {
            const ajax_url = element.dataset.ajaxUrl || '';
            const nonce = element.dataset.nonce || '';
            const loading_delay = element.dataset.loadingDelay || '500';
            const button_text = element.dataset.buttonText || 'نمایش راهنما';
            const button_icon = element.dataset.buttonIcon || '';
            const icon_position = element.dataset.iconPosition || 'left';
            const button_position = element.dataset.buttonPosition || 'bottom-right';
            const slide_width = element.dataset.slideWidth || '80';
            const slide_direction = element.dataset.slideDirection || 'right';
            const close_method = element.dataset.closeMethod || 'both';
            const button_offset_horizontal = Number.parseInt(element.dataset.buttonOffsetHorizontal, 10) || 20;
            const button_offset_vertical = Number.parseInt(element.dataset.buttonOffsetVertical, 10) || 20;
            const button_margin = {
                top: Number.parseInt(element.dataset.buttonMarginTop, 10) || 0,
                right: Number.parseInt(element.dataset.buttonMarginRight, 10) || 0,
                bottom: Number.parseInt(element.dataset.buttonMarginBottom, 10) || 0,
                left: Number.parseInt(element.dataset.buttonMarginLeft, 10) || 0,
            };
            const button_padding = {
                top: Number.parseInt(element.dataset.buttonPaddingTop, 10) || 12,
                right: Number.parseInt(element.dataset.buttonPaddingRight, 10) || 20,
                bottom: Number.parseInt(element.dataset.buttonPaddingBottom, 10) || 12,
                left: Number.parseInt(element.dataset.buttonPaddingLeft, 10) || 20,
            };
            const button_animation = element.dataset.buttonAnimation || '';
            const animation_interval = Number.parseInt(element.dataset.animationInterval, 10) || 3;
            const button_styles = {
                background_color: element.dataset.buttonBgColor || '#2563eb',
                text_color: element.dataset.buttonTextColor || '#ffffff',
                border_radius: Number.parseInt(element.dataset.buttonBorderRadius, 10) || 8,
                box_shadow: element.dataset.buttonBoxShadow === 'yes',
                border_width: Number.parseInt(element.dataset.buttonBorderWidth, 10) || 0,
                border_color: element.dataset.buttonBorderColor || '#2563eb',
                font_size: Number.parseInt(element.dataset.buttonFontSize, 10) || 16,
                font_weight: element.dataset.buttonFontWeight || '500',
                icon_size: Number.parseInt(element.dataset.buttonIconSize, 10) || 16,
                icon_spacing: Number.parseInt(element.dataset.buttonIconSpacing, 10) || 8,
                icon_color: element.dataset.buttonIconColor || '#ffffff',
            };
            const slide_styles = {
                background_color: element.dataset.slideBgColor || '#ffffff',
                border_radius: Number.parseInt(element.dataset.slideBorderRadius, 10) || 0,
                box_shadow: element.dataset.slideBoxShadow === 'yes',
            };

            if (!ajax_url || !nonce) {
                console.error('GuideWidget: Missing ajax_url or nonce for element:', element.id);
                element.innerHTML = '<p class="text-red-600 text-center">خطا: تنظیمات Ajax ناقص است</p>';
                return;
            }
            const root = ReactDOM.createRoot(element);
            root.render(
                React.createElement(GuideWidget, {
                    ajax_url,
                    nonce,
                    loading_delay,
                    button_text,
                    button_icon,
                    icon_position,
                    button_position,
                    slide_width,
                    slide_direction,
                    button_styles,
                    slide_styles,
                    close_method,
                    button_offset_horizontal,
                    button_offset_vertical,
                    button_margin,
                    button_padding,
                    button_animation,
                    animation_interval
                })
            );
        }
    };

    // Run initialization only when DOM is fully loaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        globalThis.PrintOrderForm.guideWidget.renderGuideWidget();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            globalThis.PrintOrderForm.guideWidget.renderGuideWidget();
        });
    }
})(globalThis, globalThis.React, globalThis.ReactDOM);
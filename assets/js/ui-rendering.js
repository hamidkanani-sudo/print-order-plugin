(function (global, React) {
    'use strict';

    global.PrintOrderForm = global.PrintOrderForm || {};
    global.PrintOrderForm.uiRendering = global.PrintOrderForm.uiRendering || {};

    // START: MODIFIED CODE - Added formData to function signature
    global.PrintOrderForm.uiRendering.renderInstantPrice = function (currentStep, product, formData, priceItemsState, visibleItems) {
        if (currentStep === 4 || !product) return null;

        // Determine if print-related costs should be shown
        const isPrintRequired = product && !product.no_print && !formData.no_print_needed;

        // Filter out "هزینه چاپ" if printing is not required
        let itemsToRender = Array.isArray(priceItemsState) ? priceItemsState : [];
        if (!isPrintRequired) {
            itemsToRender = itemsToRender.filter(item => item.label !== 'هزینه چاپ');
        }

        if (itemsToRender.length === 0) return null;
    // END: MODIFIED CODE

        return React.createElement(
            'div',
            {
                key: `instant-price-${currentStep}`,
                className: 'instant-price w-full p-2 bg-gray-50 rounded-lg flex flex-wrap items-center justify-start gap-1',
            },
            // START: MODIFIED CODE - Map over the filtered items
            itemsToRender.map((item, index) => {
                const shouldShow = currentStep >= item.step;
                const isVisible = visibleItems[index];
                const isLastItem = index === itemsToRender.length - 1;
            // END: MODIFIED CODE
                return shouldShow && isVisible ? [
                    index > 0 && React.createElement(
                        'div',
                        {
                            key: `plus-${index}`,
                            className: 'price-plus flex items-center animate-plus',
                        },
                        React.createElement('svg', {
                            xmlns: 'http://www.w3.org/2000/svg',
                            width: '16',
                            height: '16',
                            viewBox: '0 0 24 24',
                            fill: 'none',
                            stroke: '#000',
                            strokeWidth: '2',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                        }, [
                            React.createElement('line', { x1: '12', y1: '5', x2: '12', y2: '19' }),
                            React.createElement('line', { x1: '5', y1: '12', x2: '19', y2: '12' }),
                        ])
                    ),
                    React.createElement(
                        'div',
                        {
                            key: `price-item-${index}`,
                            className: `price-item inline-block px-3 py-1 bg-green-500 text-white rounded-full text-sm font-medium shadow-sm ${isLastItem ? 'animate-price-item' : ''}`,
                        },
                        `${item.label}: ${item.value.toLocaleString('fa-IR')} تومان`
                    ),
                ] : null;
            }).filter(Boolean)
        );
    };

    global.PrintOrderForm.uiRendering.renderTotalPrice = function (currentStep, priceItemsState) {
        if (currentStep === 4 || !Array.isArray(priceItemsState)) return null;
        const total = priceItemsState.reduce((sum, item) => sum + item.value, 0);
        return React.createElement(
            'div',
            {
                key: `total-price-${currentStep}-${total}`,
                className: 'w-full text-left flex items-center',
            },
            React.createElement('svg', {
                xmlns: 'http://www.w3.org/2000/svg',
                width: '16',
                height: '16',
                viewBox: '0 0 24 24',
                fill: 'none',
                stroke: '#374151',
                strokeWidth: '2',
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                className: 'ml-2',
            }, [
                React.createElement('path', { d: 'M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42' }),
                React.createElement('path', { d: 'M12 7a5 5 0 0 0 0 10' }),
            ]),
            React.createElement('p', { className: 'text-gray-800 font-bold' }, `جمع: ${total.toLocaleString('fa-IR')} تومان`)
        );
    };

    global.PrintOrderForm.uiRendering.renderDeliveryDays = function (currentStep, product, formData, deliveryDays) {
        if (currentStep === 4 || !product) return null;
        
        // START: MODIFIED CODE - Only check for allFieldsFilled if print is required
        const isPrintRequired = product && !product.no_print && !formData.no_print_needed;
        if (isPrintRequired) {
            const allFieldsFilled = formData.paper_type_persian && formData.size && formData.quantity && formData.sides;
            if (!allFieldsFilled) return null;
        } else {
            // For no-print orders, we don't show delivery days based on print options
            return null;
        }
        // END: MODIFIED CODE
        
        if (deliveryDays === 0) {
            return React.createElement(
                'div',
                { key: 'delivery-warning', className: 'delivery-warning w-full p-2 bg-yellow-100 rounded-lg text-right' },
                React.createElement(
                    'div',
                    { className: 'flex items-center justify-end' },
                    React.createElement('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        width: '16',
                        height: '16',
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: '#f59e0b',
                        strokeWidth: '2',
                        strokeLinecap: 'round',
                        strokeLinejoin: 'round',
                        className: 'ml-2',
                    }, [
                        React.createElement('path', { d: 'M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z' }),
                        React.createElement('line', { x1: '12', y1: '9', x2: '12', y2: '13' }),
                        React.createElement('line', { x1: '12', y1: '17', x2: '12.01', y2: '17' }),
                    ]),
                    React.createElement('p', { className: 'text-yellow-700 font-semibold' }, 'هشدار')
                ),
                React.createElement('p', { className: 'text-yellow-700 text-right mt-1' }, 'مدت آماده‌سازی برای این ترکیب مشخص نشده است.')
            );
        }
        return React.createElement(
            'div',
            {
                key: `delivery-${deliveryDays}`,
                className: 'delivery-days w-full p-2 bg-gradient-to-l from-green-50 to-white rounded-lg text-right animate-price',
            },
            React.createElement(
                'p',
                { className: 'text-green-700 text-right flex items-center justify-start' },
                React.createElement('svg', {
                    xmlns: 'http://www.w3.org/2000/svg',
                    width: '16',
                    height: '16',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: '#15803d',
                    strokeWidth: '2',
                    strokeLinecap: 'round',
                    strokeLinejoin: 'round',
                    className: 'ml-2',
                }, [
                    React.createElement('rect', { x: '3', y: '4', width: '18', height: '14', rx: '2', ry: '2' }),
                    React.createElement('line', { x1: '16', y1: '2', x2: '16', y2: '6' }),
                    React.createElement('line', { x1: '8', y1: '2', x2: '8', y2: '6' }),
                    React.createElement('line', { x1: '3', y1: '10', x2: '21', y2: '10' }),
                ]),
                `مدت تقریبی آماده‌سازی سفارش: ${deliveryDays} روز`
            )
        );
    };
})(window, window.React);
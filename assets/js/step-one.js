(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.stepOne) {
        console.log('PrintOrderForm.stepOne: Already loaded, skipping');
        return;
    }

    const renderStepOne = (formData, handleInputChange, paperTypesPersian, sizes, quantities, sidesOptions, sidesMapping, renderInstantPrice, renderDeliveryDays, currentStep, product, priceItemsState, visibleItems, deliveryDays) => {
        // START: No changes in this part, logic is correct
        if (product.no_print) {
            return React.createElement(
                'div',
                { className: 'space-y-4 text-center text-gray-600' },
                'این محصول نیازی به تنظیمات چاپ ندارد. لطفاً به مرحله بعد بروید.'
            );
        }
        // END: No changes in this part

        const elements = [
            React.createElement(
                'div',
                { className: 'form-group flex items-center' },
                [
                    React.createElement(
                        'label',
                        {
                            htmlFor: 'no_print_needed',
                            className: 'inline-flex items-center text-sm font-medium text-gray-700 cursor-pointer'
                        },
                        [
                            React.createElement('input', {
                                type: 'checkbox',
                                id: 'no_print_needed',
                                name: 'no_print_needed',
                                checked: formData.no_print_needed || false,
                                onChange: handleInputChange,
                                className: 'ml-2 h-4 w-4 text-blue-600 rounded'
                            }),
                            'نیازی به چاپ نیست'
                        ]
                    )
                ]
            )
        ];

        if (!formData.no_print_needed) {
            elements.push(
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement(
                            'label',
                            { htmlFor: 'paper_type_persian', className: 'block text-sm font-medium text-gray-700 flex items-center' },
                            [
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
                                    React.createElement('path', { d: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z' }),
                                    React.createElement('polyline', { points: '14 2 14 8 20 8' }),
                                ]),
                                'جنس کاغذ',
                            ]
                        ),
                        React.createElement(
                            'select',
                            {
                                id: 'paper_type_persian',
                                name: 'paper_type_persian',
                                value: formData.paper_type_persian || '',
                                onChange: handleInputChange,
                                // START: MODIFIED CODE
                                required: !formData.no_print_needed,
                                // END: MODIFIED CODE
                                className: 'mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm'
                            },
                            [
                                React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                paperTypesPersian.map((type, index) =>
                                    React.createElement('option', { key: index, value: type }, type)
                                ),
                            ]
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement(
                            'label',
                            { htmlFor: 'size', className: 'block text-sm font-medium text-gray-700 flex items-center' },
                            [
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
                                    React.createElement('path', { d: 'M4 4h16v16H4z' }),
                                    React.createElement('path', { d: 'M4 4l16 16' }),
                                ]),
                                'سایز',
                            ]
                        ),
                        React.createElement(
                            'select',
                            {
                                id: 'size',
                                name: 'size',
                                value: formData.size || '',
                                onChange: handleInputChange,
                                // START: MODIFIED CODE
                                required: !formData.no_print_needed,
                                // END: MODIFIED CODE
                                className: 'mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm'
                            },
                            [
                                React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                sizes.map((size, index) =>
                                    React.createElement('option', { key: index, value: size }, size)
                                ),
                            ]
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement(
                            'label',
                            { htmlFor: 'quantity', className: 'block text-sm font-medium text-gray-700 flex items-center' },
                            [
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
                                    React.createElement('line', { x1: '4', y1: '6', x2: '20', y2: '6' }),
                                    React.createElement('line', { x1: '4', y1: '12', x2: '20', y2: '12' }),
                                    React.createElement('line', { x1: '4', y1: '18', x2: '20', y2: '18' }),
                                ]),
                                'تعداد',
                            ]
                        ),
                        React.createElement(
                            'select',
                            {
                                id: 'quantity',
                                name: 'quantity',
                                value: formData.quantity || '',
                                onChange: handleInputChange,
                                // START: MODIFIED CODE
                                required: !formData.no_print_needed,
                                // END: MODIFIED CODE
                                className: 'mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm'
                            },
                            [
                                React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                quantities.map((quantity, index) =>
                                    React.createElement('option', { key: index, value: quantity }, quantity)
                                ),
                            ]
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement(
                            'label',
                            { htmlFor: 'sides', className: 'block text-sm font-medium text-gray-700 flex items-center' },
                            [
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
                                    React.createElement('path', { d: 'M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2' }),
                                    React.createElement('rect', { x: '8', y: '2', width: '8', height: '4', rx: '1', ry: '1' }),
                                ]),
                                'نوع چاپ',
                            ]
                        ),
                        React.createElement(
                            'select',
                            {
                                id: 'sides',
                                name: 'sides',
                                value: formData.sides || '',
                                onChange: handleInputChange,
                                // START: MODIFIED CODE
                                required: !formData.no_print_needed,
                                // END: MODIFIED CODE
                                className: 'mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm'
                            },
                            [
                                React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                sidesOptions.map((side, index) => {
                                    const mappedSide = sidesMapping.find(m => m.english === side);
                                    if (!mappedSide) return null;
                                    return React.createElement(
                                        'option',
                                        { key: index, value: mappedSide.value },
                                        mappedSide.label
                                    );
                                }).filter(Boolean),
                            ]
                        ),
                    ]
                )
            );
        }

        return React.createElement('div', { className: 'space-y-4' }, elements);
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.stepOne = {
        renderStepOne,
    };
})();
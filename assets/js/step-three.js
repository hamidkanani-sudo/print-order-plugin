(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.stepThree) {
        return;
    }

    const { useState, useEffect } = React;

    // Provinces map for converting name to code
    const provincesMap = {
        'تهران': 'THR',
        'البرز': 'ABZ',
        'ایلام': 'ILM',
        'بوشهر': 'BHR',
        'اردبیل': 'ADL',
        'اصفهان': 'ESF',
        'آذربایجان شرقی': 'EAZ',
        'آذربایجان غربی': 'WAZ',
        'زنجان': 'ZAN',
        'سمنان': 'SMN',
        'سیستان و بلوچستان': 'SBL',
        'فارس': 'FRS',
        'قم': 'QHM',
        'قزوین': 'QZN',
        'گلستان': 'GLS',
        'گیلان': 'GIL',
        'مازندران': 'MZN',
        'مرکزی': 'MKZ',
        'هرمزگان': 'HRZ',
        'همدان': 'HMD',
        'کردستان': 'KRD',
        'کرمانشاه': 'KRH',
        'کرمان': 'KRN',
        'کهگیلویه و بویراحمد': 'KBD',
        'خوزستان': 'KZT',
        'لرستان': 'LRS',
        'خراسان شمالی': 'KHS',
        'خراسان رضوی': 'KJR',
        'خراسان جنوبی': 'KJF',
        'چهارمحال و بختیاری': 'CHB',
        'یزد': 'YSD',
    };

    // Reverse map for code to name
    const reverseProvincesMap = Object.fromEntries(
        Object.entries(provincesMap).map(([name, code]) => [code, name])
    );

    const StepThree = ({ formData, handleInputChange, provinces, renderInstantPrice, currentStep, product, priceItemsState, visibleItems }) => {
        const [isEditing, setIsEditing] = useState(false);
        const [validationError, setValidationError] = useState('');
        const { loading, error, userInfo } = window.PrintOrderForm.userAddress.useUserAddress(currentStep, printOrder.ajax_url, printOrder.nonce, formData, () => {});

        const checkEmail = async (email) => {
            try {
                const response = await window.PrintOrderForm.dataFetching.checkEmailExists(
                    email,
                    printOrder.ajax_url,
                    printOrder.public_nonce
                );
                if (response.success && response.data.exists) {
                    return true;
                }
                return false;
            } catch (err) {
                console.error('checkEmail: Error =', err.message);
                return false;
            }
        };

        const handleEmailBlur = async (event) => {
            const email = event.target.value;
            const emailField = document.getElementById('customer_email');
            const existingMessage = document.getElementById('email-exists-message');

            if (existingMessage) {
                existingMessage.remove();
            }

            if (email && window.PrintOrderForm.utils.isEmail(email)) {
                const isLoggedIn = formData.user_id && formData.user_id !== '0';
                if (isLoggedIn) {
                    const currentUserEmail = formData.billing_email || '';
                    if (email === currentUserEmail) {
                        return;
                    }
                }

                const exists = await checkEmail(email);
                if (exists && emailField) {
                    const messageDiv = document.createElement('div');
                    messageDiv.id = 'email-exists-message';
                    messageDiv.className = 'text-red-600 text-sm mt-1';
                    messageDiv.setAttribute('aria-live', 'polite');
                    messageDiv.innerHTML = `این ایمیل در سیستم وجود دارد. <a href="${printOrder.options.login_page_url}" class="underline">وارد شوید</a>`;
                    emailField.parentNode.appendChild(messageDiv);
                }
            }
        };

        const handleInputChangeWrapper = (event) => {
            handleInputChange(event);
        };

        const validateForm = () => {
            const requiredFields = ['customer_name', 'customer_lastname', 'customer_phone', 'billing_state', 'billing_city', 'billing_address', 'billing_postcode'];
            const fieldLabels = {
                customer_name: 'نام',
                customer_lastname: 'نام خانوادگی',
                customer_phone: 'شماره تماس',
                billing_state: 'استان',
                billing_city: 'شهر',
                billing_address: 'آدرس',
                billing_postcode: 'کد پستی',
                customer_email: 'ایمیل'
            };
            const missingFields = requiredFields.filter(field => !formData[field]?.trim());
            if (!formData.customer_email && !formData.user_id) {
                missingFields.push('customer_email');
            }
            if (!formData.customer_phone || !/^09[0-9]{9}$/.test(formData.customer_phone)) {
                missingFields.push('customer_phone');
            }
            if (missingFields.length > 0) {
                setValidationError(`لطفاً فیلدهای اجباری را پر کنید: ${missingFields.map(field => fieldLabels[field]).join('، ')}`);
                return false;
            }
            if (!Object.values(provincesMap).includes(formData.billing_state)) {
                setValidationError('استان انتخاب‌شده معتبر نیست.');
                return false;
            }
            setValidationError('');
            return true;
        };

        const handleUpdateProfile = async () => {
            if (!validateForm()) return;

            try {
                // Ensure billing_state is a province code
                const provinceCode = provincesMap[formData.billing_state] || formData.billing_state;
                const updatedFormData = {
                    ...formData,
                    billing_state: provinceCode,
                };

                const response = await fetch(printOrder.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'update_user_profile',
                        nonce: printOrder.nonce,
                        form_data: JSON.stringify(updatedFormData),
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    setIsEditing(false);
                    // Update formData with province code
                    handleInputChange({ target: { name: 'billing_state', value: provinceCode } });
                } else {
                    console.error('StepThree: Failed to update profile', data.message);
                    setValidationError(data.message || 'خطا در ذخیره اطلاعات');
                }
            } catch (error) {
                console.error('StepThree: Error updating profile', error);
                setValidationError('خطا در ارتباط با سرور');
            }
        };

        const isLoggedIn = formData.user_id && formData.user_id !== '0';
        const hasProfileInfo = isLoggedIn && formData.customer_name && formData.customer_lastname && formData.customer_phone && formData.billing_state && formData.billing_city && formData.billing_address && formData.billing_postcode;

        if (loading) {
            return React.createElement(
                'div',
                { className: 'form-loading text-center text-gray-500 py-8' },
                'در حال بارگذاری اطلاعات...'
            );
        }

        if (error) {
            return React.createElement(
                'div',
                { className: 'form-error text-center text-red-500 p-4 bg-red-100 rounded-lg my-4' },
                error
            );
        }

        if (isLoggedIn && hasProfileInfo && !isEditing) {
            return React.createElement(
                'div',
                { className: 'space-y-4' },
                [
                    React.createElement(
                        'h3',
                        { className: 'text-lg font-medium text-gray-900 mb-4' },
                        'آدرس فاکتور'
                    ),
                    React.createElement(
                        'div',
                        { className: 'profile-info bg-gray-50 border border-gray-200 rounded-lg p-4' },
                        [
                            React.createElement(
                                'p',
                                { className: 'text-sm text-gray-700 mb-2' },
                                `${formData.customer_name} ${formData.customer_lastname || ''} عزیز، اطلاعات تماس و آدرس شما ثبت شده است. برای تغییر دکمه ویرایش را بزنید.`
                            ),
                            React.createElement(
                                'p',
                                { className: 'text-sm text-gray-600' },
                                `ایمیل: ${formData.customer_email || 'ثبت نشده'}`
                            ),
                            React.createElement(
                                'p',
                                { className: 'text-sm text-gray-600' },
                                `شماره تماس: ${formData.customer_phone || 'ثبت نشده'}`
                            ),
                            React.createElement(
                                'p',
                                { className: 'text-sm text-gray-600' },
                                `آدرس: ${reverseProvincesMap[formData.billing_state] || formData.billing_state || ''} / ${formData.billing_city || ''} / ${formData.billing_address || ''}. کدپستی: ${formData.billing_postcode || ''}`
                            ),
                            React.createElement(
                                'button',
                                {
                                    type: 'button',
                                    onClick: () => setIsEditing(true),
                                    className: 'edit-button mt-4 bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700'
                                },
                                'ویرایش'
                            )
                        ]
                    ),
                    React.createElement(
                        'div',
                        { className: 'form-group' },
                        [
                            React.createElement(
                                'label',
                                { className: 'flex items-center' },
                                [
                                    React.createElement('input', {
                                        type: 'checkbox',
                                        name: 'ship_to_different_address',
                                        checked: formData.ship_to_different_address || false,
                                        onChange: handleInputChangeWrapper,
                                        className: 'h-4 w-4 text-indigo-600 border-gray-300 rounded'
                                    }),
                                    React.createElement('span', { className: 'mr-2 text-sm text-gray-700' }, 'ارسال به آدرس دیگر')
                                ]
                            )
                        ]
                    ),
                    formData.ship_to_different_address && React.createElement(
                        'div',
                        { className: 'space-y-4 pl-6 border-r-2 border-indigo-200' },
                        [
                            React.createElement(
                                'p',
                                { className: `different-address-message ${formData.ship_to_different_address ? 'visible' : 'hidden'} text-sm text-indigo-600 mt-2` },
                                'لطفاً آدرس گیرنده را وارد کنید'
                            ),
                            React.createElement(
                                'div',
                                { className: 'form-group' },
                                [
                                    React.createElement('label', { htmlFor: 'shipping_first_name', className: 'block text-sm font-medium text-gray-700' }, 'نام گیرنده'),
                                    React.createElement('input', {
                                        type: 'text',
                                        id: 'shipping_first_name',
                                        name: 'shipping_first_name',
                                        value: 'shipping_first_name' in formData ? formData.shipping_first_name : '',
                                        onChange: handleInputChangeWrapper,
                                        className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                                    })
                                ]
                            ),
                            React.createElement(
                                'div',
                                { className: 'form-group' },
                                [
                                    React.createElement('label', { htmlFor: 'shipping_phone', className: 'block text-sm font-medium text-gray-700' }, 'شماره تماس گیرنده'),
                                    React.createElement('input', {
                                        type: 'tel',
                                        id: 'shipping_phone',
                                        name: 'shipping_phone',
                                        value: 'shipping_phone' in formData ? formData.shipping_phone : '',
                                        onChange: handleInputChangeWrapper,
                                        className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                        placeholder: 'مثال: 09123456789'
                                    })
                                ]
                            ),
                            React.createElement(
                                'div',
                                { className: 'form-group' },
                                [
                                    React.createElement('label', { htmlFor: 'shipping_state', className: 'block text-sm font-medium text-gray-700' }, 'استان *'),
                                    React.createElement(
                                        'select',
                                        {
                                            id: 'shipping_state',
                                            name: 'shipping_state',
                                            value: formData.shipping_state || '',
                                            onChange: handleInputChangeWrapper,
                                            required: true,
                                            className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                                        },
                                        [
                                            React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                            Object.entries(provincesMap).map(([name, code], index) =>
                                                React.createElement('option', { key: index, value: code }, name)
                                            )
                                        ]
                                    )
                                ]
                            ),
                            React.createElement(
                                'div',
                                { className: 'form-group' },
                                [
                                    React.createElement('label', { htmlFor: 'shipping_city', className: 'block text-sm font-medium text-gray-700' }, 'شهر *'),
                                    React.createElement('input', {
                                        type: 'text',
                                        id: 'shipping_city',
                                        name: 'shipping_city',
                                        value: formData.shipping_city || '',
                                        onChange: handleInputChangeWrapper,
                                        required: true,
                                        className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                        placeholder: 'نام شهر را وارد کنید'
                                    })
                                ]
                            ),
                            React.createElement(
                                'div',
                                { className: 'form-group' },
                                [
                                    React.createElement('label', { htmlFor: 'shipping_address', className: 'block text-sm font-medium text-gray-700' }, 'آدرس *'),
                                    React.createElement('textarea', {
                                        id: 'shipping_address',
                                        name: 'shipping_address',
                                        value: formData.shipping_address || '',
                                        onChange: handleInputChangeWrapper,
                                        required: true,
                                        rows: 4,
                                        className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                        placeholder: 'مثال: خیابان اصلی، پلاک 123'
                                    })
                                ]
                            ),
                            React.createElement(
                                'div',
                                { className: 'form-group' },
                                [
                                    React.createElement('label', { htmlFor: 'shipping_postcode', className: 'block text-sm font-medium text-gray-700' }, 'کد پستی *'),
                                    React.createElement('input', {
                                        type: 'text',
                                        id: 'shipping_postcode',
                                        name: 'shipping_postcode',
                                        value: 'shipping_postcode' in formData ? formData.shipping_postcode : '',
                                        onChange: handleInputChangeWrapper,
                                        required: true,
                                        className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                        placeholder: 'مثال: 1234567890'
                                    })
                                ]
                            ),
                            React.createElement('input', {
                                type: 'hidden',
                                name: 'shipping_country',
                                value: 'IR'
                            })
                        ]
                    )
                ]
            );
        }

        return React.createElement(
            'div',
            { className: 'space-y-4' },
            [
                validationError && React.createElement(
                    'div',
                    { className: 'form-error text-center text-red-500 p-4 bg-red-100 rounded-lg my-4' },
                    validationError
                ),
                React.createElement(
                    'h3',
                    { className: 'text-lg font-medium text-gray-900 mb-4' },
                    'آدرس فاکتور'
                ),
                React.createElement(
                    'div',
                    { className: 'form-group grid grid-cols-1 md:grid-cols-2 gap-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'customer_name', className: 'block text-sm font-medium text-gray-700' }, 'نام *'),
                                React.createElement('input', {
                                    type: 'text',
                                    id: 'customer_name',
                                    name: 'customer_name',
                                    value: formData.customer_name || '',
                                    onChange: handleInputChangeWrapper,
                                    required: true,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                                })
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'customer_lastname', className: 'block text-sm font-medium text-gray-700' }, 'نام خانوادگی *'),
                                React.createElement('input', {
                                    type: 'text',
                                    id: 'customer_lastname',
                                    name: 'customer_lastname',
                                    value: formData.customer_lastname || '',
                                    onChange: handleInputChangeWrapper,
                                    required: true,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                                })
                            ]
                        )
                    ]
                ),
                !isLoggedIn && React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement('label', { htmlFor: 'customer_email', className: 'block text-sm font-medium text-gray-700' }, 'ایمیل *'),
                        React.createElement('input', {
                            type: 'email',
                            id: 'customer_email',
                            name: 'customer_email',
                            value: formData.customer_email || '',
                            onChange: handleInputChangeWrapper,
                            onBlur: handleEmailBlur,
                            required: true,
                            className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                            placeholder: 'مثال: example@domain.com'
                        })
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement('label', { htmlFor: 'customer_phone', className: 'block text-sm font-medium text-gray-700' }, 'شماره تماس *'),
                        React.createElement('input', {
                            type: 'tel',
                            id: 'customer_phone',
                            name: 'customer_phone',
                            value: formData.customer_phone || '',
                            onChange: handleInputChangeWrapper,
                            required: true,
                            className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                            placeholder: 'مثال: 09123456789'
                        })
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement('label', { htmlFor: 'billing_state', className: 'block text-sm font-medium text-gray-700' }, 'استان *'),
                        React.createElement(
                            'select',
                            {
                                id: 'billing_state',
                                name: 'billing_state',
                                value: formData.billing_state || '',
                                onChange: handleInputChangeWrapper,
                                required: true,
                                className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                            },
                            [
                                React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                Object.entries(provincesMap).map(([name, code], index) =>
                                    React.createElement('option', { key: index, value: code }, name)
                                )
                            ]
                        )
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement('label', { htmlFor: 'billing_city', className: 'block text-sm font-medium text-gray-700' }, 'شهر *'),
                        React.createElement('input', {
                            type: 'text',
                            id: 'billing_city',
                            name: 'billing_city',
                            value: formData.billing_city || '',
                            onChange: handleInputChangeWrapper,
                            required: true,
                            className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                            placeholder: 'نام شهر را وارد کنید'
                        })
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement('label', { htmlFor: 'billing_address', className: 'block text-sm font-medium text-gray-700' }, 'آدرس *'),
                        React.createElement('textarea', {
                            id: 'billing_address',
                            name: 'billing_address',
                            value: 'billing_address' in formData ? formData.billing_address : '',
                            onChange: handleInputChangeWrapper,
                            required: true,
                            rows: 4,
                            className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                            placeholder: 'مثال: خیابان اصلی، پلاک 123'
                        })
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement('label', { htmlFor: 'billing_postcode', className: 'block text-sm font-medium text-gray-700' }, 'کد پستی *'),
                        React.createElement('input', {
                            type: 'text',
                            id: 'billing_postcode',
                            name: 'billing_postcode',
                            value: formData.billing_postcode || '',
                            onChange: handleInputChangeWrapper,
                            required: true,
                            className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                            placeholder: 'مثال: 1234567890'
                        })
                    ]
                ),
                React.createElement('input', {
                    type: 'hidden',
                    name: 'billing_country',
                    value: 'IR'
                }),
                isLoggedIn && hasProfileInfo && isEditing && React.createElement(
                    'div',
                    { className: 'form-group flex justify-end gap-2' },
                    [
                        React.createElement(
                            'button',
                            {
                                type: 'button',
                                onClick: handleUpdateProfile,
                                className: 'save-button bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 border-none outline-none shadow-none'
                            },
                            'ذخیره'
                        ),
                        React.createElement(
                            'button',
                            {
                                type: 'button',
                                onClick: () => setIsEditing(false),
                                className: 'cancel-button bg-gray-200 text-gray-700 p-2 rounded-lg hover:bg-gray-300'
                            },
                            'لغو'
                        )
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'form-group' },
                    [
                        React.createElement(
                            'label',
                            { className: 'flex items-center' },
                            [
                                React.createElement('input', {
                                    type: 'checkbox',
                                    name: 'ship_to_different_address',
                                    checked: formData.ship_to_different_address || false,
                                    onChange: handleInputChangeWrapper,
                                    className: 'h-4 w-4 text-indigo-600 border-gray-300 rounded'
                                }),
                                React.createElement('span', { className: 'mr-2 text-sm text-gray-700' }, 'ارسال به آدرس دیگر')
                            ]
                        )
                    ]
                ),
                formData.ship_to_different_address && React.createElement(
                    'div',
                    { className: 'space-y-4 pl-6 border-r-2 border-indigo-200' },
                    [
                        React.createElement(
                            'p',
                            { className: `different-address-message ${formData.ship_to_different_address ? 'visible' : 'hidden'} text-sm text-indigo-600 mt-2` },
                            'لطفاً آدرس گیرنده را وارد کنید'
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'shipping_first_name', className: 'block text-sm font-medium text-gray-700' }, 'نام گیرنده'),
                                React.createElement('input', {
                                    type: 'text',
                                    id: 'shipping_first_name',
                                    name: 'shipping_first_name',
                                    value: 'shipping_first_name' in formData ? formData.shipping_first_name : '',
                                    onChange: handleInputChangeWrapper,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                                })
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'shipping_phone', className: 'block text-sm font-medium text-gray-700' }, 'شماره تماس گیرنده'),
                                React.createElement('input', {
                                    type: 'tel',
                                    id: 'shipping_phone',
                                    name: 'shipping_phone',
                                    value: 'shipping_phone' in formData ? formData.shipping_phone : '',
                                    onChange: handleInputChangeWrapper,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                    placeholder: 'مثال: 09123456789'
                                })
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'shipping_state', className: 'block text-sm font-medium text-gray-700' }, 'استان *'),
                                React.createElement(
                                    'select',
                                    {
                                        id: 'shipping_state',
                                        name: 'shipping_state',
                                        value: formData.shipping_state || '',
                                        onChange: handleInputChangeWrapper,
                                        required: true,
                                        className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1'
                                    },
                                    [
                                        React.createElement('option', { value: '' }, 'انتخاب کنید'),
                                        Object.entries(provincesMap).map(([name, code], index) =>
                                            React.createElement('option', { key: index, value: code }, name)
                                        )
                                    ]
                                )
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'shipping_city', className: 'block text-sm font-medium text-gray-700' }, 'شهر *'),
                                React.createElement('input', {
                                    type: 'text',
                                    id: 'shipping_city',
                                    name: 'shipping_city',
                                    value: formData.shipping_city || '',
                                    onChange: handleInputChangeWrapper,
                                    required: true,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                    placeholder: 'نام شهر را وارد کنید'
                                })
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'shipping_address', className: 'block text-sm font-medium text-gray-700' }, 'آدرس *'),
                                React.createElement('textarea', {
                                    id: 'shipping_address',
                                    name: 'shipping_address',
                                    value: formData.shipping_address || '',
                                    onChange: handleInputChangeWrapper,
                                    required: true,
                                    rows: 4,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                    placeholder: 'مثال: خیابان اصلی، پلاک 123'
                                })
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'form-group' },
                            [
                                React.createElement('label', { htmlFor: 'shipping_postcode', className: 'block text-sm font-medium text-gray-700' }, 'کد پستی *'),
                                React.createElement('input', {
                                    type: 'text',
                                    id: 'shipping_postcode',
                                    name: 'shipping_postcode',
                                    value: 'shipping_postcode' in formData ? formData.shipping_postcode : '',
                                    onChange: handleInputChangeWrapper,
                                    required: true,
                                    className: 'mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-50 transition-all duration-200 sm:text-sm flex-1',
                                    placeholder: 'مثال: 1234567890'
                                })
                            ]
                        ),
                        React.createElement('input', {
                            type: 'hidden',
                            name: 'shipping_country',
                            value: 'IR'
                        })
                    ]
                )
            ]
        );
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.stepThree = { StepThree };
})();
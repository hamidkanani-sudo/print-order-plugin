(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.stepFour) {
        return;
    }

    const { useState, useEffect } = React;

    const truncateFileName = (name, maxLength = 20) => {
        if (name.length <= maxLength) return name;
        const ext = name.split(".").pop();
        const nameWithoutExt = name.substring(0, name.lastIndexOf("."));
        const charsToShow = Math.floor((maxLength - ext.length - 3) / 2);
        return `${nameWithoutExt.substring(0, charsToShow)}...${nameWithoutExt.substring(nameWithoutExt.length - charsToShow)}.${ext}`;
    };

    const getFileIcon = (format) => {
        const iconMap = {
            psd: "/wp-content/plugins/print-order/assets/icons/psd.svg",
            jpg: "/wp-content/plugins/print-order/assets/icons/jpg.svg",
            jpeg: "/wp-content/plugins/print-order/assets/icons/jpeg.svg",
            pdf: "/wp-content/plugins/print-order/assets/icons/pdf.svg",
            png: "/wp-content/plugins/print-order/assets/icons/png.svg",
            ai: "/wp-content/plugins/print-order/assets/icons/ai.svg",
            eps: "/wp-content/plugins/print-order/assets/icons/eps.svg",
            cdr: "/wp-content/plugins/print-order/assets/icons/cdr.svg",
        };
        return iconMap[format?.toLowerCase()] || "/wp-content/plugins/print-order/assets/icons/file.svg";
    };

    const getFormatClasses = (format, fileName) => {
        const effectiveFormat = format || (fileName ? fileName.split('.').pop()?.toLowerCase() : '');
        const classMap = {
            psd: "bg-psd",
            jpg: "bg-jpg",
            jpeg: "bg-jpeg",
            pdf: "bg-pdf",
            png: "bg-png",
            ai: "bg-ai",
            eps: "bg-eps",
            cdr: "bg-cdr",
        };
        return classMap[effectiveFormat] || "";
    };

    const reverseProvincesMap = {
        THR: 'تهران',
        ABZ: 'البرز',
        ILM: 'ایلام',
        BHR: 'بوشهر',
        ADL: 'اردبیل',
        ESF: 'اصفهان',
        EAZ: 'آذربایجان شرقی',
        WAZ: 'آذربایجان غربی',
        ZAN: 'زنجان',
        SMN: 'سمنان',
        SBL: 'سیستان و بلوچستان',
        FRS: 'فارس',
        QHM: 'قم',
        QZV: 'قزوین',
        GLS: 'گلستان',
        GIL: 'گیلان',
        MZN: 'مازندران',
        MKZ: 'مرکزی',
        HRZ: 'هرمزگان',
        HMD: 'همدان',
        KRD: 'کردستان',
        KRH: 'کرمانشاه',
        KRN: 'کرمان',
        KBD: 'کهگیلویه و بویراحمد',
        KZT: 'خوزستان',
        LRS: 'لرستان',
        KHS: 'خراسان شمالی',
        KJR: 'خراسان رضوی',
        KJF: 'خراسان جنوبی',
        CHB: 'چهارمحال و بختیاری',
        YSD: 'یزد',
    };

    const StepFour = ({ product, formData, setFormData, allCustomFields, sidesMapping, deliveryDays, priceItemsState, taxAmount, totalPrice, options, setCurrentStep, subCategory, handlePaymentClick, error }) => {
        const [loginInfo, setLoginInfo] = useState(null);
        const [loginError, setLoginError] = useState(null);
        const [discountCode, setDiscountCode] = useState('');
        const [discountError, setDiscountError] = useState(null);
        const [discountSuccess, setDiscountSuccess] = useState(null);
        const [discountAmount, setDiscountAmount] = useState(0);
        const [isLoading, setIsLoading] = useState(false);
        const isLoggedIn = formData.user_id && formData.user_id !== '0';

        useEffect(() => {
            if (discountAmount > 0) {
                setFormData(prev => ({ ...prev, discount_amount: discountAmount }));
            }
        }, [discountAmount, setFormData]);

        useEffect(() => {
            if (isLoggedIn) {
                return;
            }

            fetch(window.printOrder.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_guest_user',
                    nonce: window.printOrder.public_nonce,
                    form_data: JSON.stringify(formData),
                }),
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        setLoginInfo({
                            email: data.data.email,
                            password: data.data.password,
                        });
                        setLoginError(null);
                        setFormData(prev => ({ ...prev, user_id: data.data.user_id }));
                    } else {
                        setLoginError(data.data.message || 'خطا در ایجاد حساب کاربری');
                    }
                })
                .catch(error => {
                    setLoginError('خطا در ارتباط با سرور برای ایجاد حساب کاربری: ' + error.message);
                });
        }, []);

        const handleApplyDiscount = () => {
            const trimmedCode = discountCode.trim();
            if (!trimmedCode) {
                setDiscountError('لطفاً کد تخفیف را وارد کنید.');
                setDiscountSuccess(null);
                return;
            }
            if (trimmedCode.length < 3) {
                setDiscountError('کد تخفیف باید حداقل 3 کاراکتر باشد.');
                setDiscountSuccess(null);
                return;
            }

            setIsLoading(true);
            setDiscountError(null);
            setDiscountSuccess(null);

            fetch(window.printOrder.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'print_order_apply_discount',
                    nonce: window.printOrder.nonce,
                    code: trimmedCode,
                    form_data: JSON.stringify(formData),
                    product_id: product.id,
                    total_price: totalPrice
                }),
            })
                .then(response => response.json())
                .then(data => {
                    setIsLoading(false);
                    if (data.success) {
                        setDiscountSuccess(data.data.message);
                        setDiscountError(null);
                        setDiscountAmount(data.data.discount_amount);
                        setFormData(prev => ({ ...prev, discount_code: trimmedCode, discount_amount: data.data.discount_amount }));
                    } else {
                        setDiscountError(data.data.message || 'خطا در اعمال کد تخفیف');
                        setDiscountSuccess(null);
                        setDiscountAmount(0);
                        setFormData(prev => ({ ...prev, discount_code: '', discount_amount: 0 }));
                    }
                })
                .catch(error => {
                    setIsLoading(false);
                    setDiscountError('خطا در ارتباط با سرور: ' + error.message);
                    setDiscountSuccess(null);
                    setDiscountAmount(0);
                    setFormData(prev => ({ ...prev, discount_code: '', discount_amount: 0 }));
                });
        };

        const handleRemoveDiscount = () => {
            setDiscountCode('');
            setDiscountError(null);
            setDiscountSuccess(null);
            setDiscountAmount(0);
            setFormData(prev => ({ ...prev, discount_code: '', discount_amount: 0 }));
        };

        const isPrintRequired = product && !product.no_print && !formData.no_print_needed;

        let summaryPriceItems = Array.isArray(priceItemsState) ? priceItemsState : [];
        if (!isPrintRequired) {
            summaryPriceItems = summaryPriceItems.filter(item => item.label !== 'هزینه چاپ');
        }

        return React.createElement(
            'div',
            { className: 'space-y-6' },
            [
                React.createElement(
                    'div',
                    { className: 'text-center mb-6' },
                    [
                        React.createElement('h3', { className: 'text-lg font-medium text-gray-900' }, 'تأیید و پرداخت'),
                        React.createElement('p', { className: 'text-gray-600' }, 'لطفاً اطلاعات زیر را بررسی کنید و برای پرداخت ادامه دهید.'),
                    ]
                ),
                error && React.createElement(
                    'div',
                    { className: 'form-error text-center text-red-500 p-4 bg-red-100 rounded-lg mb-4' },
                    error
                ),
                loginError && React.createElement(
                    'div',
                    { className: 'form-error text-center text-red-500 p-4 bg-red-100 rounded-lg mb-4' },
                    loginError
                ),
                loginInfo && !isLoggedIn && React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex justify-center mb-4 | login-header' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#374151',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('path', { d: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2' }),
                                            React.createElement('circle', { cx: '12', cy: '7', r: '4' }),
                                        ]),
                                        'اطلاعات ورود',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'login-fields max-w-[50%] mx-auto space-y-2' },
                            [
                                React.createElement('label', { className: 'user-field-label' }, 'ایمیل'),
                                React.createElement('div', { className: 'user-field' }, loginInfo.email),
                                React.createElement('label', { className: 'user-field-label' }, 'رمز عبور'),
                                React.createElement('div', { className: 'user-field' }, loginInfo.password),
                                React.createElement('p', { className: 'text-sm text-gray-500 mt-2 text-center' }, 'این اطلاعات برای ورود به حساب کاربری شما در سایت استفاده می‌شود. لطفاً آن‌ها را ذخیره کنید.'),
                            ]
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-center justify-between mb-4' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#374151',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('polyline', { points: '20 12 20 22 4 22 4 12' }),
                                            React.createElement('rect', { x: '2', y: '7', width: '20', height: '5' }),
                                            React.createElement('line', { x1: '12', y1: '22', x2: '12', y2: '7' }),
                                            React.createElement('path', { d: 'M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z' }),
                                            React.createElement('path', { d: 'M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z' }),
                                        ]),
                                        isPrintRequired ? 'جزئیات چاپ' : 'جزئیات سفارش',
                                    ]
                                ),
                                React.createElement(
                                    'button',
                                    {
                                        type: 'button',
                                        onClick: () => setCurrentStep(1),
                                        className: 'edit-button',
                                    },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '14',
                                            height: '14',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#4b5563',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-1',
                                        }, [
                                            React.createElement('path', { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' }),
                                            React.createElement('path', { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z' }),
                                        ]),
                                        'ویرایش',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'p',
                            { className: 'text-gray-600' },
                            isPrintRequired
                                ? `سفارش ${product?.name || 'نامشخص'} (${subCategory || 'نامشخص'}) با چاپ ${formData.paper_type_persian || 'نامشخص'} ${sidesMapping.find(s => s.value === formData.sides)?.label || 'نامشخص'} سایز ${formData.size || 'نامشخص'} به تعداد ${formData.quantity || '0'} عدد و با زمان آماده‌سازی تقریبی ${deliveryDays || '0'} روز`
                                : `سفارش ${product?.name || 'نامشخص'} (${subCategory || 'نامشخص'}) - بدون نیاز به چاپ`
                        ),
                    ]
                ),
                (allCustomFields.some(field => formData[field.name]) || formData.files?.length > 0 || formData.print_info) && React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-center justify-between mb-4' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#374151',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('path', { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' }),
                                            React.createElement('path', { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z' }),
                                        ]),
                                        'اطلاعات طرح',
                                    ]
                                ),
                                React.createElement(
                                    'button',
                                    {
                                        type: 'button',
                                        onClick: () => setCurrentStep(2),
                                        className: 'edit-button',
                                    },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '14',
                                            height: '14',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#4b5563',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-1',
                                        }, [
                                            React.createElement('path', { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' }),
                                            React.createElement('path', { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z' }),
                                        ]),
                                        'ویرایش',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'space-y-2' },
                            [
                                allCustomFields.map((field, index) =>
                                    formData[field.name] && React.createElement(
                                        'p',
                                        { key: `${field.category_id}-${field.name}`, className: 'text-gray-600' },
                                        `${field.label}: ${formData[field.name]}`
                                    )
                                ).filter(Boolean),
                                formData.files?.length > 0 && React.createElement(
                                    'div',
                                    { className: 'form-group uploaded-files flex flex-col gap-2' },
                                    formData.files.map((fileObj, index) =>
                                        React.createElement(
                                            'div',
                                            {
                                                key: index,
                                                className: `file-item inline-flex items-center p-2 bg-gray-50 border border-gray-200 rounded-md animate-fade-in`,
                                            },
                                            [
                                                React.createElement(
                                                    'div',
                                                    { className: `icon-wrapper rounded-full p-1 ${getFormatClasses(fileObj.format, fileObj.name)}` },
                                                    React.createElement('img', {
                                                        src: getFileIcon(fileObj.format || fileObj.name.split('.').pop()?.toLowerCase()),
                                                        alt: fileObj.format || fileObj.name.split('.').pop()?.toLowerCase() || 'file',
                                                        className: `w-5 h-5 format-icon ${fileObj.format || fileObj.name.split('.').pop()?.toLowerCase() || 'file'}`,
                                                        onError: (e) => {
                                                            e.target.src = "/wp-content/plugins/print-order/assets/icons/file.svg";
                                                        },
                                                    })
                                                ),
                                                React.createElement(
                                                    'span',
                                                    { className: 'text-xs text-gray-700 truncate flex-1 mx-2' },
                                                    `${truncateFileName(fileObj.name)} (${(fileObj.size / 1024 / 1024).toFixed(1)}MB)`
                                                ),
                                            ]
                                        )
                                    )
                                ),
                                formData.print_info && React.createElement(
                                    'p',
                                    { className: 'text-gray-600' },
                                    `توضیحات چاپ: ${formData.print_info}`
                                ),
                            ].filter(Boolean)
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-center justify-between mb-4' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#374151',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('path', { d: 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z' }),
                                            React.createElement('polyline', { points: '9 22 9 12 15 12 15 22' }),
                                        ]),
                                        'آدرس فاکتور',
                                    ]
                                ),
                                React.createElement(
                                    'button',
                                    {
                                        type: 'button',
                                        onClick: () => setCurrentStep(3),
                                        className: 'edit-button',
                                    },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '14',
                                            height: '14',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#4b5563',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-1',
                                        }, [
                                            React.createElement('path', { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' }),
                                            React.createElement('path', { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z' }),
                                        ]),
                                        'ویرایش',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'space-y-2' },
                            [
                                formData.customer_name && React.createElement('p', { className: 'text-gray-600' }, `نام: ${formData.customer_name} ${formData.customer_lastname || ''}`),
                                formData.customer_email && React.createElement('p', { className: 'text-gray-600' }, `ایمیل: ${formData.customer_email}`),
                                formData.customer_phone && React.createElement('p', { className: 'text-gray-600' }, `شماره تماس: ${formData.customer_phone}`),
                                (formData.billing_state || formData.billing_city) && React.createElement('p', { className: 'text-gray-600' }, `آدرس: ${reverseProvincesMap[formData.billing_state] || formData.billing_state || ''}${formData.billing_state && formData.billing_city ? '، ' : ''}${formData.billing_city || ''}${formData.billing_address ? '، ' + formData.billing_address : ''}`),
                                formData.billing_postcode && React.createElement('p', { className: 'text-gray-600' }, `کد پستی: ${formData.billing_postcode}`),
                            ]
                        ),
                    ]
                ),
                formData.ship_to_different_address && React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-center justify-between mb-4' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#374151',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('path', { d: 'M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z' }),
                                            React.createElement('circle', { cx: '12', cy: '10', r: '3' }),
                                        ]),
                                        'آدرس ارسال',
                                    ]
                                ),
                                React.createElement(
                                    'button',
                                    {
                                        type: 'button',
                                        onClick: () => setCurrentStep(3),
                                        className: 'edit-button',
                                    },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '14',
                                            height: '14',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#4b5563',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-1',
                                        }, [
                                            React.createElement('path', { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' }),
                                            React.createElement('path', { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z' }),
                                        ]),
                                        'ویرایش',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'space-y-2' },
                            [
                                formData.shipping_first_name && React.createElement('p', { className: 'text-gray-600' }, `نام گیرنده: ${formData.shipping_first_name}`),
                                formData.shipping_phone && React.createElement('p', { className: 'text-gray-600' }, `شماره تماس: ${formData.shipping_phone}`),
                                (formData.shipping_state || formData.shipping_city) && React.createElement('p', { className: 'text-gray-600' }, `آدرس: ${reverseProvincesMap[formData.shipping_state] || formData.shipping_state || ''}${formData.shipping_state && formData.shipping_city ? '، ' : ''}${formData.shipping_city || ''}${formData.shipping_address ? '، ' + formData.shipping_address : ''}`),
                                formData.shipping_postcode && React.createElement('p', { className: 'text-gray-600' }, `کد پستی: ${formData.shipping_postcode}`),
                            ]
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-center mb-4' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#3451b2',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('path', { d: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4' }),
                                            React.createElement('polyline', { points: '17 8 12 3 7 8' }),
                                            React.createElement('line', { x1: '12', y1: '3', x2: '12', y2: '15' }),
                                        ]),
                                        'کد تخفیف',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'div',
                            { className: 'space-y-2' },
                            [
                                React.createElement(
                                    'div',
                                    { className: 'flex gap-2 items-center' },
                                    [
                                        React.createElement('input', {
                                            type: 'text',
                                            value: discountCode,
                                            onChange: (e) => setDiscountCode(e.target.value),
                                            placeholder: 'کد تخفیف را وارد کنید',
                                            className: 'border rounded px-2 py-1 flex-1',
                                            disabled: isLoading
                                        }),
                                        discountAmount > 0 ? React.createElement(
                                            'button',
                                            {
                                                type: 'button',
                                                onClick: handleRemoveDiscount,
                                                className: 'bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600 flex items-center'
                                            },
                                            'حذف کد'
                                        ) : React.createElement(
                                            'button',
                                            {
                                                type: 'button',
                                                onClick: handleApplyDiscount,
                                                className: 'bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 flex items-center',
                                                disabled: isLoading
                                            },
                                            [
                                                isLoading && React.createElement('svg', {
                                                    className: 'animate-spin h-5 w-5 mr-2 text-white',
                                                    xmlns: 'http://www.w3.org/2000/svg',
                                                    fill: 'none',
                                                    viewBox: '0 0 24 24'
                                                }, [
                                                    React.createElement('circle', {
                                                        className: 'opacity-25',
                                                        cx: '12',
                                                        cy: '12',
                                                        r: '10',
                                                        stroke: 'currentColor',
                                                        strokeWidth: '4'
                                                    }),
                                                    React.createElement('path', {
                                                        className: 'opacity-75',
                                                        fill: 'currentColor',
                                                        d: 'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z'
                                                    })
                                                ]),
                                                'اعمال کد'
                                            ]
                                        ),
                                    ]
                                ),
                                discountError && React.createElement(
                                    'div',
                                    { className: 'text-red-500 text-sm mt-2' },
                                    discountError
                                ),
                                discountSuccess && React.createElement(
                                    'div',
                                    { className: 'text-green-500 text-sm mt-2' },
                                    discountSuccess
                                ),
                            ]
                        ),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'bg-white shadow rounded-lg p-4' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-center mb-0' },
                            [
                                React.createElement(
                                    'h4',
                                    { className: 'text-md font-medium text-gray-800 flex items-center' },
                                    [
                                        React.createElement('svg', {
                                            xmlns: 'http://www.w3.org/2000/svg',
                                            width: '20',
                                            height: '20',
                                            viewBox: '0 0 24 24',
                                            fill: 'none',
                                            stroke: '#3451b2',
                                            strokeWidth: '2',
                                            strokeLinecap: 'round',
                                            strokeLinejoin: 'round',
                                            className: 'mr-2',
                                        }, [
                                            React.createElement('line', { x1: '12', y1: '1', x2: '12', y2: '23' }),
                                            React.createElement('path', { d: 'M17 5H9.5a3.5 3.5 0 0 0 0 5 7h5a3.5 7 0 0 0 0-7H6' }),
                                        ]),
                                        'خلاصه فاکتور',
                                    ]
                                ),
                            ]
                        ),
                        React.createElement(
                            'table',
                            { className: 'w-full text-right' },
                            React.createElement(
                                'tbody',
                                null,
                                [
                                    summaryPriceItems.map((item, index) =>
                                        React.createElement(
                                            'tr',
                                            { key: index, className: 'border-b' },
                                            [
                                                React.createElement('td', { className: 'py-2 text-gray-600' }, item.label),
                                                React.createElement('td', { className: 'py-2 text-gray-600' }, `${item.value.toLocaleString('fa-IR')} تومان`),
                                            ]
                                        )
                                    ),
                                    discountAmount > 0 && React.createElement(
                                        'tr',
                                        { className: 'border-b' },
                                        [
                                            React.createElement('td', { className: 'py-2 text-gray-600' }, 'تخفیف'),
                                            React.createElement('td', { className: 'py-2 text-green-600' }, `-${discountAmount.toLocaleString('fa-IR')} تومان`),
                                        ]
                                    ),
                                    React.createElement(
                                        'tr',
                                        { className: 'border-b' },
                                        [
                                            React.createElement('td', { className: 'py-2 text-gray-600' }, `مالیات (${options.tax_rate || 0}%)`),
                                            React.createElement('td', { className: 'py-2 text-gray-600' }, `${taxAmount.toLocaleString('fa-IR')} تومان`),
                                        ]
                                    ),
                                    React.createElement(
                                        'tr',
                                        null,
                                        [
                                            React.createElement('td', { className: 'py-2 font-semibold text-gray-800' }, 'جمع کل'),
                                            React.createElement('td', { className: 'py-2 font-semibold text-indigo-600' }, `${(totalPrice - discountAmount).toLocaleString('fa-IR')} تومان`),
                                        ]
                                    ),
                                ]
                            )
                        ),
                    ]
                ),
            ]
        );
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.stepFour = { StepFour };
})();
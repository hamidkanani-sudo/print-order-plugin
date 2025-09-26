(function () {
    if (globalThis.PrintOrderFormLoaded) {
        return;
    }
    globalThis.PrintOrderFormLoaded = true;

    // بررسی وجود وابستگی‌های اصلی
    const checkDependencies = () => {
        if (!globalThis.React || !globalThis.ReactDOM) {
            console.error('PrintOrderForm: React or ReactDOM not loaded');
            return false;
        }
        if (!globalThis.React?.version?.startsWith('18.3.1') || !globalThis.ReactDOM?.version?.startsWith('18.3.1')) {
            console.error('PrintOrderForm: Incorrect React/ReactDOM version detected. Expected 18.3.1, got:', globalThis.React?.version, globalThis.ReactDOM?.version);
            return false;
        }
        if (!globalThis.PrintOrderForm?.formState || !globalThis.PrintOrderForm?.dataFetching) {
            console.error('PrintOrderForm: Required dependencies (formState, dataFetching, etc.) not loaded');
            return false;
        }
        if (!globalThis.printOrderWidget) {
            console.error('PrintOrderForm: printOrderWidget is not defined');
            return false;
        }
        return true;
    };

    const { useState, useEffect, memo } = globalThis.React || {};
    const { createRoot } = globalThis.ReactDOM || {};
    const { useFormState } = globalThis.PrintOrderForm?.formState || {};
    const { fetchProduct, fetchTemplateShortcode, fetchStageTemplate, handleSubmit } = globalThis.PrintOrderForm?.dataFetching || {};
    const { nextStep, prevStep } = globalThis.PrintOrderForm?.stepNavigation || {};
    const { renderInstantPrice, renderDeliveryDays } = globalThis.PrintOrderForm?.uiRendering || {};
    const { setupEventHandlers } = globalThis.PrintOrderForm?.eventHandlers || {};
    const useCustomFields = globalThis.PrintOrderForm?.customFields?.useCustomFields || (() => []);
    const { calculatePricing } = globalThis.PrintOrderForm?.formPricing || {};
    const { useUserAddress } = globalThis.PrintOrderForm?.userAddress || {};
    const { sidesMapping, handleInputChange } = globalThis.PrintOrderForm?.utils || {};
    const { renderStepOne } = globalThis.PrintOrderForm?.stepOne || {};
    const { StepTwo } = globalThis.PrintOrderForm?.stepTwo || {};
    const { StepThree } = globalThis.PrintOrderForm?.stepThree || {};
    const { StepFour } = globalThis.PrintOrderForm?.stepFour || {};

    const provincesMap = {
        'تهران': 'THR',
        'البرز': 'ABZ',
        'ایلام': 'ILM',
        'بوشهر': 'BHR',
        'اردبیل': 'ADL',
        'اصفهان': 'ESF',
        'آذربایجان شرقی': 'EAZ',
        'آذربایان غربی': 'WAZ',
        'زنجان': 'ZAN',
        'سمنان': 'SMN',
        'سیستان و بلوچستان': 'SBL',
        'فارس': 'FRS',
        'قم': 'QHM',
        'قزوین': 'QZV',
        'گلستان': 'GLS',
        'گیلان': 'GHL',
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

    // توابع مقایسه سفارشی
    const comparePaperTypes = (a, b) => {
        // ترتیب الفبایی کنترل‌شده برای انواع کاغذ
        const paperA = a.toLowerCase();
        const paperB = b.toLowerCase();
        return paperA < paperB ? -1 : paperA > paperB ? 1 : 0;
    };

    const compareSizes = (a, b) => {
        // ترتیب استاندارد برای اندازه‌ها (مثل A3 > A4 > A5)
        const sizePriority = ['A3', 'A4', 'A5', 'A6', 'B3', 'B4', 'B5', 'B6'];
        const indexA = sizePriority.indexOf(a);
        const indexB = sizePriority.indexOf(b);
        if (indexA === -1 && indexB === -1) return a < b ? -1 : a > b ? 1 : 0;
        if (indexA === -1) return 1;
        if (indexB === -1) return -1;
        return indexA - indexB;
    };

    const compareSides = (a, b) => {
        // ترتیب ثابت: یک‌طرفه > دو‌طرفه
        const sidesPriority = ['یک‌طرفه', 'دو‌طرفه'];
        const indexA = sidesPriority.indexOf(a);
        const indexB = sidesPriority.indexOf(b);
        if (indexA === -1 && indexB === -1) return a < b ? -1 : a > b ? 1 : 0;
        if (indexA === -1) return 1;
        if (indexB === -1) return -1;
        return indexA - indexB;
    };

    const LoadingSpinner = () => React.createElement(
        'div',
        {
            style: {
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                minHeight: '100px',
                padding: '16px',
                width: '100%',
                boxSizing: 'border-box',
            }
        },
        [
            React.createElement(
                'svg',
                {
                    className: 'animate-spin',
                    style: {
                        width: '24px',
                        height: '24px',
                        marginLeft: '8px',
                        color: '#3b82f6',
                    },
                    xmlns: 'http://www.w3.org/2000/svg',
                    fill: 'none',
                    viewBox: '0 0 24 24',
                },
                [
                    React.createElement('circle', {
                        style: { opacity: 0.25 },
                        cx: '12',
                        cy: '12',
                        r: '10',
                        stroke: 'currentColor',
                        strokeWidth: '4',
                    }),
                    React.createElement('path', {
                        style: { opacity: 0.75 },
                        fill: 'currentColor',
                        d: 'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z',
                    }),
                ]
            ),
            React.createElement(
                'span',
                {
                    style: {
                        color: '#4b5563',
                        fontSize: '14px',
                    }
                },
                'در حال بارگذاری...'
            ),
        ]
    );

    const PrintOrderForm = memo(() => {
        const urlParams = new URLSearchParams(globalThis.location.search);
        const productId = urlParams.get('product_id') || globalThis.printOrderWidget?.product_id || null;
        const { ajax_url, nonce, pricing, category_fields, options, temp_id } = globalThis.printOrder || {};
        const { is_editor, preview_step } = globalThis.printOrderWidget || {};

        if (!globalThis.printOrderWidget) {
            console.error('PrintOrderForm: printOrderWidget is not defined');
            return React.createElement(
                'div',
                { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center my-4' },
                'خطا: تنظیمات ویجت یافت نشد. لطفاً صفحه را رفرش کنید.'
            );
        }

        const [localTempId, setLocalTempId] = useState(temp_id);

        useEffect(() => {
            if (!temp_id) {
                console.warn('PrintOrderForm: temp_id is missing, fetching new temp_id');
                fetchNewTempId();
            }
        }, [temp_id]);

        const fetchNewTempId = () => {
            if (!ajax_url || !nonce) {
                console.error('fetchNewTempId: Missing ajax_url or nonce');
                setError('خطای پیکربندی: اطلاعات Ajax یا نانس ناقص است');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'print_order_generate_temp_id');
            formData.append('nonce', nonce);

            fetch(ajax_url, {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.temp_id) {
                        setLocalTempId(data.data.temp_id);
                        globalThis.printOrder.temp_id = data.data.temp_id;
                    } else {
                        console.error('fetchNewTempId: Failed to fetch temp_id:', data);
                        setError('خطا در دریافت شناسه موقت');
                    }
                })
                .catch(error => {
                    console.error('fetchNewTempId: Error:', error);
                    setError('خطا در ارتباط با سرور برای دریافت شناسه موقت');
                });
        };

        if (!ajax_url || !nonce || !localTempId) {
            console.error('PrintOrderForm: Missing ajax_url, nonce, or temp_id');
            return React.createElement(
                'div',
                { className: 'form-error text-center text-red-500 p-4 bg-red-100 rounded-lg my-4' },
                'خطای پیکربندی: اطلاعات Ajax یا شناسه موقت ناقص است'
            );
        }

        const {
            product, setProduct,
            error, setError,
            stepError, setStepError,
            successMessage, setSuccessMessage,
            loading, setLoading,
            submitting, setSubmitting,
            currentStep, setCurrentStep,
            formData, setFormData,
            taxAmount, setTaxAmount,
            totalPrice, setTotalPrice,
            deliveryDays, setDeliveryDays,
            priceItemsState, setPriceItemsState,
            visibleItems, setVisibleItems,
        } = useFormState();

        useEffect(() => {
            if (currentStep === 4 && !globalThis.PrintOrderForm?.stepFour?.StepFour) {
                console.warn('StepFour not available, waiting for it to load...');
                setLoading(true);
                const checkStepFour = setInterval(() => {
                    if (globalThis.PrintOrderForm?.stepFour?.StepFour) {
                        setLoading(false);
                        clearInterval(checkStepFour);
                    }
                }, 100);
                return () => clearInterval(checkStepFour);
            }
        }, [currentStep]);

        const initialStep = is_editor && preview_step ? Number.parseInt(preview_step, 10) : 1;
        useEffect(() => {
            setCurrentStep(initialStep);
        }, [initialStep]);

        const allCustomFields = useCustomFields(product, category_fields);
        const subCategory = product?.categories?.find(cat => cat.parent !== 0)?.name || product?.category_name || 'نامشخص';

        useEffect(() => {
            const event = new CustomEvent('printOrderFormStateChange', {
                detail: { currentStep, formData, product },
            });
            globalThis.dispatchEvent(event);
        }, [currentStep, formData, product]);

        useEffect(() => {
            if (!productId) {
                setError('شناسه محصول یافت نشد');
                setLoading(false);
                return;
            }
            localStorage.removeItem("uploadedFiles");
            fetchProduct(productId, ajax_url, nonce, setProduct, setError, setLoading);
        }, [productId, ajax_url, nonce]);

        const handleNextStep = () => {
            const isPrintRequired = product?.no_print ? false : !formData.no_print_needed;
            
            if (currentStep === 1 && !isPrintRequired) {
                setStepError('');
                setSuccessMessage('');
                setCurrentStep(currentStep + 1);
            } else {
                nextStep(currentStep, formData, setCurrentStep, setStepError, setSuccessMessage);
            }
        };

        useEffect(() => {
            if (product?.no_print && currentStep === 1 && !is_editor) {
                handleNextStep();
            }
        }, [product, currentStep, is_editor]);

        useEffect(() => {
            if (!product || !options) return;

            const modifiedOptions = { ...options };
            if (product.design_price > 0) {
                modifiedOptions.design_fee = product.design_price;
            }
            
            calculatePricing(
                product,
                formData,
                pricing,
                modifiedOptions,
                currentStep,
                setDeliveryDays,
                setTaxAmount,
                setTotalPrice,
                setPriceItemsState,
                setVisibleItems
            );
        }, [product, formData, pricing, options, currentStep]);

        setupEventHandlers(currentStep);

        const handlePaymentClick = (e) => {
            e.preventDefault();
            setSubmitting(true);
            setError('');
            
            if (currentStep === 4) {
                let requiredFields = ['customer_name', 'customer_lastname', 'customer_email', 'customer_phone', 'billing_state', 'billing_city', 'billing_address'];
                
                if (product?.no_print ? false : !formData.no_print_needed) {
                    requiredFields.push('paper_type_persian', 'size', 'quantity', 'sides');
                }
                
                const missingFields = requiredFields.filter(field => !formData[field]);
                if (missingFields.length > 0) {
                    setError(`لطفاً فیلدهای اجباری را پر کنید: ${missingFields.join(', ')}`);
                    setSubmitting(false);
                    return;
                }

                if (formData.files && !Array.isArray(formData.files)) {
                    setError('ساختار فایل‌ها نامعتبر است. لطفاً دوباره فایل‌ها را آپلود کنید.');
                    setSubmitting(false);
                    return;
                }

                if (formData.files && formData.files.length > 0) {
                    const invalidFiles = formData.files.filter(file => !file.temp_url || !file.name);
                    if (invalidFiles.length > 0) {
                        setError('برخی از فایل‌ها ساختار معتبر ندارند. لطفاً دوباره آپلود کنید.');
                        setSubmitting(false);
                        return;
                    }
                }

                const provinceCode = provincesMap[formData.billing_state] || formData.billing_state;
                if (!Object.values(provincesMap).includes(provinceCode)) {
                    setError('استان انتخاب‌شده معتبر نیست. لطفاً یک استان معتبر انتخاب کنید.');
                    setSubmitting(false);
                    return;
                }

                const updatedFormData = {
                    ...formData,
                    billing_state: provinceCode,
                    shipping_state: formData.ship_to_different_address && formData.shipping_state ? (provincesMap[formData.shipping_state] || formData.shipping_state) : provinceCode,
                };

                const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('درخواست به سرور بیش از حد طول کشید')), 10000);
                });

                Promise.race([
                    handleSubmit(
                        e,
                        productId,
                        updatedFormData,
                        ajax_url,
                        nonce,
                        product,
                        category_fields,
                        setError,
                        setSubmitting,
                        localTempId
                    ),
                    timeoutPromise
                ]).catch(error => {
                    setError(error.message || 'خطا در ارتباط با سرور');
                    setSubmitting(false);
                });
            }
        };

        if (loading) return React.createElement('div', { className: 'form-loading text-center text-gray-500 py-8 px-4' }, 'در حال بارگذاری فرم...');
        if (error) return React.createElement('div', { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center' }, error);
        if (!product || !product.category_id) {
            return React.createElement('div', { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center mb-4' }, 'خطا: دسته‌بندی محصول یافت نشد.');
        }

        const categoryId = product?.category_id || '';
        const categoryName = product?.category_name || 'نامشخص';
        const paperTypesPersian = categoryId && pricing[categoryId]
            ? [...new Set(pricing[categoryId].filter(item => item.paper_type_persian).map(item => item.paper_type_persian))].filter(Boolean).sort(comparePaperTypes)
            : [];
        const sizes = categoryId && pricing[categoryId] && formData.paper_type_persian
            ? [...new Set(pricing[categoryId].filter(item => item.paper_type_persian === formData.paper_type_persian).map(item => item.size))].filter(Boolean).sort(compareSizes)
            : [];
        const quantities = categoryId && formData.paper_type_persian && formData.size && pricing[categoryId]
            ? [...new Set(pricing[categoryId].filter(item => item.paper_type_persian === formData.paper_type_persian && item.size === formData.size).map(item => String(item.quantity)))].sort((a, b) => Number(a) - Number(b))
            : [];
        const sidesOptions = categoryId && formData.paper_type_persian && formData.size && formData.quantity && pricing[categoryId]
            ? [...new Set(pricing[categoryId].filter(item => item.paper_type_persian === formData.paper_type_persian && item.size === formData.size && String(item.quantity) === String(formData.quantity)).map(item => item.sides))].sort(compareSides)
            : [];
        const hasValidPricing = paperTypesPersian.length > 0;

        if (currentStep === 1 && !hasValidPricing && !product?.no_print) {
            return React.createElement('div', { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center mb-4' }, 'خطا: هیچ اطلاعات قیمت‌گذاری یافت نشد.');
        }

        return React.createElement(
            'div',
            { className: 'order-form' },
            [
                React.createElement('style', null, `
                    .clicked { transform: scale(0.95); transition: transform 0.1s ease; }
                    .different-address-message { transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out; }
                    .different-address-message.hidden { opacity: 0; transform: translateY(-10px); }
                    .different-address-message.visible { opacity: 1; transform: translateY(0); }
                    .progress-bar { display: flex; justify-content: space-between; align-items: center; position: relative; margin-bottom: 20px; }
                    .step { flex: 1; text-align: center; position: relative; z-index: 1; }
                    .circle { width: 30px; height: 30px; line-height: 30px; border-radius: 50%; background-color: #e5e7eb; color: #6b7280; font-weight: bold; margin: 0 auto 5px; }
                    .step.completed .circle { background-color: #2563eb; color: #ffffff; }
                    .step.active .circle { background-color: #3b82f6; color: #ffffff; border: 2px solid #1e40af; }
                    .label { font-size: 14px; color: #4b5563; }
                    .progress-line { position: absolute; top: 15px; left: 0; height: 4px; background-color: #2563eb; transition: width 0.3s ease; z-index: 0; }
                    .progress-line.active { background-color: #3b82f6; }
                `),
                React.createElement(
                    'div',
                    { className: 'progress-bar' },
                    [
                        [{ label: 'نوع چاپ', step: 1 }, { label: 'اطلاعات طرح', step: 2 }, { label: 'آدرس', step: 3 }, { label: 'پرداخت', step: 4 }].map((item) =>
                            React.createElement(
                                'div',
                                {
                                    key: `step-${item.step}`,
                                    className: `step ${currentStep >= item.step ? 'completed' : ''} ${currentStep === item.step ? 'active' : ''}`,
                                },
                                [
                                    React.createElement('div', { className: 'circle' }, item.step),
                                    React.createElement('p', { className: 'label' }, item.label),
                                ]
                            )
                        ),
                        React.createElement('div', { className: 'progress-line' }),
                    ]
                ),
                React.createElement(
                    'div',
                    { className: 'card' },
                    [
                        React.createElement(
                            'div',
                            { className: 'flex items-start mb-2 border-b pb-4 flex-row' },
                            [
                                product?.image && React.createElement('img', {
                                    src: product.image,
                                    alt: product.name,
                                    className: 'w-24 h-24 object-contain rounded-lg mr-4',
                                }),
                                React.createElement(
                                    'div',
                                    { className: 'text-right flex-1' },
                                    [
                                        React.createElement('h2', { className: 'text-lg font-semibold text-gray-800' }, product.name),
                                        React.createElement(
                                            'p',
                                            { className: 'text-gray-600 flex items-center justify-end' },
                                            [
                                                React.createElement('svg', {
                                                    xmlns: 'http://www.w3.org/2000/svg',
                                                    width: '16px',
                                                    height: '16px',
                                                    viewBox: '0 0 24 24',
                                                    fill: 'none',
                                                    stroke: '#374151',
                                                    strokeWidth: '2',
                                                    strokeLinecap: 'round',
                                                    strokeLinejoin: 'round',
                                                    className: 'ml-1',
                                                }, [
                                                    React.createElement('line', { x1: '8', y1: '6', x2: '21', y2: '6' }),
                                                    React.createElement('line', { x1: '8', y1: '12', x2: '21', y2: '12' }),
                                                    React.createElement('line', { x1: '8', y1: '18', x2: '21', y2: '18' }),
                                                    React.createElement('line', { x1: '3', y1: '6', x2: '3.01', y2: '6' }),
                                                    React.createElement('line', { x1: '3', y1: '12', x2: '3.01', y2: '12' }),
                                                    React.createElement('line', { x1: '3', y1: '18', x2: '3.01', y2: '18' }),
                                                ]),
                                                categoryName,
                                            ]
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        stepError && React.createElement(
                            'div',
                            { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center mb-4' },
                            stepError
                        ),
                        error && React.createElement(
                            'div',
                            { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center mb-4' },
                            error
                        ),
                        successMessage && currentStep === 1 && React.createElement(
                            'div',
                            { className: 'form-success text-green-500 p-4 bg-green-100 rounded-lg text-center mb-4' },
                            successMessage
                        ),
                        React.createElement(
                            'form',
                            {
                                onSubmit: (e) => {
                                    e.preventDefault();
                                    handleSubmit(e, productId, formData, ajax_url, nonce, product, category_fields, setError, setSubmitting, localTempId);
                                },
                                className: 'space-y-6',
                            },
                            [
                                currentStep === 1 && renderStepOne(
                                    formData,
                                    (e) => handleInputChange(e, setFormData),
                                    paperTypesPersian,
                                    sizes,
                                    quantities,
                                    sidesOptions,
                                    sidesMapping,
                                    renderInstantPrice,
                                    renderDeliveryDays,
                                    currentStep,
                                    product,
                                    priceItemsState,
                                    visibleItems,
                                    deliveryDays
                                ),
                                currentStep === 2 && React.createElement(
                                    'div',
                                    { className: 'step-two-wrapper' },
                                    [
                                        (() => {
                                            try {
                                                return React.createElement(StepTwo, {
                                                    allCustomFields,
                                                    formData,
                                                    onChange: (e) => handleInputChange(e, setFormData),
                                                    setFormData,
                                                    setStepError,
                                                    renderInstantPrice,
                                                    currentStep,
                                                    product,
                                                    priceItemsState,
                                                    visibleItems,
                                                    temp_id: localTempId,
                                                });
                                            } catch (error) {
                                                console.error('Error rendering StepTwo:', error);
                                                return React.createElement(
                                                    'div',
                                                    { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center mb-4' },
                                                    'خطا در بارگذاری مرحله آپلود فایل. لطفاً دوباره تلاش کنید.'
                                                );
                                            }
                                        })(),
                                    ]
                                ),
                                currentStep === 3 && React.createElement(StepThree, {
                                    formData,
                                    handleInputChange: (e) => handleInputChange(e, setFormData),
                                    provinces: Object.keys(provincesMap),
                                    renderInstantPrice,
                                    currentStep,
                                    product,
                                    priceItemsState,
                                    visibleItems
                                }),
                                currentStep === 4 && (
                                    StepFour
                                        ? React.createElement(StepFour, {
                                              product,
                                              formData,
                                              setFormData,
                                              allCustomFields,
                                              sidesMapping,
                                              deliveryDays,
                                              priceItemsState,
                                              taxAmount,
                                              totalPrice,
                                              options,
                                              setCurrentStep,
                                              subCategory,
                                              handlePaymentClick
                                          })
                                        : React.createElement(
                                              'div',
                                              { className: 'form-error text-red-500 p-4 bg-red-100 rounded-lg text-center mb-4' },
                                              'خطا: کامپوننت مرحله چهارم لود نشده است'
                                          )
                                ),
                            ]
                        ),
                    ]
                ),
                !is_editor && React.createElement( 
                    'div',
                    { className: 'form-actions' },
                    [
                        currentStep < 4 && [
                            renderDeliveryDays(currentStep, product, formData, deliveryDays),
                            renderInstantPrice(currentStep, product, formData, priceItemsState, visibleItems),
                        ],
                        React.createElement(
                            'div',
                            { className: 'buttons flex justify-between w-full' },
                            [
                                currentStep === 1 && [
                                    React.createElement(
                                        'div',
                                        { className: 'w-1/2' },
                                        null
                                    ),
                                    React.createElement(
                                        'button',
                                        {
                                            type: 'button',
                                            onClick: handleNextStep,
                                            className: 'next-stage bg-blue-600 text-white px-6 py-3 rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 w-1/2 mr-2',
                                        },
                                        'بعدی'
                                    ),
                                ],
                                (currentStep === 2 || currentStep === 3) && [
                                    React.createElement(
                                        'button',
                                        {
                                            type: 'button',
                                            onClick: () => prevStep(setCurrentStep, setStepError, setSuccessMessage),
                                            className: 'prev-stage bg-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-sm hover:bg-gray-400 transition-all duration-200 w-1/2 mr-2',
                                        },
                                        'قبلی'
                                    ),
                                    React.createElement(
                                        'button',
                                        {
                                            type: 'button',
                                            onClick: handleNextStep,
                                            className: 'next-stage bg-blue-600 text-white px-6 py-3 rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 w-1/2',
                                        },
                                        'بعدی'
                                    ),
                                ],
                                currentStep === 4 && [
                                    React.createElement(
                                        'button',
                                        {
                                            type: 'button',
                                            onClick: () => prevStep(setCurrentStep, setStepError, setSuccessMessage),
                                            className: 'prev-stage bg-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-sm hover:bg-gray-400 transition-all duration-200 w-1/2 mr-2',
                                        },
                                        'قبلی'
                                    ),
                                    React.createElement(
                                        'button',
                                        {
                                            type: 'button',
                                            onClick: handlePaymentClick,
                                            disabled: submitting,
                                            className: `submit-order bg-blue-600 text-white px-6 py-3 rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 w-1/2 ${submitting ? 'opacity-50 cursor-not-allowed' : ''}`,
                                        },
                                        submitting ? React.createElement(
                                            'span',
                                            { className: 'flex items-center justify-center' },
                                            [
                                                React.createElement('svg', {
                                                    className: 'animate-spin h-5 w-5 mr-2 text-white',
                                                    xmlns: 'http://www.w3.org/2000/svg',
                                                    fill: 'none',
                                                    viewBox: '0 0 24 24',
                                                }, [
                                                    React.createElement('circle', {
                                                        className: 'opacity-25',
                                                        cx: '12',
                                                        cy: '12',
                                                        r: '10',
                                                        stroke: 'currentColor',
                                                        strokeWidth: '4',
                                                    }),
                                                    React.createElement('path', {
                                                        className: 'opacity-75',
                                                        fill: 'currentColor',
                                                        d: 'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 0.042 1.135 5.824 3 7.938l3-2.647z',
                                                    }),
                                                ]),
                                                'در حال اتصال...',
                                            ]
                                        ) : 'پرداخت'
                                    ),
                                ],
                            ]
                        ),
                    ]
                ),
            ]
        );
    });

    globalThis.PrintOrderForm = globalThis.PrintOrderForm || {};
    globalThis.PrintOrderForm.PrintOrderForm = PrintOrderForm;

    function initializeForm() {
        const domContainer = document.getElementById('print-order-form');
        if (domContainer && globalThis.PrintOrderForm?.PrintOrderForm && checkDependencies()) {
            const root = createRoot(domContainer);
            root.render(React.createElement(LoadingSpinner));
            root.render(React.createElement(globalThis.PrintOrderForm.PrintOrderForm));
        } else {
            console.error('PrintOrderForm: DOM container #print-order-form or dependencies not found');
            if (domContainer) {
                domContainer.innerHTML = '<div class="form-error text-red-500 p-4 bg-red-100 rounded-lg text-center">خطا: فرم بارگذاری نشد. لطفاً صفحه را رفرش کنید.</div>';
            }
        }
    }

    // بررسی اولیه برای اطمینان از لود کامل DOM و وابستگی‌ها
    function attemptInitialization(attempts = 10, delay = 1000) {
        if (attempts <= 0) {
            console.error('PrintOrderForm: Failed to initialize after multiple attempts');
            const domContainer = document.getElementById('print-order-form');
            if (domContainer) {
                domContainer.innerHTML = '<div class="form-error text-red-500 p-4 bg-red-100 rounded-lg text-center">خطا: فرم بارگذاری نشد. لطفاً صفحه را رفرش کنید.</div>';
            }
            return;
        }
        setTimeout(() => {
            if (document.getElementById('print-order-form') && globalThis.PrintOrderForm?.PrintOrderForm && checkDependencies()) {
                initializeForm();
            } else {
                console.warn(`PrintOrderForm: DOM or dependencies not ready, retrying (${attempts - 1} attempts left)`);
                attemptInitialization(attempts - 1, delay * 1.2);
            }
        }, delay);
    }

    // استفاده از DOMContentLoaded برای اطمینان از لود کامل DOM
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        attemptInitialization();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            attemptInitialization();
        });
    }
})();
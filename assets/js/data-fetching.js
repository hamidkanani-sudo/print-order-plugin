(function (global) {
    'use strict';

    global.PrintOrderForm = global.PrintOrderForm || {};
    global.PrintOrderForm.dataFetching = global.PrintOrderForm.dataFetching || {};

    global.PrintOrderForm.dataFetching.fetchProduct = async function (productId, ajax_url, nonce, setProduct, setError, setLoading) {
        try {
            setLoading(true);
            const response = await fetch(ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'get_product_info',
                    nonce: nonce,
                    product_id: productId,
                }),
            });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error('fetchProduct: Failed to parse JSON', err.message, 'Response text:', text);
                throw new Error('پاسخ سرور نامعتبر است: ' + text);
            }
            if (data.success && data.data) {
                const productData = {
                    ...data.data,
                    categories: Array.isArray(data.data.categories) ? data.data.categories : [],
                };
                if (!productData.category_id) {
                    throw new Error('دسته‌بندی محصول یافت نشد.');
                }
                setProduct(productData);
            } else {
                throw new Error(data.data.message || 'خطا در دریافت اطلاعات محصول');
            }
        } catch (err) {
            console.error('fetchProduct: Ajax error =', err.message);
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    global.PrintOrderForm.dataFetching.fetchTemplateShortcode = async function (categoryId, paperTypePersian, ajax_url, nonce, setShortcodeContent, setLoadingTemplate) {
        setLoadingTemplate(true);
        try {
            const formData = new FormData();
            formData.append('action', 'print_order_get_template_shortcode');
            formData.append('nonce', nonce);
            formData.append('category_id', categoryId);
            if (paperTypePersian) {
                formData.append('paper_type_persian', paperTypePersian);
            }
            const response = await fetch(ajax_url, { method: 'POST', body: formData });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error('fetchTemplateShortcode: Failed to parse JSON', err.message, 'Response text:', text);
                throw new Error('پاسخ سرور نامعتبر است');
            }
            if (data.success && data.data && data.data.content) {
                setShortcodeContent(data.data.content);
            } else {
                console.warn('fetchTemplateShortcode: No valid content found, message:', data.data?.message || 'هیچ قالبی یافت نشد');
                setShortcodeContent('<p class="text-gray-600 text-center">' + (data.data?.message || 'هیچ قالبی برای این ترکیب تعریف نشده است.') + '</p>');
            }
            return data;
        } catch (err) {
            console.error('fetchTemplateShortcode: Error =', err.message);
            setShortcodeContent('<p class="text-red-600 text-center">خطا در بارگذاری قالب: ' + err.message + '</p>');
            return { success: false, error: err.message };
        } finally {
            setLoadingTemplate(false);
        }
    };

    global.PrintOrderForm.dataFetching.fetchStageTemplate = async function (stage, ajax_url, nonce, setShortcodeContent, setLoadingTemplate) {
        setLoadingTemplate(true);
        try {
            const formData = new FormData();
            formData.append('action', 'print_order_get_stage_template');
            formData.append('nonce', nonce);
            formData.append('stage', stage);
            const response = await fetch(ajax_url, { method: 'POST', body: formData });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error('fetchStageTemplate: Failed to parse JSON', err.message, 'Response text:', text);
                throw new Error('پاسخ سرور نامعتبر است');
            }
            if (data.success && data.data && data.data.content) {
                setShortcodeContent(data.data.content);
            } else {
                console.warn('fetchStageTemplate: No valid content found, message:', data.data?.message || 'هیچ قالبی یافت نشد');
                setShortcodeContent('<p class="text-gray-600 text-center">' + (data.data?.message || 'هیچ قالبی یافت نشد.') + '</p>');
            }
            return data;
        } catch (err) {
            console.error('fetchStageTemplate: Error =', err.message);
            setShortcodeContent('<p class="text-red-600 text-center">خطا در بارگذاری قالب: ' + err.message + '</p>');
            return { success: false, error: err.message };
        } finally {
            setLoadingTemplate(false);
        }
    };

    global.PrintOrderForm.dataFetching.checkEmailExists = async function (email, ajax_url, nonce) {
        try {
            if (!nonce) {
                console.error('checkEmailExists: Nonce is missing');
                throw new Error('خطای امنیتی: نانس یافت نشد');
            }
            const response = await fetch(ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'check_email_exists',
                    nonce: nonce,
                    email: email,
                }),
            });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error('checkEmailExists: Failed to parse JSON', err.message, 'Response text:', text);
                throw new Error('پاسخ سرور نامعتبر است: ' + text);
            }
            if (data.success) {
                return data;
            } else {
                throw new Error(data.data.message || 'خطا در بررسی ایمیل');
            }
        } catch (err) {
            console.error('checkEmailExists: Ajax error =', err.message);
            throw err;
        }
    };

    global.PrintOrderForm.dataFetching.handleSubmit = async function (e, productId, formData, ajax_url, nonce, product, category_fields, setError, setSubmitting, temp_id) {
        e.preventDefault();
        if (!productId) {
            setError('شناسه محصول یافت نشد');
            return;
        }

        // START: MODIFIED CODE - Dynamic required fields validation
        const isPrintRequired = product && !product.no_print && !formData.no_print_needed;

        let requiredFields = [
            { name: 'customer_name', label: 'نام' },
            { name: 'customer_lastname', label: 'نام خانوادگی' },
            { name: 'customer_email', label: 'ایمیل' },
            { name: 'customer_phone', label: 'شماره تماس' },
            { name: 'billing_state', label: 'استان' },
            { name: 'billing_city', label: 'شهر' },
            { name: 'billing_address', label: 'آدرس' },
            { name: 'billing_postcode', label: 'کد پستی' },
        ];

        if (isPrintRequired) {
            requiredFields.push(
                { name: 'paper_type_persian', label: 'جنس کاغذ' },
                { name: 'size', label: 'سایز' },
                { name: 'quantity', label: 'تعداد' },
                { name: 'sides', label: 'نوع چاپ' }
            );
        }
        // END: MODIFIED CODE

        if (formData.ship_to_different_address) {
            requiredFields.push(
                { name: 'shipping_state', label: 'استان ارسال' },
                { name: 'shipping_city', label: 'شهر ارسال' },
                { name: 'shipping_address', label: 'آدرس ارسال' },
                { name: 'shipping_postcode', label: 'کد پستی ارسال' }
            );
        }
        
        const allCustomFields = [];
        if (product?.categories?.length > 0) {
            const sortedCategories = [...product.categories].sort((a, b) => {
                if (a.parent !== 0 && b.parent === 0) return -1;
                if (a.parent === 0 && b.parent !== 0) return 1;
                return 0;
            });
            const seenFieldNames = new Set();
            sortedCategories.forEach(cat => {
                const fields = category_fields[cat.term_id] || [];
                fields.forEach(field => {
                    if (!seenFieldNames.has(field.name)) {
                        allCustomFields.push(field);
                        seenFieldNames.add(field.name);
                    }
                });
            });
        }
        allCustomFields.forEach(field => {
            if (field.required) {
                requiredFields.push({ name: field.name, label: field.label });
            }
        });

        const missingFields = requiredFields.filter(field => !formData[field.name]);
        if (missingFields.length > 0) {
            setError(`لطفاً فیلدهای اجباری را پر کنید: ${missingFields.map(f => f.label).join('، ')}`);
            setSubmitting(false);
            return;
        }

        setSubmitting(true);
        setError(null);
        
        const formDataToSend = new FormData();
        formDataToSend.append('action', 'print_order_submit');
        formDataToSend.append('nonce', nonce);
        formDataToSend.append('wc_product_id', productId);
        formDataToSend.append('temp_id', temp_id);

        for (const [key, value] of Object.entries(formData)) {
            if (key === 'files' && Array.isArray(value)) {
                const filteredFiles = value.map(file => ({
                    temp_url: file.temp_url,
                    name: file.name
                }));
                formDataToSend.append('files', JSON.stringify(filteredFiles));
            } else if (value !== null && value !== undefined && value !== '') {
                formDataToSend.append(key, value);
            }
        }
        
        allCustomFields.forEach(field => {
            const value = formData[field.name] || '';
            if (value !== '') {
                formDataToSend.append(`extra_${field.name}`, value);
            }
        });
        
        try {
            const response = await fetch(ajax_url, { method: 'POST', body: formDataToSend });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error('handleSubmit: Failed to parse JSON', err.message);
                throw new Error('پاسخ سرور نامعتبر است');
            }
            if (data.success && data.data && data.data.redirect_url) {
                if (typeof data.data.redirect_url === 'string') {
                    window.location.href = data.data.redirect_url;
                } else {
                    throw new Error('آدرس بازگشتی نامعتبر است.');
                }
            } else {
                throw new Error(data.data.message || 'خطا در ثبت سفارش');
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };
})(window);
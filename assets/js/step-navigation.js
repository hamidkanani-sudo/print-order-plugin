(function (global) {
    'use strict';

    global.PrintOrderForm = global.PrintOrderForm || {};
    global.PrintOrderForm.stepNavigation = global.PrintOrderForm.stepNavigation || {};

    global.PrintOrderForm.stepNavigation.nextStep = function (currentStep, formData, setCurrentStep, setStepError, setSuccessMessage) {
        if (currentStep === 1) {
            const missingFields = [];
            if (!formData.paper_type_persian) missingFields.push('جنس کاغذ');
            if (!formData.size) missingFields.push('سایز');
            if (!formData.quantity) missingFields.push('تعداد');
            if (!formData.sides) missingFields.push('نوع چاپ');
            if (missingFields.length > 0) {
                setStepError(`لطفاً فیلدهای اجباری را پر کنید: ${missingFields.join('، ')}`);
                return;
            }
            setSuccessMessage('مشخصات چاپ با موفقیت ثبت شد.');
        }
        if (currentStep === 2) {
            // پیام موفقیت برای مرحله ۲ حذف شده است
        }
        if (currentStep === 3) {
            const missingFields = [];
            const invalidFields = [];
            if (!formData.customer_name) missingFields.push('نام و نام خانوادگی');
            if (!formData.customer_email) missingFields.push('ایمیل');
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.customer_email)) invalidFields.push('ایمیل (فرمت نامعتبر)');
            if (!formData.customer_phone) missingFields.push('شماره تماس');
            else if (!/^09\d{9}$/.test(formData.customer_phone)) invalidFields.push('شماره تماس (باید 11 رقم و با 09 شروع شود)');
            if (!formData.billing_state) missingFields.push('استان');
            if (!formData.billing_city) missingFields.push('شهر');
            if (!formData.billing_address) missingFields.push('آدرس');
            if (!formData.billing_postcode) missingFields.push('کد پستی');
            else if (!/^\d{10}$/.test(formData.billing_postcode)) invalidFields.push('کد پستی (باید 10 رقم باشد)');
            if (formData.ship_to_different_address) {
                if (!formData.shipping_state) missingFields.push('استان ارسال');
                if (!formData.shipping_city) missingFields.push('شهر ارسال');
                if (!formData.shipping_address) missingFields.push('آدرس ارسال');
                if (!formData.shipping_postcode) missingFields.push('کد پستی ارسال');
                else if (!/^\d{10}$/.test(formData.shipping_postcode)) invalidFields.push('کد پستی ارسال (باید 10 رقم باشد)');
                if (formData.shipping_phone && !/^09\d{9}$/.test(formData.shipping_phone)) invalidFields.push('شماره تماس گیرنده (باید 11 رقم و با 09 شروع شود)');
            }
            if (missingFields.length > 0) {
                setStepError(`لطفاً فیلدهای اجباری را پر کنید: ${missingFields.join('، ')}`);
                return;
            }
            if (invalidFields.length > 0) {
                setStepError(`لطفاً خطاها را برطرف کنید: ${invalidFields.join('، ')}`);
                return;
            }
        }
        setStepError(null);
        setTimeout(() => {
            setCurrentStep(prev => Math.min(prev + 1, 4));
        }, 500); // تأخیر 500 میلی‌ثانیه
    };

    global.PrintOrderForm.stepNavigation.prevStep = function (setCurrentStep, setStepError, setSuccessMessage) {
        setCurrentStep(prev => Math.max(prev - 1, 1));
        setStepError(null);
        setSuccessMessage(null);
    };
})(window);
(function (global, React) {
    'use strict';

    if (!global.PrintOrderForm) {
        global.PrintOrderForm = {};
    }
    global.PrintOrderForm.formState = global.PrintOrderForm.formState || {};

    if (!React || !React.useState || !React.useEffect) {
        console.error('useFormState: React, useState, or useEffect is not available');
        global.PrintOrderForm.formState.useFormState = () => {
            console.warn('useFormState: Returning empty state due to missing React dependencies');
            return {};
        };
        return;
    }

    const useFormState = () => {
        const [product, setProduct] = React.useState(null);
        const [error, setError] = React.useState('');
        const [stepError, setStepError] = React.useState('');
        const [successMessage, setSuccessMessage] = React.useState('');
        const [loading, setLoading] = React.useState(true);
        const [submitting, setSubmitting] = React.useState(false);
        const [currentStep, setCurrentStep] = React.useState(1);
        const [shortcodeContent, setShortcodeContent] = React.useState('');
        const [loadingTemplate, setLoadingTemplate] = React.useState(false);
        const [formData, setFormData] = React.useState({
            paper_type_persian: '',
            size: '',
            quantity: '',
            sides: '',
            customer_name: '',
            customer_lastname: '',
            customer_email: '',
            customer_phone: '',
            billing_state: '',
            billing_city: '',
            billing_address: '',
            billing_postcode: '',
            billing_country: 'IR',
            ship_to_different_address: false,
            shipping_first_name: '',
            shipping_phone: '',
            shipping_state: '',
            shipping_city: '',
            shipping_address: '',
            shipping_postcode: '',
            shipping_country: 'IR',
            file: null,
            print_info: '',
        });
        const [printPrice, setPrintPrice] = React.useState(0);
        const [taxAmount, setTaxAmount] = React.useState(0);
        const [totalPrice, setTotalPrice] = React.useState(0);
        const [deliveryDays, setDeliveryDays] = React.useState(0);
        const [priceItemsState, setPriceItemsState] = React.useState({});
        const [visibleItems, setVisibleItems] = React.useState({});

        return {
            product,
            setProduct,
            error,
            setError,
            stepError,
            setStepError,
            successMessage,
            setSuccessMessage,
            loading,
            setLoading,
            submitting,
            setSubmitting,
            currentStep,
            setCurrentStep,
            shortcodeContent,
            setShortcodeContent,
            loadingTemplate,
            setLoadingTemplate,
            formData,
            setFormData,
            printPrice,
            setPrintPrice,
            taxAmount,
            setTaxAmount,
            totalPrice,
            setTotalPrice,
            deliveryDays,
            setDeliveryDays,
            priceItemsState,
            setPriceItemsState,
            visibleItems,
            setVisibleItems,
        };
    };

    global.PrintOrderForm.formState.useFormState = useFormState;
})(window, window.React);
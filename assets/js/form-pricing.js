(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.formPricing) {
        console.log('PrintOrderForm.formPricing: Already loaded, skipping');
        return;
    }

    const calculatePricing = (
        product,
        formData,
        pricing,
        options,
        currentStep,
        setPrintPrice,
        setDeliveryDays,
        setTaxAmount,
        setTotalPrice,
        setPriceItemsState,
        setVisibleItems
    ) => {
        if (product && product.category_id) {
            let calculatedPrintPrice = 0;
            let calculatedDeliveryDays = 0;
            if (formData.paper_type_persian && formData.quantity && formData.sides && formData.size) {
                const categoryPricing = pricing[product.category_id] || [];
                const pricingItem = categoryPricing.find(
                    item =>
                        item.paper_type_persian === formData.paper_type_persian &&
                        item.size === formData.size &&
                        String(item.quantity) === String(formData.quantity) &&
                        item.sides === formData.sides
                );
                calculatedPrintPrice = Number(pricingItem?.price || 0);
                calculatedDeliveryDays = Number(pricingItem?.days || 0);
            }
            setPrintPrice(calculatedPrintPrice | 0);
            setDeliveryDays(calculatedDeliveryDays | 0);
            const designFee = Number(options.design_fee || 0);
            const taxRate = Number(options.tax_rate || 0);
            const shippingFee = Number(options.shipping_fee || 0);
            const subtotal = Number(product.price || 0) + calculatedPrintPrice + designFee + shippingFee;
            const tax = subtotal * (taxRate / 100);
            setTaxAmount(Math.round(tax));
            setTotalPrice(Math.round(subtotal + tax));
            const newPriceItems = [
                { label: 'طرح', value: Number(product.price || 0), step: 1 },
                ...(currentStep >= 1 && calculatedPrintPrice > 0 ? [{ label: 'چاپ', value: calculatedPrintPrice, step: 1 }] : []),
                ...(currentStep >= 2 ? [{ label: 'طراحی', value: designFee, step: 2 }] : []),
                ...(currentStep >= 3 ? [{ label: 'ارسال', value: shippingFee, step: 3 }] : []),
            ];
            setPriceItemsState(prevItems => {
                const updatedItems = [...newPriceItems];
                const newVisibleItems = updatedItems.map((item, index) => prevItems.length > index);
                setVisibleItems(newVisibleItems);
                return updatedItems;
            });
            setTimeout(() => {
                setVisibleItems(prev => {
                    const updated = [...prev];
                    updated[updated.length - 1] = true;
                    return updated;
                });
            }, 100);
        } else {
            setPrintPrice(0);
            setTaxAmount(0);
            setTotalPrice(0);
            setDeliveryDays(0);
            setPriceItemsState([]);
            setVisibleItems([]);
        }
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.formPricing = {
        calculatePricing,
    };
})();
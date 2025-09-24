(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.customFields) {
        console.log('PrintOrderForm.customFields: Already loaded, skipping');
        return;
    }

    const { useMemo, useState } = window.React;

    const useCustomFields = (product, category_fields, setUsedCategoryIds) => {
        return useMemo(() => {
            const fields = [];
            const seenFieldNames = new Set();
            const tempCategoryIds = new Set();

            if (product?.categories?.length > 0) {
                const sortedCategories = [...product.categories].sort((a, b) => {
                    if (a.parent !== 0 && b.parent === 0) return -1;
                    if (a.parent === 0 && b.parent !== 0) return 1;
                    return 0;
                });
                sortedCategories.forEach(cat => {
                    if (category_fields[cat.term_id]) {
                        const catFields = category_fields[cat.term_id] || [];
                        catFields.forEach(field => {
                            if (!seenFieldNames.has(field.name)) {
                                fields.push({ ...field, category_id: cat.term_id, category_name: cat.name });
                                seenFieldNames.add(field.name);
                                tempCategoryIds.add(cat.term_id);
                            }
                        });
                    }
                });
                setUsedCategoryIds(tempCategoryIds);
                return fields;
            }
            if (product?.category_id && category_fields[product.category_id]) {
                const catFields = category_fields[product.category_id] || [];
                catFields.forEach(field => {
                    if (!seenFieldNames.has(field.name)) {
                        fields.push({ ...field, category_id: product.category_id, category_name: product.category_name });
                        seenFieldNames.add(field.name);
                        tempCategoryIds.add(product.category_id);
                    }
                });
                setUsedCategoryIds(tempCategoryIds);
                return fields;
            }
            setUsedCategoryIds(new Set());
            return [];
        }, [product, category_fields]);
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.customFields = {
        useCustomFields,
    };
})();
(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.userAddress) {
        return;
    }

    const { useState, useEffect } = window.React;

    // Manual provinces map (name to code)
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

    const useUserAddress = (currentStep, ajax_url, nonce, formData, setFormData) => {
        const [userInfo, setUserInfo] = useState({});
        const [provinces, setProvinces] = useState([]);
        const [billingCities, setBillingCities] = useState([]);
        const [shippingCities, setShippingCities] = useState([]);
        const [provincesError, setProvincesError] = useState(null);
        const [error, setError] = useState(null);
        const [loading, setLoading] = useState(false);
        const [requestsCompleted, setRequestsCompleted] = useState({ provinces: false, userInfo: false });

        useEffect(() => {
            if (currentStep !== 3) {
                setLoading(false);
                return;
            }

            setLoading(true);
            setRequestsCompleted({ provinces: false, userInfo: false });
            let isMounted = true;

            // Fetch provinces
            fetch(ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'get_provinces',
                    nonce: nonce,
                }),
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (isMounted) {
                        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                            setProvinces(data.data);
                            setProvincesError(null);
                        } else {
                            setProvinces([]);
                            setProvincesError(data.message || 'خطا در بارگذاری لیست استان‌ها');
                        }
                        setRequestsCompleted(prev => ({ ...prev, provinces: true }));
                    }
                })
                .catch(error => {
                    if (isMounted) {
                        console.error('useUserAddress: Error fetching provinces', error);
                        setProvinces([]);
                        setProvincesError('خطا در ارتباط با سرور برای بارگذاری استان‌ها: ' + error.message);
                        setRequestsCompleted(prev => ({ ...prev, provinces: true }));
                    }
                });

            // Fetch user info
            fetch(ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'get_user_info',
                    nonce: nonce,
                }),
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (isMounted) {
                        if (data.success && data.data && data.data.user_id) {
                            const billingStateCode = reverseProvincesMap[data.data.billing_state] ? data.data.billing_state : (provincesMap[data.data.billing_state] || data.data.billing_state || '');
                            setUserInfo(data.data);
                            setFormData(prev => {
                                const newFormData = {
                                    ...prev,
                                    user_id: data.data.user_id || prev.user_id || '0',
                                    customer_name: data.data.billing_first_name || prev.customer_name || '',
                                    customer_lastname: data.data.billing_last_name || prev.customer_lastname || '',
                                    customer_email: data.data.billing_email || prev.customer_email || '',
                                    customer_phone: data.data.billing_phone || prev.customer_phone || '',
                                    billing_state: billingStateCode,
                                    billing_city: data.data.billing_city || prev.billing_city || '',
                                    billing_address: data.data.billing_address_1 || prev.billing_address || '',
                                    billing_postcode: data.data.billing_postcode || prev.billing_postcode || '',
                                };
                                return newFormData;
                            });
                        } else {
                            setUserInfo({});
                            setFormData(prev => {
                                const newFormData = {
                                    ...prev,
                                    user_id: '0',
                                    customer_name: prev.customer_name || '',
                                    customer_lastname: prev.customer_lastname || '',
                                    customer_email: prev.customer_email || '',
                                    customer_phone: prev.customer_phone || '',
                                    billing_state: prev.billing_state || '',
                                    billing_city: prev.billing_city || '',
                                    billing_address: prev.billing_address || '',
                                    billing_postcode: prev.billing_postcode || '',
                                };
                                return newFormData;
                            });
                        }
                        setRequestsCompleted(prev => ({ ...prev, userInfo: true }));
                    }
                })
                .catch(error => {
                    if (isMounted) {
                        console.error('useUserAddress: Error fetching user info', error);
                        setError('خطا در ارتباط با سرور برای بارگذاری اطلاعات کاربر: ' + error.message);
                        setRequestsCompleted(prev => ({ ...prev, userInfo: true }));
                    }
                });

            return () => {
                isMounted = false;
            };
        }, [currentStep, ajax_url, nonce]);

        useEffect(() => {
            if (requestsCompleted.provinces && requestsCompleted.userInfo) {
                setLoading(false);
            }
        }, [requestsCompleted]);

        useEffect(() => {
            if (currentStep === 3 && formData.billing_state) {
                fetch(ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'get_cities',
                        nonce: nonce,
                        state: formData.billing_state,
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            setBillingCities(data.data);
                        } else {
                            console.error('useUserAddress: Failed to fetch billing cities', data);
                            setBillingCities([]);
                        }
                    })
                    .catch(error => console.error('useUserAddress: Error fetching billing cities', error));
            }
        }, [currentStep, formData.billing_state, ajax_url, nonce]);

        useEffect(() => {
            if (currentStep === 3 && formData.ship_to_different_address && formData.shipping_state) {
                fetch(ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'get_cities',
                        nonce: nonce,
                        state: formData.shipping_state,
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            setShippingCities(data.data);
                        } else {
                            console.error('useUserAddress: Failed to fetch shipping cities', data);
                            setShippingCities([]);
                        }
                    })
                    .catch(error => console.error('useUserAddress: Error fetching shipping cities', error));
            }
        }, [currentStep, formData.ship_to_different_address, formData.shipping_state, ajax_url, nonce]);

        return {
            userInfo,
            provinces,
            billingCities,
            shippingCities,
            provincesError,
            error,
            loading,
        };
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.userAddress = {
        useUserAddress,
    };
})();
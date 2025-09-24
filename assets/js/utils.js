(function () {
    if (window.PrintOrderForm && window.PrintOrderForm.utils) {
        console.log('PrintOrderForm.utils: Already loaded, skipping');
        return;
    }

    const sidesMapping = [
        { value: 'double', label: 'دورو', english: 'double' },
        { value: 'single', label: 'یکرو', english: 'single' },
    ];

    const handleInputChange = (e, setFormData) => {
        const { name, value, type, checked } = e.target;
        if (type === 'checkbox') {
            setFormData(prev => ({ ...prev, [name]: checked }));
        } else if (name === 'files') {
            console.log('handleInputChange: Files value:', JSON.stringify(value));
            if (Array.isArray(value) && value.length > 0) {
                // فایل‌هایی که یا url و attachmentId دارند یا temp_url و name دارند معتبر هستند
                const validFiles = value.filter(file => (file.url && file.attachmentId) || (file.temp_url && file.name));
                if (validFiles.length > 0) {
                    setFormData(prev => ({ ...prev, [name]: [...validFiles] }));
                    console.log('handleInputChange: Stored valid files:', JSON.stringify(validFiles));
                } else {
                    console.warn('handleInputChange: No valid files found:', value);
                    setFormData(prev => ({ ...prev, [name]: [] }));
                }
            } else {
                console.warn('handleInputChange: Files value is invalid or empty:', value);
                setFormData(prev => ({ ...prev, [name]: [] }));
            }
        } else if (name === 'paper_type_persian') {
            setFormData(prev => ({ ...prev, [name]: value, size: '', quantity: '', sides: '' }));
        } else if (name === 'size') {
            setFormData(prev => ({ ...prev, [name]: value, quantity: '', sides: '' }));
        } else if (name === 'quantity') {
            setFormData(prev => ({ ...prev, [name]: value, sides: '' }));
        } else {
            setFormData(prev => ({ ...prev, [name]: value }));
        }
    };

    const isEmail = (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    };

    window.PrintOrderForm = window.PrintOrderForm || {};
    window.PrintOrderForm.utils = {
        sidesMapping,
        handleInputChange,
        isEmail,
    };
})();
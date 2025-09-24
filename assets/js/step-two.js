(function () {
  if (window.PrintOrderForm && window.PrintOrderForm.stepTwo) {
    return;
  }

  const { useState, useEffect } = window.React;

  const truncateFileName = (name, maxLength = 20) => {
    if (name.length <= maxLength) return name;
    const ext = name.split(".").pop();
    const nameWithoutExt = name.substring(0, name.lastIndexOf("."));
    const charsToShow = Math.floor((maxLength - ext.length - 3) / 2);
    return `${nameWithoutExt.substring(0, charsToShow)}...${nameWithoutExt.substring(nameWithoutExt.length - charsToShow)}.${ext}`;
  };

  const StepTwo = ({ allCustomFields, formData, onChange, setFormData, renderInstantPrice, currentStep, product, priceItemsState, visibleItems, temp_id, setStepError }) => {
    const [uploadedFiles, setUploadedFiles] = useState(formData.files || []);
    const [fileErrors, setFileErrors] = useState([]);
    const [validationErrors, setValidationErrors] = useState({});
    const [isDragging, setIsDragging] = useState(false);

    const maxFiles = 3;
    const maxFileSize = 30 * 1024 * 1024; // 30MB
    const allowedFormats = ["psd", "jpg", "jpeg", "pdf", "png", "ai", "eps", "cdr"];
    const ajax_url = window.printOrder?.ajax_url || '/wp-admin/admin-ajax.php';
    const nonce = window.printOrder?.nonce;
    const public_nonce = window.printOrder?.public_nonce;

    const getFileIcon = (format) => {
      const iconMap = {
        psd: "/wp-content/plugins/print-order/assets/icons/psd.svg",
        jpg: "/wp-content/plugins/print-order/assets/icons/jpg.svg",
        jpeg: "/wp-content/plugins/print-order/assets/icons/jpeg.svg",
        pdf: "/wp-content/plugins/print-order/assets/icons/pdf.svg",
        png: "/wp-content/plugins/print-order/assets/icons/png.svg",
        ai: "/wp-content/plugins/print-order/assets/icons/ai.svg",
        eps: "/wp-content/plugins/print-order/assets/icons/cdr.svg",
        cdr: "/wp-content/plugins/print-order/assets/icons/cdr.svg",
      };
      return iconMap[format.toLowerCase()] || "/wp-content/plugins/print-order/assets/icons/file.svg";
    };

    const getFormatClasses = (format) => {
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
      return classMap[format.toLowerCase()] || "";
    };

    const validateFile = (file) => {
      const format = file.name.split(".").pop().toLowerCase();
      if (!allowedFormats.includes(format)) {
        return `فایل ${file.name} فرمت غیرمجاز است. فرمت‌های مجاز: ${allowedFormats.join(", ")}`;
      }
      if (file.size > maxFileSize) {
        return `حجم فایل ${file.name} بیش از 30 مگابایت است`;
      }
      return null;
    };

    const handleFileChange = (files) => {
      const newFiles = Array.from(files);
      const errors = [];
      const validFiles = [];
      if (uploadedFiles.length + newFiles.length > maxFiles) {
        errors.push(`حداکثر ${maxFiles} فایل مجاز است.`);
      } else {
        newFiles.forEach((file) => {
          const error = validateFile(file);
          if (error) {
            errors.push(error);
          } else {
            validFiles.push({
              id: file.name + "-" + Date.now(),
              name: file.name,
              size: file.size,
              format: file.name.split(".").pop().toLowerCase(),
              progress: 0,
              file: file,
              error: null,
              temp_url: null,
              xhrRef: null,
            });
          }
        });
      }

      if (errors.length > 0) {
        setFileErrors(errors);
        return;
      }
      setFileErrors([]);
      setUploadedFiles((prev) => [...prev, ...validFiles]);
      validFiles.forEach(startUpload);
    };

    const startUpload = (fileToUpload) => {
      const xhr = new XMLHttpRequest();
      fileToUpload.xhrRef = xhr;

      if (!ajax_url) {
        console.error("startUpload: AJAX URL is not defined.");
        setUploadedFiles((prev) =>
          prev.map((f) =>
            f.id === fileToUpload.id ? { ...f, progress: null, error: "خطای پیکربندی: آدرس آپلود نامعتبر است." } : f
          )
        );
        return;
      }

      if (!temp_id) {
        console.error("startUpload: temp_id is not defined.");
        setUploadedFiles((prev) =>
          prev.map((f) =>
            f.id === fileToUpload.id ? { ...f, progress: null, error: "خطای پیکربندی: شناسه موقت نامعتبر است." } : f
          )
        );
        return;
      }

      if (!nonce && !public_nonce) {
        console.error("startUpload: Neither nonce nor public_nonce is defined.");
        setUploadedFiles((prev) =>
          prev.map((f) =>
            f.id === fileToUpload.id ? { ...f, progress: null, error: "خطای پیکربندی: نانس نامعتبر است." } : f
          )
        );
        return;
      }

      const url = ajax_url;
      const formData = new FormData();
      formData.append("action", "print_order_upload_file");
      formData.append("nonce", nonce || public_nonce || '');
      formData.append("temp_id", temp_id || '');
      formData.append("file", fileToUpload.file);
      formData.append("temp_file_id", fileToUpload.id);

      xhr.open("POST", url, true);

      xhr.upload.onprogress = (event) => {
        if (event.lengthComputable) {
          const percent = Math.round((event.loaded / event.total) * 100);
          setUploadedFiles((prev) =>
            prev.map((f) =>
              f.id === fileToUpload.id ? { ...f, progress: percent } : f
            )
          );
        }
      };

      xhr.onload = () => {
        let response;
        try {
          response = JSON.parse(xhr.responseText);
        } catch (e) {
          console.error(`Failed to parse JSON response for ${fileToUpload.name}:`, e);
          console.log("Response text:", xhr.responseText);
          setUploadedFiles((prev) =>
            prev.map((f) =>
              f.id === fileToUpload.id
                ? { ...f, progress: null, error: "خطا در آپلود فایل: پاسخ سرور نامعتبر است." }
                : f
            )
          );
          setStepError("خطا در پردازش پاسخ سرور. لطفاً دوباره تلاش کنید.");
          return;
        }

        if (xhr.status >= 200 && xhr.status < 300) {
          if (response.success && response.data.temp_url) {
            try {
              setUploadedFiles((prev) => {
                const updatedFiles = prev.map((f) =>
                  f.id === fileToUpload.id
                    ? {
                        ...f,
                        progress: 100,
                        temp_url: response.data.temp_url,
                        name: response.data.name,
                        format: response.data.format,
                      }
                    : f
                );
                setFormData((prev) => ({
                  ...prev,
                  files: [...(prev.files || []), {
                    temp_url: response.data.temp_url,
                    name: response.data.name,
                    format: response.data.format,
                  }],
                }));
                return updatedFiles;
              });
              setStepError(null);
            } catch (error) {
              console.error('startUpload: Error updating formData:', error);
              setUploadedFiles((prev) =>
                prev.map((f) =>
                  f.id === fileToUpload.id
                    ? { ...f, progress: null, error: "خطا در به‌روزرسانی داده‌های فرم." }
                    : f
                )
              );
              setStepError("خطا در به‌روزرسانی داده‌های فرم. لطفاً دوباره تلاش کنید.");
            }
          } else {
            console.error(`Upload failed for ${fileToUpload.name}:`, response.data.message);
            setUploadedFiles((prev) =>
              prev.map((f) =>
                f.id === fileToUpload.id
                  ? {
                      ...f,
                      progress: null,
                      error: response.data.message || "خطا در آپلود فایل",
                    }
                  : f
              )
            );
            setStepError(response.data.message || "خطا در آپلود فایل");
          }
        } else {
          let errorMessage = response?.data?.message || `خطا در آپلود فایل (وضعیت: ${xhr.status})`;
          setUploadedFiles((prev) => {
            const updatedFiles = prev.map((f) =>
              f.id === fileToUpload.id
                ? {
                    ...f,
                    progress: null,
                    error: errorMessage,
                  }
                : f
            );
            return updatedFiles;
          });
          setStepError(errorMessage);
        }
      };

      xhr.onerror = () => {
        console.error(`Network error during upload for ${fileToUpload.name}`);
        setUploadedFiles((prev) =>
          prev.map((f) =>
            f.id === fileToUpload.id
              ? { ...f, progress: null, error: "خطا در ارتباط با سرور." }
              : f
          )
        );
        setStepError("خطا در ارتباط با سرور. لطفاً اتصال اینترنت خود را بررسی کنید.");
      };

      xhr.send(formData);
    };

    const handleFileDelete = (fileToDelete) => {
      const isUploading = fileToDelete.progress !== 100 && fileToDelete.progress !== null;
      if (isUploading) {
        fileToDelete.xhrRef.abort();
      }

      setUploadedFiles((prev) => {
        const updatedFiles = prev.filter((f) => f.id !== fileToDelete.id);
        setFormData((prev) => ({
          ...prev,
          files: updatedFiles.map(file => ({
            temp_url: file.temp_url,
            name: file.name,
            format: file.format,
          })),
        }));
        return updatedFiles;
      });

      if (fileToDelete.file_id) {
        const formData = new FormData();
        formData.append("action", "print_order_delete_temp_file");
        formData.append("nonce", nonce || public_nonce || '');
        formData.append("file_id", fileToDelete.file_id);
        fetch(ajax_url, {
          method: "POST",
          body: formData,
        }).catch(error => console.error("Error deleting temp file:", error));
      }
    };

    const validateStepTwo = () => {
      const requiredFields = allCustomFields.filter(field => field.required && field.name !== 'description');
      const newValidationErrors = {};
      let isValid = true;

      requiredFields.forEach(field => {
        const value = formData[field.name];
        if (!value || (typeof value === 'string' && value.trim() === '')) {
          newValidationErrors[field.name] = `فیلد "${field.label}" اجباری است.`;
          isValid = false;
        }
      });

      const isUploading = uploadedFiles.some(file => file.progress !== 100 && file.progress !== null);
      if (isUploading) {
        isValid = false;
        setStepError("لطفاً منتظر بمانید تا آپلود فایل‌ها کامل شود.");
      } else if (!isValid) {
        setStepError("لطفاً تمام فیلدهای اجباری را پر کنید.");
      } else {
        setStepError(null);
      }

      setValidationErrors(newValidationErrors);
      return isValid;
    };

    const renderCustomFields = () => {
      // فیلد توضیحات به‌صورت دستی اضافه می‌شود
      const descriptionField = {
        name: 'description',
        label: 'توضیحات',
        type: 'textarea',
        required: false,
      };

      // فیلتر کردن allCustomFields برای جلوگیری از تکرار فیلد توضیحات
      const customFieldsToRender = allCustomFields.filter(field => field.name !== 'description');
      
      // اضافه کردن فیلد توضیحات به لیست فیلدها
      const fieldsToRender = [...customFieldsToRender, descriptionField];

      return fieldsToRender.map(field => {
        const value = formData[field.name] || "";
        const error = validationErrors[field.name];
        return React.createElement(
          'div',
          { key: field.name, className: 'mb-4' },
          [
            React.createElement('label', {
              className: 'block text-sm font-medium text-gray-700'
            }, `${field.label}${field.required ? ' *' : ''}`),
            field.type === 'text' && React.createElement('input', {
              type: 'text',
              value: value,
              onChange: (e) => onChange({ target: { name: field.name, value: e.target.value } }),
              required: field.required,
              className: `mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 ${error ? 'border-red-500' : ''}`
            }),
            field.type === 'textarea' && React.createElement('textarea', {
              value: value,
              onChange: (e) => onChange({ target: { name: field.name, value: e.target.value } }),
              required: field.required,
              className: `mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 ${error ? 'border-red-500' : ''}`,
              rows: 4
            }),
            field.type === 'select' && React.createElement('select', {
              value: value,
              onChange: (e) => onChange({ target: { name: field.name, value: e.target.value } }),
              required: field.required,
              className: `mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 ${error ? 'border-red-500' : ''}`
            }, [
              React.createElement('option', { value: '' }, 'انتخاب کنید'),
              ...(field.options || []).map(option => React.createElement('option', { key: option.value, value: option.value }, option.label))
            ]),
            error && React.createElement('p', { className: 'mt-1 text-sm text-red-500' }, error)
          ]
        );
      });
    };

    const renderUploadedFile = (file) => {
      const { id, name, format, size, progress, error } = file;
      return React.createElement(
        "div",
        {
          key: id,
          className: "flex items-center justify-between p-2 mb-2 bg-gray-100 rounded-md",
        },
        [
          React.createElement(
            "div",
            { className: "flex items-center space-x-2" },
            [
              React.createElement("img", {
                src: getFileIcon(format),
                alt: `${format} icon`,
                className: "w-8 h-8",
              }),
              React.createElement("div", { className: "flex flex-col" }, [
                React.createElement(
                  "span",
                  { className: "text-sm font-medium" },
                  truncateFileName(name)
                ),
                React.createElement(
                  "span",
                  { className: "text-xs text-gray-500" },
                  `${(size / 1024 / 1024).toFixed(2)} MB`
                ),
              ]),
            ]
          ),
          React.createElement(
            "div",
            { className: "flex items-center space-x-2" },
            [
              error
                ? React.createElement(
                    "span",
                    { className: "text-xs text-red-500" },
                    error
                  )
                : progress === 100
                ? React.createElement(
                    "span",
                    { className: "text-xs text-green-500" },
                    "آپلود کامل شد"
                  )
                : progress !== null
                ? React.createElement(
                    "span",
                    { className: "text-xs text-blue-500" },
                    `آپلود ${progress}%`
                  )
                : null,
              React.createElement(
                "button",
                {
                  onClick: () => handleFileDelete(file),
                  className: "text-gray-400 hover:text-red-500",
                },
                React.createElement("svg", {
                  xmlns: "http://www.w3.org/2000/svg",
                  className: "h-4 w-4",
                  fill: "none",
                  viewBox: "0 0 24 24",
                  stroke: "currentColor",
                },
                React.createElement("path", {
                  strokeLinecap: "round",
                  strokeLinejoin: "round",
                  strokeWidth: 2,
                  d: "M6 18L18 6M6 6l12 12",
                })
                )
              ),
            ]
          ),
        ]
      );
    };

    const handleDragOver = (e) => {
      e.preventDefault();
      setIsDragging(true);
    };

    const handleDragLeave = (e) => {
      e.preventDefault();
      setIsDragging(false);
    };

    const handleDrop = (e) => {
      e.preventDefault();
      setIsDragging(false);
      handleFileChange(e.dataTransfer.files);
    };

    const renderFileUpload = () => {
      return React.createElement(
        "div",
        { className: "bg-white p-4 rounded-lg shadow-sm" },
        [
          React.createElement(
            "h3",
            { className: "text-lg font-bold mb-2 text-right" },
            "آپلود فایل"
          ),
          React.createElement(
            "p",
            { className: "text-sm text-gray-500 mb-4 text-right" },
            `حداکثر ${maxFiles} فایل با فرمت‌های مجاز (${allowedFormats.join(", ")}) و حجم حداکثر ${maxFileSize / 1024 / 1024} مگابایت`
          ),
          React.createElement(
            "div",
            {
              className: `flex items-center justify-center w-full ${isDragging ? 'bg-indigo-100 border-indigo-400' : ''}`,
              onDragOver: handleDragOver,
              onDragLeave: handleDragLeave,
              onDrop: handleDrop,
            },
            React.createElement(
              "label",
              {
                htmlFor: "dropzone-file",
                className: "flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100",
              },
              [
                React.createElement(
                  "div",
                  { className: "flex flex-col items-center justify-center pt-5 pb-6" },
                  [
                    React.createElement(
                      "svg",
                      {
                        className: "w-8 h-8 mb-4 text-gray-500",
                        "aria-hidden": "true",
                        xmlns: "http://www.w3.org/2000/svg",
                        fill: "none",
                        viewBox: "0 0 20 16",
                      },
                      React.createElement("path", {
                        stroke: "currentColor",
                        strokeLinecap: "round",
                        strokeLinejoin: "round",
                        strokeWidth: "2",
                        d: "M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2",
                      })
                    ),
                    React.createElement(
                      "p",
                      { className: "mb-2 text-sm text-gray-500" },
                      [
                        React.createElement(
                          "span",
                          { className: "font-semibold" },
                          "کلیک کنید تا فایل آپلود شود"
                        ),
                        " یا فایل را اینجا بکشید و رها کنید.",
                      ]
                    ),
                    React.createElement(
                      "p",
                      { className: "text-xs text-gray-500" },
                      `فرمت‌های مجاز: ${allowedFormats.join(", ")}`
                    ),
                  ]
                ),
                React.createElement("input", {
                  id: "dropzone-file",
                  type: "file",
                  className: "hidden",
                  onChange: (e) => handleFileChange(e.target.files),
                  multiple: true,
                  accept: allowedFormats.map(f => `.${f}`).join(','),
                }),
              ]
            )
          ),
          fileErrors.length > 0 &&
            React.createElement(
              "div",
              { className: "mt-4 text-sm text-red-500" },
              fileErrors.map((error, index) =>
                React.createElement("p", { key: index }, error)
              )
            ),
          uploadedFiles.length > 0 &&
            React.createElement(
              "div",
              { className: "mt-4 border-t pt-4" },
              uploadedFiles.map(renderUploadedFile)
            ),
        ]
      );
    };

    useEffect(() => {
      if (window.PrintOrderForm && window.PrintOrderForm.stepNavigation) {
        window.PrintOrderForm.stepNavigation.validateStep = validateStepTwo;
      }
      return () => {
        if (window.PrintOrderForm && window.PrintOrderForm.stepNavigation) {
          window.PrintOrderForm.stepNavigation.validateStep = null;
        }
      };
    }, [formData, uploadedFiles]);

    return React.createElement(
      "div",
      { className: "flex flex-col gap-4" },
      [
        renderCustomFields(),
        renderFileUpload(),
      ]
    );
  };

  if (!window.PrintOrderForm) {
    window.PrintOrderForm = {};
  }
  window.PrintOrderForm.stepTwo = { StepTwo };
})();
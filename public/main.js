document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById("form-container");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");

    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const message = document.getElementById('message');
    const privacy = document.getElementById('privacy');

    const storageKey = 'animal_request_form_data';
    let messageContainer = document.getElementById('message-container');
    
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.id = 'message-container';
        messageContainer.style.cssText = 'margin: 10px 0; padding: 10px; border-radius: 5px; display: none;';
        if (submitBtn && submitBtn.parentNode) {
            submitBtn.parentNode.insertBefore(messageContainer, submitBtn);
        }
    }

    function showMessage(html, type = 'success') {
        messageContainer.innerHTML = html;
        messageContainer.style.display = 'block';
        messageContainer.className = type === 'success' ? 'success-box' : 'error-box';
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearFormData() {
        localStorage.removeItem(storageKey);
    }
    
    function saveFormData() {
        const formData = {
            fullName: fullName ? fullName.value : '',
            email: email ? email.value : '',
            phone: phone ? phone.value : '',
            message: message ? message.value : '',
            privacy: privacy ? privacy.checked : false
        };
        localStorage.setItem(storageKey, JSON.stringify(formData));
    }

    function loadFormData() {
        const savedData = localStorage.getItem(storageKey);
        if (savedData) {
            try {
                const formData = JSON.parse(savedData);
                if (fullName) fullName.value = formData.fullName || '';
                if (email) email.value = formData.email || '';
                if (phone) phone.value = formData.phone || '';
                if (message) message.value = formData.message || '';
                if (privacy) privacy.checked = formData.privacy || false;
            } catch (error) {
                console.error('Ошибка при загрузке данных:', error);
                clearFormData();
            }
        }
    }

    function validateForm() {
        let isValid = true;
        [fullName, email, message].forEach(field => {
            if (field) {
                field.style.borderColor = '';
                field.style.boxShadow = '';
            }
        });
        if (privacy) privacy.style.outline = '';
        
        if (!fullName || !fullName.value.trim()) {
            showMessage('Пожалуйста, введите ФИО', 'error');
            if (fullName) {
                fullName.style.borderColor = '#dc3545';
                fullName.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.1)';
            }
            isValid = false;
        } else if (!email || !email.value.trim()) {
            showMessage('Пожалуйста, введите email', 'error');
            if (email) {
                email.style.borderColor = '#dc3545';
                email.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.1)';
            }
            isValid = false;
        } else if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            showMessage('Пожалуйста, введите корректный email адрес', 'error');
            if (email) {
                email.style.borderColor = '#dc3545';
            }
            isValid = false;
        } else if (!message || !message.value.trim()) {
            showMessage('Пожалуйста, введите сообщение', 'error');
            if (message) {
                message.style.borderColor = '#dc3545';
            }
            isValid = false;
        } else if (!privacy || !privacy.checked) {
            showMessage('Необходимо согласие с политикой обработки персональных данных', 'error');
            if (privacy) privacy.style.outline = '2px solid #dc3545';
            isValid = false;
        }
        return isValid;
    }

    async function submitForm(event) {
        event.preventDefault();
        if (!validateForm()) return;
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
        }

        const formData = {
            fullName: fullName.value,
            email: email.value,
            phone: phone.value || '',
            message: message.value,
            privacy: privacy.checked ? '1' : '0'
        };

        const urlParams = new URLSearchParams(window.location.search);
        const currentUserId = urlParams.get('id') || (formContainer?.dataset.userId);
        const isUpdate = !!currentUserId;
        const endpoint = `/8/public/api/users${isUpdate ? '/' + currentUserId : ''}`;
        const method = isUpdate ? 'PUT' : 'POST';

        try {
            const res = await fetch(endpoint, {
                method,
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' 
                },
                body: JSON.stringify(formData)
            });

            const textResponse = await res.text();
            let result;
            try {
                result = JSON.parse(textResponse);
            } catch (e) {
                const preview = textResponse.substring(0, 200).replace(/</g, '&lt;');
                console.error('Сервер вернул не JSON:', preview);
                throw new Error('Ошибка сервера. Проверьте консоль или логи.');
            }

            if (res.ok) {
                if (!isUpdate) {
                    formContainer.innerHTML = `
                        <div style="text-align:center; padding: 30px 20px;">
                            <h3 style="margin-bottom: 20px;">Заявка отправлена</h3>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: left;">
                                <p style="margin: 8px 0;"><strong>Логин:</strong> <code>${result.login}</code></p>
                                <p style="margin: 8px 0;"><strong>Пароль:</strong> <code>${result.password}</code></p>
                            </div>
                            <a href="${result.profile_url}" class="form_btn" style="text-decoration:none; display:inline-block;">Перейти в профиль</a>
                        </div>
                    `;
                    clearFormData();
                } else {
                    showMessage(result.message || 'Данные обновлены', 'success');
                    setTimeout(() => {
                        if (formContainer) formContainer.style.display = 'none';
                    }, 1500);
                }
            } else {
                const errors = result.errors 
                    ? Object.values(result.errors).join('<br>') 
                    : (result.message || 'Произошла ошибка');
                showMessage(errors, 'error');
            }
        } catch (err) {
            console.error(err);
            showMessage(`Ошибка: ${err.message}`, 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'отправить форму';
            }
        }
    }

    loadFormData();
    [fullName, email, phone, message].forEach(field => {
        if (field) field.addEventListener('input', saveFormData);
    });
    if (privacy) privacy.addEventListener('change', saveFormData);
    if (contactForm) contactForm.addEventListener('submit', submitForm);
});

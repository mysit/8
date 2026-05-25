document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById("btn_form");
    const formContainer = document.getElementById("form-container");
    const bloom = document.getElementById("bloom");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");
    const messageContainer = document.getElementById('message-container');

    const fields = {
        fullName: document.getElementById('fullName'),
        email: document.getElementById('email'),
        phone: document.getElementById('phone'),
        organization: document.getElementById('organization'),
        message: document.getElementById('message'),
        privacy: document.getElementById('privacy')
    };

    let isFormOpen = false;

    const urlParams = new URLSearchParams(window.location.search);
    const currentUserId = urlParams.get('id') || (formContainer?.dataset.userId);

    function showMessage(html, type = 'success') {
        if (!messageContainer) return;
        messageContainer.innerHTML = html;
        messageContainer.style.display = 'block';
        messageContainer.className = type === 'success' ? 'success-box' : 'error-box';
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (btn) btn.onclick = () => {
        formContainer?.classList.replace('off', 'on');
        bloom?.classList.replace('off', 'on');
        document.body.style.overflow = 'hidden';
        isFormOpen = true;
    };

    if (bloom) bloom.onclick = closeForm;
    document.onkeydown = (e) => { if (e.key === 'Escape' && isFormOpen) closeForm(); };

    function closeForm() {
        formContainer?.classList.replace('on', 'off');
        bloom?.classList.replace('on', 'off');
        document.body.style.overflow = '';
        isFormOpen = false;
    }

    if (contactForm) {
        contactForm.onsubmit = async function(e) {
            e.preventDefault();
            
            if (messageContainer) messageContainer.style.display = 'none';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = currentUserId ? 'Сохранение...' : 'Отправка...';
            }

            const formData = {};
            for (const [key, el] of Object.entries(fields)) {
                if (!el) continue;
                if (el.type === 'checkbox') {
                    formData[key] = el.checked ? '1' : '0';
                } else {
                    formData[key] = el.value.trim();
                }
            }

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
                    console.error('Сервер вернул не JSON:', textResponse);
                    throw new Error('Ошибка сервера: неверный формат ответа');
                }

                if (res.ok) {
                    if (!isUpdate) {
                        const html = `
                            <strong>Регистрация успешна!</strong><br>
                            Логин: <code>${result.login}</code><br>
                            Пароль: <code>${result.password}</code><br>
                            <a href="${result.profile_url}" target="_blank" style="font-weight:bold; color: #007bff;">Перейти в профиль</a>
                        `;
                        showMessage(html, 'success');
                        contactForm.reset();
                        closeForm();
                    } else {
                        showMessage(result.message || 'Данные обновлены', 'success');
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
                    submitBtn.textContent = currentUserId ? 'Сохранить изменения' : 'Отправить форму';
                }
            }
        };
    }
});

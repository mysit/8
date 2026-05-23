document.addEventListener('DOMContentLoaded', function() {
    // DOM Элементы
    const btn = document.getElementById("btn_form");
    const formContainer = document.getElementById("form-container");
    const blom = document.getElementById("bloom");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");

    // Поля формы
    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const organization = document.getElementById('organization');
    const message = document.getElementById('message');
    const privacy = document.getElementById('privacy');

    let isFormOpen = false;
    const messageContainer = document.getElementById('message-container');

    // Определение контекста работы (Регистрация или Редактирование PUT)
    const urlParams = new URLSearchParams(window.location.search);
    const profileUserId = urlParams.get('id');
    const containerUserId = formContainer ? formContainer.getAttribute('data-user-id') : null;
    
    // Итоговый ID пользователя, если мы в режиме редактирования
    const currentUserId = profileUserId || containerUserId;

    function showMessage(text, type = 'success') {
        if (!messageContainer) return;
        messageContainer.innerHTML = text;
        messageContainer.style.display = 'block';
        messageContainer.style.backgroundColor = type === 'success' ? '#d4edda' : '#f8d7da';
        messageContainer.style.color = type === 'success' ? '#155724' : '#721c24';
        messageContainer.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'}`;
    }

    function openf() {
        if (!formContainer || !blom) return;
        formContainer.classList.add('on');
        formContainer.classList.remove('off');
        blom.classList.add('on');
        blom.classList.remove('off');
        isFormOpen = true;
        document.body.style.overflow = 'hidden';
    }

    function closef() {
        if (!formContainer || !blom) return;
        formContainer.classList.remove('on');
        formContainer.classList.add('off');
        blom.classList.remove('on');
        blom.classList.add('off');
        isFormOpen = false;
        document.body.style.overflow = 'auto';
    }

    // Валидация на клиенте
    function validateForm() {
        let isValid = true;
        const fields = [fullName, email, message];
        
        fields.forEach(f => { if(f) f.style.borderColor = ''; });

        if (!fullName || !fullName.value.trim()) {
            showMessage('Пожалуйста, введите ФИО', 'error');
            if (fullName) fullName.style.borderColor = 'red';
            isValid = false;
        } else if (!email || !email.value.trim() || !email.value.includes('@')) {
            showMessage('Пожалуйста, введите корректный email', 'error');
            if (email) email.style.borderColor = 'red';
            isValid = false;
        } else if (!message || !message.value.trim()) {
            showMessage('Пожалуйста, введите сообщение', 'error');
            if (message) message.style.borderColor = 'red';
            isValid = false;
        } else if (privacy && !privacy.checked) {
            showMessage('Необходимо согласие с политикой конфиденциальности', 'error');
            isValid = false;
        }
        return isValid;
    }

    // Назначаем обработчик ДИНАМИЧЕСКИ (по ТЗ для Progressive Enhancement)
    if (contactForm) {
        contactForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // Полностью отменяет перезагрузку страницы

            if (!validateForm()) return;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';
            }

            const formData = {
                fullName: fullName.value,
                email: email.value,
                phone: phone.value,
                organization: organization.value,
                message: message.value
            };

            // Автоматически подставляем правильные пути для сервера КубГУ
            let apiEndpoint = '/8/public/api/users';
            let requestMethod = 'POST';

            // Если мы редактируем профиль (есть ID) — переключаемся на PUT
            if (currentUserId) {
                apiEndpoint = `/8/public/api/users/${currentUserId}`;
                requestMethod = 'PUT';
            }

            try {
                // Универсальный fetch, работающий по нужным эндпоинтам КубГУ
                const response = await fetch(apiEndpoint, {
                    method: requestMethod,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok) {
                    if (requestMethod === 'POST') {
                        // Для неавторизованного пользователя выводим логин, пароль и ссылку на профиль
                        const successHtml = `
                            <strong>Форма успешно отправлена!</strong><br>
                            Создан профиль! Запомните данные:<br>
                            Логин: <code>${result.login}</code><br>
                            Пароль: <code>${result.password}</code><br>
                            <a href="${result.profile_url}" style="font-weight:bold; color:#155724;">Перейти в личный профиль</a>
                        `;
                        showMessage(successHtml, 'success');
                        if (contactForm) contactForm.reset();
                    } else {
                        // Для авторизованного при PUT просто пишем об успехе
                        showMessage(result.message || 'Данные успешно обновлены через PUT!', 'success');
                    }
                } else {
                    // Вывод ошибок валидации сервера на клиенте
                    if (result.errors) {
                        const serverErrors = Object.values(result.errors).join('<br>');
                        showMessage(serverErrors, 'error');
                    } else {
                        showMessage(result.message || 'Произошла ошибка сервера.', 'error');
                    }
                }
            } catch (error) {
                showMessage(`Ошибка сети: ${error.message}`, 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = currentUserId ? 'Сохранить изменения' : 'отправить форму';
                }
            }
        });
    }

    // События интерфейса модального окна
    if (btn) btn.addEventListener('click', openf);
    if (blom) blom.addEventListener('click', closef);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isFormOpen) closef();
    });
});

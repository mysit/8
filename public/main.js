document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById("btn_form");
    const formContainer = document.getElementById("form-container");
    const bloom = document.getElementById("bloom");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");
    const messageContainer = document.getElementById('message-container');

    // Поля формы
    const fields = {
        fullName: document.getElementById('fullName'),
        email: document.getElementById('email'),
        phone: document.getElementById('phone'),
        organization: document.getElementById('organization'),
        message: document.getElementById('message'),
        privacy: document.getElementById('privacy')
    };

    let isFormOpen = false;

    // Получаем ID пользователя из URL или data-атрибута
    const urlParams = new URLSearchParams(window.location.search);
    const currentUserId = urlParams.get('id') || (formContainer?.dataset.userId);

    function showMessage(html, type = 'success') {
        if (!messageContainer) return;
        messageContainer.innerHTML = html;
        messageContainer.style.display = 'block';
        messageContainer.className = type === 'success' ? 'success-box' : 'error-box';
    }

    // Открытие/закрытие модального окна
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

    // Обработчик отправки формы
    if (contactForm) {
        contactForm.onsubmit = async function(e) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = currentUserId ? 'Сохранение...' : 'Отправка...';
            }

            // Сбор данных
            const data = Object.fromEntries(
                Object.entries(fields)
                    .filter(([_, el]) => el)
                    .map(([key, el]) => {
                        if (el.type === 'checkbox') {
                            return [key, el.checked ? '1' : '0'];
                        }
                        return [key, el.value];
                    })
            );

            // Определяем метод и endpoint
            const isUpdate = !!currentUserId;
            const endpoint = `/8/public/api/users${isUpdate ? '/' + currentUserId : ''}`;
            const method = isUpdate ? 'PUT' : 'POST';

            try {
                const res = await fetch(endpoint, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (res.ok) {
                    if (!isUpdate) {
                        // Регистрация — показываем логин/пароль
                        const html = `
                            <strong>Регистрация успешна</strong><br>
                            Логин: <code>${result.login}</code><br>
                            Пароль: <code>${result.password}</code><br>
                            <a href="${result.profile_url}" style="font-weight:bold">→ Перейти в профиль</a>
                        `;
                        showMessage(html, 'success');
                        contactForm.reset();
                    } else {
                        showMessage(result.message || 'Данные обновлены', 'success');
                    }
                } else {
                    const errors = result.errors 
                        ? Object.values(result.errors).join('<br>') 
                        : result.message || 'Ошибка сервера';
                    showMessage(errors, 'error');
                }
            } catch (err) {
                showMessage(`Ошибка сети: ${err.message}`, 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = currentUserId ? 'Сохранить изменения' : 'Отправить форму';
                }
            }
        };
    }
});

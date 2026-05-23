document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById("btn_form");
    const formContainer = document.getElementById("form-container");
    const blom = document.getElementById("bloom");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");

    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const organization = document.getElementById('organization');
    const message = document.getElementById('message');
    const privacy = document.getElementById('privacy');

    let isFormOpen = false;
    const messageContainer = document.getElementById('message-container');

    const urlParams = new URLSearchParams(window.location.search);
    const profileUserId = urlParams.get('id');
    const containerUserId = formContainer ? formContainer.getAttribute('data-user-id') : null;
    const currentUserId = profileUserId || containerUserId;

    function showMessage(text, type = 'success') {
        if (!messageContainer) return;
        messageContainer.innerHTML = text;
        messageContainer.style.display = 'block';
        messageContainer.style.backgroundColor = type === 'success' ? '#d4edda' : '#f8d7da';
        messageContainer.style.color = type === 'success' ? '#155724' : '#721c24';
        messageContainer.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'}`;
    }

    if (btn) btn.addEventListener('click', () => {
        if (!formContainer || !blom) return;
        formContainer.classList.add('on'); formContainer.classList.remove('off');
        blom.classList.add('on'); blom.classList.remove('off');
        isFormOpen = true;
        document.body.style.overflow = 'hidden';
    });

    if (blom) blom.addEventListener('click', closef);

    function closef() {
        if (!formContainer || !blom) return;
        formContainer.classList.remove('on'); formContainer.classList.add('off');
        blom.classList.remove('on'); blom.classList.add('off');
        isFormOpen = false;
        document.body.style.overflow = 'auto';
    }

    if (contactForm) {
        contactForm.addEventListener('submit', async function(event) {
            event.preventDefault();

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

            let apiEndpoint = '/8/public/api/users';
            let requestMethod = 'POST';

            if (currentUserId) {
                apiEndpoint = `/8/public/api/users/${currentUserId}`;
                requestMethod = 'PUT';
            }

            try {
                const response = await fetch(apiEndpoint, {
                    method: requestMethod,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok) {
                    if (requestMethod === 'POST') {
                        const successHtml = `
                            <strong>Форма успешно отправлена!</strong><br>
                            Создан профиль! Запомните данные:<br>
                            Логин: <code>${result.login}</code><br>
                            Пароль: <code>${result.password}</code><br>
                            <a href="${result.profile_url}" style="font-weight:bold; color:#155724;">Перейти в личный профиль</a>
                        `;
                        showMessage(successHtml, 'success');
                        contactForm.reset();
                    } else {
                        showMessage(result.message || 'Данные успешно обновлены!', 'success');
                    }
                } else {
                    if (result.errors) {
                        showMessage(Object.values(result.errors).join('<br>'), 'error');
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

    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isFormOpen) closef(); });
});

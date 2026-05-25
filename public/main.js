document.addEventListener('DOMContentLoaded', function() {
    // элементы формы
    const formContainer = document.getElementById("form-container");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");
    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const message = document.getElementById('message');
    const privacy = document.getElementById('privacy');

    // элементы модального окна (для профиля)
    const btn = document.getElementById("btn_form");
    const bloom = document.getElementById("bloom");
    const messageContainer = document.getElementById('message-container');

    const storageKey = 'animal_request_form_data';
    let isFormOpen = false;

    // контейнер сообщений
    let msgBox = messageContainer;
    if (!msgBox && submitBtn?.parentNode) {
        msgBox = document.createElement('div');
        msgBox.id = 'message-container';
        msgBox.style.cssText = 'margin: 10px 0; padding: 10px; border-radius: 5px; display: none;';
        submitBtn.parentNode.insertBefore(msgBox, submitBtn);
    }

    function showMessage(html, type = 'success') {
        if (!msgBox) return;
        msgBox.innerHTML = html;
        msgBox.style.display = 'block';
        msgBox.className = type === 'success' ? 'success-box' : 'error-box';
        msgBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearFormData() {
        localStorage.removeItem(storageKey);
    }
    
    function saveFormData() {
        const data = {
            fullName: fullName?.value || '',
            email: email?.value || '',
            phone: phone?.value || '',
            message: message?.value || '',
            privacy: privacy?.checked || false
        };
        localStorage.setItem(storageKey, JSON.stringify(data));
    }

    function loadFormData() {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                if (fullName) fullName.value = data.fullName || '';
                if (email) email.value = data.email || '';
                if (phone) phone.value = data.phone || '';
                if (message) message.value = data.message || '';
                if (privacy) privacy.checked = data.privacy || false;
            } catch (e) { clearFormData(); }
        }
    }

    function validateForm() {
        let valid = true;
        [fullName, email, message].forEach(f => { if (f) { f.style.borderColor=''; f.style.boxShadow=''; }});
        if (privacy) privacy.style.outline = '';

        if (!fullName?.value.trim()) {
            showMessage('Пожалуйста, введите ФИО', 'error');
            if (fullName) { fullName.style.borderColor='#dc3545'; fullName.style.boxShadow='0 0 0 2px rgba(220,53,69,0.1)'; }
            valid = false;
        } else if (!email?.value.trim()) {
            showMessage('Пожалуйста, введите email', 'error');
            if (email) { email.style.borderColor='#dc3545'; }
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            showMessage('Некорректный email', 'error');
            if (email) email.style.borderColor='#dc3545';
            valid = false;
        } else if (!message?.value.trim()) {
            showMessage('Пожалуйста, введите сообщение', 'error');
            if (message) message.style.borderColor='#dc3545';
            valid = false;
        } else if (!privacy?.checked) {
            showMessage('Необходимо согласие на обработку данных', 'error');
            if (privacy) privacy.style.outline='2px solid #dc3545';
            valid = false;
        }
        return valid;
    }

    async function submitForm(e) {
        e.preventDefault();
        if (!validateForm()) return;
        if (submitBtn) { submitBtn.disabled=true; submitBtn.textContent='Отправка...'; }

        const data = {
            fullName: fullName.value,
            email: email.value,
            phone: phone.value || '',
            message: message.value,
            privacy: privacy.checked ? '1' : '0'
        };

        const params = new URLSearchParams(window.location.search);
        const userId = params.get('id') || formContainer?.dataset.userId;
        const isUpdate = !!userId;
        const endpoint = `/8/public/api/users${isUpdate ? '/'+userId : ''}`;
        const method = isUpdate ? 'PUT' : 'POST';

        try {
            const res = await fetch(endpoint, {
                method,
                headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                body: JSON.stringify(data)
            });
            const txt = await res.text();
            let result;
            try { result = JSON.parse(txt); }
            catch (err) { throw new Error('Ошибка сервера: неверный формат ответа'); }

            if (res.ok) {
                if (!isUpdate) {
                    formContainer.innerHTML = `
                        <div style="text-align:center;padding:30px 20px">
                            <h3 style="margin-bottom:20px">Заявка отправлена</h3>
                            <div style="background:#f8f9fa;padding:15px;border-radius:6px;margin-bottom:20px;text-align:left">
                                <p style="margin:8px 0"><strong>Логин:</strong> <code>${result.login}</code></p>
                                <p style="margin:8px 0"><strong>Пароль:</strong> <code>${result.password}</code></p>
                            </div>
                            <a href="${result.profile_url}" class="form_btn" style="text-decoration:none;display:inline-block">Перейти в профиль</a>
                        </div>`;
                    clearFormData();
                } else {
                    showMessage(result.message || 'Данные обновлены', 'success');
                    setTimeout(() => { if (formContainer) formContainer.style.display='none'; }, 1500);
                }
            } else {
                const errs = result.errors ? Object.values(result.errors).join('<br>') : (result.message || 'Ошибка');
                showMessage(errs, 'error');
            }
        } catch (err) {
            console.error(err);
            showMessage(`Ошибка: ${err.message}`, 'error');
        } finally {
            if (submitBtn) { submitBtn.disabled=false; submitBtn.textContent='отправить форму'; }
        }
    }

    // модальное окно (для профиля)
    function openForm() {
        formContainer?.classList.add('on');
        bloom?.classList.add('on');
        document.body.style.overflow='hidden';
        isFormOpen=true;
    }
    function closeForm() {
        formContainer?.classList.remove('on');
        bloom?.classList.remove('on');
        document.body.style.overflow='';
        isFormOpen=false;
    }
    if (btn) btn.onclick = openForm;
    if (bloom) bloom.onclick = closeForm;
    document.onkeydown = e => { if (e.key==='Escape' && isFormOpen) closeForm(); };

    // инициализация
    loadFormData();
    [fullName, email, phone, message].forEach(f => { if (f) f.addEventListener('input', saveFormData); });
    if (privacy) privacy.addEventListener('change', saveFormData);
    if (contactForm) contactForm.addEventListener('submit', submitForm);
});

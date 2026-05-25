document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById("form-container");
    const editModal = document.getElementById("edit-modal");
    const bloom = document.getElementById("bloom");
    const contactForm = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submit_form");
    const btnEdit = document.getElementById("btn_form");
    const messageContainer = document.getElementById('message-container');

    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const organization = document.getElementById('organization');
    const message = document.getElementById('message');
    const privacy = document.getElementById('privacy');

    const storageKey = 'animal_request_form_data';
    let isModalOpen = false;

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

    function clearFormData() { localStorage.removeItem(storageKey); }
    
    function saveFormData() {
        const data = {
            fullName: fullName?.value || '',
            email: email?.value || '',
            phone: phone?.value || '',
            organization: organization?.value || '',
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
                if (organization) organization.value = data.organization || '';
                if (message) message.value = data.message || '';
                if (privacy) privacy.checked = data.privacy || false;
            } catch (e) { clearFormData(); }
        }
    }

    function validateForm(isRegistration = false) {
        let valid = true;
        [fullName, email, message].forEach(f => { if (f) { f.style.borderColor=''; f.style.boxShadow=''; }});
        if (privacy) privacy.style.outline = '';

        if (!fullName?.value.trim()) {
            showMessage('Пожалуйста, введите ФИО', 'error');
            if (fullName) { fullName.style.borderColor='#dc3545'; fullName.style.boxShadow='0 0 0 2px rgba(220,53,69,0.1)'; }
            valid = false;
        } else if (!email?.value.trim()) {
            showMessage('Пожалуйста, введите email', 'error');
            if (email) email.style.borderColor='#dc3545';
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            showMessage('Некорректный email', 'error');
            if (email) email.style.borderColor='#dc3545';
            valid = false;
        } else if (!message?.value.trim()) {
            showMessage('Пожалуйста, введите сообщение', 'error');
            if (message) message.style.borderColor='#dc3545';
            valid = false;
        } else if (isRegistration && (!privacy || !privacy.checked)) {
            showMessage('Необходимо согласие на обработку данных', 'error');
            if (privacy) privacy.style.outline='2px solid #dc3545';
            valid = false;
        }
        return valid;
    }

    async function submitForm(e) {
        e.preventDefault();
        
        const isEdit = !!editModal?.dataset.userId;
        if (!validateForm(!isEdit)) return;
        
        if (submitBtn) { 
            submitBtn.disabled = true; 
            submitBtn.textContent = isEdit ? 'Сохранение...' : 'Отправка...'; 
        }

        const data = {
            fullName: fullName.value,
            email: email.value,
            phone: phone.value || '',
            organization: organization?.value || '',
            message: message.value,
            privacy: privacy?.checked ? '1' : '0'
        };

        const userId = isEdit ? editModal.dataset.userId : null;
        const endpoint = `/8/public/api/users${userId ? '/'+userId : ''}`;
        const method = userId ? 'PUT' : 'POST';

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
                if (!isEdit) {
                    formContainer.innerHTML = `
                        <div style="text-align:center;padding:30px 20px">
                            <h3 style="margin-bottom:20px">Заявка отправлена</h3>
                            <div style="background:#fff;padding:15px;border-radius:12px;margin-bottom:20px;text-align:left;box-shadow:0 2px 8px rgba(0,0,0,0.05)">
                                <p style="margin:8px 0"><strong>Логин:</strong> <code>${result.login}</code></p>
                                <p style="margin:8px 0"><strong>Пароль:</strong> <code>${result.password}</code></p>
                            </div>
                            <a href="${result.profile_url}" class="form_btn" style="text-decoration:none;display:inline-block">Перейти в профиль</a>
                        </div>`;
                    clearFormData();
                } else {
                    showMessage(result.message || 'Данные обновлены', 'success');
                    setTimeout(() => {
                        editModal.classList.remove('on');
                        bloom.classList.remove('on');
                        document.body.style.overflow = '';
                        isModalOpen = false;
                    }, 1500);
                }
            } else {
                const errs = result.errors ? Object.values(result.errors).join('<br>') : (result.message || 'Ошибка');
                showMessage(errs, 'error');
            }
        } catch (err) {
            console.error(err);
            showMessage(`Ошибка: ${err.message}`, 'error');
        } finally {
            if (submitBtn) { 
                submitBtn.disabled = false; 
                submitBtn.textContent = isEdit ? 'Сохранить изменения' : 'отправить форму'; 
            }
        }
    }

    function openModal() {
        if (!editModal) return;
        editModal.classList.add('on');
        bloom.classList.add('on');
        document.body.style.overflow = 'hidden';
        isModalOpen = true;
    }
    function closeModal() {
        if (!editModal) return;
        editModal.classList.remove('on');
        bloom.classList.remove('on');
        document.body.style.overflow = '';
        isModalOpen = false;
    }

    if (btnEdit) btnEdit.onclick = openModal;
    if (bloom) bloom.onclick = closeModal;
    document.onkeydown = e => { if (e.key === 'Escape' && isModalOpen) closeModal(); };

    loadFormData();
    [fullName, email, phone, organization, message].forEach(f => { if (f) f.addEventListener('input', saveFormData); });
    if (privacy) privacy.addEventListener('change', saveFormData);
    if (contactForm) contactForm.addEventListener('submit', submitForm);
});

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

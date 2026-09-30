document.addEventListener("DOMContentLoaded", function () {
    const AJAX_URL = 'ajax/get_admin_data.php';

    // Dinamički učitavanje sadržaja određenog taba u Admin panelu
    function loadAdminTab(action = 'dashboard', params = {}) {
        const adminContent = document.getElementById('admin-content');
        if (!adminContent) return;

        adminContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="text-muted mt-2">Učitavanje podataka...</div>
            </div>`;

        let url = `${AJAX_URL}?action=${action}`;
        if (params.vehicle_id) {
            url += `&vehicle_id=${params.vehicle_id}`;
        }

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP greška! Status: ${res.status}`);
                return res.text();
            })
            .then(html => {
                adminContent.innerHTML = html;
            })
            .catch(err => {
                console.error('Greška pri učitavanju taba:', err);
                adminContent.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <h5><i class="fa-solid fa-triangle-exclamation me-2"></i>Greška pri učitavanju!</h5>
                        <p class="mb-0">${err.message}</p>
                    </div>`;
            });
    }

    // Automatski učitavanje Dashboarda
    if (document.getElementById('admin-content')) {
        loadAdminTab('dashboard');
    }

    // Upravljanje tabova u admin meniju
    document.addEventListener('click', function (e) {
        if (!e.target) return;
        const btn = e.target.closest('[data-action]');
        
        if (btn && btn.closest('.list-group')) {
            const action = btn.getAttribute('data-action');
            if (action) {
                const parent = btn.closest('.list-group');
                parent.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
                btn.classList.add('active');
                loadAdminTab(action);
            }
        }
    });

    // Ffiltriranje automobila na promenu selekta
    const filterForm = document.getElementById("filterForm");
    if (filterForm) {
        const selects = filterForm.querySelectorAll("select");
        selects.forEach(select => {
            select.addEventListener("change", function () {
                filterForm.submit();
            });
        });
    }

    // Validacija polja forme za registraciju korisnika
    const regForm = document.getElementById("registerForm");
    if (regForm) {
        const fields = {
            ime: {
                element: document.getElementById("regIme"),
                regex: /^[A-ZŠĐČĆŽ][a-zšđčćžA-ZŠĐČĆŽ\s\-]{1,29}$/,
                errorMsg: "Ime mora početi velikim slovom (npr. Marko)."
            },
            prezime: {
                element: document.getElementById("regPrezime"),
                regex: /^[A-ZŠĐČĆŽ][a-zšđčćžA-ZŠĐČĆŽ\s\-]{1,29}$/,
                errorMsg: "Prezime mora početi velikim slovom (npr. Marković)."
            },
            email: {
                element: document.getElementById("regEmail"),
                regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                errorMsg: "Unesite validnu email adresu (npr. ime@gmail.com)."
            },
            telefon: {
                element: document.getElementById("regTelefon"),
                regex: /^(\+381|0)[6][0-9][\s\-]?\d{3,4}[\s\-]?\d{3,4}$/,
                errorMsg: "Ispravan format: 0641234567 ili +381641234567."
            },
            password: {
                element: document.getElementById("regPassword"),
                regex: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/,
                errorMsg: "Min. 8 karaktera, bar jedno slovo i bar jedan broj."
            }
        };

        const passConfirmInput = document.getElementById("regPasswordConfirm");

        // Validira pojedinačno ulazno polje preko definisanog Regex-a
        function validateField(fieldKey) {
            const field = fields[fieldKey];
            if (!field || !field.element) return true;
            const val = field.element.value.trim();

            if (val === "") {
                showFieldError(field.element, "Ovo polje je obavezno.");
                return false;
            } else if (!field.regex.test(val)) {
                showFieldError(field.element, field.errorMsg);
                return false;
            } else {
                showFieldSuccess(field.element);
                return true;
            }
        }

        // Proverava da li se unesena lozinka i potvrda lozinke podudaraju
        function validatePasswordConfirm() {
            if (!passConfirmInput) return true;
            const passVal = fields.password.element ? fields.password.element.value : '';
            const confirmVal = passConfirmInput.value;

            if (confirmVal === "") {
                showFieldError(passConfirmInput, "Potvrdite lozinku.");
                return false;
            } else if (passVal !== confirmVal) {
                showFieldError(passConfirmInput, "Lozinke se ne poklapaju.");
                return false;
            } else {
                showFieldSuccess(passConfirmInput);
                return true;
            }
        }

        // Dodaje live-validation slušaoce na kucanje i napuštanje polja pri registraciji
        Object.keys(fields).forEach(key => {
            const input = fields[key].element;
            if (input) {
                input.addEventListener("input", function () {
                    validateField(key);
                    if (key === "password" && passConfirmInput && passConfirmInput.value !== "") {
                        validatePasswordConfirm();
                    }
                });
                input.addEventListener("blur", function () { validateField(key); });
            }
        });

        if (passConfirmInput) {
            passConfirmInput.addEventListener("input", validatePasswordConfirm);
            passConfirmInput.addEventListener("blur", validatePasswordConfirm);
        }

        // Zaustavlja slanje registracione forme ako neka od validacija ne prolazi
        regForm.addEventListener("submit", function (e) {
            let isFormValid = true;
            Object.keys(fields).forEach(key => {
                if (!validateField(key)) isFormValid = false;
            });
            if (!validatePasswordConfirm()) isFormValid = false;
            if (!isFormValid) e.preventDefault();
        });
    }

    function showFieldError(inputElement, message) {
        inputElement.classList.remove("is-valid");
        inputElement.classList.add("is-invalid");

        let errorDiv = inputElement.parentNode.querySelector(".invalid-feedback");
        if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.className = "invalid-feedback fw-semibold mt-1";
            inputElement.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    function showFieldSuccess(inputElement) {
        inputElement.classList.remove("is-invalid");
        inputElement.classList.add("is-valid");
        const errorDiv = inputElement.parentNode.querySelector(".invalid-feedback");
        if (errorDiv) errorDiv.remove();
    }

    // Upravljanje modalom i akcijom brisanja vozila ili FAQ pitanja
    let deleteTarget = { id: null, type: null };
    document.addEventListener('click', function (e) {
        if (!e.target) return;

        // Otvara modal za potvrdu brisanja vozila ili FAQ-a
        const deleteBtn = e.target.closest('.btn-delete-vehicle, .btn-delete-faq, [data-delete-type]');
        if (deleteBtn) {
            const id = deleteBtn.getAttribute('data-id');
            const type = deleteBtn.classList.contains('btn-delete-vehicle') || deleteBtn.getAttribute('data-delete-type') === 'vehicle' ? 'vehicle' : 'faq';

            deleteTarget = { id: id, type: type };

            const modalBody = document.getElementById('deleteModalBody');
            if (modalBody) {
                modalBody.innerHTML = `Da li ste sigurni da želite da obrišete ovaj ${type === 'vehicle' ? 'automobil' : 'FAQ'}?`;
            }

            const deleteModalEl = document.getElementById('deleteConfirmModal');
            if (deleteModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
            }
        }

        // Potvrda brisanja iz modala šalje zahtev serveru i osvežava listu
        if (e.target.id === 'btnConfirmDelete') {
            if (!deleteTarget.id || !deleteTarget.type) return;

            const formData = new FormData();
            formData.append('action', deleteTarget.type === 'vehicle' ? 'delete_vehicle' : 'delete_faq');
            formData.append('id', deleteTarget.id);

            fetch(AJAX_URL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const deleteModalEl = document.getElementById('deleteConfirmModal');
                if (deleteModalEl && typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getInstance(deleteModalEl);
                    if (modalInstance) modalInstance.hide();
                }

                if (data.success) {
                    loadAdminTab(deleteTarget.type === 'vehicle' ? 'vehicles' : 'faqs');
                } else {
                    alert(data.message || 'Greška pri brisanju.');
                }
            })
            .catch(err => {
                console.error("Greška pri brisanju:", err);
                alert('Došlo je do greške pri komunikaciji sa serverom.');
            });
        }

        // Otvara i resetuje modal za dodavanje novog vozila
        if (e.target.closest('.btn-open-add-vehicle')) {
            const form = document.getElementById('vehicleForm');
            if (form) form.reset();
            
            ['vehicleId', 'vehicleExistingImage'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });

            const title = document.getElementById('vehicleModalTitle');
            if (title) title.innerText = 'Dodaj novo vozilo';

            const modalEl = document.getElementById('vehicleModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        // Dohvata podatke vozila sa servera i popunjava modal za izmenu
        const editVehBtn = e.target.closest('.btn-edit-vehicle');
        if (editVehBtn) {
            const id = editVehBtn.getAttribute('data-id');
            fetch(`${AJAX_URL}?get_single=1&action=get_vehicle&id=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const title = document.getElementById('vehicleModalTitle');
                        if (title) title.innerText = 'Izmeni vozilo';

                        const setVal = (id, val) => {
                            const el = document.getElementById(id);
                            if (el) el.value = val || '';
                        };

                        setVal('vehicleId', res.data.id);
                        setVal('vehicleBrand', res.data.brand);
                        setVal('vehicleModel', res.data.model);
                        setVal('vehicleCategory', res.data.category_id);
                        setVal('vehicleFuel', res.data.fuel_type_id);
                        setVal('vehicleTransmission', res.data.transmission_id);
                        setVal('vehicleYear', res.data.year);
                        setVal('vehicleSeats', res.data.seats || 5);
                        setVal('vehicleRegistration', res.data.registration_number);
                        setVal('vehiclePrice', res.data.price_per_day);
                        setVal('vehicleExistingImage', res.data.image);
                        
                        const modalEl = document.getElementById('vehicleModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        }
                    }
                })
                .catch(err => console.error("Greška pri dohvatanju vozila:", err));
        }

        // Otvara i resetuje modal za dodavanje novog FAQ pitanja
        if (e.target.closest('.btn-open-add-faq')) {
            const form = document.getElementById('faqForm');
            if (form) form.reset();

            const faqId = document.getElementById('faqId');
            if (faqId) faqId.value = '';
            
            const faqCat = document.getElementById('faqCategoryId');
            if (faqCat) {
                faqCat.value = '1';
            }

            const faqTitle = document.getElementById('faqModalTitle');
            if (faqTitle) faqTitle.innerText = 'Dodaj FAQ Pitanje';

            const modalEl = document.getElementById('faqModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        // Dohvata FAQ pitanje sa servera i popunjava modal za izmenu
        const editFaqBtn = e.target.closest('.btn-edit-faq');
        if (editFaqBtn) {
            const id = editFaqBtn.getAttribute('data-id');
            fetch(`${AJAX_URL}?get_single=1&action=get_faq&id=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const title = document.getElementById('faqModalTitle');
                        if (title) title.innerText = 'Izmeni FAQ Pitanje';

                        const setVal = (id, val) => {
                            const el = document.getElementById(id);
                            if (el) el.value = val || '';
                        };

                        setVal('faqId', res.data.id);
                        setVal('faqQuestion', res.data.question);
                        setVal('faqAnswer', res.data.answer);
                        setVal('faqCategoryId', res.data.faq_category_id || 1);

                        const modalEl = document.getElementById('faqModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        }
                    }
                })
                .catch(err => console.error("Greška pri dohvatanju FAQ:", err));
        }
    });

    // Šalje forme za vozilo i FAQ
    function handleFormSubmit(form) {
        const formId = form.getAttribute('id');
        const isVehicle = (formId === 'vehicleForm');
        const modalId = isVehicle ? 'vehicleModal' : 'faqModal';
        
        const formData = new FormData(form);
        formData.append('action', isVehicle ? 'save_vehicle' : 'save_faq');

        if (!isVehicle) {
            if (!formData.get('faq_category_id') || formData.get('faq_category_id') === '') {
                formData.set('faq_category_id', '1');
            }
        }
        
        const oldAlert = form.querySelector('.modal-error-alert');
        if (oldAlert) oldAlert.remove();

        fetch(AJAX_URL, { 
            method: 'POST', 
            body: formData 
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById(modalId);
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.hide();
                }
                form.reset();
                loadAdminTab(isVehicle ? 'vehicles' : 'faqs');
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger modal-error-alert my-2';
                errorDiv.innerText = data.message || 'Došlo je do greške pri čuvanju u bazi.';
                form.prepend(errorDiv);
            }
        })
        .catch(err => {
            console.error("AJAX Error:", err);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger modal-error-alert my-2';
            errorDiv.innerText = 'Greška pri komunikaciji sa serverom.';
            form.prepend(errorDiv);
        });
    }

    // Presreće klasičan submit za vozilo i FAQ i preusmerava na AJAX obradu
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && form.tagName === 'FORM') {
            const fId = form.getAttribute('id');
            if (fId === 'vehicleForm' || fId === 'faqForm') {
                e.preventDefault();
                e.stopPropagation();
                handleFormSubmit(form);
                return false;
            }
        }
    }, true);

    // Klijentska validacija kontakt forme pre slanja
    const formContact = document.getElementById('contactForm');
    if (formContact){
        formContact.addEventListener('submit', function (e) {
            let isValid = true;
            const imeInput = document.getElementById('contactIme');
            const emailInput = document.getElementById('contactEmail');
            const naslovInput = document.getElementById('contactNaslov');
            const porukaInput = document.getElementById('contactPoruka');

            const regexIme = /^[A-ZČĆŠĐŽa-zčćšđž]{2,}\s+[A-ZČĆŠĐŽa-zčćšđž]{2,}(\s+[A-ZČĆŠĐŽa-zčćšđž]{2,})*$/;
            const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            function showError(input, errorDivId, message) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                document.getElementById(errorDivId).textContent = message;
                isValid = false;
            }

            function clearError(input, errorDivId) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                document.getElementById(errorDivId).textContent = '';
            }

            const imeVal = imeInput.value.trim();
            if (imeVal === '') {
                showError(imeInput, 'errIme', 'Molimo unesite vaše ime i prezime.');
            } else if (!regexIme.test(imeVal)) {
                showError(imeInput, 'errIme', 'Ime i prezime moraju sadržati najmanje dve reči (npr. Marko Marković).');
            } else {
                clearError(imeInput, 'errIme');
            }

            const emailVal = emailInput.value.trim();
            if (emailVal === '') {
                showError(emailInput, 'errEmail', 'Molimo unesite vašu email adresu.');
            } else if (!regexEmail.test(emailVal)) {
                showError(emailInput, 'errEmail', 'E-mail adresa nije u ispravnom formatu (npr. ime@primer.com).');
            } else {
                clearError(emailInput, 'errEmail');
            }

            const naslovVal = naslovInput.value.trim();
            if (naslovVal === '') {
                showError(naslovInput, 'errNaslov', 'Molimo unesite naslov poruke.');
            } else if (naslovVal.length < 3) {
                showError(naslovInput, 'errNaslov', 'Naslov mora imati najmanje 3 karaktera.');
            } else {
                clearError(naslovInput, 'errNaslov');
            }

            const porukaVal = porukaInput.value.trim();
            if (porukaVal === '') {
                showError(porukaInput, 'errPoruka', 'Molimo unesite tekst poruke.');
            } else if (porukaVal.length < 10) {
                showError(porukaInput, 'errPoruka', 'Poruka mora sadržati najmanje 10 karaktera.');
            } else {
                clearError(porukaInput, 'errPoruka');
            }

            if (!isValid) e.preventDefault();
        });
    }

    // Inicijalizacija Swiper slajdera za prikaz utisaka/recenzija korisnika
    if (document.querySelector('.reviews-swiper')) {
        new Swiper('.reviews-swiper', {
            slidesPerView: 1,
            spaceBetween: 24,
            loop: false,
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: '.review-next',
                prevEl: '.review-prev',
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 20 },
                992: { slidesPerView: 3, spaceBetween: 24 }
            }
        });
    }

    // Otvara i priprema modal za unos novog servisa
    document.addEventListener('click', function (e) {
        const btnAdd = e.target.closest('.btn-open-add-service');
        if (btnAdd) {
            const form = document.getElementById('serviceForm');
            if (form) form.reset();
            
            const serviceIdInput = document.getElementById('serviceId');
            if (serviceIdInput) serviceIdInput.value = '';
            
            const modalTitle = document.getElementById('serviceModalTitle');
            if (modalTitle) modalTitle.textContent = 'Evidentiraj novi servis';

            const modalEl = document.getElementById('serviceModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
    });

    // Dohvata podatke o servisu i popunjava modal za izmenu
    document.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit-service');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');

            fetch(`${AJAX_URL}?get_single=1&action=get_service&id=${id}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success && res.data) {
                        document.getElementById('serviceId').value = res.data.id;
                        document.getElementById('serviceVehicleId').value = res.data.vehicle_id;
                        document.getElementById('serviceDate').value = res.data.service_date;
                        document.getElementById('serviceDescription').value = res.data.description;
                        document.getElementById('serviceCost').value = res.data.cost;

                        document.getElementById('serviceModalTitle').textContent = 'Izmeni podatak o servisu';

                        const modalEl = document.getElementById('serviceModal');
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    } else {
                        alert('Greška pri učitavanju podataka servisa.');
                    }
                })
                .catch(err => console.error('Greška:', err));
        }
    });

    // Šalje formu servisa
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.getAttribute('id') === 'serviceForm') {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            formData.append('action', 'save_service');

            fetch(AJAX_URL, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const modalEl = document.getElementById('serviceModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    if (typeof loadAdminTab === 'function') {
                        loadAdminTab('services');
                    } else {
                        location.reload();
                    }
                } else {
                    alert(res.message || 'Došlo je do greške prilikom čuvanja.');
                }
            })
            .catch(err => {
                console.error('Greška:', err);
                alert('Greška u komunikaciji sa serverom.');
            });
        }
    });

   // Otvara modal za potvrdu brisanja servisa
    document.addEventListener('click', function (e) {
        const btnDelete = e.target.closest('.btn-delete-service');
        if (!btnDelete) return;

        deleteTargetId = btnDelete.getAttribute('data-id');
        deleteTargetAction = 'delete_service';

        const deleteModalBody = document.getElementById('deleteModalBody');
        if (deleteModalBody) {
            deleteModalBody.textContent = 'Da li ste sigurni da želite da obrišete ovaj zapis o servisu?';
        }

        const deleteModalEl = document.getElementById('deleteConfirmModal');
        if (deleteModalEl) {
            const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            deleteModal.show();
        }
    });

    // Filtrira listu servisa u adminu
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'filterServiceVehicle') {
            const vehicleId = e.target.value;
            if (typeof loadAdminTab === 'function') {
                loadAdminTab('services', { vehicle_id: vehicleId });
            }
        }
    });

    // Generiše i preuzima PDF izveštaj o servisima koristeći html2pdf
    document.addEventListener('click', function (e) {
        const pdfBtn = e.target.closest('.btn-export-pdf');
        if (pdfBtn) {
            const element = document.getElementById('pdf-export-content');
            if (!element) return;

            const ignoreElements = element.querySelectorAll('.no-print-pdf');
            ignoreElements.forEach(el => el.style.display = 'none');

            const opt = {
                margin:       10,
                filename:     `Izvestaj_servisa_${new Date().toISOString().slice(0,10)}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                ignoreElements.forEach(el => el.style.display = '');
            });
        }
    });

    // Ažurira status rezervacije na promenu u padajućoj listi u adminu
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('update-status')) {
            const selectEl = e.target;
            const reservationId = selectEl.getAttribute('data-id');
            const newStatus = selectEl.value;

            const formData = new FormData();
            formData.append('action', 'update_reservation_status');
            formData.append('id', reservationId);
            formData.append('status', newStatus);

            fetch('ajax/get_admin_data.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof loadAdminTab === 'function') {
                        loadAdminTab('reservations');
                    } else {
                        location.reload();
                    }
                } else {
                    alert(data.message || 'Greška pri ažuriranju statusa!');
                }
            })
            .catch(err => {
                console.error('Greška:', err);
                alert('Došlo je do greške u komunikaciji sa serverom.');
            });
        }
    });

    // Upravljanje brisanjem recenzija korisnika pomoću potvrdnog modala
    let deleteTargetId = null;
    let deleteTargetAction = null;

    // Otvara modal za potvrdu brisanja recenzije
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-review');
        if (!btn) return;

        deleteTargetId = btn.dataset.id;
        deleteTargetAction = 'delete_review';

        const deleteModalBody = document.getElementById('deleteModalBody');
        if (deleteModalBody) {
            deleteModalBody.textContent = 'Da li ste sigurni da želite da obrišete ovu recenziju?';
        }

        const deleteModalEl = document.getElementById('deleteConfirmModal');
        if (deleteModalEl) {
            const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            deleteModal.show();
        }
    });
    // Otvara modal za potvrdu brisanja korisnika
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-user');
        if (!btn) return;

        deleteTargetId = btn.dataset.id;
        deleteTargetAction = 'delete_user';

        const deleteModalBody = document.getElementById('deleteModalBody');
        if (deleteModalBody) {
            deleteModalBody.textContent = 'Da li ste sigurni da želite da obrišete ovog korisnika?';
        }

        const deleteModalEl = document.getElementById('deleteConfirmModal');
        if (deleteModalEl) {
            const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            deleteModal.show();
        }
    });
  	// Zahtev za brisanje (recenzija ili korisnik) kada se potvrdi u modalu
    document.getElementById('btnConfirmDelete')?.addEventListener('click', function () {
        if (!deleteTargetId || (deleteTargetAction !== 'delete_review' && deleteTargetAction !== 'delete_user')) return;

        const formData = new FormData();
        formData.append('action', deleteTargetAction);
        formData.append('id', deleteTargetId);

        fetch('ajax/get_admin_data.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const deleteModalEl = document.getElementById('deleteConfirmModal');
            if (deleteModalEl) {
                const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                if (deleteModal) deleteModal.hide();
            }

            if (data.success) {
                const currentTab = deleteTargetAction === 'delete_user' ? 'users' : 'reviews';
                deleteTargetId = null;
                deleteTargetAction = null;

                if (typeof loadAdminTab === 'function') {
                    loadAdminTab(currentTab);
                }
            } else {
                alert(data.message || 'Greška pri brisanju.');
            }
        })
        .catch(err => console.error(err));
    });
    
    // Univerzalni zahtev za brisanje kada se klikne "Obriši" u modalu
    document.getElementById('btnConfirmDelete')?.addEventListener('click', function () {
        if (!deleteTargetId || !deleteTargetAction) return;

        const formData = new FormData();
        formData.append('action', deleteTargetAction);
        formData.append('id', deleteTargetId);

        const targetUrl = (deleteTargetAction === 'delete_service') ? AJAX_URL : 'ajax/get_admin_data.php';

        fetch(targetUrl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const deleteModalEl = document.getElementById('deleteConfirmModal');
            if (deleteModalEl) {
                const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                if (deleteModal) deleteModal.hide();
            }

            if (data.success) {
                const currentAction = deleteTargetAction;
                deleteTargetId = null;
                deleteTargetAction = null;

                if (currentAction === 'delete_service') {
                    if (typeof loadAdminTab === 'function') {
                        loadAdminTab('services');
                    } else {
                        location.reload();
                    }
                } else {
                    const reviewsBtn = document.querySelector('[data-action="reviews"]');
                    if (reviewsBtn) reviewsBtn.click();
                }
            } else {
                alert(data.message || 'Greška pri brisanju.');
            }
        })
        .catch(err => console.error(err));
    });

});
// ============================================
// index.js - Contrôle de saisie
// Plateforme GlobalHealth Connect
// ============================================

(function() {
    'use strict';

    console.log("✅ index.js chargé");

    // ========== FONCTIONS UTILITAIRES ==========
    
    function showError(input, message) {
        if (!input) return;
        removeError(input);
        input.classList.add('is-invalid');
        let errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            input.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    function removeError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
        let errorDiv = input.parentNode?.querySelector('.invalid-feedback');
        if (errorDiv) errorDiv.remove();
    }

    function markValid(input) {
        if (!input) return;
        removeError(input);
        input.classList.add('is-valid');
    }

    function showMessage(msg, isError = true) {
        const toast = document.getElementById('notificationToast');
        if (toast) {
            toast.textContent = msg;
            toast.style.borderLeftColor = isError ? '#dc3545' : '#2ecc71';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        }
    }

    // ========== VALIDATION INSCRIPTION ==========
    
    function initSignupValidation() {
        const form = document.getElementById('signupForm');
        if (!form) return;

        const nameInput = document.getElementById('signupName');
        const emailInput = document.getElementById('signupEmail');
        const phoneInput = document.getElementById('signupPhone');
        const pwdInput = document.getElementById('signupPassword');
        const confirmInput = document.getElementById('signupConfirmPassword');

        function validateName() {
            const value = nameInput.value.trim();
            if (!value) { showError(nameInput, "Nom obligatoire"); return false; }
            if (value.length < 2) { showError(nameInput, "Minimum 2 caractères"); return false; }
            if (value.length > 100) { showError(nameInput, "Maximum 100 caractères"); return false; }
            if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(value)) { showError(nameInput, "Lettres et espaces uniquement"); return false; }
            markValid(nameInput);
            return true;
        }

        function validateEmail() {
            const value = emailInput.value.trim();
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!value) { showError(emailInput, "Email obligatoire"); return false; }
            if (!emailRegex.test(value)) { showError(emailInput, "Email invalide"); return false; }
            markValid(emailInput);
            return true;
        }

        function validatePhone() {
            const value = phoneInput.value.trim();
            const phoneRegex = /^(?:(?:\+|00)33|0)[1-9]\d{8}$/;
            if (value && !phoneRegex.test(value)) { showError(phoneInput, "Téléphone invalide"); return false; }
            markValid(phoneInput);
            return true;
        }

        function validatePassword() {
            const value = pwdInput.value;
            if (!value) { showError(pwdInput, "Mot de passe obligatoire"); return false; }
            if (value.length < 8) { showError(pwdInput, "Minimum 8 caractères"); return false; }
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) { showError(pwdInput, "1 majuscule, 1 minuscule, 1 chiffre"); return false; }
            
            let score = 0;
            if (value.length >= 8) score++;
            if (value.length >= 12) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;
            const strength = Math.min(4, Math.floor(score / 1.5));
            const texts = ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];
            const colors = ['#dc3545', '#f39c12', '#ffc107', '#2ecc71', '#27ae60'];
            const strengthEl = document.getElementById('passwordStrength');
            if (strengthEl) {
                strengthEl.textContent = `Force: ${texts[strength]}`;
                strengthEl.style.color = colors[strength];
            }
            
            markValid(pwdInput);
            return true;
        }

        function validateConfirm() {
            if (!confirmInput.value) { showError(confirmInput, "Confirmation obligatoire"); return false; }
            if (pwdInput.value !== confirmInput.value) { showError(confirmInput, "Les mots de passe ne correspondent pas"); return false; }
            markValid(confirmInput);
            return true;
        }

        nameInput?.addEventListener('blur', validateName);
        emailInput?.addEventListener('blur', validateEmail);
        phoneInput?.addEventListener('blur', validatePhone);
        pwdInput?.addEventListener('blur', validatePassword);
        confirmInput?.addEventListener('blur', validateConfirm);

        form.addEventListener('submit', function(e) {
            const isValid = validateName() && validateEmail() && validatePhone() && validatePassword() && validateConfirm();
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                showMessage("Veuillez corriger les erreurs");
            }
        });
    }

    // ========== VALIDATION CONNEXION ==========
    
    function initSigninValidation() {
        const form = document.getElementById('signinForm');
        if (!form) return;

        const emailInput = document.getElementById('signinEmail');
        const pwdInput = document.getElementById('signinPassword');

        function validateEmail() {
            const value = emailInput?.value.trim();
            if (!value) { showError(emailInput, "Email obligatoire"); return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) { showError(emailInput, "Email invalide"); return false; }
            removeError(emailInput);
            return true;
        }

        function validatePassword() {
            if (!pwdInput?.value) { showError(pwdInput, "Mot de passe obligatoire"); return false; }
            removeError(pwdInput);
            return true;
        }

        form.addEventListener('submit', function(e) {
            if (!validateEmail() || !validatePassword()) {
                e.preventDefault();
                e.stopPropagation();
                showMessage("Email ou mot de passe incorrect");
            }
        });
    }

    // ========== VALIDATION RENDEZ-VOUS ==========
    
    function initAppointmentValidation() {
        const form = document.getElementById('appointmentForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            let valid = true;
            
            const name = document.getElementById('patientName');
            const email = document.getElementById('patientEmail');
            const phone = document.getElementById('patientPhone');
            const type = document.getElementById('consultationType');
            const symptoms = document.getElementById('symptoms');
            
            if (!name.value.trim()) { showError(name, "Nom obligatoire"); valid = false; }
            else if (name.value.trim().length < 2) { showError(name, "Nom trop court"); valid = false; }
            else removeError(name);
            
            if (!email.value.trim()) { showError(email, "Email obligatoire"); valid = false; }
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { showError(email, "Email invalide"); valid = false; }
            else removeError(email);
            
            if (!phone.value.trim()) { showError(phone, "Téléphone obligatoire"); valid = false; }
            else if (!/^(?:(?:\+|00)33|0)[1-9]\d{8}$/.test(phone.value.trim())) { showError(phone, "Téléphone invalide"); valid = false; }
            else removeError(phone);
            
            if (!type.value) { showError(type, "Sélectionnez un type"); valid = false; }
            else removeError(type);
            
            if (!symptoms.value.trim()) { showError(symptoms, "Symptômes obligatoires"); valid = false; }
            else if (symptoms.value.trim().length < 5) { showError(symptoms, "Minimum 5 caractères"); valid = false; }
            else removeError(symptoms);
            
            if (!valid) {
                e.preventDefault();
                e.stopPropagation();
                showMessage("Veuillez compléter tous les champs");
            }
        });
    }

    // ========== VALIDATION AVIS ==========
    
    function initReviewValidation() {
        const form = document.getElementById('submitReviewForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            let valid = true;
            
            const name = document.getElementById('reviewName');
            const doctor = document.getElementById('reviewDoctorId');
            const rating = document.querySelector('input[name="reviewRating"]:checked');
            const comment = document.getElementById('reviewComment');
            
            if (!name.value.trim()) { showError(name, "Nom obligatoire"); valid = false; }
            else if (name.value.trim().length < 2) { showError(name, "Nom trop court"); valid = false; }
            else removeError(name);
            
            if (!doctor.value) { showError(doctor, "Sélectionnez un médecin"); valid = false; }
            else removeError(doctor);
            
            if (!rating) {
                let ratingContainer = document.querySelector('.rating-input');
                let errorDiv = ratingContainer?.parentNode?.querySelector('.rating-error');
                if (!errorDiv && ratingContainer) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block rating-error';
                    errorDiv.textContent = "Sélectionnez une note";
                    ratingContainer.parentNode.appendChild(errorDiv);
                }
                valid = false;
            } else {
                document.querySelector('.rating-error')?.remove();
            }
            
            if (!comment.value.trim()) { showError(comment, "Commentaire obligatoire"); valid = false; }
            else if (comment.value.trim().length < 10) { showError(comment, "Minimum 10 caractères"); valid = false; }
            else removeError(comment);
            
            if (!valid) {
                e.preventDefault();
                e.stopPropagation();
                showMessage("Veuillez corriger les erreurs");
            }
        });
    }

    // ========== VALIDATION DOSSIER MÉDICAL ==========
    
    function initMedicalRecordValidation() {
        const form = document.getElementById('medicalRecordForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            let valid = true;
            
            const medecin = document.getElementById('id_medecin');
            const symptomes = document.getElementById('symptomes');
            const diagnostic = document.getElementById('diagnostic');
            const ordonnanceTexte = document.getElementById('ordonnance_texte');
            const ordonnanceFile = document.getElementById('ordonnanceFile');
            
            if (!medecin.value) { showError(medecin, "Sélectionnez un médecin"); valid = false; }
            else removeError(medecin);
            
            if (!symptomes.value.trim()) { showError(symptomes, "Symptômes obligatoires"); valid = false; }
            else if (symptomes.value.trim().length < 5) { showError(symptomes, "Minimum 5 caractères"); valid = false; }
            else removeError(symptomes);
            
            if (!diagnostic.value.trim()) { showError(diagnostic, "Diagnostic obligatoire"); valid = false; }
            else if (diagnostic.value.trim().length < 3) { showError(diagnostic, "Diagnostic trop court"); valid = false; }
            else removeError(diagnostic);
            
            const hasOrdonnance = ordonnanceTexte.value.trim() || (ordonnanceFile.files && ordonnanceFile.files.length > 0);
            if (!hasOrdonnance) { showError(ordonnanceTexte, "Veuillez fournir une ordonnance"); valid = false; }
            else removeError(ordonnanceTexte);
            
            if (!valid) {
                e.preventDefault();
                e.stopPropagation();
                showMessage("Veuillez compléter le dossier médical");
            }
        });
    }

    // ========== VALIDATION SUIVI ==========
    
    function initFollowupValidation() {
        const form = document.getElementById('followupForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            let valid = true;
            
            const date = document.getElementById('followupDate');
            const doctor = document.getElementById('followupDoctor');
            const subject = document.getElementById('followupSubject');
            const content = document.getElementById('followupContent');
            
            if (!date.value) { showError(date, "Date obligatoire"); valid = false; }
            else {
                const selected = new Date(date.value);
                const today = new Date(); today.setHours(0,0,0,0);
                if (selected > today) { showError(date, "Date future non autorisée"); valid = false; }
                else removeError(date);
            }
            
            if (!doctor.value) { showError(doctor, "Sélectionnez un médecin"); valid = false; }
            else removeError(doctor);
            
            if (!subject.value.trim()) { showError(subject, "Sujet obligatoire"); valid = false; }
            else if (subject.value.trim().length < 5) { showError(subject, "Minimum 5 caractères"); valid = false; }
            else removeError(subject);
            
            if (!content.value.trim()) { showError(content, "Contenu obligatoire"); valid = false; }
            else if (content.value.trim().length < 20) { showError(content, "Minimum 20 caractères"); valid = false; }
            else removeError(content);
            
            if (!valid) {
                e.preventDefault();
                e.stopPropagation();
                showMessage("Veuillez corriger les erreurs");
            }
        });
    }

    // ========== VALIDATION TÉLÉCONSULTATION ==========
    
    function initTeleconsultationValidation() {
        const joinBtn = document.querySelector('#teleconsultation .btn-medical');
        if (joinBtn) {
            const originalOnclick = joinBtn.getAttribute('onclick');
            joinBtn.addEventListener('click', function(e) {
                const link = document.getElementById('consultationLink');
                if (!link.value.trim()) {
                    e.preventDefault();
                    e.stopPropagation();
                    showError(link, "Lien obligatoire");
                    showMessage("Veuillez entrer un lien");
                } else if (!link.value.trim().startsWith('http')) {
                    e.preventDefault();
                    e.stopPropagation();
                    showError(link, "Lien invalide (http:// ou https://)");
                    showMessage("Lien invalide");
                } else {
                    removeError(link);
                }
            });
        }
    }

    // ========== NETTOYAGE ==========
    
    function initCleanup() {
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('input', () => removeError(el));
            el.addEventListener('change', () => removeError(el));
        });
    }

    // ========== INITIALISATION ==========
    
    document.addEventListener('DOMContentLoaded', function() {
        initSignupValidation();
        initSigninValidation();
        initAppointmentValidation();
        initReviewValidation();
        initMedicalRecordValidation();
        initFollowupValidation();
        initTeleconsultationValidation();
        initCleanup();
        console.log("✅ Contrôle de saisie actif");
    });

})();
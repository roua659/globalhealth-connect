// ============================================
// backoffice.js - CONTRÔLE DE SAISIE
// À ajouter dans backoffice.html après le script principal
// ============================================

(function() {
    'use strict';

    console.log("✅ backoffice.js - Contrôle de saisie chargé");

    // ========== STYLES DE VALIDATION ==========
    function addValidationStyles() {
        if (document.getElementById('validation-styles')) return;
        const style = document.createElement('style');
        style.id = 'validation-styles';
        style.textContent = `
            .is-invalid {
                border-color: #dc3545 !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            .invalid-feedback {
                display: block;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            .is-valid {
                border-color: #2ecc71 !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
        `;
        document.head.appendChild(style);
    }

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

    function showNotificationMessage(msg, isError = true) {
        const toast = document.getElementById('notificationToast');
        if (toast) {
            toast.textContent = msg;
            toast.style.borderLeftColor = isError ? '#dc3545' : '#2ecc71';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    }

    // ========== VALIDATION AJOUT UTILISATEUR ==========
    function validateAddUserForm() {
        let isValid = true;
        const name = document.getElementById('newUserName');
        const email = document.getElementById('newUserEmail');
        const phone = document.getElementById('newUserPhone');
        const role = document.getElementById('newUserRole');
        const specialty = document.getElementById('newUserSpecialty');

        // Nom
        if (!name.value.trim()) {
            showError(name, "Le nom est obligatoire");
            isValid = false;
        } else if (name.value.trim().length < 2) {
            showError(name, "Minimum 2 caractères");
            isValid = false;
        } else if (name.value.trim().length > 100) {
            showError(name, "Maximum 100 caractères");
            isValid = false;
        } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(name.value.trim())) {
            showError(name, "Lettres, espaces et tirets uniquement");
            isValid = false;
        } else {
            markValid(name);
        }

        // Email
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!email.value.trim()) {
            showError(email, "L'email est obligatoire");
            isValid = false;
        } else if (!emailRegex.test(email.value.trim())) {
            showError(email, "Email invalide (ex: nom@domaine.com)");
            isValid = false;
        } else {
            markValid(email);
        }

        // Téléphone (optionnel)
        const phoneRegex = /^(?:(?:\+|00)33|0)[1-9]\d{8}$/;
        if (phone.value.trim() && !phoneRegex.test(phone.value.trim())) {
            showError(phone, "Téléphone invalide (ex: 0612345678)");
            isValid = false;
        } else if (phone.value.trim()) {
            markValid(phone);
        }

        // Spécialité pour médecin
        if (role.value === 'doctor' && specialty) {
            if (!specialty.value.trim()) {
                showError(specialty, "Spécialité obligatoire pour un médecin");
                isValid = false;
            } else if (specialty.value.trim().length < 2) {
                showError(specialty, "Minimum 2 caractères");
                isValid = false;
            } else if (specialty.value.trim().length > 50) {
                showError(specialty, "Maximum 50 caractères");
                isValid = false;
            } else {
                markValid(specialty);
            }
        }

        return isValid;
    }

    // ========== VALIDATION MODIFICATION UTILISATEUR ==========
    function validateEditUserForm() {
        let isValid = true;
        const name = document.getElementById('editUserName');
        const email = document.getElementById('editUserEmail');
        const phone = document.getElementById('editUserPhone');
        const specialty = document.getElementById('editUserSpecialty');

        if (!name.value.trim()) {
            showError(name, "Nom obligatoire");
            isValid = false;
        } else if (name.value.trim().length < 2) {
            showError(name, "Minimum 2 caractères");
            isValid = false;
        } else if (name.value.trim().length > 100) {
            showError(name, "Maximum 100 caractères");
            isValid = false;
        } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(name.value.trim())) {
            showError(name, "Lettres uniquement");
            isValid = false;
        } else {
            markValid(name);
        }

        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!email.value.trim()) {
            showError(email, "Email obligatoire");
            isValid = false;
        } else if (!emailRegex.test(email.value.trim())) {
            showError(email, "Email invalide");
            isValid = false;
        } else {
            markValid(email);
        }

        const phoneRegex = /^(?:(?:\+|00)33|0)[1-9]\d{8}$/;
        if (phone.value.trim() && !phoneRegex.test(phone.value.trim())) {
            showError(phone, "Téléphone invalide");
            isValid = false;
        } else if (phone.value.trim()) {
            markValid(phone);
        }

        if (specialty && specialty.value.trim()) {
            if (specialty.value.trim().length < 2) {
                showError(specialty, "Minimum 2 caractères");
                isValid = false;
            } else if (specialty.value.trim().length > 50) {
                showError(specialty, "Maximum 50 caractères");
                isValid = false;
            } else {
                markValid(specialty);
            }
        }

        return isValid;
    }

    // ========== VALIDATION AJOUT PUBLICATION ==========
    function validateAddPostForm() {
        let isValid = true;
        const doctor = document.getElementById('postDoctorId');
        const content = document.getElementById('postContent');
        const image = document.getElementById('postImage');
        const video = document.getElementById('postVideo');

        if (!doctor.value) {
            showError(doctor, "Sélectionnez un médecin");
            isValid = false;
        } else {
            markValid(doctor);
        }

        if (!content.value.trim()) {
            showError(content, "Contenu obligatoire");
            isValid = false;
        } else if (content.value.trim().length < 10) {
            showError(content, "Minimum 10 caractères");
            isValid = false;
        } else if (content.value.trim().length > 2000) {
            showError(content, "Maximum 2000 caractères");
            isValid = false;
        } else {
            markValid(content);
        }

        // More permissive URL validation that allows query strings
        const urlRegex = /^https?:\/\/[^\s]+$/i;
        
        if (image.value.trim() && !urlRegex.test(image.value.trim())) {
            showError(image, "URL d'image invalide (doit commencer par http:// ou https://)");
            isValid = false;
        } else if (image.value.trim()) {
            markValid(image);
        }

        if (video.value.trim() && !urlRegex.test(video.value.trim())) {
            showError(video, "URL de vidéo invalide (doit commencer par http:// ou https://)");
            isValid = false;
        } else if (video.value.trim()) {
            markValid(video);
        }

        return isValid;
    }

    // ========== VALIDATION NOTIFICATION PATIENT ==========
    function validateNotifyReviewForm() {
        let isValid = true;
        const patient = document.getElementById('notifyPatientId');
        const message = document.getElementById('notifyMessage');

        if (!patient.value) {
            showError(patient, "Sélectionnez un patient");
            isValid = false;
        } else {
            markValid(patient);
        }

        if (!message.value.trim()) {
            showError(message, "Message obligatoire");
            isValid = false;
        } else if (message.value.trim().length < 10) {
            showError(message, "Minimum 10 caractères");
            isValid = false;
        } else if (message.value.trim().length > 500) {
            showError(message, "Maximum 500 caractères");
            isValid = false;
        } else {
            markValid(message);
        }

        return isValid;
    }

    // ========== VALIDATION AJOUT CONSULTATION ==========
    function validateAddConsultationForm() {
        let isValid = true;
        const patientId = document.getElementById('consultation_id_patient');
        const medecinId = document.getElementById('consultation_id_medecin');
        const date = document.getElementById('consultation_date');
        const symptomes = document.getElementById('consultation_symptomes');
        const diagnostic = document.getElementById('consultation_diagnostic');

        if (!patientId.value.trim()) {
            showError(patientId, "ID Patient obligatoire");
            isValid = false;
        } else {
            markValid(patientId);
        }

        if (!medecinId.value.trim()) {
            showError(medecinId, "ID Médecin obligatoire");
            isValid = false;
        } else {
            markValid(medecinId);
        }

        if (!date.value) {
            showError(date, "Date obligatoire");
            isValid = false;
        } else {
            markValid(date);
        }

        if (!symptomes.value.trim()) {
            showError(symptomes, "Symptômes obligatoires");
            isValid = false;
        } else if (symptomes.value.trim().length < 5) {
            showError(symptomes, "Minimum 5 caractères");
            isValid = false;
        } else {
            markValid(symptomes);
        }

        if (!diagnostic.value.trim()) {
            showError(diagnostic, "Diagnostic obligatoire");
            isValid = false;
        } else if (diagnostic.value.trim().length < 3) {
            showError(diagnostic, "Minimum 3 caractères");
            isValid = false;
        } else {
            markValid(diagnostic);
        }

        return isValid;
    }

    // ========== VALIDATION MODIFICATION CONSULTATION ==========
    function validateEditConsultationForm() {
        let isValid = true;
        const patientId = document.getElementById('edit_consultation_id_patient');
        const medecinId = document.getElementById('edit_consultation_id_medecin');
        const date = document.getElementById('edit_consultation_date');
        const symptomes = document.getElementById('edit_consultation_symptomes');
        const diagnostic = document.getElementById('edit_consultation_diagnostic');

        if (!patientId.value.trim()) {
            showError(patientId, "ID Patient obligatoire");
            isValid = false;
        } else {
            markValid(patientId);
        }

        if (!medecinId.value.trim()) {
            showError(medecinId, "ID Médecin obligatoire");
            isValid = false;
        } else {
            markValid(medecinId);
        }

        if (!date.value) {
            showError(date, "Date obligatoire");
            isValid = false;
        } else {
            markValid(date);
        }

        if (!symptomes.value.trim()) {
            showError(symptomes, "Symptômes obligatoires");
            isValid = false;
        } else if (symptomes.value.trim().length < 5) {
            showError(symptomes, "Minimum 5 caractères");
            isValid = false;
        } else {
            markValid(symptomes);
        }

        if (!diagnostic.value.trim()) {
            showError(diagnostic, "Diagnostic obligatoire");
            isValid = false;
        } else if (diagnostic.value.trim().length < 3) {
            showError(diagnostic, "Minimum 3 caractères");
            isValid = false;
        } else {
            markValid(diagnostic);
        }

        return isValid;
    }

    // ========== ATTACHEMENT DES VALIDATEURS ==========
    // On utilise un écouteur en phase de capture pour intercepter avant les handlers existants
    function attachValidation() {
        const addUserForm = document.getElementById('addUserForm');
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
                if (!validateAddUserForm()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showNotificationMessage("Veuillez corriger les erreurs du formulaire", true);
                }
            }, true); // capture phase
        }

        const editUserForm = document.getElementById('editUserForm');
        if (editUserForm) {
            editUserForm.addEventListener('submit', function(e) {
                if (!validateEditUserForm()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showNotificationMessage("Veuillez corriger les erreurs", true);
                }
            }, true);
        }

        const addPostForm = document.getElementById('addPostForm');
        if (addPostForm) {
            addPostForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                if (!validateAddPostForm()) {
                    showNotificationMessage("Veuillez corriger les erreurs", true);
                    return;
                }

                try {
                    const formData = {
                        id_medecin: document.getElementById('postDoctorId').value,
                        contenu: document.getElementById('postContent').value.trim(),
                        url_image: document.getElementById('postImage').value.trim() || null,
                        url_video: document.getElementById('postVideo').value.trim() || null
                    };

                    const response = await fetch('../../api/publications.php?action=store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        showNotificationMessage("Publication ajoutée avec succès!", false);
                        addPostForm.reset();
                        const modal = document.getElementById('addPostModal');
                        if (modal) {
                            const bsModal = new bootstrap.Modal(modal);
                            bsModal.hide();
                        }
                        // Reload publications if function exists
                        if (typeof loadPublications === 'function') {
                            loadPublications();
                        }
                    } else {
                        showNotificationMessage(result.error || "Erreur lors de l'ajout", true);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    showNotificationMessage("Erreur lors de l'ajout: " + error.message, true);
                }
            }, true);
        }

        const notifyReviewForm = document.getElementById('notifyReviewForm');
        if (notifyReviewForm) {
            notifyReviewForm.addEventListener('submit', function(e) {
                if (!validateNotifyReviewForm()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showNotificationMessage("Veuillez corriger les erreurs", true);
                }
            }, true);
        }

        const addConsultationForm = document.getElementById('addConsultationForm');
        if (addConsultationForm) {
            addConsultationForm.addEventListener('submit', function(e) {
                if (!validateAddConsultationForm()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showNotificationMessage("Veuillez corriger les erreurs", true);
                }
            }, true);
        }

        const editConsultationForm = document.getElementById('editConsultationForm');
        if (editConsultationForm) {
            editConsultationForm.addEventListener('submit', function(e) {
                if (!validateEditConsultationForm()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    showNotificationMessage("Veuillez corriger les erreurs", true);
                }
            }, true);
        }
    }

    // ========== NETTOYAGE DES ERREURS EN TEMPS RÉEL ==========
    function attachRealTimeCleanup() {
        const allInputs = document.querySelectorAll('#addUserForm input, #addUserForm select, #addUserForm textarea, ' +
            '#editUserForm input, #editUserForm select, #editUserForm textarea, ' +
            '#addPostForm input, #addPostForm select, #addPostForm textarea, ' +
            '#notifyReviewForm input, #notifyReviewForm select, #notifyReviewForm textarea, ' +
            '#addConsultationForm input, #addConsultationForm select, #addConsultationForm textarea, ' +
            '#editConsultationForm input, #editConsultationForm select, #editConsultationForm textarea');
        
        allInputs.forEach(input => {
            input.addEventListener('input', () => removeError(input));
            input.addEventListener('change', () => removeError(input));
        });
    }

    // ========== INITIALISATION ==========
    function init() {
        addValidationStyles();
        attachValidation();
        attachRealTimeCleanup();
        console.log("✅ backoffice.js - Validation active sur tous les formulaires");
    }

    // Exécuter après le chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
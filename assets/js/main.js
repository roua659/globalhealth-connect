// ============================================
// GlobalHealth Connect - JavaScript Principal
// ============================================

(function() {
    'use strict';

    // ========== INITIALISATION ==========
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ GlobalHealth Connect - JS chargé');
        
        initNavbarScroll();
        initTooltips();
        initFormValidation();
        initDataTables();
        initDeleteConfirmation();
        initNotificationSystem();
        initChatbot();
        initReminderCheck();
        
        // Charger les statistiques si sur la page dashboard
        if (document.getElementById('statsContainer')) {
            loadStatistics();
        }
    });

    // ========== NAVBAR SCROLL ==========
    function initNavbarScroll() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('navbar-scrolled');
                } else {
                    navbar.classList.remove('navbar-scrolled');
                }
            });
        }
    }

    // ========== TOOLTIPS ==========
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltips.length && typeof bootstrap !== 'undefined') {
            tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
        }
    }

    // ========== VALIDATION DES FORMULAIRES ==========
    function initFormValidation() {
        // Validation des champs en temps réel
        const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        // Validation des emails
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && !isValidEmail(this.value)) {
                    showFieldError(this, 'Email invalide');
                } else {
                    removeFieldError(this);
                }
            });
        });
        
        // Validation des téléphones
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && !isValidPhone(this.value)) {
                    showFieldError(this, 'Numéro de téléphone invalide');
                } else {
                    removeFieldError(this);
                }
            });
        });
    }
    
    function validateField(input) {
        if (!input.value.trim()) {
            showFieldError(input, 'Ce champ est obligatoire');
            return false;
        }
        
        if (input.hasAttribute('minlength') && input.value.length < parseInt(input.getAttribute('minlength'))) {
            showFieldError(input, `Minimum ${input.getAttribute('minlength')} caractères`);
            return false;
        }
        
        removeFieldError(input);
        return true;
    }
    
    function showFieldError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        
        let errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            input.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    function removeFieldError(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        const errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) errorDiv.remove();
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function isValidPhone(phone) {
        return /^(?:(?:\+|00)33|0)[1-9]\d{8}$/.test(phone);
    }

    // ========== DATATABLES ==========
    function initDataTables() {
        const tables = document.querySelectorAll('.datatable');
        tables.forEach(table => {
            // Ajouter la recherche
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Rechercher...';
            searchInput.className = 'form-control form-control-sm mb-3';
            searchInput.style.width = '300px';
            table.parentNode.insertBefore(searchInput, table);
            
            searchInput.addEventListener('keyup', function() {
                const searchText = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchText) ? '' : 'none';
                });
            });
        });
    }

    // ========== CONFIRMATION SUPPRESSION ==========
    function initDeleteConfirmation() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
                    e.preventDefault();
                }
            });
        });
    }

    // ========== SYSTÈME DE NOTIFICATION ==========
    function initNotificationSystem() {
        window.showNotification = function(message, type = 'success') {
            const toast = document.getElementById('notificationToast');
            if (toast) {
                toast.textContent = message;
                toast.className = 'notification-toast';
                if (type === 'error') toast.classList.add('error');
                if (type === 'warning') toast.classList.add('warning');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 4000);
            } else {
                alert(message);
            }
        };
        
        window.showSuccess = function(message) {
            showNotification(message, 'success');
        };
        
        window.showError = function(message) {
            showNotification(message, 'error');
        };
        
        window.showWarning = function(message) {
            showNotification(message, 'warning');
        };
    }

    // ========== CHATBOT ==========
    function initChatbot() {
        window.toggleChatbot = function() {
            const window = document.getElementById('chatbotWindow');
            if (window) {
                window.classList.toggle('show');
            }
        };
        
        window.sendMessage = function() {
            const input = document.getElementById('chatInput');
            const messages = document.getElementById('chatMessages');
            
            if (!input || !messages) return;
            
            const message = input.value.trim();
            if (!message) return;
            
            // Ajouter le message utilisateur
            addChatMessage(message, 'user');
            input.value = '';
            
            // Simuler réponse du bot
            setTimeout(() => {
                const response = getBotResponse(message);
                addChatMessage(response, 'bot');
            }, 500);
        };
        
        function addChatMessage(message, type) {
            const messages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }
        
        function getBotResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('rendez-vous') || lowerMessage.includes('rdv')) {
                return "Pour prendre un rendez-vous, allez dans la section 'Consultation' ou cliquez sur 'Prendre rendez-vous' sur la page d'accueil. Vous pouvez choisir entre téléconsultation ou consultation présentielle.";
            }
            if (lowerMessage.includes('dossier') || lowerMessage.includes('medical')) {
                return "Votre dossier médical est accessible dans la section 'Mon Dossier Médical'. Vous y trouverez tous vos diagnostics, traitements et ordonnances.";
            }
            if (lowerMessage.includes('medecin') || lowerMessage.includes('docteur')) {
                return "Nous avons plusieurs médecins experts dans différentes spécialités. Consultez la section 'Nos médecins' pour voir la liste complète et leurs disponibilités.";
            }
            if (lowerMessage.includes('teleconsultation') || lowerMessage.includes('visio')) {
                return "La téléconsultation vous permet de consulter un médecin à distance. Rendez-vous dans la section 'Téléconsultation', entrez le lien fourni par votre médecin et rejoignez la consultation.";
            }
            if (lowerMessage.includes('ordonnance')) {
                return "Vos ordonnances sont disponibles dans votre dossier médical. Vous pouvez les télécharger au format PDF ou les visualiser directement en ligne.";
            }
            if (lowerMessage.includes('contact') || lowerMessage.includes('aide')) {
                return "Pour toute assistance, vous pouvez nous contacter au 01 23 45 67 89 ou par email à support@globalhealth.com. Notre service est disponible 24h/24 et 7j/7.";
            }
            
            return "Je suis votre assistant GlobalHealth. Je peux vous aider pour :\n- Prendre un rendez-vous\n- Accéder à votre dossier médical\n- Consulter un médecin\n- Télécharger une ordonnance\n- Obtenir de l'aide\n\nQue souhaitez-vous faire ?";
        }
        
        // Envoyer avec la touche Entrée
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
    }

    // ========== RAPPEL CALENDRIER ==========
    function initReminderCheck() {
        // Vérifier les rappels toutes les minutes
        setInterval(checkReminders, 60000);
        checkReminders(); // Vérifier immédiatement
    }
    
    function checkReminders() {
        fetch('index.php?action=checkReminders')
            .then(response => response.json())
            .then(data => {
                if (data.reminders && data.reminders.length > 0) {
                    data.reminders.forEach(reminder => {
                        showReminderAlert(reminder);
                    });
                }
            })
            .catch(error => console.log('Erreur rappel:', error));
    }
    
    function showReminderAlert(reminder) {
        const now = new Date();
        const rdvDateTime = new Date(reminder.date_rdv + ' ' + reminder.heure_rdv);
        const diffMinutes = Math.floor((rdvDateTime - now) / 60000);
        
        if (diffMinutes <= 60 && diffMinutes > 0 && !reminder.notified) {
            // Notification navigateur
            if (Notification.permission === 'granted') {
                new Notification('⏰ Rappel de rendez-vous', {
                    body: `Vous avez un rendez-vous avec Dr. ${reminder.medecin_nom} dans ${diffMinutes} minutes`,
                    icon: '/favicon.ico',
                    tag: 'rdv-reminder'
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission();
            }
            
            // Alerte classique
            const message = `⏰ RAPPEL DE RENDEZ-VOUS ⏰\n\n` +
                           `Médecin : Dr. ${reminder.medecin_nom}\n` +
                           `Date : ${reminder.date_rdv}\n` +
                           `Heure : ${reminder.heure_rdv}\n` +
                           `Dans : ${diffMinutes} minutes\n\n` +
                           `Préparez-vous pour votre consultation !`;
            
            if (confirm(message + '\n\nVoulez-vous rejoindre la consultation ?')) {
                if (reminder.lien_visio) {
                    window.open(reminder.lien_visio, '_blank');
                }
            }
            
            // Marquer comme notifié
            reminder.notified = true;
        }
    }

    // ========== STATISTIQUES ==========
    function loadStatistics() {
        fetch('index.php?action=getStatistics')
            .then(response => response.json())
            .then(data => {
                updateStatisticsDisplay(data);
            })
            .catch(error => console.log('Erreur statistiques:', error));
    }
    
    function updateStatisticsDisplay(data) {
        const container = document.getElementById('statsContainer');
        if (!container) return;
        
        // Mettre à jour les cartes de statistiques
        if (data.rdv && data.rdv.total) {
            const totalRdv = document.getElementById('statTotalRdv');
            if (totalRdv) totalRdv.textContent = data.rdv.total;
        }
        
        if (data.dossiers && data.dossiers.total) {
            const totalDossiers = document.getElementById('statTotalDossiers');
            if (totalDossiers) totalDossiers.textContent = data.dossiers.total;
        }
        
        // Créer graphique simple si Chart.js est disponible
        if (typeof Chart !== 'undefined' && data.rdv && data.rdv.by_month) {
            const ctx = document.getElementById('statsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.rdv.by_month.map(item => item.mois),
                        datasets: [{
                            label: 'Nombre de rendez-vous',
                            data: data.rdv.by_month.map(item => item.count),
                            borderColor: '#2b7be4',
                            backgroundColor: 'rgba(43, 123, 228, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        }
                    }
                });
            }
        }
    }

    // ========== EXPORT FONCTIONS ==========
    window.exportToPDF = function(type) {
        const pageMap = {
            rendezvous: 'rendezvous',
            dossiers: 'dossiers'
        };
        const page = pageMap[type] || type;
        window.location.href = `index.php?page=${encodeURIComponent(page)}&action=exportPDF`;
    };
    
    window.exportToCSV = function(type) {
        const pageMap = {
            rendezvous: 'rendezvous',
            dossiers: 'dossiers'
        };
        const page = pageMap[type] || type;
        window.location.href = `index.php?page=${encodeURIComponent(page)}&action=exportCSV`;
    };
    
    window.sendReminderEmail = function(id) {
        if (confirm('Envoyer un rappel par email au patient ?')) {
            fetch(`index.php?action=sendReminder&id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Rappel envoyé avec succès');
                } else {
                    showError('Erreur lors de l\'envoi');
                }
            });
        }
    };
    
    window.sendVideoLink = function(id) {
        if (confirm('Envoyer le lien de téléconsultation par email ?')) {
            fetch(`index.php?action=sendVideoLink&id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Lien de visio envoyé avec succès');
                } else {
                    showError('Erreur lors de l\'envoi');
                }
            });
        }
    };

})();

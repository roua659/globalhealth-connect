<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth - Gestion des Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --medical-white: #ffffff;
            --medical-blue: #2b7be4;
            --medical-light-blue: #e8f4ff;
            --medical-green: #2ecc71;
            --medical-gray: #f5f7fa;
            --medical-text: #2c3e50;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--medical-gray);
            color: var(--medical-text);
        }
        .navbar-custom {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
        }
        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .stat-number { font-size: 2rem; font-weight: 800; margin: 10px 0 5px; color: var(--medical-blue); }
        .stat-label { color: #6c7a8a; font-size: 0.9rem; }
        .data-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .data-table th {
            font-weight: 600;
            color: var(--medical-blue);
            background: var(--medical-light-blue);
        }
        .btn-medical {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-medical:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(43,123,228,0.3); }
        .btn-outline-medical {
            background: transparent;
            border: 2px solid var(--medical-blue);
            color: var(--medical-blue);
            padding: 8px 22px;
            border-radius: 40px;
            font-weight: 600;
        }
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            margin: 0 3px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .icon-btn:hover { background: var(--medical-gray); }
        .icon-btn.edit { color: var(--medical-blue); }
        .icon-btn.delete { color: #e74c3c; }
        .modal-custom .modal-content {
            border-radius: 28px;
            border: none;
            padding: 10px;
        }
        .form-control-custom {
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 12px 16px;
        }
        .form-control-custom:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.1);
        }
        .notification-toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: white;
            padding: 14px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateX(450px);
            transition: transform 0.3s;
            z-index: 1001;
            border-left: 4px solid #2ecc71;
        }
        .notification-toast.show { transform: translateX(0); }
        .is-invalid {
            border-color: #dc3545 !important;
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
        }
        .nav-links {
            display: flex;
            gap: 20px;
            margin-left: auto;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--medical-text);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 40px;
            transition: all 0.3s;
        }
        .nav-links a:hover, .nav-links a.active {
            background: var(--medical-light-blue);
            color: var(--medical-blue);
        }
    </style>
</head>
<body>

<div class="navbar-custom">
    <div class="d-flex align-items-center">
        <div class="logo-icon me-3"><i class="fas fa-heartbeat"></i></div>
        <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>Backoffice Médical</small></div>
        <div class="nav-links">
            <a href="?controller=admin"><i class="fas fa-home me-1"></i>Dashboard</a>
            <a href="?controller=consultation" class="active"><i class="fas fa-stethoscope me-1"></i>Consultations</a>
            <a href="?controller=suivie"><i class="fas fa-chart-line me-1"></i>Suivis</a>
            <a href="?controller=auth&action=logout" style="color:#e74c3c;background:#ffeaea;"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><i class="fas fa-stethoscope me-2"></i>Gestion des Consultations</h1>
        <button class="btn-medical" onclick="showAddModal()"><i class="fas fa-plus me-2"></i>Nouvelle consultation</button>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalConsultations"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total consultations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($consultations); ?></div>
            <div class="stat-label">Consultations enregistrées</div>
        </div>
    </div>

    <!-- Liste des consultations -->
    <div class="data-card">
        <h5 class="mb-3"><i class="fas fa-list me-2"></i>Liste des consultations</h5>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Date RDV</th>
                        <th>Diagnostic</th>
                        <th>Traitement</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="consultationsTableBody">
                    <?php foreach($consultations as $c): ?>
                    <tr>
                        <td><?php echo $c['id_consultation']; ?></td>
                        <td><?php echo htmlspecialchars($c['patient_nom'] . ' ' . $c['patient_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($c['medecin_nom'] . ' ' . $c['medecin_prenom']); ?></td>
                        <td><?php echo $c['date_rdv']; ?></td>
                        <td><?php echo htmlspecialchars(substr($c['diagnostic'], 0, 30)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars(substr($c['traitement'], 0, 30)) . '...'; ?></td>
                        <td><?php echo $c['date_creation']; ?></td>
                        <td>
                            <button class="icon-btn edit" onclick="editConsultation(<?php echo $c['id_consultation']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="icon-btn delete" onclick="deleteConsultation(<?php echo $c['id_consultation']; ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajouter/Modifier Consultation -->
<div class="modal fade" id="consultationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-stethoscope me-2"></i>Ajouter une consultation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="consultationForm">
                    <input type="hidden" id="consultationId">
                    <div class="mb-3">
                        <label>Rendez-vous associé *</label>
                        <select class="form-select form-control-custom" id="rdvId">
                            <option value="">Sélectionner un rendez-vous</option>
                            <?php foreach($rendezvous as $r): ?>
                            <option value="<?php echo $r['id_rdv']; ?>">
                                <?php echo $r['patient_nom'] . ' ' . $r['patient_prenom'] . ' - ' . $r['date_rdv']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback" id="rdvIdError"></div>
                    </div>
                    <div class="mb-3">
                        <label>Diagnostic *</label>
                        <textarea class="form-control form-control-custom" id="diagnostic" rows="3" placeholder="Description du diagnostic..."></textarea>
                        <div class="invalid-feedback" id="diagnosticError"></div>
                    </div>
                    <div class="mb-3">
                        <label>Traitement *</label>
                        <textarea class="form-control form-control-custom" id="traitement" rows="3" placeholder="Traitement prescrit..."></textarea>
                        <div class="invalid-feedback" id="traitementError"></div>
                    </div>
                    <div class="mb-3">
                        <label>Notes complémentaires</label>
                        <textarea class="form-control form-control-custom" id="notes" rows="2" placeholder="Notes supplémentaires..."></textarea>
                        <div class="invalid-feedback" id="notesError"></div>
                    </div>
                    <button type="submit" class="btn btn-medical w-100">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="notification-toast" id="notificationToast"></div>

<script>
    // ============================================
    // CONTRÔLE DE SAISIE JAVASCRIPT
    // ============================================
    const validationStyles = `
        <style>
            .is-invalid { border-color: #dc3545 !important; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(0.375em + 0.1875rem) center; background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); }
            .invalid-feedback { display: block; width: 100%; margin-top: 0.25rem; font-size: 0.875em; color: #dc3545; }
            .is-valid { border-color: #2ecc71 !important; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(0.375em + 0.1875rem) center; background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); }
        </style>
    `;
    document.head.insertAdjacentHTML('beforeend', validationStyles);

    function showError(input, message) {
        removeError(input);
        input.classList.add('is-invalid');
        let errorDiv = input.parentNode.querySelector('.invalid-feedback:not(#rdvIdError, #diagnosticError, #traitementError, #notesError)');
        if (!errorDiv || errorDiv.id === '') {
            errorDiv = input.parentNode.querySelector('.invalid-feedback');
        }
        if (errorDiv) errorDiv.textContent = message;
    }

    function removeError(input) {
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
        let errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) errorDiv.textContent = '';
    }

    function markValid(input) {
        removeError(input);
        input.classList.add('is-valid');
    }

    function showNotification(msg, isError = false) {
        const toast = document.getElementById('notificationToast');
        if (toast) {
            toast.textContent = msg;
            toast.style.borderLeftColor = isError ? '#dc3545' : '#2ecc71';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    }

    function validateConsultationForm() {
        let isValid = true;
        
        const rdvId = document.getElementById('rdvId');
        const diagnostic = document.getElementById('diagnostic');
        const traitement = document.getElementById('traitement');
        
        // Validation RDV
        if (!rdvId.value) {
            showError(rdvId, "Veuillez sélectionner un rendez-vous");
            isValid = false;
        } else {
            markValid(rdvId);
        }
        
        // Validation Diagnostic
        if (!diagnostic.value.trim()) {
            showError(diagnostic, "Le diagnostic est obligatoire");
            isValid = false;
        } else if (diagnostic.value.trim().length < 5) {
            showError(diagnostic, "Minimum 5 caractères");
            isValid = false;
        } else if (diagnostic.value.trim().length > 500) {
            showError(diagnostic, "Maximum 500 caractères");
            isValid = false;
        } else {
            markValid(diagnostic);
        }
        
        // Validation Traitement
        if (!traitement.value.trim()) {
            showError(traitement, "Le traitement est obligatoire");
            isValid = false;
        } else if (traitement.value.trim().length < 3) {
            showError(traitement, "Minimum 3 caractères");
            isValid = false;
        } else if (traitement.value.trim().length > 500) {
            showError(traitement, "Maximum 500 caractères");
            isValid = false;
        } else {
            markValid(traitement);
        }
        
        return isValid;
    }

    // Nettoyage en temps réel
    document.querySelectorAll('#consultationForm input, #consultationForm select, #consultationForm textarea').forEach(input => {
        input.addEventListener('input', () => removeError(input));
        input.addEventListener('change', () => removeError(input));
    });

    // ============================================
    // FONCTIONS CRUD
    // ============================================
    async function loadConsultations() {
        try {
            const response = await fetch('?controller=consultation&action=list');
            const consultations = await response.json();
            const tbody = document.getElementById('consultationsTableBody');
            if (tbody) {
                tbody.innerHTML = consultations.map(c => `
                    <tr>
                        <td>${c.id_consultation}</td>
                        <td>${c.patient_nom || ''} ${c.patient_prenom || ''}</td>
                        <td>${c.medecin_nom || ''} ${c.medecin_prenom || ''}</td>
                        <td>${c.date_rdv || ''}</td>
                        <td>${c.diagnostic ? c.diagnostic.substring(0, 30) + '...' : ''}</td>
                        <td>${c.traitement ? c.traitement.substring(0, 30) + '...' : ''}</td>
                        <td>${c.date_creation || ''}</td>
                        <td>
                            <button class="icon-btn edit" onclick="editConsultation(${c.id_consultation})"><i class="fas fa-edit"></i></button>
                            <button class="icon-btn delete" onclick="deleteConsultation(${c.id_consultation})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
            // Mettre à jour le compteur
            document.getElementById('totalConsultations').textContent = consultations.length;
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    async function editConsultation(id) {
        try {
            const response = await fetch(`?controller=consultation&action=get&id=${id}`);
            const consultation = await response.json();
            if (consultation) {
                document.getElementById('consultationId').value = consultation.id_consultation;
                document.getElementById('rdvId').value = consultation.id_rdv;
                document.getElementById('diagnostic').value = consultation.diagnostic;
                document.getElementById('traitement').value = consultation.traitement;
                document.getElementById('notes').value = consultation.notes;
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier la consultation';
                new bootstrap.Modal(document.getElementById('consultationModal')).show();
            }
        } catch (error) {
            showNotification('Erreur lors du chargement', true);
        }
    }

    async function deleteConsultation(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette consultation ?')) {
            try {
                const response = await fetch(`?controller=consultation&action=delete&id=${id}`, {
                    method: 'DELETE'
                });
                const result = await response.json();
                if (result.success) {
                    showNotification(result.message);
                    loadConsultations();
                } else {
                    showNotification(result.message, true);
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression', true);
            }
        }
    }

    function showAddModal() {
        document.getElementById('consultationForm').reset();
        document.getElementById('consultationId').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Ajouter une consultation';
        // Nettoyer les erreurs
        document.querySelectorAll('#consultationForm .is-invalid, #consultationForm .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
        });
        new bootstrap.Modal(document.getElementById('consultationModal')).show();
    }

    // Soumission du formulaire
    document.getElementById('consultationForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validateConsultationForm()) {
            showNotification('Veuillez corriger les erreurs du formulaire', true);
            return;
        }
        
        const id = document.getElementById('consultationId').value;
        const data = {
            id_rdv: document.getElementById('rdvId').value,
            diagnostic: document.getElementById('diagnostic').value,
            traitement: document.getElementById('traitement').value,
            notes: document.getElementById('notes').value
        };
        
        if (id) {
            data.id_consultation = id;
        }
        
        const url = id ? '?controller=consultation&action=update' : '?controller=consultation&action=create';
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                showNotification(result.message);
                bootstrap.Modal.getInstance(document.getElementById('consultationModal')).hide();
                loadConsultations();
            } else {
                showNotification(result.message, true);
            }
        } catch (error) {
            showNotification('Erreur lors de l\'enregistrement', true);
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
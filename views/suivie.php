<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth - Gestion des Suivis</title>
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
            <a href="?controller=consultation"><i class="fas fa-stethoscope me-1"></i>Consultations</a>
            <a href="?controller=suivie" class="active"><i class="fas fa-chart-line me-1"></i>Suivis</a>
            <a href="?controller=auth&action=logout" style="color:#e74c3c;background:#ffeaea;"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><i class="fas fa-chart-line me-2"></i>Gestion des Suivis Patients</h1>
        <button class="btn-medical" onclick="showAddModal()"><i class="fas fa-plus me-2"></i>Nouveau suivi</button>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalSuivis"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total suivis</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['moyenne_poids'] ?: '0'; ?> kg</div>
            <div class="stat-label">Poids moyen des patients</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($suivis); ?></div>
            <div class="stat-label">Suivis enregistrés</div>
        </div>
    </div>

    <!-- Liste des suivis -->
    <div class="data-card">
        <h5 class="mb-3"><i class="fas fa-list me-2"></i>Liste des suivis patients</h5>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Date suivi</th>
                        <th>Poids</th>
                        <th>Tension</th>
                        <th>État général</th>
                        <th>Prochain RDV</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="suivisTableBody">
                    <?php foreach($suivis as $s): ?>
                    <tr>
                        <td><?php echo $s['id_suivie']; ?></td>
                        <td><?php echo htmlspecialchars($s['patient_nom'] . ' ' . $s['patient_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($s['medecin_nom'] . ' ' . $s['medecin_prenom']); ?></td>
                        <td><?php echo $s['date_suivi']; ?></td>
                        <td><?php echo $s['poids'] ?: '-'; ?> kg</td>
                        <td><?php echo $s['tension'] ?: '-'; ?></td>
                        <td><?php echo htmlspecialchars(substr($s['etat_general'], 0, 30)) . '...'; ?></td>
                        <td><?php echo $s['prochain_rdv'] ?: '-'; ?></td>
                        <td>
                            <button class="icon-btn edit" onclick="editSuivi(<?php echo $s['id_suivie']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="icon-btn delete" onclick="deleteSuivi(<?php echo $s['id_suivie']; ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajouter/Modifier Suivi -->
<div class="modal fade" id="suiviModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-custom">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-chart-line me-2"></i>Ajouter un suivi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="suiviForm">
                    <input type="hidden" id="suiviId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Patient *</label>
                            <select class="form-select form-control-custom" id="patientId">
                                <option value="">Sélectionner un patient</option>
                                <?php foreach($patients as $p): ?>
                                <option value="<?php echo $p['id_patient']; ?>"><?php echo htmlspecialchars($p['nom'] . ' ' . $p['prenom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="patientIdError"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Médecin *</label>
                            <select class="form-select form-control-custom" id="medecinId">
                                <option value="">Sélectionner un médecin</option>
                                <?php foreach($medecins as $m): ?>
                                <option value="<?php echo $m['id_medecin']; ?>"><?php echo htmlspecialchars($m['nom'] . ' ' . $m['prenom'] . ' (' . $m['specialite'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="medecinIdError"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Consultation associée</label>
                            <select class="form-select form-control-custom" id="consultationId">
                                <option value="">Sélectionner une consultation</option>
                                <?php foreach($consultations as $c): ?>
                                <option value="<?php echo $c['id_consultation']; ?>"><?php echo $c['diagnostic'] . ' - ' . $c['date_rdv']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date du suivi *</label>
                            <input type="date" class="form-control form-control-custom" id="dateSuivi">
                            <div class="invalid-feedback" id="dateSuiviError"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Poids (kg)</label>
                            <input type="number" step="0.1" class="form-control form-control-custom" id="poids" placeholder="Ex: 72.5">
                            <div class="invalid-feedback" id="poidsError"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tension artérielle</label>
                            <input type="text" class="form-control form-control-custom" id="tension" placeholder="Ex: 12/8">
                            <div class="invalid-feedback" id="tensionError"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Prochain rendez-vous</label>
                            <input type="date" class="form-control form-control-custom" id="prochainRdv">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>État général *</label>
                        <textarea class="form-control form-control-custom" id="etatGeneral" rows="2" placeholder="Description de l'état général du patient..."></textarea>
                        <div class="invalid-feedback" id="etatGeneralError"></div>
                    </div>
                    <div class="mb-3">
                        <label>Analyses à réaliser</label>
                        <textarea class="form-control form-control-custom" id="analyses" rows="2" placeholder="Analyses prescrites..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Régime alimentaire</label>
                            <textarea class="form-control form-control-custom" id="regime" rows="2" placeholder="Recommandations alimentaires..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Activité physique</label>
                            <textarea class="form-control form-control-custom" id="activite" rows="2" placeholder="Recommandations sportives..."></textarea>
                        </div>
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

    function showError(input, message, errorId) {
        removeError(input);
        input.classList.add('is-invalid');
        const errorDiv = document.getElementById(errorId);
        if (errorDiv) errorDiv.textContent = message;
    }

    function removeError(input) {
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
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

    function validateSuiviForm() {
        let isValid = true;
        
        const patientId = document.getElementById('patientId');
        const medecinId = document.getElementById('medecinId');
        const dateSuivi = document.getElementById('dateSuivi');
        const etatGeneral = document.getElementById('etatGeneral');
        
        // Validation Patient
        if (!patientId.value) {
            showError(patientId, "Veuillez sélectionner un patient", 'patientIdError');
            isValid = false;
        } else {
            markValid(patientId);
        }
        
        // Validation Médecin
        if (!medecinId.value) {
            showError(medecinId, "Veuillez sélectionner un médecin", 'medecinIdError');
            isValid = false;
        } else {
            markValid(medecinId);
        }
        
        // Validation Date
        if (!dateSuivi.value) {
            showError(dateSuivi, "La date du suivi est obligatoire", 'dateSuiviError');
            isValid = false;
        } else {
            markValid(dateSuivi);
        }
        
        // Validation État général
        if (!etatGeneral.value.trim()) {
            showError(etatGeneral, "L'état général est obligatoire", 'etatGeneralError');
            isValid = false;
        } else if (etatGeneral.value.trim().length < 5) {
            showError(etatGeneral, "Minimum 5 caractères", 'etatGeneralError');
            isValid = false;
        } else {
            markValid(etatGeneral);
        }
        
        return isValid;
    }

    // Nettoyage en temps réel
    document.querySelectorAll('#suiviForm input, #suiviForm select, #suiviForm textarea').forEach(input => {
        input.addEventListener('input', () => removeError(input));
        input.addEventListener('change', () => removeError(input));
    });

    // ============================================
    // FONCTIONS CRUD
    // ============================================
    async function loadSuivis() {
        try {
            const response = await fetch('?controller=suivie&action=list');
            const suivis = await response.json();
            const tbody = document.getElementById('suivisTableBody');
            if (tbody) {
                tbody.innerHTML = suivis.map(s => `
                    <tr>
                        <td>${s.id_suivie}</td>
                        <td>${s.patient_nom || ''} ${s.patient_prenom || ''}</td>
                        <td>${s.medecin_nom || ''} ${s.medecin_prenom || ''}</td>
                        <td>${s.date_suivi || ''}</td>
                        <td>${s.poids ? s.poids + ' kg' : '-'}</td>
                        <td>${s.tension || '-'}</td>
                        <td>${s.etat_general ? s.etat_general.substring(0, 30) + '...' : '-'}</td>
                        <td>${s.prochain_rdv || '-'}</td>
                        <td>
                            <button class="icon-btn edit" onclick="editSuivi(${s.id_suivie})"><i class="fas fa-edit"></i></button>
                            <button class="icon-btn delete" onclick="deleteSuivi(${s.id_suivie})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
            document.getElementById('totalSuivis').textContent = suivis.length;
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    async function editSuivi(id) {
        try {
            const response = await fetch(`?controller=suivie&action=get&id=${id}`);
            const suivi = await response.json();
            if (suivi) {
                document.getElementById('suiviId').value = suivi.id_suivie;
                document.getElementById('patientId').value = suivi.id_patient;
                document.getElementById('medecinId').value = suivi.id_medecin;
                document.getElementById('consultationId').value = suivi.id_consultation;
                document.getElementById('dateSuivi').value = suivi.date_suivi;
                document.getElementById('poids').value = suivi.poids;
                document.getElementById('tension').value = suivi.tension;
                document.getElementById('etatGeneral').value = suivi.etat_general;
                document.getElementById('analyses').value = suivi.analyses_a_realiser;
                document.getElementById('regime').value = suivi.regime_alimentaire;
                document.getElementById('activite').value = suivi.activite_physique;
                document.getElementById('prochainRdv').value = suivi.prochain_rdv;
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier le suivi';
                new bootstrap.Modal(document.getElementById('suiviModal')).show();
            }
        } catch (error) {
            showNotification('Erreur lors du chargement', true);
        }
    }

    async function deleteSuivi(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce suivi ?')) {
            try {
                const response = await fetch(`?controller=suivie&action=delete&id=${id}`, {
                    method: 'DELETE'
                });
                const result = await response.json();
                if (result.success) {
                    showNotification(result.message);
                    loadSuivis();
                } else {
                    showNotification(result.message, true);
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression', true);
            }
        }
    }

    function showAddModal() {
        document.getElementById('suiviForm').reset();
        document.getElementById('suiviId').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Ajouter un suivi';
        document.querySelectorAll('#suiviForm .is-invalid, #suiviForm .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
        });
        document.getElementById('dateSuivi').value = new Date().toISOString().split('T')[0];
        new bootstrap.Modal(document.getElementById('suiviModal')).show();
    }

    // Soumission du formulaire
    document.getElementById('suiviForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validateSuiviForm()) {
            showNotification('Veuillez corriger les erreurs du formulaire', true);
            return;
        }
        
        const id = document.getElementById('suiviId').value;
        const data = {
            id_patient: document.getElementById('patientId').value,
            id_medecin: document.getElementById('medecinId').value,
            id_consultation: document.getElementById('consultationId').value || null,
            date_suivi: document.getElementById('dateSuivi').value,
            poids: document.getElementById('poids').value || null,
            tension: document.getElementById('tension').value || null,
            etat_general: document.getElementById('etatGeneral').value,
            analyses_a_realiser: document.getElementById('analyses').value || null,
            regime_alimentaire: document.getElementById('regime').value || null,
            activite_physique: document.getElementById('activite').value || null,
            prochain_rdv: document.getElementById('prochainRdv').value || null
        };
        
        if (id) {
            data.id_suivie = id;
        }
        
        const url = id ? '?controller=suivie&action=update' : '?controller=suivie&action=create';
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                showNotification(result.message);
                bootstrap.Modal.getInstance(document.getElementById('suiviModal')).hide();
                loadSuivis();
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
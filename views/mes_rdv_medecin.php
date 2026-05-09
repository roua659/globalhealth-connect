<?php
require_once __DIR__ . '/../config/session.php';
Session::start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendez-vous medecin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --medical-blue: #2b7be4;
            --medical-green: #2ecc71;
        }
        body {
            background: linear-gradient(180deg, #eef5ff 0%, #f8fbff 100%);
            color: #244056;
        }
        .page-shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 16px 56px;
        }
        .hero-card,
        .rdv-card,
        .empty-box {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(43, 123, 228, 0.10);
        }
        .hero-card {
            background: linear-gradient(135deg, #1f6fd8 0%, #38b27a 100%);
            color: #fff;
        }
        .badge-soft {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
        }
        .rdv-card,
        .empty-box {
            background: #fff;
        }
        .status-pill {
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 8px 12px;
            text-transform: capitalize;
        }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-confirme { background: #d1e7dd; color: #0f5132; }
        .status-termine { background: #dbeafe; color: #1d4ed8; }
        .status-annule { background: #f8d7da; color: #842029; }
        .empty-box {
            padding: 48px 24px;
            text-align: center;
        }
        .rdv-meta {
            color: #6c7f90;
            font-size: 0.95rem;
        }
        .btn-confirm-rdv {
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-green) 100%);
            color: #fff;
            font-weight: 700;
            min-height: 52px;
            width: 100%;
        }
        .btn-confirm-rdv:hover,
        .btn-confirm-rdv:focus {
            color: #fff;
        }
        .top-alert {
            display: none;
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="hero-card p-4 p-md-5 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <span class="badge-soft mb-3"><i class="fas fa-user-doctor"></i> Espace medecin</span>
                    <h1 class="h2 mb-2">Rendez-vous de mes patients</h1>
                    <p class="mb-0">Retrouvez les patients qui ont reserve avec vous et confirmez les rendez-vous en attente.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="frontoffice.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <div id="doctorAlert" class="alert top-alert"></div>
        <div id="doctorAppointmentsContainer"></div>
    </div>

    <script>
        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function showDoctorAlert(message, type = 'warning') {
            const alert = document.getElementById('doctorAlert');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alert.style.display = '';
        }

        function getCurrentUser() {
            const saved = localStorage.getItem('globalhealth_currentPatient');
            if (!saved) return null;
            try {
                return JSON.parse(saved);
            } catch (error) {
                return null;
            }
        }

        async function loadDoctorAppointments() {
            const currentUser = getCurrentUser();
            const container = document.getElementById('doctorAppointmentsContainer');

            if (!currentUser) {
                showDoctorAlert('Connectez-vous comme medecin pour voir cette page.');
                container.innerHTML = '';
                return;
            }

            if (!currentUser.id_medecin) {
                showDoctorAlert('Cette page est reservee aux medecins.');
                container.innerHTML = '';
                return;
            }

            try {
                const response = await fetch(`../index.php?page=rendezvous&action=getByMedecin&id_medecin=${encodeURIComponent(currentUser.id_medecin)}`);
                const rendezVous = await response.json();

                if (!Array.isArray(rendezVous) || rendezVous.length === 0) {
                    container.innerHTML = `
                        <div class="empty-box">
                            <i class="fas fa-calendar-xmark fa-3x mb-3 text-muted"></i>
                            <h2 class="h4">Aucun rendez-vous pour le moment</h2>
                            <p class="text-muted mb-0">Les rendez-vous reserves avec vous apparaitront ici.</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = `
                    <div class="row g-4">
                        ${rendezVous.map(rdv => `
                            <div class="col-12">
                                <div class="rdv-card p-4">
                                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                        <div>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                <h2 class="h5 mb-0">${escapeHtml(rdv.motif || 'Consultation medicale')}</h2>
                                                <span class="status-pill status-${escapeHtml(rdv.statut || 'en_attente')}">
                                                    ${escapeHtml((rdv.statut || 'en_attente').replace('_', ' '))}
                                                </span>
                                            </div>
                                            <div class="rdv-meta mb-2">
                                                <i class="fas fa-user me-2"></i>
                                                ${escapeHtml(rdv.patient_nom || 'Patient')}
                                                ${rdv.patient_email ? ` - ${escapeHtml(rdv.patient_email)}` : ''}
                                            </div>
                                            <div class="rdv-meta mb-2">
                                                <i class="fas fa-calendar-day me-2"></i>${escapeHtml(rdv.date_rdv || '')}
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-clock me-2"></i>${escapeHtml((rdv.heure_rdv || '').substring(0, 5))}
                                            </div>
                                            <div class="rdv-meta">
                                                <i class="fas fa-stethoscope me-2"></i>
                                                ${escapeHtml(rdv.type_consultation === 'video' ? 'Teleconsultation' : 'Consultation presentielle')}
                                            </div>
                                        </div>
                                        <div style="min-width: 220px;">
                                            ${(rdv.statut || '') === 'en_attente'
                                                ? `<button type="button" class="btn btn-confirm-rdv" onclick="confirmDoctorAppointment(${Number(rdv.id_rdv)}, ${Number(currentUser.id_medecin)})">
                                                        <i class="fas fa-check-circle me-2"></i>Confirmer le RDV
                                                   </button>`
                                                : `<div class="alert alert-light border mb-0">Ce rendez-vous est deja ${escapeHtml((rdv.statut || '').replace('_', ' '))}.</div>`
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } catch (error) {
                showDoctorAlert('Impossible de charger les rendez-vous du medecin.', 'danger');
                container.innerHTML = '';
            }
        }

        async function confirmDoctorAppointment(idRdv, idMedecin) {
            try {
                const response = await fetch(`../index.php?page=rendezvous&action=changeStatus&id=${encodeURIComponent(idRdv)}&id_medecin=${encodeURIComponent(idMedecin)}&statut=confirme`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Confirmation impossible');
                }

                showDoctorAlert('Rendez-vous confirme avec succes.', 'success');
                await loadDoctorAppointments();
            } catch (error) {
                showDoctorAlert(error.message || 'Erreur lors de la confirmation.', 'danger');
            }
        }

        document.addEventListener('DOMContentLoaded', loadDoctorAppointments);
    </script>
</body>
</html>

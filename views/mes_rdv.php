<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/RendezVousController.php';

Session::start();

$controller = new RendezVousController();
$patients = $controller->getPatients();
$medecins = $controller->getMedecins();
$defaultPatient = $patients[0] ?? null;
$patientId = (int)($defaultPatient['id_patient'] ?? 0);
$rendezVous = $patientId > 0 ? $controller->getByPatient($patientId) : [];
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes rendez-vous</title>
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
            max-width: 1180px;
            margin: 0 auto;
            padding: 40px 16px 56px;
        }
        .hero-card,
        .rdv-card {
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
        .rdv-card {
            background: #fff;
        }
        .rdv-meta {
            color: #6c7f90;
            font-size: 0.95rem;
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
            border: 2px dashed #cfe0f6;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.85);
            padding: 48px 24px;
            text-align: center;
        }
        .rdv-actions {
            min-width: 190px;
        }
        .btn-edit-rdv {
            width: 100%;
            min-height: 52px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #2b7be4 0%, #4da3ff 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(43, 123, 228, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }
        .btn-edit-rdv:hover,
        .btn-edit-rdv:focus {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(43, 123, 228, 0.28);
        }
        .btn-delete-rdv {
            min-height: 52px;
            border-radius: 16px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="hero-card p-4 p-md-5 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <span class="badge-soft mb-3"><i class="fas fa-calendar-check"></i> Espace patient</span>
                    <h1 class="h2 mb-2">Mes rendez-vous</h1>
                    <p class="mb-0">Consultez vos rendez-vous, modifiez ceux a venir et supprimez ceux dont vous n avez plus besoin.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="frontoffice.php#consultation" class="btn btn-light">
                        <i class="fas fa-plus me-2"></i>Nouveau RDV
                    </a>
                    <a href="frontoffice.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (!$defaultPatient): ?>
            <div class="alert alert-warning">Aucun patient de test n est disponible dans la base.</div>
        <?php elseif (empty($rendezVous)): ?>
            <div class="empty-box">
                <i class="fas fa-calendar-xmark fa-3x mb-3 text-muted"></i>
                <h2 class="h4">Aucun rendez-vous trouve</h2>
                <p class="text-muted mb-4">Commencez par reserver votre premier rendez-vous depuis le front office.</p>
                <a href="frontoffice.php#consultation" class="btn btn-primary">Prendre un rendez-vous</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($rendezVous as $rdv): ?>
                    <?php $isConfirmed = ($rdv['statut'] ?? '') === 'confirme'; ?>
                    <div class="col-12">
                        <div class="rdv-card p-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <h2 class="h5 mb-0"><?php echo htmlspecialchars($rdv['motif'] ?: 'Consultation medicale'); ?></h2>
                                        <span class="status-pill status-<?php echo htmlspecialchars($rdv['statut']); ?>">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $rdv['statut'])); ?>
                                        </span>
                                    </div>
                                    <div class="rdv-meta mb-2">
                                        <i class="fas fa-user-doctor me-2"></i>
                                        Dr. <?php echo htmlspecialchars($rdv['medecin_nom'] ?? 'Medecin'); ?>
                                        <?php if (!empty($rdv['specialite'])): ?>
                                            - <?php echo htmlspecialchars($rdv['specialite']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rdv-meta mb-2">
                                        <i class="fas fa-calendar-day me-2"></i><?php echo htmlspecialchars($rdv['date_rdv']); ?>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-clock me-2"></i><?php echo htmlspecialchars(substr($rdv['heure_rdv'], 0, 5)); ?>
                                    </div>
                                    <div class="rdv-meta">
                                        <i class="fas fa-stethoscope me-2"></i>
                                        <?php echo htmlspecialchars($rdv['type_consultation'] === 'video' ? 'Teleconsultation' : 'Consultation presentielle'); ?>
                                    </div>
                                </div>
                                <div class="rdv-actions d-flex flex-column gap-2">
                                    <?php if (!$isConfirmed): ?>
                                        <button
                                            type="button"
                                            class="btn btn-edit-rdv"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editRdvModal"
                                            data-rdv-id="<?php echo (int)$rdv['id_rdv']; ?>"
                                        >
                                            <i class="fas fa-pen-to-square me-2"></i>Modifier
                                        </button>
                                        <form method="POST" action="../index.php?page=rendezvous&action=delete" onsubmit="return confirm('Supprimer ce rendez-vous ?');">
                                            <input type="hidden" name="id_rdv" value="<?php echo (int)$rdv['id_rdv']; ?>">
                                            <input type="hidden" name="redirect_to" value="views/mes_rdv.php">
                                            <button type="submit" class="btn btn-outline-danger btn-delete-rdv w-100">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="editRdvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 24px;">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen-to-square me-2"></i>Modifier le rendez-vous</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form id="editRdvForm" method="POST" action="../index.php?page=rendezvous&action=update">
                    <div class="modal-body">
                        <input type="hidden" name="id_rdv" id="editRdvId">
                        <input type="hidden" name="id_patient" value="<?php echo $patientId; ?>">
                        <input type="hidden" name="statut" id="editRdvStatut">
                        <input type="hidden" name="redirect_to" value="views/mes_rdv.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Medecin</label>
                                <select class="form-select" name="id_medecin" id="editRdvMedecin" required>
                                    <option value="">Selectionnez un medecin</option>
                                    <?php foreach ($medecins as $medecin): ?>
                                        <option value="<?php echo (int)$medecin['id_medecin']; ?>">
                                            Dr. <?php echo htmlspecialchars(trim(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de consultation</label>
                                <select class="form-select" name="type_consultation" id="editRdvType" required>
                                    <option value="video">Visioconference</option>
                                    <option value="presentiel">Presentiel</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="date_rdv" id="editRdvDate" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Heure</label>
                                <input type="time" class="form-control" name="heure_rdv" id="editRdvHeure" max="17:00" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Motif</label>
                                <input type="text" class="form-control" name="motif" id="editRdvMotif" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editRdvModal = document.getElementById('editRdvModal');

        if (editRdvModal) {
            editRdvModal.addEventListener('show.bs.modal', async function(event) {
                const button = event.relatedTarget;
                const rdvId = button ? button.getAttribute('data-rdv-id') : '';
                if (!rdvId) return;

                try {
                    const response = await fetch(`../index.php?page=rendezvous&action=getOne&id=${rdvId}`);
                    const data = await response.json();

                    document.getElementById('editRdvId').value = data.id_rdv || '';
                    document.getElementById('editRdvStatut').value = data.statut || 'en_attente';
                    document.getElementById('editRdvMedecin').value = data.id_medecin || '';
                    document.getElementById('editRdvType').value = data.type_consultation || 'presentiel';
                    document.getElementById('editRdvDate').value = data.date_rdv || '';
                    document.getElementById('editRdvHeure').value = data.heure_rdv || '';
                    document.getElementById('editRdvMotif').value = data.motif || '';
                } catch (error) {
                    alert('Impossible de charger le rendez-vous.');
                }
            });
        }
    </script>
</body>
</html>

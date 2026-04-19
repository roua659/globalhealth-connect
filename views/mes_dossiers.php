<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/DossierMedicalController.php';
require_once __DIR__ . '/../controllers/RendezVousController.php';

Session::start();

$dossierController = new DossierMedicalController();
$rdvController = new RendezVousController();

$patients = $rdvController->getPatients();
$medecins = $rdvController->getMedecins();
$defaultPatient = $patients[0] ?? null;
$patientId = (int)($defaultPatient['id_patient'] ?? 0);
$rendezVousPatient = $patientId > 0 ? $rdvController->getByPatient($patientId) : [];
$patientDossiers = $patientId > 0 ? $dossierController->getByPatient($patientId) : [];
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes dossiers medicaux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --medical-blue: #2b7be4;
            --medical-green: #2ecc71;
            --medical-red: #dc3545;
        }
        body {
            background: linear-gradient(180deg, #eef8f2 0%, #eef5ff 100%);
            color: #244056;
        }
        .page-shell {
            max-width: 1220px;
            margin: 0 auto;
            padding: 40px 16px 56px;
        }
        .hero-card,
        .panel-card,
        .dossier-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(43, 123, 228, 0.10);
        }
        .hero-card {
            background: linear-gradient(135deg, #1f6fd8 0%, #38b27a 100%);
            color: #fff;
        }
        .panel-card,
        .dossier-card {
            background: #fff;
        }
        .badge-soft {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .form-label {
            font-weight: 600;
            color: #32536d;
        }
        .form-control,
        .form-select {
            min-height: 52px;
            border-radius: 16px;
        }
        textarea.form-control {
            min-height: 110px;
        }
        .upload-box {
            min-height: 150px;
            border: 2px dashed #8cb8f1;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            background: #f8fbff;
            cursor: pointer;
        }
        .dossier-meta {
            color: #63788b;
        }
        .action-stack {
            min-width: 180px;
        }
        .btn-edit-dossier {
            width: 100%;
            min-height: 50px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #2b7be4 0%, #4da3ff 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(43, 123, 228, 0.20);
        }
        .btn-edit-dossier:hover,
        .btn-edit-dossier:focus {
            color: #fff;
        }
        .btn-delete-dossier {
            min-height: 50px;
            border-radius: 16px;
            font-weight: 600;
        }
        .history-box {
            white-space: pre-wrap;
            font-family: inherit;
            background: #f6faff;
            border-radius: 16px;
            padding: 14px 16px;
        }
        .empty-box {
            border: 2px dashed #cfe0f6;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.85);
            padding: 48px 24px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="hero-card p-4 p-md-5 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <span class="badge-soft mb-3"><i class="fas fa-folder-medical"></i> Espace patient</span>
                    <h1 class="h2 mb-2">Mes dossiers medicaux</h1>
                    <p class="mb-0">Creez, modifiez et consultez vos informations medicales, ordonnances et historique.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="frontoffice.php" class="btn btn-light">
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

        <div class="panel-card p-4 p-md-5 mb-4">
            <div class="section-title"><i class="fas fa-notes-medical me-2"></i>Nouveau dossier medical</div>
            <?php if (!$defaultPatient): ?>
                <div class="alert alert-warning mb-0">Aucun patient de test n est disponible dans la base.</div>
            <?php else: ?>
                <form id="medicalRecordForm" method="POST" action="../index.php?page=dossiers&action=create" enctype="multipart/form-data">
                    <input type="hidden" name="id_dossier" id="medicalRecordId" value="">
                    <input type="hidden" name="id_patient" value="<?php echo $patientId; ?>">
                    <input type="hidden" name="redirect_to" value="views/mes_dossiers.php">
                    <input type="hidden" name="remove_ordonnance_file" id="removeOrdonnanceFile" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Patient</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars(trim(($defaultPatient['prenom'] ?? '') . ' ' . ($defaultPatient['nom'] ?? ''))); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Medecin traitant</label>
                            <select class="form-select" name="id_medecin" id="medicalDoctor" required>
                                <option value="">Selectionnez un medecin</option>
                                <?php foreach ($medecins as $medecin): ?>
                                    <option value="<?php echo (int)$medecin['id_medecin']; ?>">
                                        Dr. <?php echo htmlspecialchars(trim(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''))); ?>
                                        <?php if (!empty($medecin['specialite'])): ?>
                                            - <?php echo htmlspecialchars($medecin['specialite']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rendez-vous associe</label>
                            <select class="form-select" name="id_rdv" id="medicalRdv">
                                <option value="">Aucun</option>
                                <?php foreach ($rendezVousPatient as $rdv): ?>
                                    <option value="<?php echo (int)$rdv['id_rdv']; ?>">
                                        <?php echo htmlspecialchars(($rdv['date_rdv'] ?? '') . ' - Dr. ' . ($rdv['medecin_nom'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Symptomes</label>
                            <textarea class="form-control" name="symptomes" id="medicalSymptomes" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Diagnostic</label>
                            <textarea class="form-control" name="diagnostic" id="medicalDiagnostic" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Traitement</label>
                            <textarea class="form-control" name="traitement" id="medicalTraitement"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Ordonnance texte</label>
                            <textarea class="form-control" name="ordonnance_texte" id="medicalOrdonnanceTexte"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fichier ordonnance</label>
                            <div class="upload-box" onclick="document.getElementById('medicalOrdonnanceFile').click()">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--medical-blue);"></i>
                                    <div id="medicalOrdonnanceFileLabel">Cliquez pour telecharger un fichier</div>
                                    <div id="medicalOrdonnanceCurrent" class="small mt-2"></div>
                                </div>
                            </div>
                            <input type="file" id="medicalOrdonnanceFile" name="ordonnance_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes du medecin</label>
                            <textarea class="form-control" name="notes_medecin" id="medicalNotes"></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="medicalSubmitBtn">
                                <i class="fas fa-save me-2"></i>Enregistrer le dossier
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetMedicalForm()">
                                <i class="fas fa-rotate-left me-2"></i>Reinitialiser
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="panel-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title mb-0"><i class="fas fa-folder-open me-2"></i>Mes dossiers medicaux</div>
                <span class="badge bg-light text-primary"><?php echo count($patientDossiers); ?> dossier(s)</span>
            </div>
            <?php if (empty($patientDossiers)): ?>
                <div class="empty-box">
                    <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                    <h2 class="h4">Aucun dossier medical</h2>
                    <p class="text-muted mb-0">Creez votre premier dossier avec le formulaire ci-dessus.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($patientDossiers as $dossier): ?>
                        <div class="col-12">
                            <div class="dossier-card p-4">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <h2 class="h5 mb-0"><?php echo htmlspecialchars($dossier['diagnostic'] ?: 'Dossier medical'); ?></h2>
                                            <span class="badge bg-light text-primary"><?php echo htmlspecialchars($dossier['date_creation'] ?? ''); ?></span>
                                        </div>
                                        <div class="dossier-meta mb-2">
                                            <i class="fas fa-user-doctor me-2"></i>
                                            Dr. <?php echo htmlspecialchars($dossier['medecin_nom'] ?? ''); ?>
                                        </div>
                                        <?php if (!empty($dossier['date_rdv'])): ?>
                                            <div class="dossier-meta mb-2">
                                                <i class="fas fa-calendar-check me-2"></i>
                                                <?php echo htmlspecialchars($dossier['date_rdv'] . ' ' . substr($dossier['heure_rdv'] ?? '', 0, 5)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($dossier['symptomes'])): ?>
                                            <p class="mb-2"><strong>Symptomes :</strong> <?php echo nl2br(htmlspecialchars($dossier['symptomes'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($dossier['traitement'])): ?>
                                            <p class="mb-2"><strong>Traitement :</strong> <?php echo nl2br(htmlspecialchars($dossier['traitement'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($dossier['ordonnance_texte'])): ?>
                                            <p class="mb-2"><strong>Ordonnance :</strong> <?php echo nl2br(htmlspecialchars($dossier['ordonnance_texte'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($dossier['ordonnance_fichier'])): ?>
                                            <p class="mb-2">
                                                <a class="btn btn-sm btn-outline-primary" href="../uploads/<?php echo rawurlencode($dossier['ordonnance_fichier']); ?>" target="_blank">
                                                    <i class="fas fa-file-arrow-down me-2"></i>Voir le fichier
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($dossier['historique_modification'])): ?>
                                            <div class="history-box mt-3"><?php echo htmlspecialchars($dossier['historique_modification']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="action-stack d-flex flex-column gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-edit-dossier"
                                            onclick='editMedicalRecord(<?php echo json_encode([
                                                "id_dossier" => (int)$dossier["id_dossier"],
                                                "id_medecin" => $dossier["id_medecin"] ?? "",
                                                "id_rdv" => $dossier["id_rdv"] ?? "",
                                                "symptomes" => $dossier["symptomes"] ?? "",
                                                "diagnostic" => $dossier["diagnostic"] ?? "",
                                                "traitement" => $dossier["traitement"] ?? "",
                                                "ordonnance_texte" => $dossier["ordonnance_texte"] ?? "",
                                                "ordonnance_fichier" => $dossier["ordonnance_fichier"] ?? "",
                                                "notes_medecin" => $dossier["notes_medecin"] ?? ""
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'
                                        >
                                            <i class="fas fa-pen-to-square me-2"></i>Modifier
                                        </button>
                                        <form method="POST" action="../index.php?page=dossiers&action=delete" onsubmit="return confirm('Supprimer ce dossier medical ?');">
                                            <input type="hidden" name="id_dossier" value="<?php echo (int)$dossier['id_dossier']; ?>">
                                            <input type="hidden" name="redirect_to" value="views/mes_dossiers.php">
                                            <button type="submit" class="btn btn-outline-danger btn-delete-dossier w-100">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const medicalRecordForm = document.getElementById('medicalRecordForm');
        const medicalOrdonnanceFile = document.getElementById('medicalOrdonnanceFile');
        const medicalOrdonnanceFileLabel = document.getElementById('medicalOrdonnanceFileLabel');
        const medicalOrdonnanceCurrent = document.getElementById('medicalOrdonnanceCurrent');

        if (medicalOrdonnanceFile) {
            medicalOrdonnanceFile.addEventListener('change', function() {
                const file = this.files && this.files[0] ? this.files[0] : null;
                medicalOrdonnanceFileLabel.textContent = file ? file.name : 'Cliquez pour telecharger un fichier';
            });
        }

        function resetMedicalForm() {
            if (!medicalRecordForm) return;

            medicalRecordForm.reset();
            medicalRecordForm.action = '../index.php?page=dossiers&action=create';
            document.getElementById('medicalRecordId').value = '';
            document.getElementById('removeOrdonnanceFile').value = '0';
            document.getElementById('medicalSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer le dossier';
            if (medicalOrdonnanceFileLabel) medicalOrdonnanceFileLabel.textContent = 'Cliquez pour telecharger un fichier';
            if (medicalOrdonnanceCurrent) medicalOrdonnanceCurrent.innerHTML = '';
        }

        function editMedicalRecord(dossier) {
            if (!medicalRecordForm || !dossier) return;

            medicalRecordForm.action = '../index.php?page=dossiers&action=update';
            document.getElementById('medicalRecordId').value = dossier.id_dossier || '';
            document.getElementById('medicalDoctor').value = dossier.id_medecin || '';
            document.getElementById('medicalRdv').value = dossier.id_rdv || '';
            document.getElementById('medicalSymptomes').value = dossier.symptomes || '';
            document.getElementById('medicalDiagnostic').value = dossier.diagnostic || '';
            document.getElementById('medicalTraitement').value = dossier.traitement || '';
            document.getElementById('medicalOrdonnanceTexte').value = dossier.ordonnance_texte || '';
            document.getElementById('medicalNotes').value = dossier.notes_medecin || '';
            document.getElementById('removeOrdonnanceFile').value = '0';
            document.getElementById('medicalSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Mettre a jour le dossier';

            if (medicalOrdonnanceFileLabel) medicalOrdonnanceFileLabel.textContent = 'Cliquez pour remplacer le fichier';
            if (medicalOrdonnanceCurrent) {
                if (dossier.ordonnance_fichier) {
                    medicalOrdonnanceCurrent.innerHTML = `
                        <a href="../uploads/${encodeURIComponent(dossier.ordonnance_fichier)}" target="_blank">Fichier actuel</a>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" onclick="removeCurrentOrdonnanceFile()">Retirer</button>
                    `;
                } else {
                    medicalOrdonnanceCurrent.innerHTML = '';
                }
            }

            window.location.hash = 'top';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function removeCurrentOrdonnanceFile() {
            document.getElementById('removeOrdonnanceFile').value = '1';
            if (medicalOrdonnanceCurrent) medicalOrdonnanceCurrent.innerHTML = '<span class="text-danger">Le fichier sera supprime apres enregistrement.</span>';
        }
    </script>
</body>
</html>

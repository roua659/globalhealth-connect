<!-- Modal Ajouter/Modifier Dossier Medical -->
<div class="modal fade" id="dossierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-folder-medical me-2"></i>
                    <span id="dossierModalTitle">Dossier medical</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="dossierForm" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" id="dossierAction" value="create">
                <input type="hidden" name="id_dossier" id="dossierId" value="">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-medical">Patient *</label>
                            <select class="form-control form-control-medical" name="id_patient" id="dossierPatient" required>
                                <option value="">Selectionnez un patient</option>
                                <?php if (isset($patients) && $patients): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id_patient']; ?>">
                                            <?php echo htmlspecialchars($patient['nom']); ?> (ID: <?php echo $patient['id_patient']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Medecin traitant *</label>
                            <select class="form-control form-control-medical" name="id_medecin" id="dossierMedecin" required>
                                <option value="">Selectionnez un medecin</option>
                                <?php if (isset($medecins) && $medecins): ?>
                                    <?php foreach ($medecins as $medecin): ?>
                                        <option value="<?php echo $medecin['id_medecin']; ?>">
                                            Dr. <?php echo htmlspecialchars($medecin['nom']); ?> - <?php echo htmlspecialchars($medecin['specialite']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Rendez-vous associe (optionnel)</label>
                            <select class="form-control form-control-medical" name="id_rdv" id="dossierRdv">
                                <option value="">Aucun</option>
                                <?php if (isset($rendez_vous) && $rendez_vous): ?>
                                    <?php foreach ($rendez_vous as $rdv): ?>
                                        <option value="<?php echo $rdv['id_rdv']; ?>">
                                            <?php echo $rdv['date_rdv']; ?> - Dr. <?php echo $rdv['medecin_nom']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Date de creation</label>
                            <input type="text" class="form-control form-control-medical" value="<?php echo date('d/m/Y H:i'); ?>" disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Symptomes *</label>
                            <textarea class="form-control form-control-medical" name="symptomes" id="dossierSymptomes" rows="3" placeholder="Decrivez les symptomes du patient..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Diagnostic *</label>
                            <textarea class="form-control form-control-medical" name="diagnostic" id="dossierDiagnostic" rows="3" placeholder="Diagnostic medical..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Traitement</label>
                            <textarea class="form-control form-control-medical" name="traitement" id="dossierTraitement" rows="3" placeholder="Traitement prescrit..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Ordonnance</label>
                            <textarea class="form-control form-control-medical" name="ordonnance_texte" id="dossierOrdonnance" rows="4" placeholder="Medicaments, posologie, duree..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Fichier ordonnance</label>
                            <div class="file-upload-area" onclick="document.getElementById('dossierOrdonnanceFile').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--medical-blue);"></i>
                                <p class="mb-0"><small>Cliquez pour telecharger un fichier (PDF, JPG, PNG, DOC)</small></p>
                                <input type="file" id="dossierOrdonnanceFile" name="ordonnance_file" style="display: none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <div id="dossierFileName" class="mt-2 small text-muted"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Notes du medecin</label>
                            <textarea class="form-control form-control-medical" name="notes_medecin" id="dossierNotes" rows="3" placeholder="Notes complementaires..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-medical">
                        <i class="fas fa-save me-2"></i>Enregistrer le dossier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('dossierOrdonnanceFile')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const display = document.getElementById('dossierFileName');
    if (display) {
        display.textContent = fileName ? `Fichier selectionne : ${fileName}` : '';
    }
});

function setDossierFieldError(field, message) {
    if (!field) return;
    field.classList.add('is-invalid');
    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearDossierFieldError(field) {
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.remove();
}

document.getElementById('dossierForm')?.addEventListener('submit', function(e) {
    const patientField = document.getElementById('dossierPatient');
    const medecinField = document.getElementById('dossierMedecin');
    const symptomesField = document.getElementById('dossierSymptomes');
    const diagnosticField = document.getElementById('dossierDiagnostic');
    let isValid = true;

    [patientField, medecinField, symptomesField, diagnosticField].forEach(clearDossierFieldError);

    if (!patientField.value) {
        setDossierFieldError(patientField, 'Veuillez selectionner un patient');
        isValid = false;
    }

    if (!medecinField.value) {
        setDossierFieldError(medecinField, 'Veuillez selectionner un medecin');
        isValid = false;
    }

    if (!symptomesField.value.trim()) {
        setDossierFieldError(symptomesField, 'Les symptomes sont obligatoires');
        isValid = false;
    }

    if (!diagnosticField.value.trim()) {
        setDossierFieldError(diagnosticField, 'Le diagnostic est obligatoire');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        if (typeof showError === 'function') {
            showError('Veuillez corriger les champs du dossier medical');
        }
        return false;
    }
});
</script>

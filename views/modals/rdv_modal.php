<!-- Modal Ajouter/Modifier Rendez-vous -->
<div class="modal fade" id="rdvModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>
                    <span id="rdvModalTitle">Prendre rendez-vous</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rdvForm" method="POST" action="index.php?page=rendezvous" novalidate>
                <input type="hidden" name="action" id="rdvAction" value="create">
                <input type="hidden" name="id_rdv" id="rdvId" value="">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-medical">Patient *</label>
                            <select class="form-control form-control-medical" name="id_patient" id="rdvPatient" required>
                                <option value="">-- Selectionnez un patient --</option>
                                <?php if (isset($patients) && $patients): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id_patient']; ?>">
                                            <?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?>
                                            (<?php echo htmlspecialchars($patient['email']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Aucun patient trouve</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Medecin *</label>
                            <select class="form-control form-control-medical" name="id_medecin" id="rdvMedecin" required>
                                <option value="">-- Selectionnez un medecin --</option>
                                <?php if (isset($medecins) && $medecins): ?>
                                    <?php foreach ($medecins as $medecin): ?>
                                        <option value="<?php echo $medecin['id_medecin']; ?>">
                                            Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']); ?>
                                            - <?php echo htmlspecialchars($medecin['specialite']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Aucun medecin trouve</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Date *</label>
                            <input type="date" class="form-control form-control-medical" name="date_rdv" id="rdvDate" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Heure *</label>
                            <input type="time" class="form-control form-control-medical" name="heure_rdv" id="rdvHeure" required max="17:00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Type de consultation *</label>
                            <select class="form-control form-control-medical" name="type_consultation" id="rdvType" required>
                                <option value="">-- Selectionnez --</option>
                                <option value="presentiel">Presentiel</option>
                                <option value="video">Visioconference</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-medical">Statut</label>
                            <select class="form-control form-control-medical" name="statut" id="rdvStatut">
                                <option value="en_attente">En attente</option>
                                <option value="confirme">Confirme</option>
                                <option value="termine">Termine</option>
                                <option value="annule">Annule</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Motif</label>
                            <input type="text" class="form-control form-control-medical" name="motif" id="rdvMotif" placeholder="Motif de la consultation">
                        </div>

                        <div class="col-12">
                            <label class="form-label-medical">Symptomes</label>
                            <textarea class="form-control form-control-medical" name="symptomes" id="rdvSymptomes" rows="3" placeholder="Decrivez les symptomes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-medical">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRdvModal(id = null) {
    const modal = new bootstrap.Modal(document.getElementById('rdvModal'));
    const form = document.getElementById('rdvForm');

    if (id) {
        document.getElementById('rdvModalTitle').textContent = 'Modifier le rendez-vous';
        document.getElementById('rdvAction').value = 'update';
        document.getElementById('rdvId').value = id;

        fetch(`index.php?page=rendezvous&action=getOne&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('rdvPatient').value = data.id_patient;
                    document.getElementById('rdvMedecin').value = data.id_medecin;
                    document.getElementById('rdvDate').value = data.date_rdv;
                    document.getElementById('rdvHeure').value = data.heure_rdv;
                    document.getElementById('rdvType').value = data.type_consultation;
                    document.getElementById('rdvStatut').value = data.statut;
                    document.getElementById('rdvMotif').value = data.motif || '';
                    document.getElementById('rdvSymptomes').value = data.symptomes || '';
                }
            })
            .catch(error => console.error('Erreur:', error));
    } else {
        document.getElementById('rdvModalTitle').textContent = 'Nouveau rendez-vous';
        document.getElementById('rdvAction').value = 'create';
        document.getElementById('rdvId').value = '';
        form.reset();
    }

    modal.show();
}

function editRdv(id) {
    openRdvModal(id);
}

function deleteRdv(id) {
    if (confirm('Etes-vous sur de vouloir supprimer ce rendez-vous ?')) {
        window.location.href = `index.php?page=rendezvous&action=delete&id=${id}`;
    }
}

function setRdvFieldError(field, message) {
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

function clearRdvFieldError(field) {
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.remove();
}

function startsWithUppercase(value) {
    const trimmed = value.trim();
    if (!trimmed) return true;
    return /^[A-ZÀÂÄÇÉÈÊËÎÏÔÖÙÛÜŸ]/.test(trimmed);
}

document.getElementById('rdvHeure')?.addEventListener('change', function() {
    clearRdvFieldError(this);
    if (this.value && this.value > '17:00') {
        setRdvFieldError(this, 'L heure du rendez-vous ne doit pas depasser 17:00');
    }
});

document.getElementById('rdvForm')?.addEventListener('submit', function(e) {
    const patientField = document.getElementById('rdvPatient');
    const medecinField = document.getElementById('rdvMedecin');
    const dateField = document.getElementById('rdvDate');
    const heureField = document.getElementById('rdvHeure');
    const typeField = document.getElementById('rdvType');
    const motifField = document.getElementById('rdvMotif');
    const symptomesField = document.getElementById('rdvSymptomes');
    let isValid = true;

    [patientField, medecinField, dateField, heureField, typeField, motifField, symptomesField].forEach(clearRdvFieldError);

    if (!patientField.value) {
        setRdvFieldError(patientField, 'Veuillez selectionner un patient');
        isValid = false;
    }

    if (!medecinField.value) {
        setRdvFieldError(medecinField, 'Veuillez selectionner un medecin');
        isValid = false;
    }

    if (!dateField.value) {
        setRdvFieldError(dateField, 'Veuillez selectionner une date');
        isValid = false;
    }

    if (!heureField.value) {
        setRdvFieldError(heureField, 'Veuillez selectionner une heure');
        isValid = false;
    } else if (heureField.value > '17:00') {
        setRdvFieldError(heureField, 'L heure du rendez-vous ne doit pas depasser 17:00');
        isValid = false;
    }

    if (!typeField.value) {
        setRdvFieldError(typeField, 'Veuillez choisir un type de consultation');
        isValid = false;
    }

    if (dateField.value && heureField.value) {
        const selectedDate = new Date(dateField.value + ' ' + heureField.value);
        if (selectedDate < new Date()) {
            setRdvFieldError(dateField, 'La date du rendez-vous ne peut pas etre dans le passe');
            isValid = false;
        }
    }

    if (motifField.value.trim() && !startsWithUppercase(motifField.value)) {
        setRdvFieldError(motifField, 'Le motif doit commencer par une lettre majuscule');
        isValid = false;
    }

    if (symptomesField.value.trim() && !startsWithUppercase(symptomesField.value)) {
        setRdvFieldError(symptomesField, 'Les symptomes doivent commencer par une lettre majuscule');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        if (typeof showError === 'function') {
            showError('Veuillez corriger les champs du formulaire rendez-vous');
        }
        return false;
    }
});
</script>

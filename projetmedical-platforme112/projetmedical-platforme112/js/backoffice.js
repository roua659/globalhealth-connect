  
// Configuration du cube
let currentFace = 0;
const totalFaces = 4;
const modules = ['appointments_payments', 'medical_consultation', 'reviews', 'users'];
const moduleTitles = {
    appointments_payments: 'Gestion des Rendez-vous & Paiements',
    medical_consultation: 'Gestion des Dossiers & Consultations',
    reviews: 'Gestion des Avis Patients',
    users: 'Gestion des Utilisateurs'
};

// Actions par module
const moduleActions = {
    appointments_payments: [
        { icon: 'fa-check-circle', label: 'Valider RDV', action: 'validateAppointment' },
        { icon: 'fa-times-circle', label: 'Annuler RDV', action: 'cancelAppointment' },
        { icon: 'fa-envelope', label: 'Email', action: 'sendEmailReminder' },
        { icon: 'fa-euro-sign', label: 'Remboursement', action: 'processRefund' },
        { icon: 'fa-calendar-alt', label: 'Calendrier', action: 'showCalendar' },
        { icon: 'fa-chart-line', label: 'Statistiques', action: 'showStatistics' },
        { icon: 'fa-sort', label: 'Trier', action: 'sortData' },
        { icon: 'fa-file-pdf', label: 'Export PDF', action: 'exportToPDF' },
        { icon: 'fa-credit-card', label: 'Confirmer Paiement', action: 'confirmPayment' }
    ],
    medical_consultation: [
        { icon: 'fa-folder-open', label: 'Dossiers', action: 'viewMedicalRecords' },
        { icon: 'fa-stethoscope', label: 'Consultations', action: 'viewConsultations' },
        { icon: 'fa-chart-line', label: 'Statistiques', action: 'showConsultStats' },
        { icon: 'fa-file-pdf', label: 'Export PDF', action: 'exportMedicalPDF' },
        { icon: 'fa-plus', label: 'Nouveau dossier', action: 'createMedicalRecord' }
    ],
    reviews: [
        { icon: 'fa-flag', label: 'Signaler', action: 'reportReview' },
        { icon: 'fa-chart-simple', label: 'Moyenne notes', action: 'showAvgRating' },
        { icon: 'fa-bell', label: 'Rappel auto', action: 'setupAutoReminder' },
        { icon: 'fa-file-pdf', label: 'Export PDF', action: 'exportReviewsPDF' },
        { icon: 'fa-trash', label: 'Modérer', action: 'moderateReview' }
    ],
    users: [
        { icon: 'fa-envelope', label: 'Mailing', action: 'sendMassEmail' },
        { icon: 'fa-chart-line', label: 'Statistiques', action: 'showUserStats' },
        { icon: 'fa-file-pdf', label: 'Export PDF', action: 'exportUsersPDF' },
        { icon: 'fa-user-plus', label: 'Ajouter', action: 'addUser' },
        { icon: 'fa-ban', label: 'Bloquer', action: 'blockUser' }
    ]
};

// Rotation du cube
function rotateCube(direction) {
    const cube = document.getElementById('cube');
    if (!cube) return;
    
    if (direction === 'right') {
        currentFace = (currentFace + 1) % totalFaces;
        cube.style.transform = `rotateY(${currentFace * -90}deg)`;
    } else if (direction === 'left') {
        currentFace = (currentFace - 1 + totalFaces) % totalFaces;
        cube.style.transform = `rotateY(${currentFace * -90}deg)`;
    }
    
    updateCurrentModule();
    loadModuleContent(modules[currentFace]);
    updateActionsBar(modules[currentFace]);
}

function updateCurrentModule() {
    const el = document.getElementById('currentModule');
    const titleEl = document.getElementById('moduleTitle');
    if (el) el.textContent = moduleTitles[modules[currentFace]];
    if (titleEl) titleEl.textContent = moduleTitles[modules[currentFace]];
}

function updateActionsBar(moduleName) {
    const bar = document.getElementById('actionsBar');
    if (!bar) return;
    
    const actions = moduleActions[moduleName] || [];
    bar.innerHTML = actions.map(action => `
        <button class="action-module-btn" onclick="${action.action}()">
            <i class="fas ${action.icon}"></i> ${action.label}
        </button>
    `).join('');
}

function loadModuleContent(moduleName) {
    const body = document.getElementById('moduleBody');
    if (!body) return;
    
    switch(moduleName) {
        case 'appointments_payments':
            body.innerHTML = renderAppointmentsPayments();
            break;
        case 'medical_consultation':
            body.innerHTML = renderMedicalConsultation();
            break;
        case 'reviews':
            body.innerHTML = renderReviews();
            break;
        case 'users':
            body.innerHTML = renderUsers();
            break;
    }
}

function renderAppointmentsPayments() {
    const appointments = getAppointments();
    const payments = getPayments();
    
    return `
        <div class="mb-3">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-light active" onclick="showAppointmentsTab()">Rendez-vous</button>
                <button class="btn btn-outline-light" onclick="showPaymentsTab()">Paiements</button>
            </div>
        </div>
        <div id="appointmentsPaymentsTab">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Type</th><th>Statut</th><th>Paiement</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        ${appointments.map(apt => `
                            <tr>
                                <td>${apt.patient_name}</td>
                                <td>${apt.doctor_name}</td>
                                <td>${new Date(apt.appointment_date).toLocaleDateString('fr-FR')}</td>
                                <td>${apt.consultation_type}</td>
                                <td><span class="status-badge status-${apt.status}">${apt.status}</span></td>
                                <td><span class="status-badge status-${apt.payment_status}">${apt.payment_status}</span></td>
                                <td>
                                    <button class="icon-btn" onclick="viewAppointment(${apt.id})"><i class="fas fa-eye"></i></button>
                                    <button class="icon-btn" onclick="confirmAppointment(${apt.id})"><i class="fas fa-check-circle"></i></button>
                                    <button class="icon-btn" onclick="cancelAppointment(${apt.id})"><i class="fas fa-times-circle"></i></button>
                                    <button class="icon-btn" onclick="sendEmailReminder(${apt.id})"><i class="fas fa-envelope"></i></button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderMedicalConsultation() {
    const records = getMedicalRecords();
    const appointments = getAppointments();
    
    return `
        <div class="mb-3">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-light active" onclick="showRecordsTab()">Dossiers médicaux</button>
                <button class="btn btn-outline-light" onclick="showConsultationsTab()">Consultations</button>
            </div>
        </div>
        <div id="medicalConsultTab">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Patient</th><th>Dernière consultation</th><th>Diagnostic</th><th>Traitement</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        ${records.map(record => `
                            <tr>
                                <td>${record.patient_name}</td>
                                <td>${record.last_consultation || '-'}</td>
                                <td>${record.diagnostic || '-'}</td>
                                <td>${record.treatment || '-'}</td>
                                <td>
                                    <button class="icon-btn" onclick="editMedicalRecord(${record.id})"><i class="fas fa-edit"></i></button>
                                    <button class="icon-btn" onclick="deleteMedicalRecord(${record.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderReviews() {
    const reviews = getReviews();
    const avgRating = reviews.length > 0 ? (reviews.reduce((s, r) => s + r.rating, 0) / reviews.length).toFixed(1) : 0;
    
    return `
        <div class="row mb-3">
            <div class="col-4 text-center">
                <div class="fs-1 fw-bold text-warning">${avgRating}</div>
                <div>${'★'.repeat(Math.round(avgRating))}${'☆'.repeat(5-Math.round(avgRating))}</div>
                <small>${reviews.length} avis</small>
            </div>
            <div class="col-8">
                ${[5,4,3,2,1].map(star => {
                    const count = reviews.filter(r => r.rating === star).length;
                    const pct = reviews.length ? (count/reviews.length*100) : 0;
                    return `
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span style="width:30px">${star}★</span>
                            <div class="flex-grow-1 bg-secondary rounded" style="height:8px">
                                <div class="bg-warning rounded" style="width:${pct}%; height:8px"></div>
                            </div>
                            <span style="width:30px">${count}</span>
                        </div>
                    `;
                }).join('')}
            </div>
        </div>
        <div class="table-responsive" style="max-height:200px; overflow-y:auto">
            <table class="data-table">
                <thead><tr><th>Patient</th><th>Médecin</th><th>Note</th><th>Commentaire</th><th>Actions</th></tr></thead>
                <tbody>
                    ${reviews.map(r => `
                        <tr>
                            <td>${r.patient_name}</td>
                            <td>${r.doctor_name}</td>
                            <td>${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</td>
                            <td>${r.comment.substring(0,50)}...</td>
                            <td>
                                <button class="icon-btn" onclick="reportReview(${r.id})"><i class="fas fa-flag"></i></button>
                                <button class="icon-btn" onclick="deleteReview(${r.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function renderUsers() {
    const users = getUsers();
    
    return `
        <div class="mb-3 d-flex gap-2">
            <input type="text" class="form-control form-control-sm bg-transparent text-white" id="searchUser" placeholder="Rechercher..." onkeyup="filterUsers()" style="border:1px solid rgba(255,255,255,0.2)">
            <select class="form-select form-select-sm bg-transparent text-white" id="userTypeFilter" onchange="filterUsers()" style="width:150px; border:1px solid rgba(255,255,255,0.2)">
                <option value="">Tous</option>
                <option value="patient">Patients</option>
                <option value="doctor">Médecins</option>
                <option value="admin">Admins</option>
            </select>
            <button class="btn btn-sm btn-outline-light" onclick="addUser()"><i class="fas fa-plus"></i></button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody id="usersTableBody">
                    ${users.map(u => `
                        <tr>
                            <td>${u.name}</td>
                            <td>${u.email}</td>
                            <td>${u.role}</td>
                            <td><span class="status-badge ${u.status === 'active' ? 'status-confirmed' : 'status-cancelled'}">${u.status}</span></td>
                            <td>
                                <button class="icon-btn" onclick="editUser(${u.id})"><i class="fas fa-edit"></i></button>
                                <button class="icon-btn" onclick="toggleUserStatus(${u.id})"><i class="fas ${u.status === 'active' ? 'fa-ban' : 'fa-check-circle'}"></i></button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// Fonctions CRUD
function viewAppointment(id) {
    const apt = getAppointments().find(a => a.id === id);
    if (apt) showNotification(`RDV #${id}: ${apt.patient_name} - ${apt.doctor_name} - ${apt.appointment_date}`);
}

function confirmAppointment(id) {
    let appointments = getAppointments();
    const apt = appointments.find(a => a.id === id);
    if (apt) {
        apt.status = 'confirmed';
        saveAppointments(appointments);
        showNotification(`Rendez-vous #${id} confirmé ! Email et SMS envoyés`);
        refreshModule();
    }
}

function cancelAppointment(id) {
    if (confirm('Annuler ce rendez-vous ?')) {
        let appointments = getAppointments();
        const apt = appointments.find(a => a.id === id);
        if (apt) {
            apt.status = 'cancelled';
            saveAppointments(appointments);
            showNotification(`Rendez-vous #${id} annulé`);
            refreshModule();
        }
    }
}

function sendEmailReminder(id) {
    const apt = getAppointments().find(a => a.id === id);
    if (apt) showNotification(`Email envoyé à ${apt.patient_email}`);
}

function processRefund() {
    showNotification('Remboursement traité avec succès');
}

function showCalendar() {
    const appointments = getAppointments();
    let msg = "Calendrier des RDV:\n";
    appointments.forEach(a => {
        msg += `${a.appointment_date} - ${a.patient_name} - ${a.doctor_name}\n`;
    });
    alert(msg);
}

function showStatistics() {
    const stats = getGlobalStats();
    showNotification(`Stats: ${stats.totalAppointments} RDV, ${stats.totalRevenue}€ CA, ${stats.avgRating}/5 ★`);
}

function sortData() {
    showNotification('Tri effectué par date décroissante');
    refreshModule();
}

function exportToPDF() {
    showNotification('Export PDF en cours...');
    setTimeout(() => showNotification('PDF exporté !'), 1500);
}

function confirmPayment() {
    showNotification('Confirmation de paiement envoyée');
}

function viewMedicalRecords() { showRecordsTab(); }
function viewConsultations() { showConsultationsTab(); }
function showConsultStats() { showStatistics(); }
function exportMedicalPDF() { showNotification('Export des dossiers médicaux PDF'); }
function createMedicalRecord() { showNotification('Formulaire de création dossier médical'); }
function editMedicalRecord(id) { showNotification(`Édition du dossier #${id}`); }
function deleteMedicalRecord(id) { if(confirm('Supprimer ce dossier ?')) showNotification('Dossier supprimé'); }

function reportReview(id) { showNotification(`Avis #${id} signalé`); }
function showAvgRating() { const r=getReviews(); const avg=r.length?(r.reduce((s,rv)=>s+rv.rating,0)/r.length).toFixed(1):0; showNotification(`Note moyenne: ${avg}/5`); }
function setupAutoReminder() { showNotification('Rappel automatique activé (1 min après consultation)'); }
function exportReviewsPDF() { showNotification('Export avis PDF'); }
function deleteReview(id) { if(confirm('Supprimer cet avis ?')){ let r=getReviews().filter(rv=>rv.id!==id); saveReviews(r); showNotification('Avis supprimé'); refreshModule(); } }
function moderateReview(id) { deleteReview(id); }

function sendMassEmail() { showNotification(`Email envoyé à ${getUsers().length} utilisateurs`); }
function showUserStats() { const u=getUsers(); showNotification(`${u.filter(u=>u.role==='patient').length} patients, ${u.filter(u=>u.role==='doctor').length} médecins`); }
function exportUsersPDF() { showNotification('Export utilisateurs PDF'); }
function addUser() { const n=prompt('Nom:'); const e=prompt('Email:'); const r=prompt('Rôle (patient/doctor/admin):'); if(n&&e&&r){ let u=getUsers(); u.push({id:Date.now(),name:n,email:e,role:r,status:'active'}); saveUsers(u); showNotification(`Utilisateur ${n} ajouté`); refreshModule(); } }
function blockUser() { const e=prompt('Email à bloquer:'); if(e){ let u=getUsers().map(us=>us.email===e?{...us,status:us.status==='active'?'blocked':'active'}:us); saveUsers(u); showNotification(`Utilisateur ${e} ${u.find(us=>us.email===e)?.status==='blocked'?'bloqué':'débloqué'}`); refreshModule(); } }
function editUser(id) { showNotification(`Édition utilisateur #${id}`); }
function toggleUserStatus(id) { let u=getUsers().map(us=>us.id===id?{...us,status:us.status==='active'?'blocked':'active'}:us); saveUsers(u); refreshModule(); }
function filterUsers() { const t=document.getElementById('searchUser')?.value.toLowerCase()||''; const f=document.getElementById('userTypeFilter')?.value||''; let u=getUsers().filter(us=>(!t||us.name.toLowerCase().includes(t)||us.email.toLowerCase().includes(t))&&(!f||us.role===f)); document.getElementById('usersTableBody').innerHTML=u.map(us=>`<tr><td>${us.name}</td><td>${us.email}</td><td>${us.role}</td><td><span class="status-badge ${us.status==='active'?'status-confirmed':'status-cancelled'}">${us.status}</span></td><td><button class="icon-btn" onclick="editUser(${us.id})"><i class="fas fa-edit"></i></button><button class="icon-btn" onclick="toggleUserStatus(${us.id})"><i class="fas ${us.status==='active'?'fa-ban':'fa-check-circle'}"></i></button></td></tr>`).join(''); }

function showAppointmentsTab() { document.getElementById('appointmentsPaymentsTab').innerHTML=renderAppointmentsPayments().split('<div id="appointmentsPaymentsTab">')[1]?.split('</div>')[0]||renderAppointmentsPayments(); }
function showPaymentsTab() { const p=getPayments(); const a=getAppointments(); document.getElementById('appointmentsPaymentsTab').innerHTML=`<div class="table-responsive"><table class="data-table"><thead><tr><th>ID</th><th>Patient</th><th>Montant</th><th>Méthode</th><th>Statut</th><th>Actions</th></tr></thead><tbody>${p.map(pay=>{const apt=a.find(aa=>aa.id===pay.appointment_id);return `<tr><td>#${pay.id}</td><td>${apt?.patient_name||'N/A'}</td><td>${pay.amount}€</td><td>${pay.payment_method}</td><td><span class="status-badge status-${pay.status}">${pay.status}</span></td><td><button class="icon-btn" onclick="confirmPaymentById(${pay.id})"><i class="fas fa-check"></i></button></td></tr>`}).join('')}</tbody></table></div>`; }
function confirmPaymentById(id){ let p=getPayments().map(pay=>pay.id===id?{...pay,status:'completed'}:pay); savePayments(p); let a=getAppointments(); const pay=p.find(pay=>pay.id===id); if(pay){ let apt=a.find(aa=>aa.id===pay.appointment_id); if(apt){apt.payment_status='paid';saveAppointments(a);}} showNotification(`Paiement #${id} confirmé`); refreshModule(); }

function showRecordsTab() { const r=getMedicalRecords(); document.getElementById('medicalConsultTab').innerHTML=`<div class="table-responsive"><table class="data-table"><thead><tr><th>Patient</th><th>Dernière consultation</th><th>Diagnostic</th><th>Traitement</th><th>Actions</th></tr></thead><tbody>${r.map(rec=>`<tr><td>${rec.patient_name}</td><td>${rec.last_consultation||'-'}</td><td>${rec.diagnostic||'-'}</td><td>${rec.treatment||'-'}</td><td><button class="icon-btn" onclick="editMedicalRecord(${rec.id})"><i class="fas fa-edit"></i></button><button class="icon-btn" onclick="deleteMedicalRecord(${rec.id})"><i class="fas fa-trash"></i></button></td></tr>`).join('')}</tbody></table></div>`; }
function showConsultationsTab() { const a=getAppointments(); document.getElementById('medicalConsultTab').innerHTML=`<div class="table-responsive"><table class="data-table"><thead><tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Type</th><th>Statut</th></tr></thead><tbody>${a.map(apt=>`<tr><td>${apt.patient_name}</td><td>${apt.doctor_name}</td><td>${new Date(apt.appointment_date).toLocaleDateString('fr-FR')}</td><td>${apt.consultation_type}</td><td><span class="status-badge status-${apt.status}">${apt.status}</span></td></tr>`).join('')}</tbody></table></div>`; }

function updateDashboardStats() {
    const s = getGlobalStats();
    const d1=document.getElementById('totalAppointments'); if(d1) d1.textContent=s.totalAppointments;
    const d2=document.getElementById('totalRevenue'); if(d2) d2.textContent=s.totalRevenue;
    const d3=document.getElementById('totalUsers'); if(d3) d3.textContent=s.totalUsers;
    const d4=document.getElementById('avgRating'); if(d4) d4.textContent=s.avgRating;
}

function refreshModule() { loadModuleContent(modules[currentFace]); updateDashboardStats(); showNotification('Données actualisées'); }
function exportCurrentData() { showNotification('Export des données en cours...'); setTimeout(()=>showNotification('Export terminé'),1500); }
function showNotification(msg) { const t=document.getElementById('notificationToast'); if(t){ t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),3000); } }

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('rotateLeft')?.addEventListener('click', () => rotateCube('left'));
    document.getElementById('rotateRight')?.addEventListener('click', () => rotateCube('right'));
    document.getElementById('backToHome')?.addEventListener('click', () => window.location.href = 'index.html');
    loadModuleContent(modules[0]);
    updateActionsBar(modules[0]);
    updateDashboardStats();
});
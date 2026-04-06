  
document.addEventListener('DOMContentLoaded', () => {
    loadDoctors();
    loadReviews();
    document.getElementById('appointmentForm')?.addEventListener('submit', handleAppointmentSubmit);
});

function loadDoctors() {
    const container = document.getElementById('doctorsList');
    if (!container) return;
    const doctors = getDoctors();
    container.innerHTML = doctors.map(d => `
        <div class="col-md-4">
            <div class="doctor-card" onclick="selectDoctor(${d.id})">
                <img src="${d.image}" alt="${d.name}">
                <div class="p-3">
                    <h5>${d.name}</h5>
                    <p class="text-primary">${d.specialty}</p>
                    <div class="text-warning">${'★'.repeat(Math.floor(d.rating))}${'☆'.repeat(5-Math.floor(d.rating))} ${d.rating}</div>
                    <p><i class="fas fa-map-marker-alt"></i> ${d.location}</p>
                    <p><i class="fas fa-euro-sign"></i> ${d.price}€</p>
                    <div>${d.consultations.map(c => `<span class="consultation-badge badge-${c}">${c==='video'?'📹 Visio':c==='audio'?'🎧 Audio':'🏥 Présentiel'}</span>`).join('')}</div>
                    <button class="btn btn-custom w-100 mt-3">Prendre RDV</button>
                </div>
            </div>
        </div>
    `).join('');
}

function loadReviews() {
    const container = document.getElementById('reviewsList');
    if (!container) return;
    const reviews = getReviews();
    container.innerHTML = reviews.map(r => `
        <div class="col-md-6">
            <div class="review-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px">${r.patient_name.charAt(0)}</div>
                    <div><h6 class="mb-0">${r.patient_name}</h6><small>Consultation avec ${r.doctor_name}</small></div>
                </div>
                <div class="text-warning mb-2">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
                <p>"${r.comment}"</p>
                <small class="text-muted">${new Date(r.date).toLocaleDateString('fr-FR')}</small>
            </div>
        </div>
    `).join('');
}

function handleAppointmentSubmit(e) {
    e.preventDefault();
    const data = {
        id: Date.now(),
        patient_name: document.getElementById('patientName').value,
        patient_email: document.getElementById('patientEmail').value,
        patient_phone: document.getElementById('patientPhone').value,
        country: document.getElementById('country').value,
        symptoms: document.getElementById('symptoms').value,
        consultation_type: document.getElementById('consultationType').value,
        payment_method: document.getElementById('paymentMethod').value,
        doctor_name: "Dr. Sophie Martin",
        doctor_specialty: "Généraliste",
        appointment_date: new Date(Date.now() + 7*86400000).toISOString().split('T')[0],
        appointment_time: "10:00",
        status: "pending",
        payment_status: "pending",
        amount: 80,
        created_at: new Date().toISOString()
    };
    let a = getAppointments();
    a.push(data);
    saveAppointments(a);
    let p = getPayments();
    p.push({ id: Date.now(), appointment_id: data.id, amount: 80, payment_method: data.payment_method, status: "pending", created_at: new Date().toISOString() });
    savePayments(p);
    showConfirmation(data);
    e.target.reset();
}

function showConfirmation(apt) {
    document.getElementById('confirmationDetails').innerHTML = `
        <p>Votre rendez-vous avec ${apt.doctor_name} est confirmé.</p>
        <p>Date: ${new Date(apt.appointment_date).toLocaleDateString('fr-FR')} à ${apt.appointment_time}</p>
        <p>Montant: ${apt.amount}€</p>
        <p>Mode: ${apt.consultation_type === 'video' ? 'Visioconférence' : apt.consultation_type === 'audio' ? 'Audio' : 'Présentiel'}</p>
        <p>Un email de confirmation vous a été envoyé.</p>
    `;
    new bootstrap.Modal(document.getElementById('confirmationModal')).show();
}

function selectDoctor(id) { document.getElementById('consultation')?.scrollIntoView({ behavior: 'smooth' }); }
function showProfile() { alert('Profil patient - Fonctionnalité à venir'); }
function showAppointments() { const a=getAppointments(); alert('Mes RDV:\n'+a.map(apt=>`${apt.appointment_date} - ${apt.doctor_name} - ${apt.status}`).join('\n')); }
function showMedicalRecord() { alert('Dossier médical - Fonctionnalité à venir'); }
function logout() { alert('Déconnexion'); }
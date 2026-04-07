// API Service - À ajouter dans vos fichiers HTML existants
const API = {
    baseUrl: '/api',
    
    // Auth
    login: (username, password) => {
        return fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        }).then(res => res.json());
    },
    
    logout: () => {
        return fetch('/api/logout').then(res => res.json());
    },
    
    // Médecins
    getDoctors: () => {
        return fetch('/api/getDoctors').then(res => res.json());
    },
    
    addDoctor: (doctor) => {
        return fetch('/api/doctors/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(doctor)
        }).then(res => res.json());
    },
    
    deleteDoctor: (id) => {
        return fetch('/api/doctors/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(res => res.json());
    },
    
    // Patients
    getPatients: () => {
        return fetch('/api/getPatients').then(res => res.json());
    },
    
    addPatient: (patient) => {
        return fetch('/api/patients/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(patient)
        }).then(res => res.json());
    },
    
    // Rendez-vous
    submitAppointment: (data) => {
        return fetch('/api/submitAppointment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json());
    },
    
    getAppointments: () => {
        return fetch('/api/getAppointments').then(res => res.json());
    },
    
    confirmPayment: (id) => {
        return fetch('/api/appointments/confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(res => res.json());
    },
    
    // Avis
    submitReview: (data) => {
        return fetch('/api/submitReview', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json());
    },
    
    getReviews: () => {
        return fetch('/api/getReviews').then(res => res.json());
    },
    
    approveReview: (id) => {
        return fetch('/api/reviews/approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(res => res.json());
    },
    
    reportReview: (id) => {
        return fetch('/api/reviews/report', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(res => res.json());
    },
    
    deleteReview: (id) => {
        return fetch('/api/reviews/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(res => res.json());
    },
    
    notifyPatient: (data) => {
        return fetch('/api/reviews/notify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json());
    },
    
    // Statistiques
    getStats: () => {
        return fetch('/api/getStats').then(res => res.json());
    },
    
    // Dossiers médicaux
    getMedicalRecords: () => {
        return fetch('/api/getMedicalRecords').then(res => res.json());
    },
    
    addMedicalRecord: (data) => {
        return fetch('/api/medical-records/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json());
    }
};
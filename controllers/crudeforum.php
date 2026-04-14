<!DOCTYPE html>
<html>
<head>
    <title>Backoffice - Gestion des publications</title>
    <!-- Bootstrap, FontAwesome, etc. -->
</head>
<body>
<div id="publicationsList"></div>

<script>
// URL absolue ou relative vers le contrôleur
const API_URL = '../../controllers/crudeforum.php';

// Récupérer la liste des publications
async function loadPublications() {
    const response = await fetch(`${API_URL}?action=list&page=1&limit=20`);
    const result = await response.json();
    if (result.success) {
        afficherPublications(result.data);
    } else {
        alert('Erreur chargement');
    }
}

// Ajouter / Modifier une publication
async function savePublication(data) {
    const action = data.id ? 'update' : 'create';
    const response = await fetch(`${API_URL}?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await response.json();
    if (result.success) {
        alert('Opération réussie');
        loadPublications();
    } else {
        alert('Erreur: ' + (result.error || result.errors?.join(', ')));
    }
}

// Supprimer
async function deletePublication(id) {
    if (confirm('Supprimer ?')) {
        const response = await fetch(`${API_URL}?action=delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (result.success) loadPublications();
    }
}

// Exemple d'affichage simple
function afficherPublications(pubs) {
    const container = document.getElementById('publicationsList');
    container.innerHTML = pubs.map(p => `
        <div>
            <strong>${p.medecin_nom}</strong><br>
            ${p.contenu}<br>
            <button onclick="deletePublication(${p.id_publication})">Supprimer</button>
            <button onclick="editPublication(${p.id_publication})">Modifier</button>
            <hr>
        </div>
    `).join('');
}

// Récupérer une publication pour l'édition
async function editPublication(id) {
    const response = await fetch(`${API_URL}?action=get&id=${id}`);
    const result = await response.json();
    if (result.success) {
        // Remplir un formulaire avec result.data
        console.log(result.data);
    }
}

loadPublications();
</script>
</body>
</html>
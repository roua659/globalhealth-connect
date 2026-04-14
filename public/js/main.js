/**
 * Main Application JavaScript
 * Handles UI interactions and view updates
 */

// Initialize services
const api = new APIService();
const publicationService = new PublicationService(api);
const commentaireService = new CommentaireService(api);

/**
 * DOM Elements Cache
 */
const DOM = {
    // Publications
    pubForm: null,
    pubList: null,
    pubSearch: null,
    pubModal: null,
    
    // Comments
    comForm: null,
    comList: null,
    comModal: null,
    
    // Moderation
    modPanel: null,
    pendingList: null,
    flaggedList: null,
    
    // Messages
    alertContainer: null,
    loadingSpinner: null
};

/**
 * Initialize application
 */
document.addEventListener('DOMContentLoaded', async function() {
    console.log('Initializing application...');
    cacheDOM();
    attachEventListeners();
    await loadInitialData();
});

/**
 * Cache all DOM elements
 */
function cacheDOM() {
    DOM.pubForm = document.getElementById('pubForm');
    DOM.pubList = document.getElementById('pubList');
    DOM.pubSearch = document.getElementById('pubSearch');
    DOM.pubModal = document.getElementById('pubModal');
    
    DOM.comForm = document.getElementById('comForm');
    DOM.comList = document.getElementById('comList');
    DOM.comModal = document.getElementById('comModal');
    
    DOM.modPanel = document.getElementById('modPanel');
    DOM.pendingList = document.getElementById('pendingList');
    DOM.flaggedList = document.getElementById('flaggedList');
    
    DOM.alertContainer = document.getElementById('alertContainer');
    DOM.loadingSpinner = document.getElementById('loadingSpinner');
}

/**
 * Attach event listeners
 */
function attachEventListeners() {
    // Publication events
    if (DOM.pubForm) {
        DOM.pubForm.addEventListener('submit', handleCreatePublication);
    }
    if (DOM.pubSearch) {
        DOM.pubSearch.addEventListener('submit', handleSearchPublication);
    }
    
    // Comment events
    if (DOM.comForm) {
        DOM.comForm.addEventListener('submit', handleCreateComment);
    }
}

/**
 * Load initial data
 */
async function loadInitialData() {
    try {
        showLoading();
        await loadPublications();
        hideLoading();
    } catch (error) {
        showAlert('Error loading data: ' + error.message, 'danger');
        hideLoading();
    }
}

// ==================== PUBLICATION HANDLERS ====================

/**
 * Load and display all publications
 */
async function loadPublications(limit = 10, offset = 0, id_medecin = null) {
    try {
        showLoading();
        const result = await publicationService.getAll(limit, offset, id_medecin);
        
        if (result.success && DOM.pubList) {
            renderPublications(result.data);
            renderPagination(result.pagination, 'loadPublications');
        } else {
            showAlert(result.error || 'Failed to load publications', 'danger');
        }
        hideLoading();
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
        hideLoading();
    }
}

/**
 * Handle publication form submission
 */
async function handleCreatePublication(e) {
    e.preventDefault();
    
    const formData = new FormData(DOM.pubForm);
    const data = Object.fromEntries(formData);
    
    // Validate
    const validation = publicationService.validate(data);
    if (!validation.valid) {
        showAlert(validation.errors.join('<br>'), 'warning');
        return;
    }
    
    try {
        showLoading();
        const result = await publicationService.create(data);
        
        if (result.success) {
            showAlert('Publication created successfully!', 'success');
            DOM.pubForm.reset();
            await loadPublications();
        } else {
            showAlert(result.error || 'Failed to create publication', 'danger');
        }
        hideLoading();
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
        hideLoading();
    }
}

/**
 * Handle publication search
 */
async function handleSearchPublication(e) {
    e.preventDefault();
    
    const keyword = document.getElementById('searchKeyword')?.value || '';
    if (keyword.length < 2) {
        showAlert('Search keyword must be at least 2 characters', 'warning');
        return;
    }
    
    try {
        showLoading();
        const result = await publicationService.search(keyword);
        
        if (result.success && DOM.pubList) {
            renderPublications(result.data);
            showAlert(`Found ${result.data.length} publication(s)`, 'info');
        } else {
            showAlert(result.error || 'No publications found', 'info');
        }
        hideLoading();
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
        hideLoading();
    }
}

/**
 * Render publications list
 */
function renderPublications(publications) {
    if (!DOM.pubList) return;
    
    DOM.pubList.innerHTML = '';
    
    if (publications.length === 0) {
        DOM.pubList.innerHTML = '<p class="text-muted text-center py-4">No publications found</p>';
        return;
    }
    
    publications.forEach(pub => {
        const html = `
            <div class="publication-card card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Publication #${pub.id_publication}</h5>
                    <p class="card-text">${pub.contenu.substring(0, 100)}...</p>
                    <small class="text-muted">
                        By Doctor #${pub.id_medecin} on ${new Date(pub.date_publication).toLocaleString()}
                    </small>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-info" onclick="viewPublicationDetails(${pub.id_publication})">
                            View Details
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editPublication(${pub.id_publication})">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePublication(${pub.id_publication})">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        DOM.pubList.innerHTML += html;
    });
}

/**
 * View publication details with comments
 */
async function viewPublicationDetails(id) {
    try {
        showLoading();
        const result = await publicationService.getWithComments(id);
        
        if (result.success) {
            displayPublicationModal(result);
        } else {
            showAlert(result.error || 'Failed to load publication details', 'danger');
        }
        hideLoading();
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
        hideLoading();
    }
}

/**
 * Display publication modal
 */
function displayPublicationModal(data) {
    const { publication, comments, comment_count } = data;
    
    const modal = `
        <div class="modal" id="pubDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Publication #${publication.id_publication}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${publication.contenu}</p>
                        ${publication.url_image ? `<img src="${publication.url_image}" class="img-fluid mb-3">` : ''}
                        ${publication.url_video ? `<a href="${publication.url_video}" target="_blank" class="btn btn-sm btn-primary mb-3">Watch Video</a>` : ''}
                        <hr>
                        <h6>Comments (${comment_count})</h6>
                        <div id="commentsContainer">
                            ${comments.map(c => `
                                <div class="comment mb-2">
                                    <strong>${c.nom} ${c.prenom}</strong>
                                    <p>${c.contenu}</p>
                                    <small class="text-muted">${c.statut}</small>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Show modal
    const modalElement = document.createElement('div');
    modalElement.innerHTML = modal;
    document.body.appendChild(modalElement);
    new bootstrap.Modal(modalElement.querySelector('.modal')).show();
}

/**
 * Delete publication
 */
async function deletePublication(id) {
    if (!confirm('Are you sure you want to delete this publication?')) return;
    
    try {
        showLoading();
        const result = await publicationService.delete(id);
        
        if (result.success) {
            showAlert('Publication deleted successfully', 'success');
            await loadPublications();
        } else {
            showAlert(result.error || 'Failed to delete publication', 'danger');
        }
        hideLoading();
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
        hideLoading();
    }
}

// ==================== COMMENT HANDLERS ====================

/**
 * Load comments for publication
 */
async function loadComments(id_publication) {
    try {
        const result = await commentaireService.getByPublication(id_publication);
        
        if (result.success && DOM.comList) {
            renderComments(result.data);
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

/**
 * Handle comment form submission
 */
async function handleCreateComment(e) {
    e.preventDefault();
    
    const formData = new FormData(DOM.comForm);
    const data = Object.fromEntries(formData);
    
    // Validate
    const validation = commentaireService.validate(data);
    if (!validation.valid) {
        showAlert(validation.errors.join('<br>'), 'warning');
        return;
    }
    
    try {
        showLoading();
        const result = await commentaireService.create(data);
        
        if (result.success) {
            showAlert('Comment created successfully!', 'success');
            DOM.comForm.reset();
            await loadComments(data.id_publication);
        } else {
            showAlert(result.error || 'Failed to create comment', 'danger');
        }
        hideLoading();
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
        hideLoading();
    }
}

/**
 * Render comments list
 */
function renderComments(comments) {
    if (!DOM.comList) return;
    
    DOM.comList.innerHTML = '';
    
    if (comments.length === 0) {
        DOM.comList.innerHTML = '<p class="text-muted">No comments yet</p>';
        return;
    }
    
    comments.forEach(com => {
        const html = `
            <div class="comment-card card mb-2">
                <div class="card-body">
                    <h6 class="card-title">${com.nom} ${com.prenom}</h6>
                    <p class="card-text">${com.contenu}</p>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="likeComment(${com.id_commentaire})">
                            👍 ${com.note || 0}
                        </button>
                        <button class="btn btn-outline-warning" onclick="reportComment(${com.id_commentaire})">
                            🚩 ${com.signalements || 0}
                        </button>
                        ${com.statut === 'pending' ? `
                            <button class="btn btn-outline-success" onclick="approveComment(${com.id_commentaire})">Approve</button>
                            <button class="btn btn-outline-danger" onclick="rejectComment(${com.id_commentaire})">Reject</button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        DOM.comList.innerHTML += html;
    });
}

/**
 * Like comment
 */
async function likeComment(id) {
    try {
        const result = await commentaireService.like(id);
        if (result.success) {
            showAlert('Comment liked!', 'success');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

/**
 * Report comment
 */
async function reportComment(id) {
    try {
        const result = await commentaireService.report(id);
        if (result.success) {
            showAlert('Comment reported!', 'info');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

/**
 * Approve comment
 */
async function approveComment(id) {
    try {
        const result = await commentaireService.approve(id);
        if (result.success) {
            showAlert('Comment approved!', 'success');
            location.reload(); // Refresh page
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

/**
 * Reject comment
 */
async function rejectComment(id) {
    try {
        const result = await commentaireService.reject(id);
        if (result.success) {
            showAlert('Comment rejected!', 'warning');
            location.reload(); // Refresh page
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

// ==================== UI HELPERS ====================

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertId = 'alert-' + Date.now();
    const html = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    if (DOM.alertContainer) {
        DOM.alertContainer.innerHTML += html;
        setTimeout(() => {
            const el = document.getElementById(alertId);
            if (el) el.remove();
        }, 5000);
    } else {
        alert(message);
    }
}

/**
 * Show loading spinner
 */
function showLoading() {
    if (DOM.loadingSpinner) {
        DOM.loadingSpinner.style.display = 'block';
    }
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    if (DOM.loadingSpinner) {
        DOM.loadingSpinner.style.display = 'none';
    }
}

/**
 * Render pagination controls
 */
function renderPagination(pagination, callbackName) {
    const totalPages = Math.ceil(pagination.total / pagination.limit);
    const currentPage = Math.floor(pagination.offset / pagination.limit) + 1;
    
    // Simple pagination - can be enhanced
    console.log(`Page ${currentPage} of ${totalPages}`);
}

// Export for use elsewhere
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        publicationService,
        commentaireService,
        loadPublications,
        loadComments
    };
}
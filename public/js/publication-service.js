/**
 * Publication Service - Handles all publication-related API calls
 */
class PublicationService {
    constructor(apiService = null) {
        this.api = apiService || new APIService();
    }

    /**
     * Get all publications
     * @param {number} limit - Records per page
     * @param {number} offset - Offset for pagination
     * @param {number} id_medecin - Optional: Filter by doctor ID
     */
    async getAll(limit = 10, offset = 0, id_medecin = null) {
        const params = { action: 'index', limit, offset };
        if (id_medecin) params.id_medecin = id_medecin;
        return await this.api.get('/publications.php', params);
    }

    /**
     * Get single publication by ID
     */
    async getById(id) {
        return await this.api.get('/publications.php', { action: 'show', id });
    }

    /**
     * Get publication with comments
     */
    async getWithComments(id) {
        return await this.api.get('/publications.php', { action: 'show', id });
    }

    /**
     * Get publications by doctor
     */
    async getByDoctor(id_medecin, limit = 10, offset = 0) {
        return await this.api.get('/publications.php', { action: 'index', id_medecin, limit, offset });
    }

    /**
     * Create new publication
     */
    async create(data) {
        return await this.api.post('/publications.php', data, { action: 'store' });
    }

    /**
     * Update publication
     */
    async update(id, data) {
        return await this.api.post('/publications.php', data, { action: 'update', id });
    }

    /**
     * Delete publication
     */
    async delete(id) {
        return await this.api.post('/publications.php', {}, { action: 'destroy', id });
    }

    /**
     * Search publications
     */
    async search(keyword, limit = 10, offset = 0) {
        return await this.api.get('/publications.php', { 
            action: 'search', 
            keyword, 
            limit, 
            offset 
        });
    }

    /**
     * Get approved comments for publication
     */
    async getApprovedComments(id) {
        return await this.api.get('/publications.php', { 
            action: 'approved-comments', 
            id 
        });
    }

    /**
     * Validate publication data
     */
    validate(data) {
        const errors = [];

        if (!data.id_medecin) {
            errors.push('Doctor ID is required');
        }

        if (!data.contenu || data.contenu.trim().length < 10) {
            errors.push('Content must be at least 10 characters');
        }

        if (data.contenu && data.contenu.length > 50000) {
            errors.push('Content is too long (max 50000 characters)');
        }

        if (data.url_image && !this.isValidUrl(data.url_image)) {
            errors.push('Invalid image URL format');
        }

        if (data.url_video && !this.isValidUrl(data.url_video)) {
            errors.push('Invalid video URL format');
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Helper: Validate URL
     */
    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PublicationService;
}
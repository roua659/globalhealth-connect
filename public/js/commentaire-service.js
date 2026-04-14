/**
 * Commentaire Service - Handles all comment-related API calls
 */
class CommentaireService {
    constructor(apiService = null) {
        this.api = apiService || new APIService();
    }

    /**
     * Get all comments for publication
     */
    async getByPublication(id_publication, limit = 100, offset = 0) {
        return await this.api.get('/commentaires.php', { 
            action: 'index', 
            id_publication, 
            limit, 
            offset 
        });
    }

    /**
     * Get single comment
     */
    async getById(id) {
        return await this.api.get('/commentaires.php', { action: 'show', id });
    }

    /**
     * Get approved comments for publication
     */
    async getApprovedByPublication(id_publication, limit = 100, offset = 0) {
        return await this.api.get('/commentaires.php', { 
            action: 'approved', 
            id_publication, 
            limit, 
            offset 
        });
    }

    /**
     * Get comments by user
     */
    async getByUser(id_user, limit = 50, offset = 0) {
        return await this.api.get('/commentaires.php', { 
            action: 'by-user', 
            id_user, 
            limit, 
            offset 
        });
    }

    /**
     * Get pending comments (for moderation)
     */
    async getPending(limit = 50, offset = 0) {
        return await this.api.get('/commentaires.php', { 
            action: 'pending', 
            limit, 
            offset 
        });
    }

    /**
     * Get flagged comments (reported)
     */
    async getFlagged(limit = 50, offset = 0) {
        return await this.api.get('/commentaires.php', { 
            action: 'flagged', 
            limit, 
            offset 
        });
    }

    /**
     * Create new comment
     */
    async create(data) {
        return await this.api.post('/commentaires.php', data, { action: 'store' });
    }

    /**
     * Update comment
     */
    async update(id, data) {
        return await this.api.post('/commentaires.php', data, { action: 'update', id });
    }

    /**
     * Approve comment
     */
    async approve(id) {
        return await this.api.post('/commentaires.php', {}, { action: 'approve', id });
    }

    /**
     * Reject comment
     */
    async reject(id) {
        return await this.api.post('/commentaires.php', {}, { action: 'reject', id });
    }

    /**
     * Like/upvote comment
     */
    async like(id) {
        return await this.api.post('/commentaires.php', {}, { action: 'like', id });
    }

    /**
     * Report comment
     */
    async report(id) {
        return await this.api.post('/commentaires.php', {}, { action: 'report', id });
    }

    /**
     * Delete comment
     */
    async delete(id) {
        return await this.api.post('/commentaires.php', {}, { action: 'destroy', id });
    }

    /**
     * Validate comment data
     */
    validate(data) {
        const errors = [];

        if (!data.id_publication) {
            errors.push('Publication ID is required');
        }

        if (!data.id_user) {
            errors.push('User ID is required');
        }

        if (!data.contenu || data.contenu.trim().length < 2) {
            errors.push('Comment must be at least 2 characters');
        }

        if (data.contenu && data.contenu.length > 5000) {
            errors.push('Comment is too long (max 5000 characters)');
        }

        const validStatuses = ['pending', 'approved', 'rejected'];
        if (data.statut && !validStatuses.includes(data.statut)) {
            errors.push('Invalid status. Must be: ' + validStatuses.join(', '));
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Format comment data for display
     */
    formatComment(comment) {
        return {
            ...comment,
            dateFormatted: new Date(comment.date_publication).toLocaleString(),
            isApproved: comment.statut === 'approved',
            isPending: comment.statut === 'pending',
            isRejected: comment.statut === 'rejected',
            hasReports: comment.signalements > 0,
            likeCount: comment.note || 0,
            reportCount: comment.signalements || 0
        };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CommentaireService;
}
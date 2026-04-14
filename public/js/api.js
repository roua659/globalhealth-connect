/**
 * API Service - Base API utility class
 * Handles all HTTP requests to the backend
 */
class APIService {
    constructor(baseURL = '/globalhealth-connect1/api') {
        this.baseURL = baseURL;
        this.headers = {
            'Content-Type': 'application/json'
        };
    }

    /**
     * Make GET request
     */
    async get(endpoint, params = {}) {
        const url = new URL(this.baseURL + endpoint, window.location.origin);
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: this.headers
            });
            return await this.handleResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    /**
     * Make POST request
     */
    async post(endpoint, data = {}, params = {}) {
        const url = new URL(this.baseURL + endpoint, window.location.origin);
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });

        try {
            const response = await fetch(url.toString(), {
                method: 'POST',
                headers: this.headers,
                body: JSON.stringify(data)
            });
            return await this.handleResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    /**
     * Handle API response
     */
    async handleResponse(response) {
        const data = await response.json();
        
        if (!response.ok) {
            return {
                success: false,
                error: data.error || `HTTP Error: ${response.status}`,
                status: response.status
            };
        }
        
        return data;
    }

    /**
     * Handle API error
     */
    handleError(error) {
        console.error('API Error:', error);
        return {
            success: false,
            error: error.message || 'An error occurred while processing your request'
        };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APIService;
}
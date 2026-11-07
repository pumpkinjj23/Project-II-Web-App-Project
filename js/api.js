/**
 * api.js - Northwind BMS RESTful API Client with JWT Auth
 * จัดการการสื่อสารกับ Backend RESTful API และการเก็บรักษา JWT Token
 */

const API = {
    // กำหนด Base URL ให้ยืดหยุ่น รองรับทั้งการเปิดผ่าน Apache Subdirectory และ root
    getBaseUrl: function() {
        const path = window.location.pathname;
        const dir = path.substring(0, path.lastIndexOf('/'));
        return dir + '/api';
    },

    // Token Management
    getToken: function() {
        return localStorage.getItem('northwind_jwt_token');
    },

    setToken: function(token) {
        localStorage.setItem('northwind_jwt_token', token);
    },

    removeToken: function() {
        localStorage.removeItem('northwind_jwt_token');
        localStorage.removeItem('northwind_user');
    },

    getUser: function() {
        const user = localStorage.getItem('northwind_user');
        return user ? JSON.parse(user) : null;
    },

    setUser: function(user) {
        localStorage.setItem('northwind_user', JSON.stringify(user));
    },

    isLoggedIn: function() {
        return !!this.getToken();
    },

    /**
     * ส่ง HTTP Request ไปยัง RESTful API
     */
    request: async function(endpoint, method = 'GET', data = null) {
        const baseUrl = this.getBaseUrl();
        let cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
        
        // ตรวจสอบ URL รูปแบบ Clean REST หรือ Fallback Query String
        let url = `${baseUrl}/${cleanEndpoint}`;

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const options = {
            method: method.toUpperCase(),
            headers: headers
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);

            // หากติด 404 ให้ลอง Fallback ไปยัง api/index.php?route=...
            if (response.status === 404 && !url.includes('?route=')) {
                const fallbackUrl = `${baseUrl}/index.php?route=${cleanEndpoint}`;
                const fallbackRes = await fetch(fallbackUrl, options);
                return await fallbackRes.json();
            }

            const json = await response.json();
            return json;
        } catch (err) {
            console.warn(`API direct call failed (${url}), trying query string fallback...`, err);
            // Fallback: Query string router
            try {
                const fallbackUrl = `${baseUrl}/index.php?route=${cleanEndpoint}`;
                const fallbackRes = await fetch(fallbackUrl, options);
                return await fallbackRes.json();
            } catch (fallbackErr) {
                console.error("API Fetch Error:", fallbackErr);
                return {
                    status: false,
                    success: false,
                    message: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ API ได้ หรือเกิดข้อผิดพลาดในการเชื่อมต่อ'
                };
            }
        }
    },

    // Shortcuts
    get: function(endpoint, params = null) {
        if (params) {
            const qs = new URLSearchParams(params).toString();
            endpoint += (endpoint.includes('?') ? '&' : '?') + qs;
        }
        return this.request(endpoint, 'GET');
    },

    post: function(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    },

    put: function(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    },

    delete: function(endpoint) {
        return this.request(endpoint, 'DELETE');
    }
};


import axios from 'axios';

interface User {
    // Define the structure of the user object
    // Example:
    id: number;
    name: string;
    // Add other properties as needed
}

class Auth {
    token: string | null;
    user: User | null;

    constructor() {
        this.token = window.localStorage.getItem('token');
        const userData = window.localStorage.getItem('user');
        this.user = userData ? JSON.parse(userData) : null;

        if (this.token) {
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.token;
        }
    }

    login(token: string, user: User) {
        window.localStorage.setItem('token', token);
        window.localStorage.setItem('user', JSON.stringify(user));
        this.token = token;
        this.user = user;
    }

    getToken(): string | null {
        return this.token;
    }

    check(): boolean {
        return !!this.token;
    }

    logout() {
        // window.localStorage.clear();
        window.localStorage.removeItem('token');
        window.localStorage.removeItem('user');
        location.reload();
        this.user = null;
    }
}

export default new Auth();

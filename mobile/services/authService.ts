import api from './api';
import { AUTH_API } from '../constants/Config';
import * as SecureStore from 'expo-secure-store';

export const authService = {
    async login(username: string, password: string) {
        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append('password', password);

        const response = await api.post('/auth/api/proses-login.php', formData.toString());

        // Note: PHP processes-login.php redirected on success.
        // We might need to modify the PHP API to return JSON for the mobile app.
        return response.data;
    },

    async register(username: string, password: string) {
        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append('password', password);

        const response = await api.post('/auth/api/proses-register.php', formData.toString());
        return response.data;
    },

    async setSession(userData: any) {
        await SecureStore.setItemAsync('user_session', JSON.stringify(userData));
    },

    async getSession() {
        const session = await SecureStore.getItemAsync('user_session');
        return session ? JSON.parse(session) : null;
    },

    async logout() {
        await SecureStore.deleteItemAsync('user_session');
    }
};

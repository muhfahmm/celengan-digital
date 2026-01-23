import axios from 'axios';
import { API_URL } from '../constants/Config';

const api = axios.create({
    baseURL: API_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
});

export default api;

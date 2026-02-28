// resources/js/services/cardiovascularService.js
import axios from 'axios';

const API_URL = '/api/cardiovascular';

export default {
    getAll(limit = 100, offset = 0) {
        return axios.get(API_URL, { params: { limit, offset } });
    },
    getByState(state) {
        return axios.get(`${API_URL}/state/${state}`);
    },
    getByYear(year) {
        return axios.get(`${API_URL}/year/${year}`);
    }
};
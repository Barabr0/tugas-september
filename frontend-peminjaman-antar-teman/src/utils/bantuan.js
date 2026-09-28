import api from './api';

export default {
    ajukan(data) {
        return api.post('/bantuan', data);
    },
    getAll() {
        return api.get('/bantuan');
    },
    delete(id) {
        return api.delete(`/bantuan/${id}`);
    },
    getAllAdmin() {
        return api.get('/admin/bantuan');
    },
    respond(id, data) {
        return api.patch(`/admin/bantuan/${id}/respond`, data);
    }
};
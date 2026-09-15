import api from './api';

export default {
    getMyBarangs() {
        return api.get('/barangs');
    },
    addBarang(data) {
        return api.post('/barangs', data);
    },
    updateBarang(id, data) {
        return api.put('/barangs/' + id, data);
    },
    deleteBarang(id) {
        return api.delete('/barangs/' + id);
    }
};
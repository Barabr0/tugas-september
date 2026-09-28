import api from './api';

export default {
    getAll() {
        return api.get('/admin/kategori');
    },
    
    addKategori(data) {
        return api.post('/admin/kategori', data);
    },

    updateKategori(id, data) {
        return api.put('/admin/kategori/' + id, data);
    },

    deleteKategori(id) {
        return api.delete('/admin/kategori/' + id);
    }
};
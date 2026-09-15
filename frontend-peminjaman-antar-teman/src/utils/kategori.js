import api from './api';

export default {
    getAll() {
        return api.get('/kategoris');
    },
    
    // Tambahkan metode ini untuk membuat kategori baru
    addKategori(data) {
        return api.post('/kategoris', data);
    }
};
import api from './api';

export default {
    getAll() {
        return api.get('/kategori');
    },
    
    // Tambahkan metode ini untuk membuat kategori baru
    addKategori(data) {
        return api.post('/kategori', data);
    }
};
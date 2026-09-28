import api from './api';

export default {
    // Untuk halaman List Barang Saya
    getMyBarangs() {
        return api.get('/barang');
    },
    
    // Untuk halaman Add Peminjaman (ambil semua barang berstatus T)
    getBarangTersedia() {
        return api.get('/barang/tersedia');
    },

    // CRUD Barang
    addBarang(data) {
        return api.post('/barang', data);
    },
    
    updateBarang(id, data) {
        return api.put('/barang/' + id, data);
    },
    
    deleteBarang(id) {
        return api.delete('/barang/' + id, data);
    }
};
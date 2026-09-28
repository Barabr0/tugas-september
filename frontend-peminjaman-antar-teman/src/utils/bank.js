import api from './api';

export default {
    // Ambil semua rekening milik user yang login (sudah include saldo)
    getAll() {
        return api.get('/banks');
    },

    // Tambah rekening: { nama_bank, nomor_rekening, atas_nama }
    store(data) {
        return api.post('/banks', data);
    },

    // Top up saldo rekening: { nominal }
    topUp(id, data) {
        return api.post(`/banks/${id}/topup`, data);
    },

    // Hapus rekening (hanya jika saldo 0)
    destroy(id) {
        return api.delete(`/banks/${id}`);
    }
};
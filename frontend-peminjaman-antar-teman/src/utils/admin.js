import api from './api';

export default {
    // ==========================================
    // FITUR BANTUAN (HELPDESK)
    // ==========================================

    // Admin: Ambil request bantuan (default hanya yang pending)
    getBantuanRequests(params = { status: 'pending' }) {
        return api.get('/admin/bantuan', { params });
    },

    // Admin: Setujui / tolak request bantuan
    // data: { status: 'disetujui' | 'ditolak', alasan?: string }
    respondBantuan(id, data) {
        return api.patch(`/admin/bantuan/${id}/respond`, data);
    },

    // User biasa: Ajukan request bantuan
    ajukanBantuan(data) {
        return api.post('/bantuan', data);
    },

    // User biasa: Ambil bantuan miliknya (peminta / target)
    getUserBantuan() {
        return api.get('/bantuan');
    },

    // User biasa: Batalkan bantuannya sendiri
    deleteBantuan(id) {
        return api.delete(`/bantuan/${id}`);
    },

    // ==========================================
    // AKSI PAKSA ADMIN (FORCE DELETE/CANCEL)
    // ==========================================

    forceDeleteBarang(id) {
        return api.delete(`/admin/barangs/${id}/force-delete`);
    },

    adminBatalkanPeminjaman(id) {
        return api.patch(`/admin/peminjaman/${id}/batalkan`);
    },

    // ==========================================
    // MONITORING DATA SISTEM
    // ==========================================

    getAllPeminjaman() {
        return api.get('/peminjaman');
    },

    getAllPeminjamanUang() {
        return api.get('/peminjaman-uang');
    },

    getAllUsers() {
        return api.get('/users');
    },

    // Admin: Edit data user
    updateUser(id, data) {
        return api.put(`/admin/users/${id}`, data);
    },

    // Admin: Hapus user
    deleteUser(id) {
        return api.delete(`/admin/users/${id}`);
    },

    getAllBarangs() {
        return api.get('/barang');
    }
};
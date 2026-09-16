import api from './api';

export default {
    // ==========================================
    // FITUR BANTUAN (HELPDESK)
    // ==========================================

    // Admin: Ambil semua request bantuan yang statusnya pending
    getBantuanRequests() {
        return api.get('/admin/bantuan');
    },

    // Admin: Tandai request bantuan sebagai sudah diproses
    processBantuan(id) {
        return api.patch(`/admin/bantuan/${id}/process`);
    },

    // User biasa: Ajukan request bantuan ke admin (edit/hapus/batalkan)
    ajukanBantuan(data) {
        return api.post('/bantuan', data);
    },

    // ==========================================
    // AKSI PAKSA ADMIN (FORCE DELETE/CANCEL)
    // ==========================================

    // Admin: Hapus barang secara paksa (meskipun sedang dipinjam)
    forceDeleteBarang(id) {
        return api.delete(`/admin/barangs/${id}/force-delete`);
    },

    // Admin: Batalkan transaksi peminjaman barang secara paksa
    adminBatalkanPeminjaman(id) {
        return api.patch(`/admin/peminjaman/${id}/batalkan`);
    },

    // ==========================================
    // MONITORING DATA SISTEM
    // ==========================================

    // Admin: Ambil semua transaksi peminjaman barang di sistem
    getAllPeminjaman() {
        return api.get('/peminjaman');
    },

    // Admin: Ambil semua transaksi peminjaman uang di sistem
    getAllPeminjamanUang() {
        return api.get('/peminjaman-uang');
    },

    // Admin: Ambil semua user terdaftar
    getAllUsers() {
        return api.get('/users');
    },

    // Admin: Ambil semua barang di sistem (panggil endpoint biasa, backend otomatis tahu kalau admin yang request)
    getAllBarangs() {
        return api.get('/barang');
    }
};
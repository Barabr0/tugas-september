import api from './api';

export default {
    // Semua laporan yang saya buat + yang ditujukan ke saya
    getAll() {
        return api.get('/laporan');
    },

    // Ajukan laporan ke user lain
    // data: { target_id, tipe_laporan, deskripsi, peminjaman_id?, barang_baru_id? }
    store(data) {
        return api.post('/laporan', data);
    },

    // Target menyetujui / menolak
    // data: { status: 'disetujui' | 'ditolak', alasan_respons?: string }
    respond(id, data) {
        return api.patch(`/laporan/${id}/respond`, data);
    },

    // Pelapor membatalkan pengajuan (hanya saat pending)
    destroy(id) {
        return api.delete(`/laporan/${id}`);
    },

    // Daftar user untuk dipilih sebagai tujuan
    getTargets() {
        return api.get('/users');
    },

    // Admin: monitoring semua laporan
    getAllAdmin(params = {}) {
        return api.get('/admin/laporan', { params });
    }
};
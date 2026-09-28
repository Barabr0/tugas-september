import api from './api';

export function getBarangTersedia() {
  return api.get('/barang');
}

export function getPeminjaman() {
  return api.get('/peminjaman');
}

export function ajukanPinjamBarang(data) {
  return api.post('/peminjaman', data);
}

export function setujuiPeminjaman(id) {
  return api.patch(`/peminjaman/${id}/setujui`);
}

export function tolakPeminjaman(id) {
  return api.patch(`/peminjaman/${id}/tolak`);
}

export function batalkanPeminjaman(id) {
  return api.patch(`/peminjaman/${id}/batalkan`);
}

export function aktifkanPeminjaman(id) {
  return api.patch(`/peminjaman/${id}/aktifkan`);
}

export function kembalikanBarang(peminjamanId, barangId) {
  return api.patch(`/peminjaman/${peminjamanId}/kembalikan/${barangId}`);
}
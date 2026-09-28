import api from './api';

export function getPeminjamanUang() {
  return api.get('/peminjaman-uang');
}

export function ajukanPinjamUang(data) {
  return api.post('/peminjaman-uang', data);
}

export function setujuiPeminjamanUang(id) {
  return api.patch(`/peminjaman-uang/${id}/setujui`);
}

export function tolakPeminjamanUang(id) {
  return api.patch(`/peminjaman-uang/${id}/tolak`);
}

export function batalkanPeminjamanUang(id) {
  return api.patch(`/peminjaman-uang/${id}/batalkan`);
}

export function aktifkanPeminjamanUang(id) {
  return api.patch(`/peminjaman-uang/${id}/aktifkan`);
}

export function lunasPeminjamanUang(id) {
  return api.patch(`/peminjaman-uang/${id}/lunas`);
}
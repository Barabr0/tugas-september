import api from './api';
import { useToast } from 'vue-toastification';

const toast = useToast();

export async function register(data) {
  try {
    const response = await api.post('/register', data);
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.data));
    localStorage.setItem('role', response.data.data.role);
    toast.success('Registrasi berhasil!');
    return response;
  } catch (error) {
    const pesan = error.response?.data?.message || 'Registrasi gagal.';
    toast.error(pesan);
    throw error;
  }
}

export async function login(data) {
  try {
    const response = await api.post('/login', data);
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.data));
    localStorage.setItem('role', response.data.data.role);
    toast.success('Login berhasil!');
    return response;
  } catch (error) {
    const pesan = error.response?.data?.message || 'Login gagal. Periksa email dan password.';
    toast.error(pesan);
    throw error;
  }
}

export async function logout() {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout API gagal:', error);
  } finally {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('role');
    toast.info('Anda telah logout.');
  }
}

export function getProfile() {
  return api.get('/profile');
}
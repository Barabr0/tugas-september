import api from './api';

export function register(data) {
  return api.post('/register', data);
}

export function login(data) {
  return api.post('/login', data);
}

export function logout() {
  return api.post('/logout');
}

export function getProfile() {
  return api.get('/profile');
}
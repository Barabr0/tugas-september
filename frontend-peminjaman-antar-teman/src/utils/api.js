import axios from 'axios'

// Buat instance axios dengan base URL API Laravel kita
const api = axios.create({
  baseURL: 'http://192.168.1.4:8000/api',
})

// Interceptor = "petugas" yang berjalan sebelum setiap request dikirim
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  // Jika token ada, tambahkan ke header Authorization
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export default api
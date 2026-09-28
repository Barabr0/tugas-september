import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Auth/login.vue';
import Register from '../views/Auth/register.vue';
import Dashboard from '../views/Dashboard.vue';
import index_peminjaman from '../views/crud_pinjam/index_peminjaman.vue';
import pinjaman_saya from '../views/pinjaman_saya.vue';
import edit_pinjaman from '../views/crud_pinjam/edit.vue';
import barang from '../views/barang/index.vue';
import create_barang from '../views/barang/create.vue';
import bantuan from '../views/bantuan/index.vue';
import bank from '../views/bank/index.vue';
import laporan from '../views/laporan/index.vue';

// Admin
import Admindashboard from '../views/Admin/dashboardAdmin.vue';
import index_user from '../views/Admin/user/index.vue';
import index_barang from '../views/Admin/barang/index.vue';
import index_kategori from '../views/Admin/kategori/index.vue';
import permintaan_bantuan from '../views/Admin/bantuan/index.vue';

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },

  // Routes User Biasa (meta: requiresAuth)
  { path: '/dashboard', name: 'Dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/peminjaman', name: 'index_peminjaman', component: index_peminjaman, meta: { requiresAuth: true } },
  { path: '/pinjaman_saya', name: 'pinjaman_saya', component: pinjaman_saya, meta: { requiresAuth: true } },
  { path: '/edit_pinjaman/:id', name: 'edit_pinjaman', component: edit_pinjaman, meta: { requiresAuth: true } },
  { path: '/barang', name: 'barang', component: barang, meta: { requiresAuth: true } },
  { path: '/barang/tambah', name: 'barang_tambah', component: create_barang, meta: { requiresAuth: true } },
  { path: '/bantuan', name: 'bantuan', component: bantuan, meta: { requiresAuth: true } },
  { path: '/bank', name: 'bank', component: bank, meta: { requiresAuth: true } },
  { path: '/laporan', name: 'laporan', component: laporan, meta: { requiresAuth: true } },
  {
    path: '/teman',
    name: 'ListTeman',
    component: () => import('../views/Teman/ListTeman.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/teman/:id',
    name: 'DetailTeman',
    component: () => import('../views/Teman/DetailTeman.vue'),
    meta: { requiresAuth: true }
  },

  // Routes Admin (meta: requiresAuth & requiresAdmin)
  { path: '/admin/dashboard', name: 'Admindashboard', component: Admindashboard, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/user', name: 'index_user', component: index_user, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/barang', name: 'index_barang', component: index_barang, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/kategori', name: 'index_kategori', component: index_kategori, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/bantuan', name: 'permintaan_bantuan', component: permintaan_bantuan, meta: { requiresAuth: true, requiresAdmin: true } }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// ROUTE GUARD
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const role = localStorage.getItem('role');

  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else if (to.meta.requiresAdmin && role !== 'admin') {
    next('/dashboard');
  } else if (to.path === '/dashboard' && role === 'admin') {
    next('/admin/dashboard');
  } else {
    next();
  }
});

export default router;
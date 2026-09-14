import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Auth/login.vue';
import Register from '../views/Auth/register.vue';
import Dashboard from '../views/Dashboard.vue';
import index_peminjaman from '../views/crud_pinjam/index_peminjaman.vue'; 
import pinjaman_saya from '../views/pinjaman_saya.vue';
import edit_pinjaman from '../views/crud_pinjam/edit.vue';

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard , meta:{ requireAuth: true}},
  { path: '/peminjaman', name: 'index_peminjaman', component: index_peminjaman , meta:{ requireAuth: true}},
  { path: '/pinjaman_saya', name: 'pinjaman_saya', component: pinjaman_saya , meta:{ requireAuth: true}},
  { path: '/edit_pinjaman/:id', name: 'edit_pinjaman', component: edit_pinjaman, meta: { requireAuth: true } },
  {}
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
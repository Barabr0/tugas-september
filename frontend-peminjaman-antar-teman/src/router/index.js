import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Dashboard from '../views/Dashboard.vue';
import Login from '../views/Auth/login.vue';

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/Dashboard', name: 'Dashboard', component: Dashboard },
  { path: '/Login', name: 'Login', component: Login }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
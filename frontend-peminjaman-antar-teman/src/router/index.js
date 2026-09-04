import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Auth/login.vue';
import Register from '../views/Auth/register.vue';
import Dashboard from '../views/Dashboard.vue';

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/Login', name: 'Login', component: Login },
  { path: '/Register', name: 'Register', component: Register },
  { path: '/Dashboard', name: 'dDashboard', component: Dashboard }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
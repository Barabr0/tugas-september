import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

const options = {
  position: 'top-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false
};

createApp(App).use(router).use(Toast, options).mount('#app')
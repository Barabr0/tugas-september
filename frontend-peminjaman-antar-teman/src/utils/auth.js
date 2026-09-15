import { login } from '@/utils/auth'; // Import fungsi login yang baru

export default {
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      loading: false
    };
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      try {
        // Panggil fungsi login dari utils/auth.js
        await login(this.form);
        
        // Ambil role dari localStorage untuk menentukan redirect
        const role = localStorage.getItem('role');
        
        if (role === 'admin') {
          this.$router.push('/admin/dashboard');
        } else {
          this.$router.push('/dashboard');
        }

      } catch (error) {
        // Error dan Toast sudah ditangani di auth.js, jadi di sini kosong
        // atau kamu bisa taruh this.loading = false di finally
      } finally {
        this.loading = false;
      }
    }
  }
};
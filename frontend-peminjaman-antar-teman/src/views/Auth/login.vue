<template>
  <div class="login-wrapper">
    <div class="login-card">
      <!-- Header / Logo -->
      <div class="login-header">
        <div class="brand-icon">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2>Masuk ke PinjamTeman</h2>
        <p>Kelola catatan hutang dan pinjaman barangmu lagi.</p>
      </div>

      <!-- Form Login -->
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email / Nomor HP</label>
          <div class="input-icon-wrapper">
            <i class="bi bi-envelope icon-input"></i>
            <input 
              id="email"
              v-model="email" 
              type="text" 
              placeholder="Masukkan email atau no. HP" 
              required
            />
          </div>
        </div>

        <div class="form-group">
          <div class="label-row">
            <label for="password">Kata Sandi</label>
            <a href="#" class="forgot-link">Lupa sandi?</a>
          </div>
          <div class="input-icon-wrapper">
            <i class="bi bi-key icon-input"></i>
            <input 
              id="password"
              v-model="password" 
              :type="showPassword ? 'text' : 'password'" 
              placeholder="Masukkan kata sandi" 
              required
            />
            <i 
              :class="['bi', showPassword ? 'bi-eye-slash' : 'bi-eye', 'toggle-password']" 
              @click="showPassword = !showPassword"
            ></i>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          <span>Masuk Sekarang</span>
          <i class="bi bi-arrow-right-short"></i>
        </button>
      </form>

      <!-- Footer / Switch to Register -->
      <div class="login-footer">
        <p>Belum punya akun? <router-link to="/register" class="register-link">Daftar Akun Baru</router-link></p>
        <router-link to="/" class="back-home">
          <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import authService from '../../utils/auth';

export default {
  name: 'LoginView',
  data() {
    return {
      form: {
        email: '',
        password: '',
        showPassword: false
      },
      errorMessage: '',
      loading: false
    };
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      this.errorMessage = '';

      try {
        const response = await login({
          email: this.email,
          password: this.password
        });
        // Simpan token ke localStorage
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        // Redirect ke dashboard
        this.$router.push('/dashboard');
      } catch (error) {
        if (error.response && error.response.status === 401) {
          this.errorMessage = 'Login gagal. Silakan periksa email dan kata sandi Anda.';
        } else {
          this.errorMessage = 'server gagal. Silakan coba lagi nanti.';
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #003049;
  padding: 20px;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.login-card {
  background: white;
  width: 100%;
  max-width: 400px;
  border-radius: 16px;
  padding: 32px 28px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.login-header {
  text-align: center;
  margin-bottom: 28px;
}

.brand-icon {
  width: 50px;
  height: 50px;
  background-color: rgba(247, 127, 0, 0.15);
  color: #F77F00;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin: 0 auto 12px auto;
}

.login-header h2 {
  color: #003049;
  font-size: 22px;
  margin: 0 0 6px 0;
}

.login-header p {
  color: #718096;
  font-size: 13px;
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  font-weight: 700;
  color: #2D3748;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.forgot-link {
  font-size: 11px;
  color: #F77F00;
  text-decoration: none;
  font-weight: 600;
}

.input-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.icon-input {
  position: absolute;
  left: 12px;
  color: #A0AEC0;
  font-size: 16px;
}

.toggle-password {
  position: absolute;
  right: 12px;
  color: #A0AEC0;
  font-size: 16px;
  cursor: pointer;
}

.input-icon-wrapper input {
  width: 100%;
  padding: 10px 38px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
  box-sizing: border-box;
}

.input-icon-wrapper input:focus {
  border-color: #F77F00;
}

.btn-submit {
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 8px;
}

.login-footer {
  text-align: center;
  margin-top: 24px;
  border-top: 1px solid #EDF2F7;
  padding-top: 16px;
  font-size: 12px;
  color: #718096;
}

.register-link {
  color: #003049;
  font-weight: 700;
  text-decoration: none;
}

.back-home {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 12px;
  color: #A0AEC0;
  text-decoration: none;
  font-size: 11px;
}

.back-home:hover {
  color: #003049;
}
</style>
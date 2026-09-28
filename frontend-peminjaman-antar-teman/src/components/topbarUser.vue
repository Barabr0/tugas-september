<template>
  <header class="topbar">
    <div class="topbar-right">
      <!-- Profil User -->
      <div class="user-profile-top">
        <div class="avatar">{{ userInitial }}</div>
        <div class="profile-info">
          <span class="name">{{ userData.name || 'User' }}</span>
          <span class="role">{{ userData.email || '-' }}</span>
        </div>
        <button class="btn-logout-top" title="Keluar" @click="handleLogout">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: 'TopbarUser',
  props: {
    title: { type: String, default: 'Dashboard' },
    subtitle: { type: String, default: '' },
    showBackButton: { type: Boolean, default: false },
    backUrl: { type: String, default: '/dashboard' },
    showSearch: { type: Boolean, default: false },
    searchQuery: { type: String, default: '' },
    searchPlaceholder: { type: String, default: 'Cari...' }
  },
  data() {
    return {
      userData: { name: '', email: '' }
    };
  },
  computed: {
    userInitial() {
      return this.userData.name ? this.userData.name.charAt(0).toUpperCase() : 'U';
    }
  },
  mounted() {
    this.loadUserProfile();
  },
  methods: {
    loadUserProfile() {
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          this.userData = JSON.parse(userStr);
        } catch (e) {
          console.error('Gagal membaca profil user', e);
        }
      }
    },
    handleLogout() {
      if (confirm('Yakin ingin keluar?')) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('user');
        this.$router.push('/login');
      }
    }
  }
};
</script>

<style scoped>
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.header-title-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.btn-back {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background-color: #ffffff;
  border: 1px solid #E2E8F0;
  color: #003049;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 16px;
  transition: all 0.2s ease;
}

.btn-back:hover {
  background-color: #003049;
  color: #ffffff;
}

.topbar-left h1 {
  margin: 0;
  font-size: 24px;
  color: #003049;
  font-weight: 800;
}

.topbar-left p {
  margin: 0;
  color: #718096;
  font-size: 13px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 14px;
  color: #A0AEC0;
  font-size: 14px;
}

.search-box input {
  padding: 10px 14px 10px 38px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 13px;
  outline: none;
  width: 240px;
  background-color: #ffffff;
}

.search-box input:focus {
  border-color: #F77F00;
}

.user-profile-top {
  display: flex;
  align-items: center;
  gap: 10px;
  background-color: #ffffff;
  padding: 6px 12px;
  border-radius: 30px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-color: #FCBF49;
  color: #003049;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 14px;
}

.profile-info {
  display: flex;
  flex-direction: column;
}

.profile-info .name {
  font-size: 12px;
  font-weight: 700;
  color: #003049;
  line-height: 1.2;
}

.profile-info .role {
  font-size: 10px;
  color: #718096;
}

.btn-logout-top {
  background: none;
  border: none;
  color: #A0AEC0;
  font-size: 16px;
  cursor: pointer;
  padding: 4px;
  margin-left: 2px;
  transition: color 0.2s ease;
}

.btn-logout-top:hover {
  color: #D62828;
}
</style>
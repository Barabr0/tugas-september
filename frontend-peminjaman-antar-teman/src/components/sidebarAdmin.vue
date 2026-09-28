<template>
  <aside class="sidebar">
    <!-- Brand / Logo -->
    <div class="sidebar-brand">
      <i class="bi bi-shield-lock-fill brand-icon"></i>
      <h2>JaMan <span>Admin</span></h2>
    </div>

    <!-- Menu Navigasi -->
    <nav class="sidebar-menu">
      <div class="menu-label">UTAMA</div>
      
      <router-link to="/admin/dashboard" class="menu-item" active-class="active">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard</span>
      </router-link>

      <router-link to="/admin/user" class="menu-item" active-class="active">
        <i class="bi bi-person-fill"></i>
        <span>Kelola User</span>
      </router-link>

      <router-link to="/admin/kategori" class="menu-item" active-class="active">
        <i class="bi bi-tags-fill"></i>
        <span>Kategori Barang</span>
      </router-link>

      <div class="menu-label">SISTEM</div>
      
      <router-link to="/admin/bantuan" class="menu-item" active-class="active">
        <i class="bi bi-life-preserver"></i>
        <span>Permintaan Bantuan</span>
        <span v-if="bantuanCount > 0" class="badge-dot-menu">{{ bantuanCount }}</span>
      </router-link>
    </nav>

    <!-- Footer Profil Admin -->
    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="avatar">{{ userInitial }}</div>
        <div class="profile-info">
          <span class="name">{{ adminData.name || 'Admin' }}</span>
          <span class="role">{{ adminData.email || '-' }}</span>
        </div>
      </div>
      <button class="btn-logout" title="Keluar" @click="handleLogout">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </div>
  </aside>
</template>

<script>
import adminApi from '@/utils/admin';

export default {
  name: 'SidebarAdmin',
  data() {
    return {
      adminData: {
        name: '',
        email: ''
      },
      bantuanCount: 0
    };
  },
  computed: {
    userInitial() {
      return this.adminData.name ? this.adminData.name.charAt(0).toUpperCase() : 'A';
    }
  },
  mounted() {
    this.loadAdminProfile();
    this.fetchBantuanCount();
  },
  methods: {
    loadAdminProfile() {
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          this.adminData = JSON.parse(userStr);
        } catch (e) {
          console.error('Gagal membaca profil admin', e);
        }
      }
    },
    async fetchBantuanCount() {
      try {
        const res = await adminApi.getBantuanRequests();
        const data = res.data.data || res.data || [];
        this.bantuanCount = data.length;
      } catch (e) {
        console.error('Gagal memuat jumlah bantuan', e);
      }
    },
    handleLogout() {
      if (confirm('Yakin ingin keluar dari panel admin?')) {
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
.sidebar {
  width: 260px;
  background-color: #003049;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  padding: 24px 16px;
  flex-shrink: 0;
  min-height: 100vh;
  box-sizing: border-box;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 12px 24px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-icon {
  font-size: 24px;
  color: #F77F00;
}

.sidebar-brand h2 {
  font-size: 20px;
  margin: 0;
  color: #ffffff;
  font-weight: 800;
}

.sidebar-brand span {
  font-size: 12px;
  color: #FCBF49;
  font-weight: 600;
  background: rgba(252, 191, 73, 0.15);
  padding: 2px 8px;
  border-radius: 6px;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 20px;
  flex: 1;
}

.menu-label {
  font-size: 10px;
  font-weight: 700;
  color: #A0AEC0;
  letter-spacing: 1px;
  padding: 12px 12px 6px 12px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  color: #EAE2B7;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.2s ease;
  position: relative;
}

.menu-item i {
  font-size: 16px;
}

.menu-item:hover, .menu-item.active {
  background-color: #F77F00;
  color: #ffffff;
}

.badge-dot-menu {
  margin-left: auto;
  background: #D62828;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 700;
}

.sidebar-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-color: #F77F00;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

.profile-info {
  display: flex;
  flex-direction: column;
}

.profile-info .name {
  font-size: 12px;
  font-weight: 700;
  color: white;
}

.profile-info .role {
  font-size: 10px;
  color: #A0AEC0;
}

.btn-logout {
  background: none;
  border: none;
  color: #EAE2B7;
  font-size: 18px;
  cursor: pointer;
  padding: 4px;
  transition: color 0.2s;
}

.btn-logout:hover {
  color: #D62828;
}
</style>
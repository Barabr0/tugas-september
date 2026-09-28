<template>
  <div class="admin-wrapper">
    <!-- 1. Sidebar Admin -->
    <SidebarAdmin />

    <!-- 2. Main Content Area -->
    <main class="main-content">
      <!-- Top Header Nav -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>Overview Sistem</h1>
          <p>Pantau statistik harian dan aktivitas pengguna PinjamTeman.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Cari user, barang, transaksi..." />
          </div>
        </div>
      </header>

      <!-- Summary Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon users">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-info">
            <span class="stat-title">Total Pengguna</span>
            <h3 class="stat-value">{{ users.length }}</h3>
            <span class="stat-trend neutral">Terdaftar di sistem</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon items">
            <i class="bi bi-box-seam-fill"></i>
          </div>
          <div class="stat-info">
            <span class="stat-title">Barang Terdaftar</span>
            <h3 class="stat-value">{{ barangs.length }}</h3>
            <span class="stat-trend neutral">Total barang aktif</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon active-loans">
            <i class="bi bi-life-preserver"></i>
          </div>
          <div class="stat-info">
            <span class="stat-title">Request Bantuan</span>
            <h3 class="stat-value">{{ bantuanRequests.length }}</h3>
            <span class="stat-trend neutral">Menunggu review</span>
          </div>
        </div>
      </div>

      <!-- Content Grid: Tabel Bantuan & Barang -->
      <div class="dashboard-grid">
        <!-- Tabel Permintaan Bantuan User -->
        <div class="card-section main-table-card">
          <div class="section-header">
            <div>
              <h3>Permintaan Bantuan User</h3>
              <p>User yang meminta admin untuk edit/hapus/batalkan sesuatu.</p>
            </div>
          </div>

          <div class="table-wrapper">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Tipe</th>
                  <th>Aksi Diminta</th>
                  <th>Alasan</th>
                  <th>Aksi Admin</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingBantuan">
                  <td colspan="5" class="empty-state">Memuat data bantuan...</td>
                </tr>
                <tr v-else-if="bantuanRequests.length === 0">
                  <td colspan="5" class="empty-state">Tidak ada permintaan bantuan saat ini.</td>
                </tr>
                <tr v-for="req in bantuanRequests" :key="req.id">
                  <td class="fw-bold">{{ req.user?.name || 'Unknown' }}</td>
                  <td>{{ req.tipe_request }}</td>
                  <td><span class="tag-status menunggu">{{ req.aksi_diminta }}</span></td>
                  <td style="max-width: 200px;">{{ req.alasan }}</td>
                  <td>
                    <button @click="handleProcessBantuan(req.id)" class="btn-action btn-edit">
                      Proses / Selesai
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Quick System Status / Barang Sistem -->
        <div class="card-section side-card">
          <div class="section-header">
            <h3>Barang Sistem Terbaru</h3>
          </div>

          <div class="user-list">
            <div v-if="loadingBarangs" class="empty-state">Memuat barang...</div>
            <div v-else-if="barangs.length === 0" class="empty-state">Belum ada barang.</div>
            <div v-for="barang in barangs.slice(0, 5)" :key="barang.id" class="user-item">
              <div class="user-avatar">
                <i class="bi bi-box"></i>
              </div>
              <div class="user-detail">
                <span class="user-name">{{ barang.nama_barang }}</span>
                <span class="user-email">Pemilik: {{ barang.pemilik?.name || '-' }}</span>
              </div>
              <span class="user-time">{{ barang.status }}</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import api from '@/utils/api'; 
import adminApi from '@/utils/admin'; 
import SidebarAdmin from '@/components/sidebarAdmin.vue';

export default {
  components: {
    SidebarAdmin
  },
  name: 'AdminDashboardView',
  data() {
    return {
      // Tambahan untuk menyimpan data admin yang login
      adminData: {
        name: '',
        email: ''
      },
      users: [],
      barangs: [],
      bantuanRequests: [],
      loadingUsers: false,
      loadingBarangs: false,
      loadingBantuan: false
    };
  },
  computed: {
    // Ambil huruf pertama untuk inisial avatar
    userInitial() {
      if (this.adminData.name) {
        return this.adminData.name.charAt(0).toUpperCase();
      }
      return 'A'; // Default jika tidak ada nama
    }
  },
  mounted() {
    this.loadAdminProfile(); // Panggil fungsi ambil profil
    this.fetchAdminData();
  },
  methods: {
    loadAdminProfile() {
      // Ambil data user dari localStorage (disimpan dalam bentuk string JSON saat login)
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          this.adminData = JSON.parse(userStr);
        } catch (e) {
          console.error('Gagal parse data user', e);
        }
      }
    },

    async fetchAdminData() {
      this.loadingUsers = true;
      this.loadingBarangs = true;
      this.loadingBantuan = true;

      try {
        // Fetch Users
        const resUsers = await adminApi.getAllUsers();
        this.users = resUsers.data.data || resUsers.data || [];
      } catch (error) {
        console.error('Gagal ambil users', error);
      } finally {
        this.loadingUsers = false;
      }

      try {
        // Fetch Barangs
        const resBarangs = await adminApi.getAllBarangs();
        this.barangs = resBarangs.data.data || resBarangs.data || [];
      } catch (error) {
        console.error('Gagal ambil barangs', error);
      } finally {
        this.loadingBarangs = false;
      }

      try {
        // Fetch Bantuan Requests
        const resBantuan = await adminApi.getBantuanRequests();
        this.bantuanRequests = resBantuan.data.data || resBantuan.data || [];
      } catch (error) {
        console.error('Gagal ambil bantuan', error);
      } finally {
        this.loadingBantuan = false;
      }
    },
    
    async handleProcessBantuan(id) {
      if (!confirm('Tandai request ini sudah diproses?')) return;
      try {
        await adminApi.processBantuan(id);
        this.$toast.success('Request ditandai selesai');
        // Refresh data bantuan
        const resBantuan = await adminApi.getBantuanRequests();
        this.bantuanRequests = resBantuan.data.data || resBantuan.data || [];
      } catch (error) {
        this.$toast.error('Gagal memproses request');
      }
    },

    handleLogout() {
      if (confirm('Yakin ingin keluar dari panel admin?')) {
        // Hapus semua data sesi dari localStorage
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('user');
        
        // Arahkan ke halaman login
        this.$router.push('/login');
      }
    }
  }
};
</script>

<style scoped>
/* Full Screen Admin Layout */
.admin-wrapper {
  display: flex;
  min-height: 100vh;
  background-color: #F8F9FA;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #2D3748;
}

/* 1. Sidebar Styling */
.sidebar {
  width: 260px;
  background-color: #003049;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  padding: 24px 16px;
  flex-shrink: 0;
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

/* 2. Main Content Styling */
.main-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.topbar-left h1 {
  margin: 0 0 4px 0;
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
  left: 12px;
  color: #A0AEC0;
  font-size: 14px;
}

.search-box input {
  padding: 10px 14px 10px 36px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 13px;
  outline: none;
  width: 240px;
  background-color: #ffffff;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 28px;
}

.stat-card {
  background: #ffffff;
  padding: 20px;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
}

.stat-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.users { background: rgba(0, 48, 73, 0.1); color: #003049; }
.stat-icon.active-loans { background: rgba(247, 127, 0, 0.15); color: #F77F00; }
.stat-icon.items { background: rgba(123, 31, 162, 0.15); color: #7B1FA2; }

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-title {
  font-size: 12px;
  color: #718096;
  font-weight: 600;
}

.stat-value {
  margin: 4px 0;
  font-size: 20px;
  color: #003049;
  font-weight: 800;
}

.stat-trend {
  font-size: 11px;
  font-weight: 700;
}

.stat-trend.positive { color: #2E7D32; }
.stat-trend.neutral { color: #A0AEC0; }

/* Dashboard Sections */
.dashboard-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.card-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.section-header h3 {
  margin: 0 0 4px 0;
  font-size: 16px;
  color: #003049;
}

.section-header p {
  margin: 0;
  font-size: 12px;
  color: #718096;
}

/* Custom Table */
.custom-table {
  width: 100%;
  border-collapse: collapse;
}

.custom-table th {
  padding: 10px 12px;
  font-size: 11px;
  color: #718096;
  text-transform: uppercase;
  border-bottom: 2px solid #EDF2F7;
  text-align: left;
}

.custom-table td {
  padding: 12px;
  font-size: 13px;
  border-bottom: 1px solid #F7FAFC;
}

.empty-state {
  text-align: center;
  color: #A0AEC0;
  padding: 20px;
  font-size: 13px;
}

.fw-bold { font-weight: 700; color: #003049; }

.tag-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 700;
}

.tag-status.menunggu { background: #FFF3E0; color: #F77F00; }

.btn-action {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn-edit { background-color: rgba(0, 48, 73, 0.1); color: #003049; }
.btn-edit:hover { background-color: rgba(0, 48, 73, 0.2); }

/* Side User List */
.user-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background-color: #003049;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
}

.user-detail {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.user-name {
  font-size: 13px;
  font-weight: 700;
  color: #003049;
}

.user-email {
  font-size: 11px;
  color: #718096;
}

.user-time {
  font-size: 10px;
  color: #A0AEC0;
  background: #EDF2F7;
  padding: 4px 8px;
  border-radius: 12px;
}
</style>
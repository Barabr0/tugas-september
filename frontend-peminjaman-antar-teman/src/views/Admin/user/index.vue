<template>
  <div class="admin-wrapper">
    <sidebar-admin />
    <!-- 2. Main Content Area -->
    <main class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-left">
          <!-- Wrapper untuk menyatukan tombol kembali dan judul secara sejajar -->
          <div class="header-title-wrapper">
            <h1>Kelola Pengguna</h1>
          </div>
          <p>Daftar seluruh akun terdaftar dan penilaian skor reputasi transaksi.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari nama, email, atau no HP..." 
            />
          </div>
        </div>
      </header>

      <!-- Main Card / Table Wrapper -->
      <div class="card-section">
        <div class="section-header">
          <div class="title-info">
            <h3>Daftar User Terdaftar</h3>
            <span class="badge-count">{{ filteredUsers.length }} User</span>
          </div>
        </div>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 60px;" class="text-center">No</th>
                <th style="width: 220px;">Nama</th>
                <th style="width: 220px;">Email</th>
                <th style="width: 160px;">No HP</th>
                <th style="width: 160px;" class="text-center">Skor Reputasi</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- State Loading -->
              <tr v-if="loading">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data pengguna...</p>
                </td>
              </tr>

              <!-- State Kosong -->
              <tr v-else-if="filteredUsers.length === 0">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-person-x"></i>
                  <p>Tidak ada data pengguna yang ditemukan.</p>
                </td>
              </tr>

              <!-- Data Loop -->
              <tr v-for="(user, index) in filteredUsers" :key="user.id" v-else>
                <td class="text-center id-col">#{{ index + 1 }}</td>
                <td>
                  <div class="user-profile-cell">
                    <div class="user-avatar-small">{{ getInitial(user.name) }}</div>
                    <span class="user-name">{{ user.name }}</span>
                  </div>
                </td>
                <td class="email-col">{{ user.email }}</td>
                <td class="phone-col">
                  <i class="bi bi-telephone"></i> {{ user.no_hp || '-' }}
                </td>
                <td class="text-center">
                  <div class="reputation-wrapper">
                    <span :class="['reputation-badge', getReputationClass(user.skor_reputasi)]">
                      <i class="bi bi-star-fill"></i> {{ user.skor_reputasi || 0 }} / 100
                    </span>
                  </div>
                </td>
                <td class="action-col">
                  <div class="action-buttons">
                    <button class="btn-icon edit" title="Edit User" @click="editUser(user)">
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn-icon delete" title="Hapus User" @click="deleteUser(user.id)">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import adminApi from '@/utils/admin';
import SidebarAdmin from '@/components/sidebarAdmin.vue';

export default {
  components: {
    SidebarAdmin
  },
  name: 'AdminUsersView',
  data() {
    return {
      searchQuery: '',
      loading: false,
      users: [] // Data akan diisi dari API
    };
  },
  computed: {
    filteredUsers() {
      if (!this.searchQuery) return this.users;
      const query = this.searchQuery.toLowerCase();
      return this.users.filter(u => 
        u.name.toLowerCase().includes(query) ||
        u.email.toLowerCase().includes(query) ||
        (u.no_hp && u.no_hp.includes(query))
      );
    }
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      try {
        // Panggil API get users dari utils/admin.js
        const response = await adminApi.getAllUsers();
        this.users = response.data.data || response.data || [];
      } catch (error) {
        console.error('Gagal memuat data user:', error);
        this.$toast.error('Gagal memuat data pengguna.');
      } finally {
        this.loading = false;
      }
    },
    getInitial(name) {
      return name ? name.charAt(0).toUpperCase() : 'U';
    },
    getReputationClass(score) {
      if (score >= 85) return 'sangat-baik';
      if (score >= 70) return 'baik';
      if (score >= 50) return 'cukup';
      return 'buruk';
    },
    editUser(user) {
      // Untuk sementara toast, nanti bisa diarahkan ke halaman edit/modal
      this.$toast.info(`Fitur edit user: ${user.name} akan segera hadir.`);
    },
    async deleteUser(id) {
      if (!confirm('Apakah Anda yakin ingin menghapus user ini secara permanen?')) return;
      
      try {
        // Asumsi endpoint hapus user pakai endpoint standar /users/{id}
        await adminApi.deleteUser(id); 
        this.users = this.users.filter(u => u.id !== id);
        this.$toast.success('User berhasil dihapus.');
      } catch (error) {
        console.error('Gagal menghapus user:', error);
        this.$toast.error('Gagal menghapus user.');
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

/* Main Content Styling */
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

/* CSS Tambahan untuk Penataan Judul & Tombol Kembali */
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
  width: 280px;
  background-color: #ffffff;
}

.search-box input:focus {
  border-color: #F77F00;
}

/* Card Section */
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
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #EDF2F7;
}

.title-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-header h3 {
  margin: 0;
  font-size: 18px;
  color: #003049;
}

.badge-count {
  background-color: #F0F4F8;
  color: #003049;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
}

.btn-primary-add {
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s;
}

.btn-primary-add:hover {
  background-color: #E07300;
}

/* Table Design */
.table-wrapper {
  overflow-x: auto;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.custom-table th {
  padding: 12px 14px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #718096;
  border-bottom: 2px solid #EDF2F7;
}

.custom-table td {
  padding: 14px;
  border-bottom: 1px solid #F7FAFC;
  font-size: 14px;
  color: #2D3748;
  vertical-align: middle;
}

.id-col {
  color: #A0AEC0;
  font-weight: 600;
  font-size: 13px;
}

.user-profile-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar-small {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: #003049;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 12px;
}

.user-name {
  font-weight: 700;
  color: #003049;
}

.email-col {
  color: #4A5568;
  font-size: 13px;
}

.phone-col {
  color: #718096;
  font-size: 13px;
}

.phone-col i {
  color: #A0AEC0;
  margin-right: 4px;
}

/* Reputation Badge Styling */
.reputation-wrapper {
  display: flex;
  justify-content: center;
}

.reputation-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.reputation-badge.sangat-baik {
  background-color: #E8F5E9;
  color: #2E7D32;
}

.reputation-badge.baik {
  background-color: #E3F2FD;
  color: #1565C0;
}

.reputation-badge.cukup {
  background-color: #FFF3E0;
  color: #E65100;
}

.reputation-badge.buruk {
  background-color: #FFEBEE;
  color: #C62828;
}

/* Action Buttons */
.action-col {
  vertical-align: middle;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.btn-icon {
  width: 34px;
  height: 34px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
  transition: transform 0.1s ease;
}

.btn-icon:hover {
  transform: translateY(-1px);
}

.btn-icon.edit { background-color: #FFF8E1; color: #F57F17; }
.btn-icon.delete { background-color: #FFEBEE; color: #C62828; }

.text-center { text-align: center; }

.empty-state {
  text-align: center;
  padding: 40px;
  color: #A0AEC0;
}

.empty-state i {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}
</style>
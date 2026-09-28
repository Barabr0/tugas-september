<template>
  <div class="admin-wrapper">
    <sidebar-admin />
    <main class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-left">
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
                <th style="width: 100px;" class="text-center">Role</th>
                <th style="width: 160px;" class="text-center">Skor Reputasi</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="7" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data pengguna...</p>
                </td>
              </tr>

              <tr v-else-if="filteredUsers.length === 0">
                <td colspan="7" class="empty-state">
                  <i class="bi bi-person-x"></i>
                  <p>Tidak ada data pengguna yang ditemukan.</p>
                </td>
              </tr>

              <tr v-else v-for="(user, index) in filteredUsers" :key="user.id">
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
                  <span :class="['role-badge', user.role === 'admin' ? 'admin' : 'user']">
                    {{ user.role || 'user' }}
                  </span>
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
                    <button class="btn-icon edit" title="Edit User" @click="openEditModal(user)">
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button
                      class="btn-icon delete"
                      :title="isSelf(user) ? 'Tidak bisa menghapus akun sendiri' : 'Hapus User'"
                      :disabled="isSelf(user)"
                      @click="deleteUser(user.id)"
                    >
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

    <!-- Modal Edit User -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>Edit Pengguna</h2>
          <p>Ubah data akun pengguna lalu simpan perubahan.</p>
        </div>

        <div class="form-body">
          <div class="form-group">
            <label>Nama</label>
            <input v-model="form.name" type="text" class="input-control" :class="{ invalid: errors.name }" />
            <small v-if="errors.name" class="error-text">{{ errors.name[0] }}</small>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" class="input-control" :class="{ invalid: errors.email }" />
            <small v-if="errors.email" class="error-text">{{ errors.email[0] }}</small>
          </div>

          <div class="form-group">
            <label>No HP</label>
            <input v-model="form.no_hp" type="text" class="input-control" :class="{ invalid: errors.no_hp }" />
            <small v-if="errors.no_hp" class="error-text">{{ errors.no_hp[0] }}</small>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Role</label>
              <select
                v-model="form.role"
                class="input-control"
                :disabled="selectedUser && isSelf(selectedUser)"
              >
                <option value="user">user</option>
                <option value="admin">admin</option>
              </select>
              <small v-if="errors.role" class="error-text">{{ errors.role[0] }}</small>
            </div>

            <div class="form-group">
              <label>Skor Reputasi (0-100)</label>
              <input
                v-model.number="form.skor_reputasi"
                type="number"
                min="0"
                max="100"
                class="input-control"
                :class="{ invalid: errors.skor_reputasi }"
              />
              <small v-if="errors.skor_reputasi" class="error-text">{{ errors.skor_reputasi[0] }}</small>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal" :disabled="saving">Batal</button>
            <button type="button" class="btn-save" @click="saveUser" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import adminApi from '@/utils/admin';
import SidebarAdmin from '@/components/sidebarAdmin.vue';

export default {
  name: 'AdminUsersView',
  components: {
    SidebarAdmin
  },
  data() {
    return {
      searchQuery: '',
      loading: false,
      saving: false,
      showModal: false,
      selectedUser: null,
      currentUserId: null,
      errors: {},
      form: {
        name: '',
        email: '',
        no_hp: '',
        role: 'user',
        skor_reputasi: 0
      },
      users: []
    };
  },
  computed: {
    filteredUsers() {
      if (!this.searchQuery) return this.users;
      const query = this.searchQuery.toLowerCase();
      return this.users.filter(u =>
        (u.name && u.name.toLowerCase().includes(query)) ||
        (u.email && u.email.toLowerCase().includes(query)) ||
        (u.no_hp && String(u.no_hp).includes(query))
      );
    }
  },
  mounted() {
    this.loadCurrentUser();
    this.fetchUsers();
  },
  methods: {
    loadCurrentUser() {
      try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        this.currentUserId = user.id || null;
      } catch (e) {
        this.currentUserId = null;
      }
    },
    isSelf(user) {
      return this.currentUserId !== null && user.id === this.currentUserId;
    },

    async fetchUsers() {
      this.loading = true;
      try {
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

    openEditModal(user) {
      this.selectedUser = user;
      this.errors = {};
      this.form = {
        name: user.name || '',
        email: user.email || '',
        no_hp: user.no_hp || '',
        role: user.role || 'user',
        skor_reputasi: user.skor_reputasi ?? 0
      };
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.selectedUser = null;
      this.errors = {};
    },

    async saveUser() {
      if (!this.selectedUser) return;
      this.saving = true;
      this.errors = {};

      try {
        const res = await adminApi.updateUser(this.selectedUser.id, this.form);
        const updated = res.data.data || { ...this.selectedUser, ...this.form };

        const idx = this.users.findIndex(u => u.id === this.selectedUser.id);
        if (idx !== -1) this.users.splice(idx, 1, { ...this.users[idx], ...updated });

        this.$toast.success('Data pengguna berhasil diperbarui.');
        this.closeModal();
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          this.$toast.error(error.response?.data?.message || 'Gagal memperbarui pengguna.');
        }
      } finally {
        this.saving = false;
      }
    },

    async deleteUser(id) {
      if (!confirm('Apakah Anda yakin ingin menghapus user ini secara permanen?')) return;

      try {
        await adminApi.deleteUser(id);
        this.users = this.users.filter(u => u.id !== id);
        this.$toast.success('User berhasil dihapus.');
      } catch (error) {
        console.error('Gagal menghapus user:', error);
        this.$toast.error(error.response?.data?.message || 'Gagal menghapus user.');
      }
    }
  }
};
</script>

<style scoped>
.admin-wrapper {
  display: flex;
  min-height: 100vh;
  background-color: #F8F9FA;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #2D3748;
}

.main-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
  min-width: 0;
}

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

.role-badge {
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}

.role-badge.admin { background: #FFF3E0; color: #E65100; }
.role-badge.user { background: #F0F4F8; color: #003049; }

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

.reputation-badge.sangat-baik { background-color: #E8F5E9; color: #2E7D32; }
.reputation-badge.baik { background-color: #E3F2FD; color: #1565C0; }
.reputation-badge.cukup { background-color: #FFF3E0; color: #E65100; }
.reputation-badge.buruk { background-color: #FFEBEE; color: #C62828; }

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

.btn-icon:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn-icon:disabled {
  opacity: 0.4;
  cursor: not-allowed;
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

.spin {
  display: inline-block;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 48, 73, 0.4);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 24px;
}

.modal-content {
  width: 100%;
  max-width: 480px;
}

.form-card {
  background: #ffffff;
  padding: 28px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.form-header h2 {
  color: #003049;
  margin: 0 0 4px 0;
  font-size: 20px;
}

.form-header p {
  color: #718096;
  font-size: 13px;
  margin: 0 0 20px 0;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  font-weight: 700;
  color: #003049;
}

.input-control {
  padding: 10px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
  width: 100%;
  box-sizing: border-box;
  font-family: inherit;
  background: #ffffff;
}

.input-control:focus {
  border-color: #F77F00;
}

.input-control.invalid {
  border-color: #C62828;
}

.input-control:disabled {
  background: #F8F9FA;
  cursor: not-allowed;
}

.error-text {
  color: #C62828;
  font-size: 11px;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.btn-save {
  flex: 1;
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  font-size: 13px;
}

.btn-cancel {
  background-color: transparent;
  color: #718096;
  border: 1px solid #CBD5E0;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 13px;
}

.btn-save:disabled,
.btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
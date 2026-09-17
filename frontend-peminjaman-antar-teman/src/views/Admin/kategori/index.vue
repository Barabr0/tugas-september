
<template>
  <div class="admin-wrapper">
    <!-- 1. Sidebar Navigasi User -->
    <sidebar-admin />

    <!-- 2. Main Content Area -->
    <main class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-left">
          <div class="header-title-wrapper">
            <h1>Daftar Kategori</h1>
          </div>
          <p>Kelola kategori pengelompokan barang milik Anda.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari kategori..." 
            />
          </div>
        </div>
      </header>

      <!-- Main Card / Table Wrapper -->
      <div class="card-section">
        <div class="section-header">
          <div class="title-info">
            <h3>Tabel Data Kategori</h3>
            <span class="badge-count">{{ filteredKategoris.length }} Kategori</span>
          </div>
          <button class="btn-primary-add" @click="openAddModal">
            <i class="bi bi-plus-lg"></i> Tambah Kategori
          </button>
        </div>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 80px;" class="text-center">ID</th>
                <th>Nama Kategori</th>
                <th style="width: 140px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- State Loading -->
              <tr v-if="loading">
                <td colspan="3" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data kategori...</p>
                </td>
              </tr>

              <!-- State Kosong -->
              <tr v-else-if="filteredKategoris.length === 0">
                <td colspan="3" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>Belum ada kategori. Silakan tambahkan kategori baru.</p>
                </td>
              </tr>

              <!-- Data Loop -->
              <tr v-for="kat in filteredKategoris" :key="kat.id" v-else>
                <td class="text-center id-col">#{{ kat.id }}</td>
                <td class="fw-bold text-navy">{{ kat.nama_kategori }}</td>
                <td class="action-col">
                  <div class="action-buttons">
                    <button class="btn-icon edit" title="Edit Kategori" @click="openEditModal(kat)">
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn-icon delete" title="Hapus Kategori" @click="handleDelete(kat.id)">
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

    <!-- Modal Tambah / Edit Kategori -->
    <div v-if="showModal" class="modal-overlay">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>{{ isEditMode ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
          <p>{{ isEditMode ? 'Perbarui nama kategori.' : 'Buat kategori baru untuk mengelompokkan barang.' }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="form-body">
          <div class="form-group">
            <label>Nama Kategori</label>
            <input 
              v-model="form.nama_kategori" 
              type="text" 
              placeholder="Contoh: Elektronik, Alat Musik" 
              required 
              class="input-control" 
            />
            <p v-if="errors.nama_kategori" class="error-text">{{ errors.nama_kategori[0] }}</p>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? 'Menyimpan...' : (isEditMode ? 'Simpan Perubahan' : 'Tambah Kategori') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import kategoriApi from '@/utils/kategori';
import SidebarAdmin from '@/components/sidebarAdmin.vue';

export default {
  components: {
    SidebarAdmin
  },  
  name: 'ListKategori',
  data() {
    return {
      userData: {
        name: '',
        email: ''
      },
      searchQuery: '',
      kategoris: [],
      loading: false,
      showModal: false,
      isEditMode: false,
      saving: false,
      form: {
        id: null,
        nama_kategori: ''
      },
      errors: {}
    };
  },
  computed: {
    userInitial() {
      if (this.userData.name) {
        return this.userData.name.charAt(0).toUpperCase();
      }
      return 'U';
    },
    filteredKategoris() {
      if (!this.searchQuery) return this.kategoris;
      const query = this.searchQuery.toLowerCase();
      return this.kategoris.filter(k => 
        k.nama_kategori.toLowerCase().includes(query)
      );
    }
  },
  mounted() {
    this.loadUserProfile();
    this.fetchKategoris();
  },
  methods: {
    loadUserProfile() {
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          this.userData = JSON.parse(userStr);
        } catch (e) {
          console.error('Gagal parse data user', e);
        }
      }
    },
    async fetchKategoris() {
      this.loading = true;
      try {
        const response = await kategoriApi.getAll();
        this.kategoris = response.data?.data || response.data || [];
      } catch (error) {
        console.error('Gagal mengambil kategori:', error);
      } finally {
        this.loading = false;
      }
    },
    openAddModal() {
      this.isEditMode = false;
      this.errors = {};
      this.form = { id: null, nama_kategori: '' };
      this.showModal = true;
    },
    openEditModal(kategori) {
      this.isEditMode = true;
      this.errors = {};
      this.form = {
        id: kategori.id,
        nama_kategori: kategori.nama_kategori
      };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.errors = {};
    },
    async handleSubmit() {
      this.saving = true;
      this.errors = {};
      try {
        if (this.isEditMode) {
          await kategoriApi.updateKategori(this.form.id, { nama_kategori: this.form.nama_kategori });
        } else {
          await kategoriApi.addKategori({ nama_kategori: this.form.nama_kategori });
        }
        this.closeModal();
        await this.fetchKategoris();
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          alert('Terjadi kesalahan saat menyimpan kategori.');
        }
      } finally {
        this.saving = false;
      }
    },
    async handleDelete(id) {
      if (!confirm('Apakah Anda yakin ingin menghapus kategori ini?')) return;
      try {
        await kategoriApi.deleteKategori(id);
        this.kategoris = this.kategoris.filter(k => k.id !== id);
      } catch (error) {
        alert('Gagal menghapus kategori.');
      }
    },
    handleLogout() {
      if (confirm('Yakin ingin keluar?')) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
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
}

.menu-item i {
  font-size: 16px;
}

.menu-item:hover, .menu-item.active {
  background-color: #F77F00;
  color: #ffffff;
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
  font-size: 13px;
  color: #2D3748;
  vertical-align: middle;
}

.id-col {
  color: #A0AEC0;
  font-weight: 600;
  font-size: 12px;
}

.text-navy {
  color: #003049;
}

.fw-bold {
  font-weight: 700;
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

.btn-icon.edit { background-color: rgba(0, 48, 73, 0.1); color: #003049; }
.btn-icon.edit:hover { background-color: rgba(0, 48, 73, 0.2); }
.btn-icon.delete { background-color: #FFEBEE; color: #D62828; }

.text-center { text-align: center; }

.empty-state {
  text-align: center;
  padding: 40px;
  color: #A0AEC0;
  font-size: 13px;
}

.empty-state i {
  font-size: 28px;
  display: block;
  margin-bottom: 8px;
}

/* Modal Styling */
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
  max-width: 440px;
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
  margin-bottom: 20px;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
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
}

.input-control:focus {
  border-color: #F77F00;
}

.error-text {
  color: #D62828;
  font-size: 11px;
  margin: 0;
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
</style>
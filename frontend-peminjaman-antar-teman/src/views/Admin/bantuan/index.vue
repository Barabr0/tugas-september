<template>
  <div class="admin-wrapper">
    <SidebarAdmin />
    <main class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-left">
          <div class="header-title-wrapper">
            <h1>Permintaan Bantuan</h1>
          </div>
          <p>Kelola permohonan bantuan dan laporan kendala dari para pengguna.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari user, tipe, atau alasan..." 
            />
          </div>
        </div>
      </header>

      <!-- Main Card / Table Wrapper -->
      <div class="card-section">
        <div class="section-header">
          <div class="title-info">
            <h3>Tabel Permintaan Bantuan</h3>
            <span class="badge-count">{{ filteredRequests.length }} Permintaan</span>
          </div>
        </div>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 60px;" class="text-center">No</th>
                <th style="width: 180px;">User</th>
                <th style="width: 140px;">Tipe</th>
                <th style="width: 160px;">Aksi Diminta</th>
                <th>Alasan</th>
                <th style="width: 150px;" class="text-center">Aksi Admin</th>
              </tr>
            </thead>
            <tbody>
              <!-- State Loading -->
              <tr v-if="loading">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data permintaan bantuan...</p>
                </td>
              </tr>

              <!-- State Kosong -->
              <tr v-else-if="filteredRequests.length === 0">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>Tidak ada data permintaan bantuan yang ditemukan.</p>
                </td>
              </tr>

              <!-- Data Loop -->
              <tr v-for="(req, index) in filteredRequests" :key="req.id" v-else>
                <td class="text-center id-col">#{{ index + 1 }}</td>
                <td>
                  <div class="user-profile-cell">
                    <div class="user-avatar-small">{{ getInitial(req.user?.name) }}</div>
                    <span class="user-name">{{ req.user?.name || 'Unknown' }}</span>
                  </div>
                </td>
                <td>
                  <span class="tag-type">
                    {{ req.tipe_request }}
                  </span>
                </td>
                <td class="fw-bold text-navy">{{ req.aksi_diminta }}</td>
                <td class="reason-col">{{ req.alasan }}</td>
                <td class="action-col">
                  <div class="action-buttons">
                    <button 
                      class="btn-icon approve" 
                      title="Tandai Selesai" 
                      @click="processRequest(req.id)"
                    >
                      <i class="bi bi-check-lg"></i>
                    </button>
                    <button 
                      class="btn-icon edit" 
                      title="Detail & Tindakan" 
                      @click="openModal(req)"
                    >
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Tindakan Admin -->
    <div v-if="showModal" class="modal-overlay">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>Tindakan Admin</h2>
          <p>Tinjau dan tanggapi permintaan bantuan dari user.</p>
        </div>

        <div class="form-body" v-if="selectedReq">
          <div class="detail-row">
            <span class="label">Pengaju:</span>
            <span class="val fw-bold">{{ selectedReq.user?.name || 'Unknown' }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Aksi Diminta:</span>
            <span class="val">{{ selectedReq.aksi_diminta }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Alasan User:</span>
            <p class="val-box">{{ selectedReq.alasan }}</p>
          </div>

          <div class="form-group">
            <label>Catatan Admin</label>
            <textarea 
              v-model="adminNote" 
              rows="3" 
              placeholder="Berikan catatan atau instruksi tindak lanjut..." 
              class="input-control"
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
            <button type="button" class="btn-save" @click="saveAction">Simpan & Proses</button>
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
    components: {
        SidebarAdmin
    },
  name: 'AdminPermintaanBantuanView',
  data() {
    return {
      searchQuery: '',
      loading: false,
      showModal: false,
      selectedReq: null,
      adminNote: '',
      requests: [] // Data diambil dari API
    };
  },
  computed: {
    filteredRequests() {
      if (!this.searchQuery) return this.requests;
      const query = this.searchQuery.toLowerCase();
      return this.requests.filter(r => 
        (r.user?.name && r.user.name.toLowerCase().includes(query)) ||
        (r.tipe_request && r.tipe_request.toLowerCase().includes(query)) ||
        (r.aksi_diminta && r.aksi_diminta.toLowerCase().includes(query)) ||
        (r.alasan && r.alasan.toLowerCase().includes(query))
      );
    }
  },
  mounted() {
    this.fetchRequests();
  },
  methods: {
    async fetchRequests() {
      this.loading = true;
      try {
        // Panggil API getBantuanRequests dari utils/admin.js
        const response = await adminApi.getBantuanRequests();
        this.requests = response.data.data || response.data || [];
      } catch (error) {
        console.error('Gagal memuat data permintaan bantuan:', error);
        this.$toast.error('Gagal memuat data bantuan.');
      } finally {
        this.loading = false;
      }
    },
    getInitial(name) {
      return name ? name.charAt(0).toUpperCase() : 'U';
    },
    async processRequest(id) {
      if (!confirm('Apakah Anda yakin ingin menandai permintaan ini sebagai SELESAI/DIPROSES?')) return;
      
      try {
        // Panggil API processBantuan
        await adminApi.processBantuan(id);
        this.$toast.success('Permintaan bantuan berhasil diproses!');
        this.fetchRequests(); // Refresh tabel
      } catch (error) {
        console.error('Gagal memproses bantuan:', error);
        this.$toast.error('Gagal memproses bantuan.');
      }
    },
    openModal(req) {
      this.selectedReq = req;
      this.adminNote = '';
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.selectedReq = null;
    },
    async saveAction() {
      // Panggil API processBantuan ketika admin klik simpan di modal
      try {
        await adminApi.processBantuan(this.selectedReq.id);
        this.$toast.success('Tindakan admin berhasil disimpan!');
        this.closeModal();
        this.fetchRequests(); // Refresh tabel
      } catch (error) {
        console.error('Gagal menyimpan tindakan:', error);
        this.$toast.error('Gagal menyimpan tindakan.');
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

.text-navy {
  color: #003049;
}

.fw-bold {
  font-weight: 700;
}

.reason-col {
  color: #4A5568;
  font-size: 13px;
  max-width: 260px;
}

/* Tipe Badge */
.tag-type {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
  background-color: #F0F4F8;
  color: #003049;
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

.btn-icon.approve { background-color: #E8F5E9; color: #2E7D32; }
.btn-icon.reject { background-color: #FFEBEE; color: #C62828; }
.btn-icon.edit { background-color: #FFF8E1; color: #F57F17; }

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
  margin-bottom: 20px;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 13px;
}

.detail-row .label {
  color: #718096;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.val-box {
  background: #F8F9FA;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #E2E8F0;
  margin: 0;
  color: #2D3748;
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
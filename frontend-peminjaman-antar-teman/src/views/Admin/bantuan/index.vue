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
              placeholder="Cari peminta, target, tipe, atau alasan..."
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
          <select v-model="filterStatus" @change="fetchRequests" class="filter-select">
            <option value="pending">Menunggu</option>
            <option value="disetujui">Disetujui</option>
            <option value="ditolak">Ditolak</option>
            <option value="">Semua</option>
          </select>
        </div>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 60px;" class="text-center">No</th>
                <th style="width: 170px;">Peminta</th>
                <th style="width: 150px;">Target</th>
                <th style="width: 130px;">Tipe</th>
                <th>Alasan / Deskripsi</th>
                <th style="width: 110px;">Status</th>
                <th style="width: 200px;">Catatan Respons</th>
                <th style="width: 120px;" class="text-center">Aksi Admin</th>
              </tr>
            </thead>
            <tbody>
              <!-- State Loading -->
              <tr v-if="loading">
                <td colspan="8" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data permintaan bantuan...</p>
                </td>
              </tr>

              <!-- State Kosong -->
              <tr v-else-if="filteredRequests.length === 0">
                <td colspan="8" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>Tidak ada data permintaan bantuan yang ditemukan.</p>
                </td>
              </tr>

              <!-- Data Loop -->
              <tr v-else v-for="(req, index) in filteredRequests" :key="req.id">
                <td class="text-center id-col">#{{ index + 1 }}</td>
                <td>
                  <div class="user-profile-cell">
                    <div class="user-avatar-small">{{ getInitial(namaPeminta(req)) }}</div>
                    <span class="user-name">{{ namaPeminta(req) }}</span>
                  </div>
                </td>
                <td>{{ namaTarget(req) }}</td>
                <td>
                  <span class="tag-type">{{ req.tipe_request }}</span>
                </td>
                <td class="reason-col">{{ req.deskripsi || '-' }}</td>
                <td>
                  <span class="tag-status" :class="req.status">{{ req.status }}</span>
                </td>
                <td class="reason-col">{{ req.alasan || '-' }}</td>
                <td class="action-col">
                  <div v-if="req.status === 'pending'" class="action-buttons">
                    <button
                      class="btn-icon approve"
                      title="Setujui"
                      @click="openModal(req, 'disetujui')"
                    >
                      <i class="bi bi-check-lg"></i>
                    </button>
                    <button
                      class="btn-icon reject"
                      title="Tolak"
                      @click="openModal(req, 'ditolak')"
                    >
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <div v-else class="text-center done-text">Selesai</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Tindakan Admin -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>{{ actionStatus === 'disetujui' ? 'Setujui Permintaan' : 'Tolak Permintaan' }}</h2>
          <p>Tinjau permintaan bantuan lalu berikan catatan bila perlu.</p>
        </div>

        <div class="form-body" v-if="selectedReq">
          <div class="detail-row">
            <span class="label">Peminta:</span>
            <span class="val fw-bold">{{ namaPeminta(selectedReq) }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Target:</span>
            <span class="val">{{ namaTarget(selectedReq) }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Tipe:</span>
            <span class="val">{{ selectedReq.tipe_request }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Alasan / Deskripsi:</span>
            <p class="val-box">{{ selectedReq.deskripsi || '-' }}</p>
          </div>

          <div class="form-group">
            <label>Catatan Admin (opsional)</label>
            <textarea
              v-model="adminNote"
              rows="3"
              placeholder="Berikan catatan atau instruksi tindak lanjut..."
              class="input-control"
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal" :disabled="saving">Batal</button>
            <button
              type="button"
              class="btn-save"
              :class="{ 'btn-danger': actionStatus === 'ditolak' }"
              @click="saveAction"
              :disabled="saving"
            >
              {{ saving ? 'Menyimpan...' : (actionStatus === 'disetujui' ? 'Setujui' : 'Tolak') }}
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
  name: 'AdminPermintaanBantuanView',
  components: {
    SidebarAdmin
  },
  data() {
    return {
      searchQuery: '',
      filterStatus: 'pending',
      loading: false,
      saving: false,
      showModal: false,
      selectedReq: null,
      actionStatus: 'disetujui',
      adminNote: '',
      requests: []
    };
  },
  computed: {
    filteredRequests() {
      if (!this.searchQuery) return this.requests;
      const query = this.searchQuery.toLowerCase();
      const has = (v) => v && String(v).toLowerCase().includes(query);
      return this.requests.filter(r =>
        has(this.namaPeminta(r)) ||
        has(this.namaTarget(r)) ||
        has(r.tipe_request) ||
        has(r.deskripsi) ||
        has(r.alasan)
      );
    }
  },
  mounted() {
    this.fetchRequests();
  },
  methods: {
    namaPeminta(req) {
      return req.peminta_nama || req.peminta?.name || 'Unknown';
    },
    namaTarget(req) {
      return req.target_nama || req.target?.name || '-';
    },
    getInitial(name) {
      return name ? name.charAt(0).toUpperCase() : 'U';
    },

    async fetchRequests() {
      this.loading = true;
      try {
        const params = this.filterStatus ? { status: this.filterStatus } : {};
        const response = await adminApi.getBantuanRequests(params);
        this.requests = response.data.data || response.data || [];
      } catch (error) {
        console.error('Gagal memuat data permintaan bantuan:', error);
        this.$toast.error('Gagal memuat data bantuan.');
      } finally {
        this.loading = false;
      }
    },

    openModal(req, status) {
      this.selectedReq = req;
      this.actionStatus = status;
      this.adminNote = '';
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.selectedReq = null;
    },

    notify(type, message) {
      try {
        if (this.$toast && this.$toast[type]) this.$toast[type](message);
      } catch (e) {
        console.warn('Toast gagal ditampilkan:', e);
      }
    },

    async saveAction() {
      if (!this.selectedReq || this.saving) return;
      this.saving = true;

      const id = this.selectedReq.id;
      const status = this.actionStatus;
      const alasan = this.adminNote || null;

      try {
        await adminApi.respondBantuan(id, { status, alasan });

        // Tutup modal langsung setelah request berhasil
        this.closeModal();
        this.notify('success', `Permintaan berhasil ${status}!`);
        await this.fetchRequests();
      } catch (error) {
        console.error('Gagal menyimpan tindakan:', error);
        this.notify('error', error.response?.data?.message || 'Gagal menyimpan tindakan.');
      } finally {
        this.saving = false;
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
  width: 300px;
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

.filter-select {
  padding: 8px 12px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  background: #ffffff;
  color: #003049;
  outline: none;
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
  white-space: nowrap;
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
  flex-shrink: 0;
}

.user-name {
  font-weight: 700;
  color: #003049;
}

.fw-bold {
  font-weight: 700;
}

.reason-col {
  color: #4A5568;
  font-size: 13px;
  max-width: 260px;
  word-break: break-word;
}

/* Badges */
.tag-type {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
  background-color: #F0F4F8;
  color: #003049;
}

.tag-status {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}

.tag-status.pending { background: #FFF3E0; color: #F77F00; }
.tag-status.disetujui { background: #E8F5E9; color: #2E7D32; }
.tag-status.ditolak { background: #FFEBEE; color: #C62828; }

.done-text {
  font-size: 12px;
  color: #A0AEC0;
}

/* Action Buttons */
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
  background-color: #2E7D32;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  font-size: 13px;
}

.btn-save.btn-danger {
  background-color: #C62828;
}

.btn-save:disabled,
.btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.spin {
  display: inline-block;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
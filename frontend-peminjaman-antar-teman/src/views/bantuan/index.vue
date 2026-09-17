<template>
  <div class="app-layout">
    <!-- 1. Sidebar Navigasi User -->
    <sidebarUser />

    <!-- 2. Area Konten Utama -->
    <main class="main-content">
      <!-- Topbar Header dengan Profil di Pojok Kanan Atas -->
      <!-- <TopbarUser 
        title="Permintaan Bantuan Saya" 
        subtitle="Kelola dan pantau status permohonan bantuan Anda."
        :showBackButton="true"
        backUrl="/dashboard"
      /> -->

      <!-- Main Card / Table Wrapper -->
      <div class="card-section">
        <div class="section-header">
          <div class="title-info">
            <h3>Riwayat Permintaan</h3>
            <span class="badge-count">{{ requests.length }} Permintaan</span>
          </div>
          <button class="btn-primary-add" @click="openAddModal">
            <i class="bi bi-plus-lg"></i> Ajukan Bantuan
          </button>
        </div>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 60px;" class="text-center">No</th>
                <th style="width: 160px;">Tipe Bantuan</th>
                <th style="width: 180px;">Aksi Diminta</th>
                <th>Alasan / Deskripsi</th>
                <th style="width: 130px;" class="text-center">Status</th>
                <th style="width: 100px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- State Loading -->
              <tr v-if="loading">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat riwayat bantuan Anda...</p>
                </td>
              </tr>

              <!-- State Kosong -->
              <tr v-else-if="requests.length === 0">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>Belum ada riwayat permintaan bantuan. Klik tombol di atas jika butuh bantuan.</p>
                </td>
              </tr>

              <!-- Data Loop -->
              <tr v-for="(item, index) in requests" :key="item.id" v-else>
                <td class="text-center id-col">#{{ index + 1 }}</td>
                <td>
                  <span class="tag-type">{{ item.tipe_request }}</span>
                </td>
                <td class="fw-bold text-navy">{{ item.aksi_diminta }}</td>
                <td class="desc-col">{{ item.alasan }}</td>
                <td class="text-center">
                  <span :class="['tag-status', statusClass(item.status)]">
                    {{ mapStatus(item.status) }}
                  </span>
                </td>
                <td class="action-col">
                  <div class="action-buttons">
                    <button 
                      v-if="item.status === 'pending'" 
                      class="btn-icon delete" 
                      title="Batalkan Permintaan"
                      @click="cancelRequest(item.id)"
                    >
                      <i class="bi bi-trash-fill"></i>
                    </button>
                    <button 
                      v-else 
                      class="btn-icon view" 
                      title="Lihat Detail"
                      @click="viewDetail(item)"
                    >
                      <i class="bi bi-eye-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Ajukan Bantuan Baru -->
    <div v-if="showModal" class="modal-overlay">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>Ajukan Bantuan Baru</h2>
          <p>Sampaikan kendala atau permohonan aksi kepada tim admin.</p>
        </div>

        <form @submit.prevent="submitRequest" class="form-body">
          <div class="form-group">
            <label>Tipe Bantuan</label>
            <select v-model="form.tipe_request" required class="input-select">
              <option value="" disabled>Pilih tipe masalah</option>
              <option value="Kendala Transaksi">Kendala Transaksi</option>
              <option value="Akun & Profil">Akun & Profil</option>
              <option value="Sengketa Barang">Sengketa Barang</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div class="form-group">
            <label>Aksi yang Diminta</label>
            <input 
              v-model="form.aksi_diminta" 
              type="text" 
              placeholder="Contoh: Pembatalan Pinjaman, Reset Reputasi" 
              required 
              class="input-control" 
            />
          </div>

          <div class="form-group">
            <label>Alasan Lengkap</label>
            <textarea 
              v-model="form.alasan" 
              rows="3" 
              placeholder="Jelaskan kronologi atau alasan secara detail..." 
              required 
              class="input-control"
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
            <button type="submit" class="btn-save" :disabled="submitting">
              {{ submitting ? 'Kirim...' : 'Kirim Permintaan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import adminApi from '@/utils/admin';
import sidebarUser from '@/components/sidebarUser.vue';
//import TopbarUser from '@/components/TopbarUser.vue';

export default {
  components: {
    sidebarUser,
    
  },
  name: 'UserPermintaanBantuanView',
  data() {
    return {
      loading: false,
      showModal: false,
      submitting: false,
      form: {
        tipe_request: '',
        aksi_diminta: '',
        alasan: ''
      },
      requests: []
    };
  },
  mounted() {
    this.fetchRequests();
  },
  methods: {
    async fetchRequests() {
      this.loading = true;
      try {
        const response = await adminApi.getUserBantuan();
        this.requests = response.data.data || response.data || [];
      } catch (error) {
        console.error('Gagal memuat data bantuan:', error);
        if (this.$toast) this.$toast.error('Gagal memuat riwayat bantuan.');
      } finally {
        this.loading = false;
      }
    },
    mapStatus(status) {
      const map = {
        pending: 'Menunggu',
        processed: 'Selesai',
        rejected: 'Ditolak'
      };
      return map[status] || status;
    },
    statusClass(status) {
      const mapped = this.mapStatus(status);
      const map = {
        'Menunggu': 'menunggu',
        'Selesai': 'selesai',
        'Ditolak': 'ditolak'
      };
      return map[mapped] || '';
    },
    openAddModal() {
      this.form = { tipe_request: '', aksi_diminta: '', alasan: '' };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async submitRequest() {
      this.submitting = true;
      try {
        await adminApi.ajukanBantuan(this.form);
        if (this.$toast) this.$toast.success('Permintaan bantuan berhasil dikirim ke admin.');
        this.closeModal();
        this.fetchRequests();
      } catch (error) {
        console.error('Gagal mengirim bantuan:', error);
        const pesan = error.response?.data?.message || 'Gagal mengirim permintaan.';
        if (this.$toast) this.$toast.error(pesan);
      } finally {
        this.submitting = false;
      }
    },
    async cancelRequest(id) {
      if (!confirm('Apakah Anda yakin ingin membatalkan/menghapus permintaan ini?')) return;
      
      try {
        await adminApi.deleteBantuan(id);
        this.requests = this.requests.filter(r => r.id !== id);
        if (this.$toast) this.$toast.success('Permintaan berhasil dihapus.');
      } catch (error) {
        console.error('Gagal menghapus bantuan:', error);
        if (this.$toast) this.$toast.error('Gagal menghapus permintaan.');
      }
    },
    viewDetail(item) {
      const statusText = this.mapStatus(item.status);
      alert(`Status Permintaan: ${statusText}\nAlasan: ${item.alasan}`);
    }
  }
};
</script>

<style scoped>
/* Main Full Layout */
.app-layout {
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

.desc-col {
  color: #4A5568;
  font-size: 13px;
}

.tag-type {
  background-color: #F0F4F8;
  color: #003049;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

.tag-status {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

.tag-status.menunggu { background-color: #FFF3E0; color: #F77F00; }
.tag-status.selesai { background-color: #E3F2FD; color: #1565C0; }
.tag-status.ditolak { background-color: #FFEBEE; color: #C62828; }

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

.btn-icon.delete { background-color: #FFEBEE; color: #C62828; }
.btn-icon.view { background-color: #F0F4F8; color: #003049; }

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

.input-control, .input-select {
  padding: 10px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
  width: 100%;
  box-sizing: border-box;
  font-family: inherit;
}

.input-control:focus, .input-select:focus {
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
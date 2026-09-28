<template>
  <div class="user-wrapper">
    <SidebarUser />

    <main class="main-content">
      <TopbarUser />

      <!-- Header -->
      <header class="page-header">
        <div>
          <h1>Laporan & Bantuan</h1>
          <p>Ajukan pembatalan pinjam atau pergantian barang ke user lain, dan tanggapi pengajuan yang masuk.</p>
        </div>
        <button class="btn-primary" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i> Buat Laporan
        </button>
      </header>

      <!-- Tabs -->
      <div class="tabs">
        <button :class="['tab', { active: activeTab === 'masuk' }]" @click="activeTab = 'masuk'">
          Masuk untuk Saya
          <span v-if="pendingMasuk > 0" class="tab-badge">{{ pendingMasuk }}</span>
        </button>
        <button :class="['tab', { active: activeTab === 'dibuat' }]" @click="activeTab = 'dibuat'">
          Laporan Saya
        </button>
      </div>

      <!-- Tabel -->
      <div class="card-section">
        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th>{{ activeTab === 'masuk' ? 'Dari' : 'Tujuan' }}</th>
                <th>Tipe</th>
                <th>Alasan / Deskripsi</th>
                <th>Status</th>
                <th>Catatan Respons</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data...</p>
                </td>
              </tr>

              <tr v-else-if="currentList.length === 0">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>{{ activeTab === 'masuk' ? 'Belum ada laporan masuk.' : 'Kamu belum membuat laporan.' }}</p>
                </td>
              </tr>

              <tr v-else v-for="item in currentList" :key="item.id">
                <td class="fw-bold">
                  {{ activeTab === 'masuk' ? (item.pelapor_nama || 'Unknown') : (item.target_nama || 'Unknown') }}
                </td>
                <td>
                  <span class="tag-type">{{ tipeLabel(item.tipe_laporan) }}</span>
                </td>
                <td class="text-cell">
                  {{ item.deskripsi }}
                  <div v-if="item.peminjaman_id || item.barang_baru_id" class="meta">
                    <span v-if="item.peminjaman_id">Pinjaman #{{ item.peminjaman_id }}</span>
                    <span v-if="item.barang_baru_id">Barang pengganti #{{ item.barang_baru_id }}</span>
                  </div>
                </td>
                <td><span class="tag-status" :class="item.status">{{ item.status }}</span></td>
                <td class="text-cell">{{ item.alasan_respons || '-' }}</td>
                <td>
                  <!-- Masuk: target merespons -->
                  <div v-if="activeTab === 'masuk' && item.status === 'pending'" class="action-buttons">
                    <button class="btn-icon approve" title="Setujui" @click="openRespondModal(item, 'disetujui')">
                      <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="btn-icon reject" title="Tolak" @click="openRespondModal(item, 'ditolak')">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>

                  <!-- Dibuat: pelapor bisa batalkan -->
                  <div v-else-if="activeTab === 'dibuat' && item.status === 'pending'" class="action-buttons">
                    <button class="btn-cancel-req" @click="cancelReport(item)">Batalkan</button>
                  </div>

                  <div v-else class="text-center done-text">Selesai</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Buat Laporan -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
      <div class="form-card">
        <div class="form-header">
          <h2>Buat Laporan</h2>
          <p>Ajukan permintaan ke user lain.</p>
        </div>

        <div class="form-body">
          <div class="form-group">
            <label>Tujuan (User)</label>
            <select v-model="form.target_id" class="input-control" :class="{ invalid: errors.target_id }">
              <option value="" disabled>Pilih user</option>
              <option v-for="u in targets" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
            <small v-if="errors.target_id" class="error-text">{{ errors.target_id[0] }}</small>
          </div>

          <div class="form-group">
            <label>Tipe</label>
            <select v-model="form.tipe_laporan" class="input-control" :class="{ invalid: errors.tipe_laporan }">
              <option v-for="(label, key) in tipeOptions" :key="key" :value="key">{{ label }}</option>
            </select>
            <small v-if="errors.tipe_laporan" class="error-text">{{ errors.tipe_laporan[0] }}</small>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>ID Peminjaman (opsional)</label>
              <input v-model.number="form.peminjaman_id" type="number" min="1" class="input-control" placeholder="Contoh: 12" />
              <small v-if="errors.peminjaman_id" class="error-text">{{ errors.peminjaman_id[0] }}</small>
            </div>

            <div v-if="form.tipe_laporan === 'ganti_barang'" class="form-group">
              <label>ID Barang Pengganti</label>
              <input
                v-model.number="form.barang_baru_id"
                type="number"
                min="1"
                class="input-control"
                :class="{ invalid: errors.barang_baru_id }"
                placeholder="Contoh: 5"
              />
              <small v-if="errors.barang_baru_id" class="error-text">{{ errors.barang_baru_id[0] }}</small>
            </div>
          </div>

          <div class="form-group">
            <label>Alasan / Deskripsi</label>
            <textarea
              v-model="form.deskripsi"
              rows="3"
              class="input-control"
              :class="{ invalid: errors.deskripsi }"
              placeholder="Jelaskan alasan pengajuanmu..."
            ></textarea>
            <small v-if="errors.deskripsi" class="error-text">{{ errors.deskripsi[0] }}</small>
          </div>

          <div class="form-actions">
            <button class="btn-cancel" @click="closeCreateModal" :disabled="saving">Batal</button>
            <button class="btn-save" @click="submitCreate" :disabled="saving">
              {{ saving ? 'Mengirim...' : 'Kirim' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Respons -->
    <div v-if="showRespondModal" class="modal-overlay" @click.self="closeRespondModal">
      <div class="form-card">
        <div class="form-header">
          <h2>{{ respondStatus === 'disetujui' ? 'Setujui Laporan' : 'Tolak Laporan' }}</h2>
          <p>Tinjau pengajuan lalu berikan catatan.</p>
        </div>

        <div v-if="selectedItem" class="form-body">
          <div class="detail-row">
            <span class="label">Dari</span>
            <span class="fw-bold">{{ selectedItem.pelapor_nama }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Tipe</span>
            <span>{{ tipeLabel(selectedItem.tipe_laporan) }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Alasan</span>
            <p class="val-box">{{ selectedItem.deskripsi }}</p>
          </div>

          <div class="form-group">
            <label>Catatan {{ respondStatus === 'ditolak' ? '(wajib)' : '(opsional)' }}</label>
            <textarea
              v-model="respondNote"
              rows="3"
              class="input-control"
              :class="{ invalid: errors.alasan_respons }"
              placeholder="Tulis catatan untuk pengaju..."
            ></textarea>
            <small v-if="errors.alasan_respons" class="error-text">{{ errors.alasan_respons[0] }}</small>
          </div>

          <div class="form-actions">
            <button class="btn-cancel" @click="closeRespondModal" :disabled="saving">Batal</button>
            <button
              class="btn-save"
              :class="{ 'btn-danger': respondStatus === 'ditolak' }"
              @click="submitRespond"
              :disabled="saving"
            >
              {{ saving ? 'Menyimpan...' : (respondStatus === 'disetujui' ? 'Setujui' : 'Tolak') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import laporanApi from '@/utils/laporan';
import SidebarUser from '@/components/sidebarUser.vue';
import TopbarUser from '@/components/topbarUser.vue';

export default {
  name: 'LaporanUserView',
  components: {
    SidebarUser,
    TopbarUser
  },
  data() {
    return {
      laporans: [],
      targets: [],
      currentUserId: null,
      activeTab: 'masuk',
      loading: false,
      saving: false,
      errors: {},

      showCreateModal: false,
      form: {
        target_id: '',
        tipe_laporan: 'batal_pinjam',
        peminjaman_id: null,
        barang_baru_id: null,
        deskripsi: ''
      },

      showRespondModal: false,
      selectedItem: null,
      respondStatus: 'disetujui',
      respondNote: '',

      tipeOptions: {
        batal_pinjam: 'Batal Pinjam',
        ganti_barang: 'Ganti Barang',
        lainnya: 'Lainnya'
      }
    };
  },
  computed: {
    masuk() {
      return this.laporans.filter(l => Number(l.target_id) === Number(this.currentUserId));
    },
    dibuat() {
      return this.laporans.filter(l => Number(l.user_id) === Number(this.currentUserId));
    },
    currentList() {
      return this.activeTab === 'masuk' ? this.masuk : this.dibuat;
    },
    pendingMasuk() {
      return this.masuk.filter(l => l.status === 'pending').length;
    }
  },
  mounted() {
    this.loadCurrentUser();
    this.fetchLaporans();
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

    notify(type, message) {
      try {
        if (this.$toast && this.$toast[type]) this.$toast[type](message);
      } catch (e) {
        console.warn('Toast gagal ditampilkan:', e);
      }
    },

    tipeLabel(tipe) {
      return this.tipeOptions[tipe] || tipe;
    },

    async fetchLaporans() {
      this.loading = true;
      try {
        const res = await laporanApi.getAll();
        this.laporans = res.data.data || [];
      } catch (error) {
        console.error('Gagal memuat laporan:', error);
        this.notify('error', 'Gagal memuat data laporan.');
      } finally {
        this.loading = false;
      }
    },

    async fetchTargets() {
      try {
        const res = await laporanApi.getTargets();
        const list = res.data.data || res.data || [];
        this.targets = list.filter(u => Number(u.id) !== Number(this.currentUserId));
      } catch (error) {
        console.error('Gagal memuat daftar user:', error);
      }
    },

    // ---------- Buat ----------
    async openCreateModal() {
      this.form = {
        target_id: '',
        tipe_laporan: 'batal_pinjam',
        peminjaman_id: null,
        barang_baru_id: null,
        deskripsi: ''
      };
      this.errors = {};
      this.showCreateModal = true;
      if (this.targets.length === 0) await this.fetchTargets();
    },
    closeCreateModal() {
      this.showCreateModal = false;
      this.errors = {};
    },
    async submitCreate() {
      if (this.saving) return;
      this.saving = true;
      this.errors = {};

      const payload = {
        ...this.form,
        peminjaman_id: this.form.peminjaman_id || null,
        barang_baru_id: this.form.tipe_laporan === 'ganti_barang' ? (this.form.barang_baru_id || null) : null
      };

      try {
        await laporanApi.store(payload);
        this.closeCreateModal();
        this.notify('success', 'Laporan berhasil dikirim.');
        this.activeTab = 'dibuat';
        await this.fetchLaporans();
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          this.notify('error', error.response?.data?.message || 'Gagal mengirim laporan.');
        }
      } finally {
        this.saving = false;
      }
    },

    // ---------- Respons ----------
    openRespondModal(item, status) {
      this.selectedItem = item;
      this.respondStatus = status;
      this.respondNote = '';
      this.errors = {};
      this.showRespondModal = true;
    },
    closeRespondModal() {
      this.showRespondModal = false;
      this.selectedItem = null;
      this.errors = {};
    },
    async submitRespond() {
      if (this.saving || !this.selectedItem) return;
      this.saving = true;
      this.errors = {};

      const id = this.selectedItem.id;
      const status = this.respondStatus;

      try {
        await laporanApi.respond(id, {
          status,
          alasan_respons: this.respondNote || null
        });
        this.closeRespondModal();
        this.notify('success', `Laporan berhasil ${status}.`);
        await this.fetchLaporans();
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          this.notify('error', error.response?.data?.message || 'Gagal merespons laporan.');
        }
      } finally {
        this.saving = false;
      }
    },

    // ---------- Batalkan ----------
    async cancelReport(item) {
      if (!confirm('Batalkan laporan ini?')) return;
      try {
        await laporanApi.destroy(item.id);
        this.laporans = this.laporans.filter(l => l.id !== item.id);
        this.notify('success', 'Laporan dibatalkan.');
      } catch (error) {
        this.notify('error', error.response?.data?.message || 'Gagal membatalkan laporan.');
      }
    }
  }
};
</script>

<style scoped>
.user-wrapper {
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
  box-sizing: border-box;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.page-header h1 {
  margin: 0 0 4px 0;
  font-size: 24px;
  color: #003049;
  font-weight: 800;
}

.page-header p {
  margin: 0;
  color: #718096;
  font-size: 13px;
}

.btn-primary {
  background-color: #F77F00;
  color: #ffffff;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover { background-color: #E07300; }

/* Tabs */
.tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.tab {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  padding: 9px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: #718096;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.tab.active {
  background: #003049;
  color: #ffffff;
  border-color: #003049;
}

.tab-badge {
  background: #D62828;
  color: #ffffff;
  font-size: 10px;
  padding: 1px 7px;
  border-radius: 10px;
}

/* Card + table */
.card-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
}

.table-wrapper { overflow-x: auto; }

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
  vertical-align: top;
}

.fw-bold { font-weight: 700; color: #003049; }

.text-cell {
  max-width: 260px;
  word-break: break-word;
  font-size: 13px;
  color: #4A5568;
}

.meta {
  display: flex;
  gap: 8px;
  margin-top: 6px;
  flex-wrap: wrap;
}

.meta span {
  font-size: 11px;
  background: #F0F4F8;
  color: #003049;
  padding: 2px 8px;
  border-radius: 6px;
  font-weight: 600;
}

.tag-type {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  background: #F0F4F8;
  color: #003049;
  white-space: nowrap;
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

.done-text { font-size: 12px; color: #A0AEC0; }
.text-center { text-align: center; }

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
}

.btn-icon.approve { background: #E8F5E9; color: #2E7D32; }
.btn-icon.reject { background: #FFEBEE; color: #C62828; }

.btn-cancel-req {
  background: #FFEBEE;
  color: #C62828;
  border: none;
  padding: 7px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

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

.spin { display: inline-block; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

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

.form-card {
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
  background: #ffffff;
  padding: 28px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.form-header h2 { margin: 0 0 4px 0; font-size: 20px; color: #003049; }
.form-header p { margin: 0 0 20px 0; font-size: 13px; color: #718096; }

.form-body { display: flex; flex-direction: column; gap: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 700; color: #003049; }

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

.input-control:focus { border-color: #F77F00; }
.input-control.invalid { border-color: #C62828; }
.error-text { color: #C62828; font-size: 11px; }

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
}

.form-actions { display: flex; gap: 12px; margin-top: 8px; }

.btn-save {
  flex: 1;
  background-color: #F77F00;
  color: #ffffff;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}

.btn-save.btn-danger { background-color: #C62828; }

.btn-cancel {
  background: transparent;
  color: #718096;
  border: 1px solid #CBD5E0;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
}

.btn-save:disabled,
.btn-cancel:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 640px) {
  .main-content { padding: 20px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>
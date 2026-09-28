<template>
  <div class="user-wrapper">
    <SidebarUser />

    <main class="main-content">
    <TopbarUser />

    <!-- Header -->
    <header class="page-header">
      <div>
        <h1>Rekening Bank</h1>
        <p>Kelola rekening dan saldo yang dipakai untuk transaksi pinjam uang.</p>
      </div>
      <button class="btn-primary" @click="openAddModal">
        <i class="bi bi-plus-lg"></i> Tambah Rekening
      </button>
    </header>

    <!-- Ringkasan Saldo -->
    <div class="summary-card">
      <div class="summary-icon"><i class="bi bi-wallet2"></i></div>
      <div>
        <span class="summary-label">Total Saldo</span>
        <h2 class="summary-value">{{ formatRupiah(totalSaldo) }}</h2>
        <span class="summary-sub">{{ banks.length }} rekening terdaftar</span>
      </div>
    </div>

    <!-- List Bank -->
    <div v-if="loading" class="empty-state">
      <i class="bi bi-arrow-repeat spin"></i>
      <p>Memuat data rekening...</p>
    </div>

    <div v-else-if="banks.length === 0" class="empty-state">
      <i class="bi bi-bank"></i>
      <p>Belum ada rekening. Tambahkan rekening pertamamu.</p>
    </div>

    <div v-else class="bank-grid">
      <div v-for="bank in banks" :key="bank.id" class="bank-card">
        <div class="bank-card-top">
          <div class="bank-icon"><i class="bi bi-bank2"></i></div>
          <div class="bank-title">
            <span class="bank-name">{{ bank.nama_bank }}</span>
            <span class="bank-number">{{ bank.nomor_rekening }}</span>
          </div>
        </div>

        <div class="bank-owner">
          <span class="label">Atas nama</span>
          <span class="value">{{ bank.atas_nama }}</span>
        </div>

        <div class="bank-balance">
          <span class="label">Saldo</span>
          <span class="balance-value">{{ formatRupiah(bank.saldo) }}</span>
          <span class="topup-total">Total top up: {{ formatRupiah(bank.jumlah_topup) }}</span>
        </div>

        <div class="bank-actions">
          <button class="btn-topup" @click="openTopupModal(bank)">
            <i class="bi bi-plus-circle"></i> Top Up
          </button>
          <button class="btn-delete" title="Hapus rekening" @click="deleteBank(bank)">
            <i class="bi bi-trash-fill"></i>
          </button>
        </div>
      </div>
    </div>

    </main>

    <!-- Modal Tambah Rekening -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="closeAddModal">
      <div class="form-card">
        <div class="form-header">
          <h2>Tambah Rekening</h2>
          <p>Masukkan data rekening bank kamu.</p>
        </div>

        <div class="form-body">
          <div class="form-group">
            <label>Nama Bank</label>
            <input
              v-model="addForm.nama_bank"
              list="daftar-bank"
              type="text"
              class="input-control"
              :class="{ invalid: errors.nama_bank }"
              placeholder="Contoh: BCA"
            />
            <datalist id="daftar-bank">
              <option v-for="b in daftarBank" :key="b" :value="b" />
            </datalist>
            <small v-if="errors.nama_bank" class="error-text">{{ errors.nama_bank[0] }}</small>
          </div>

          <div class="form-group">
            <label>Nomor Rekening</label>
            <input
              v-model="addForm.nomor_rekening"
              type="text"
              inputmode="numeric"
              class="input-control"
              :class="{ invalid: errors.nomor_rekening }"
              placeholder="Contoh: 1234567890"
            />
            <small v-if="errors.nomor_rekening" class="error-text">{{ errors.nomor_rekening[0] }}</small>
          </div>

          <div class="form-group">
            <label>Atas Nama</label>
            <input
              v-model="addForm.atas_nama"
              type="text"
              class="input-control"
              :class="{ invalid: errors.atas_nama }"
              placeholder="Nama pemilik rekening"
            />
            <small v-if="errors.atas_nama" class="error-text">{{ errors.atas_nama[0] }}</small>
          </div>

          <div class="form-actions">
            <button class="btn-cancel" @click="closeAddModal" :disabled="saving">Batal</button>
            <button class="btn-save" @click="submitAdd" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Top Up -->
    <div v-if="showTopupModal" class="modal-overlay" @click.self="closeTopupModal">
      <div class="form-card">
        <div class="form-header">
          <h2>Top Up Saldo</h2>
          <p v-if="selectedBank">
            {{ selectedBank.nama_bank }} - {{ selectedBank.nomor_rekening }}
          </p>
        </div>

        <div class="form-body">
          <div class="form-group">
            <label>Nominal</label>
            <input
              v-model.number="topupForm.nominal"
              type="number"
              min="1000"
              class="input-control"
              :class="{ invalid: errors.nominal }"
              placeholder="Minimal Rp 1.000"
            />
            <small v-if="errors.nominal" class="error-text">{{ errors.nominal[0] }}</small>
          </div>

          <div class="quick-amounts">
            <button
              v-for="n in quickAmounts"
              :key="n"
              type="button"
              class="chip"
              :class="{ active: topupForm.nominal === n }"
              @click="topupForm.nominal = n"
            >
              {{ formatRupiah(n) }}
            </button>
          </div>

          <div class="form-actions">
            <button class="btn-cancel" @click="closeTopupModal" :disabled="saving">Batal</button>
            <button class="btn-save" @click="submitTopup" :disabled="saving">
              {{ saving ? 'Memproses...' : 'Top Up' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import bankApi from '@/utils/bank';
import SidebarUser from '@/components/sidebarUser.vue';
import TopbarUser from '@/components/topbarUser.vue';

export default {
  name: 'BankIndexView',
  components: {
    SidebarUser,
    TopbarUser
  },
  data() {
    return {
      banks: [],
      loading: false,
      saving: false,
      errors: {},

      showAddModal: false,
      addForm: { nama_bank: '', nomor_rekening: '', atas_nama: '' },

      showTopupModal: false,
      selectedBank: null,
      topupForm: { nominal: null },

      quickAmounts: [50000, 100000, 200000, 500000],
      daftarBank: ['BCA', 'BNI', 'BRI', 'Mandiri', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'Jago']
    };
  },
  computed: {
    totalSaldo() {
      return this.banks.reduce((sum, b) => sum + (b.saldo || 0), 0);
    }
  },
  mounted() {
    this.fetchBanks();
  },
  methods: {
    formatRupiah(value) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
      }).format(value || 0);
    },

    notify(type, message) {
      try {
        if (this.$toast && this.$toast[type]) this.$toast[type](message);
      } catch (e) {
        console.warn('Toast gagal ditampilkan:', e);
      }
    },

    async fetchBanks() {
      this.loading = true;
      try {
        const res = await bankApi.getAll();
        this.banks = res.data.data || [];
      } catch (error) {
        console.error('Gagal memuat rekening:', error);
        this.notify('error', 'Gagal memuat data rekening.');
      } finally {
        this.loading = false;
      }
    },

    // ---------- Tambah ----------
    openAddModal() {
      this.addForm = { nama_bank: '', nomor_rekening: '', atas_nama: '' };
      this.errors = {};
      this.showAddModal = true;
    },
    closeAddModal() {
      this.showAddModal = false;
      this.errors = {};
    },
    async submitAdd() {
      if (this.saving) return;
      this.saving = true;
      this.errors = {};
      try {
        const res = await bankApi.store(this.addForm);
        this.banks.unshift(res.data.data);
        this.closeAddModal();
        this.notify('success', 'Rekening berhasil ditambahkan.');
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          this.notify('error', error.response?.data?.message || 'Gagal menambahkan rekening.');
        }
      } finally {
        this.saving = false;
      }
    },

    // ---------- Top Up ----------
    openTopupModal(bank) {
      this.selectedBank = bank;
      this.topupForm = { nominal: null };
      this.errors = {};
      this.showTopupModal = true;
    },
    closeTopupModal() {
      this.showTopupModal = false;
      this.selectedBank = null;
      this.errors = {};
    },
    async submitTopup() {
      if (this.saving || !this.selectedBank) return;
      this.saving = true;
      this.errors = {};
      try {
        const res = await bankApi.topUp(this.selectedBank.id, { nominal: this.topupForm.nominal });
        const idx = this.banks.findIndex(b => b.id === this.selectedBank.id);
        if (idx !== -1) this.banks.splice(idx, 1, res.data.data);
        this.closeTopupModal();
        this.notify('success', 'Top up berhasil.');
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          this.notify('error', error.response?.data?.message || 'Gagal melakukan top up.');
        }
      } finally {
        this.saving = false;
      }
    },

    // ---------- Hapus ----------
    async deleteBank(bank) {
      if (!confirm(`Hapus rekening ${bank.nama_bank} - ${bank.nomor_rekening}?`)) return;
      try {
        await bankApi.destroy(bank.id);
        this.banks = this.banks.filter(b => b.id !== bank.id);
        this.notify('success', 'Rekening dihapus.');
      } catch (error) {
        this.notify('error', error.response?.data?.message || 'Gagal menghapus rekening.');
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

/* Summary */
.summary-card {
  display: flex;
  align-items: center;
  gap: 18px;
  background: linear-gradient(135deg, #003049, #00507a);
  color: #ffffff;
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 24px;
  box-shadow: 0 6px 18px rgba(0, 48, 73, 0.18);
}

.summary-icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #FCBF49;
}

.summary-label { font-size: 12px; opacity: 0.8; }
.summary-value { margin: 4px 0; font-size: 28px; font-weight: 800; }
.summary-sub { font-size: 12px; opacity: 0.7; }

/* Bank grid */
.bank-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.bank-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.bank-card-top {
  display: flex;
  align-items: center;
  gap: 12px;
}

.bank-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(247, 127, 0, 0.15);
  color: #F77F00;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.bank-title { display: flex; flex-direction: column; }
.bank-name { font-weight: 800; color: #003049; font-size: 16px; }
.bank-number { font-size: 13px; color: #718096; letter-spacing: 1px; }

.label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #A0AEC0;
  text-transform: uppercase;
  margin-bottom: 2px;
}

.value { font-weight: 600; color: #2D3748; font-size: 14px; }

.bank-balance {
  background: #F8F9FA;
  border-radius: 12px;
  padding: 12px 14px;
}

.balance-value {
  display: block;
  font-size: 20px;
  font-weight: 800;
  color: #003049;
}

.topup-total { font-size: 11px; color: #718096; }

.bank-actions { display: flex; gap: 10px; }

.btn-topup {
  flex: 1;
  background: #E8F5E9;
  color: #2E7D32;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-topup:hover { background: #d7ecd9; }

.btn-delete {
  width: 40px;
  border: none;
  border-radius: 8px;
  background: #FFEBEE;
  color: #C62828;
  cursor: pointer;
}

.btn-delete:hover { background: #ffdde1; }

.empty-state {
  text-align: center;
  padding: 48px;
  color: #A0AEC0;
}

.empty-state i {
  font-size: 36px;
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
  max-width: 440px;
  background: #ffffff;
  padding: 28px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.form-header h2 { margin: 0 0 4px 0; font-size: 20px; color: #003049; }
.form-header p { margin: 0 0 20px 0; font-size: 13px; color: #718096; }

.form-body { display: flex; flex-direction: column; gap: 16px; }
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
}

.input-control:focus { border-color: #F77F00; }
.input-control.invalid { border-color: #C62828; }
.error-text { color: #C62828; font-size: 11px; }

.quick-amounts { display: flex; flex-wrap: wrap; gap: 8px; }

.chip {
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #E2E8F0;
  background: #ffffff;
  font-size: 12px;
  font-weight: 600;
  color: #003049;
  cursor: pointer;
}

.chip.active,
.chip:hover {
  background: #003049;
  color: #ffffff;
  border-color: #003049;
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
}
</style>
<template>
  <div class="page-container">
    <div class="content-wrapper">
      
      <div class="header-section">
        <div>
          <h2>Daftar Barang Saya</h2>
          <p>Kelola barang-barang yang Anda miliki untuk dipinjamkan.</p>
        </div>
        <router-link to="/barang/tambah" class="btn-add">
          + Tambah Barang
        </router-link>
      </div>

      <div class="table-card">
        <table class="custom-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Barang</th>
              <th>Kategori</th>
              <th>Deskripsi</th>
              <th>Kondisi</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <!-- Ubah colspan menjadi 7 karena ada 7 kolom -->
              <td colspan="7" class="empty-state">Memuat data...</td>
            </tr>
            <tr v-else-if="barangs.length === 0">
              <td colspan="7" class="empty-state">Belum ada barang. Silakan tambahkan.</td>
            </tr>
            <tr v-for="(barang, index) in barangs" :key="barang.id" v-else>
              <td>{{ index + 1 }}</td>
              <td class="text-bold">{{ barang.nama_barang }}</td>
              <td>{{ barang.kategori?.nama_kategori || '-' }}</td>
              
              <!-- TAMBAHAN: Kolom Deskripsi yang hilang -->
              <td>{{ barang.deskripsi || '-' }}</td>

              <td>
                <span :class="['badge', kondisiClass(barang.kondisi)]">
                  {{ getKondisiText(barang.kondisi) }}
                </span>
              </td>
              <td>
                <span :class="['badge', statusClass(barang.status)]">
                  {{ getStatusText(barang.status) }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="openEditModal(barang)" class="btn-action btn-edit">
                    Edit
                  </button>
                  <button @click="handleDelete(barang.id)" class="btn-action btn-delete">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Edit -->
    <div v-if="showModal" class="modal-overlay">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>Edit Barang</h2>
          <p>Perbarui detail barang Anda.</p>
        </div>

        <form @submit.prevent="handleUpdate" class="form-body">
          <div class="form-group">
            <label>Nama Barang</label>
            <input v-model="editForm.nama_barang" type="text" required class="input-control" />
            <p v-if="errors.nama_barang" class="error-text">{{ errors.nama_barang[0] }}</p>
          </div>

          <div class="form-group">
            <label>Kategori</label>
            <select v-model="editForm.kategori_id" required class="input-select">
              <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">{{ kat.nama_kategori }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <!-- PERBAIKAN: Pindahkan error text ke LUAR textarea -->
            <textarea v-model="editForm.deskripsi" rows="3" required class="input-control"></textarea>
            <p v-if="errors.deskripsi" class="error-text">{{ errors.deskripsi[0] }}</p>
          </div>

          <div class="form-group">
            <label>Kondisi</label>
            <select v-model="editForm.kondisi" required class="input-select">
              <option value="B">Baik</option>
              <option value="R">Rusak Ringan</option>
              <option value="P">Diperbaiki</option>
            </select>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
            <button type="submit" class="btn-save" :disabled="updating">
              {{ updating ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script>
import barangApi from '@/utils/barang';
import kategoriApi from '@/utils/kategori';

export default {
  name: 'ListBarang',
  data() {
    return {
      barangs: [],
      kategoris: [],
      loading: false,
      showModal: false,
      updating: false,
      editForm: {
        id: null,
        nama_barang: '',
        kategori_id: '',
        deskripsi: '',
        kondisi: 'B',
      },
      errors: {}
    };
  },
  mounted() {
    this.fetchBarangs();
    this.fetchKategoris();
  },
  methods: {
    async fetchBarangs() {
      this.loading = true;
      try {
        const response = await barangApi.getMyBarangs();
        this.barangs = response.data?.data || response.data || [];
      } catch (error) {
        console.error('Gagal mengambil data barang:', error);
        alert('Gagal memuat data barang.');
      } finally {
        this.loading = false;
      }
    },

    async fetchKategoris() {
      try {
        const response = await kategoriApi.getAll();
        this.kategoris = response.data?.data || response.data || [];
      } catch (error) {
        console.error('Gagal mengambil kategori:', error);
      }
    },

    openEditModal(barang) {
      this.errors = {};
      this.editForm = {
        id: barang.id,
        nama_barang: barang.nama_barang,
        kategori_id: barang.kategori_id,
        deskripsi: barang.deskripsi,
        kondisi: barang.kondisi
      };
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.errors = {};
    },

    async handleUpdate() {
      this.updating = true;
      this.errors = {};
      try {
        await barangApi.updateBarang(this.editForm.id, this.editForm);
        this.closeModal();
        await this.fetchBarangs();
        alert('Barang berhasil diperbarui!');
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          alert('Terjadi kesalahan saat memperbarui barang.');
          console.error(error);
        }
      } finally {
        this.updating = false;
      }
    },

    async handleDelete(id) {
      if (!confirm('Apakah Anda yakin ingin menghapus barang ini?')) return;
      
      try {
        await barangApi.deleteBarang(id);
        this.barangs = this.barangs.filter(b => b.id !== id);
        alert('Barang berhasil dihapus.');
      } catch (error) {
        if (error.response && error.response.status === 409) {
          alert(error.response.data.message || 'Barang tidak dapat dihapus karena sedang digunakan.');
        } else {
          alert('Gagal menghapus barang.');
          console.error(error);
        }
      }
    },

    getKondisiText(k) {
      const map = { B: 'Baik', R: 'Rusak', P: 'Diperbaiki' };
      return map[k] || '-';
    },

    getStatusText(s) {
      const map = { T: 'Tersedia', D: 'Dipinjam', M: 'Maintenance' };
      return map[s] || '-';
    },

    kondisiClass(k) {
      const map = { B: 'badge-green', R: 'badge-yellow', P: 'badge-red' };
      return map[k] || 'badge-gray';
    },

    statusClass(s) {
      const map = { T: 'badge-blue', D: 'badge-purple', M: 'badge-gray' };
      return map[s] || 'badge-gray';
    }
  }
};
</script>

<style scoped>
/* Mewarisi tema dari AddPinjaman.vue */
.page-container {
  min-height: 100vh;
  background-color: #FDFBF7;
  padding: 24px;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #003049;
}

.content-wrapper {
  max-width: 900px;
  margin: 0 auto;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-section h2 {
  margin: 0 0 6px 0;
  font-size: 22px;
  color: #003049;
}

.header-section p {
  margin: 0;
  color: #718096;
  font-size: 13px;
}

.btn-add {
  background-color: #F77F00;
  color: white;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 13px;
  font-weight: 700;
  transition: background 0.2s;
}

.btn-add:hover {
  background-color: #e67100;
}

/* Styling Tabel */
.table-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  overflow: hidden;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
}

.custom-table thead {
  background-color: #F8F9FA;
  border-bottom: 1px solid #E2E8F0;
}

.custom-table th {
  text-align: left;
  padding: 14px 16px;
  font-size: 12px;
  font-weight: 700;
  color: #003049;
}

.custom-table td {
  padding: 14px 16px;
  font-size: 13px;
  border-bottom: 1px solid #F1F5F9;
}

.custom-table tr:last-child td {
  border-bottom: none;
}

.text-bold {
  font-weight: 600;
}

.empty-state {
  text-align: center;
  color: #A0AEC0;
  padding: 40px 16px;
}

/* Styling Badge */
.badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  display: inline-block;
}

.badge-green { background: rgba(34, 197, 94, 0.1); color: #15803d; }
.badge-yellow { background: rgba(234, 179, 8, 0.1); color: #a16207; }
.badge-red { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
.badge-blue { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
.badge-purple { background: rgba(147, 51, 234, 0.1); color: #6b21a8; }
.badge-gray { background: #E2E8F0; color: #475569; }

/* Styling Tombol Aksi */
.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-action {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-action:hover { opacity: 0.85; }

.btn-edit {
  background-color: #E2E8F0;
  color: #003049;
}

.btn-delete {
  background-color: rgba(239, 68, 68, 0.1);
  color: #b91c1c;
}

/* Styling Modal & Form */
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 24px;
}

.modal-content {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-card {
  background: #ffffff;
  padding: 32px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

.form-header h2 {
  color: #003049;
  margin: 0 0 6px 0;
  font-size: 22px;
}

.form-header p {
  color: #718096;
  font-size: 13px;
  margin-bottom: 24px;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 18px;
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
  border-color: #003049;
}

.error-text {
  color: #b91c1c;
  font-size: 11px;
  margin: 0;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

.btn-save {
  flex: 1;
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  font-size: 13px;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel {
  background-color: transparent;
  color: #718096;
  border: 1px solid #CBD5E0;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 13px;
}
</style>
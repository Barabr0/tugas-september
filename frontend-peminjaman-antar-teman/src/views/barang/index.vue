<template>
  <div class="pinjam-page">
    <div class="main-card">
      <!-- Top Bar / Card Header -->
      <div class="card-top">
        <div class="header-left">
          <router-link to="/dashboard" class="btn-back" title="Kembali ke Beranda">
            <i class="bi bi-arrow-left"></i>
          </router-link>
          <div class="title-group">
            <span class="sub-title">Inventaris Saya</span>
            <h2>Daftar Barang Saya</h2>
          </div>
        </div>
        <router-link to="/barang/tambah" class="btn-add">
          <i class="bi bi-plus-lg"></i> Tambah Barang
        </router-link>
      </div>

      <!-- Table Wrapper -->
      <div class="table-wrapper">
        <table class="custom-table">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">No</th>
              <th style="width: 160px;">Nama Barang</th>
              <th style="width: 120px;">Kategori</th>
              <th>Deskripsi</th>
              <th style="width: 120px;" class="text-center">Kondisi</th>
              <th style="width: 120px;" class="text-center">Status</th>
              <th style="width: 140px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <!-- State Loading -->
            <tr v-if="loading">
              <td colspan="7" class="empty-state">
                <i class="bi bi-arrow-repeat spin"></i>
                <p>Memuat data barang...</p>
              </td>
            </tr>

            <!-- State Empty -->
            <tr v-else-if="barangs.length === 0">
              <td colspan="7" class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada barang. Silakan tambahkan barang baru.</p>
              </td>
            </tr>

            <!-- Data Loop -->
            <tr v-for="(barang, index) in barangs" :key="barang.id" v-else>
              <td class="text-center id-col">#{{ index + 1 }}</td>
              <td class="name-col">{{ barang.nama_barang }}</td>
              <td>
                <span class="tag-kategori-barang">
                  {{ barang.kategori?.nama_kategori || '-' }}
                </span>
              </td>
              <td class="desc-col">{{ barang.deskripsi || '-' }}</td>
              <td class="text-center">
                <span :class="['tag-status', kondisiClass(barang.kondisi)]">
                  {{ getKondisiText(barang.kondisi) }}
                </span>
              </td>
              <td class="text-center">
                <span :class="['tag-status', statusClass(barang.status)]">
                  {{ getStatusText(barang.status) }}
                </span>
              </td>
              <td class="action-col">
                <div class="action-buttons">
                  <button @click="openEditModal(barang)" class="btn-icon edit" title="Edit Barang">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <button @click="handleDelete(barang.id)" class="btn-icon delete" title="Hapus Barang">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Edit Barang -->
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
      const map = { B: 'baik', R: 'rusak', P: 'diperbaiki' };
      return map[k] || 'batal';
    },

    statusClass(s) {
      const map = { T: 'tersedia', D: 'dipinjam', M: 'maintenance' };
      return map[s] || 'batal';
    }
  }
};
</script>

<style scoped>
/* Layout Utama Halaman (Disamakan dengan Peminjaman) */
.pinjam-page {
  background-color: #FDFBF7;
  min-height: 100vh;
  padding: 40px 24px;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  box-sizing: border-box;
}

.main-card {
  background: #ffffff;
  max-width: 1100px;
  margin: 0 auto;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0, 48, 73, 0.06);
  border: 1px solid #E2E8F0;
}

/* Header Atas (Card Top) */
.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #EDF2F7;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-back {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background-color: #F0F4F8;
  color: #003049;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 18px;
  transition: all 0.2s ease;
}

.btn-back:hover {
  background-color: #003049;
  color: #ffffff;
}

.title-group .sub-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #F77F00;
  display: block;
}

.title-group h2 {
  margin: 2px 0 0 0;
  color: #003049;
  font-size: 22px;
  font-weight: 800;
}

.btn-add {
  background-color: #F77F00;
  color: #ffffff;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s ease;
}

.btn-add:hover {
  background-color: #E07300;
}

/* Tabel Custom */
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
  padding: 16px 14px;
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

.name-col {
  font-weight: 700;
  color: #003049;
}

.desc-col {
  color: #4A5568;
  font-size: 13px;
}

.tag-kategori-barang {
  background-color: #F0F4F8;
  color: #003049;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

/* Style Badge Kondisi & Status */
.tag-status {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

/* Kondisi Barang */
.tag-status.baik { background-color: #E8F5E9; color: #2E7D32; }
.tag-status.rusak { background-color: #FFF3E0; color: #E65100; }
.tag-status.diperbaiki { background-color: #FFEBEE; color: #C62828; }

/* Status Barang */
.tag-status.tersedia { background-color: #E3F2FD; color: #1565C0; }
.tag-status.dipinjam { background-color: #F3E5F5; color: #7B1FA2; }
.tag-status.maintenance { background-color: #ECEFF1; color: #455A64; }
.tag-status.batal { background-color: #F0F0F0; color: #999999; }

/* Tombol Aksi */
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

/* Modal Edit Styling */
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
  max-height: 90vh;
  overflow-y: auto;
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

.btn-save:disabled {
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
</style>
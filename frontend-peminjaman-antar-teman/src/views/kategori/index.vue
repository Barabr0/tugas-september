<template>
  <div class="pinjam-page">
    <div class="main-card">
      <!-- Card Top Header -->
      <div class="card-top">
        <div class="header-left">
          <router-link to="/dashboard" class="btn-back" title="Kembali ke Beranda">
            <i class="bi bi-arrow-left"></i>
          </router-link>
          <div class="title-group">
            <span class="sub-title">Pengaturan Sistem</span>
            <h2>Daftar Kategori</h2>
          </div>
        </div>
        <button class="btn-add" @click="openAddModal">
          <i class="bi bi-plus-lg"></i> Tambah Kategori
        </button>
      </div>

      <!-- Table Wrapper -->
      <div class="table-wrapper">
        <table class="custom-table">
          <thead>
            <tr>
              <th style="width: 60px;" class="text-center">No</th>
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

            <!-- State Empty -->
            <tr v-else-if="kategoris.length === 0">
              <td colspan="3" class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada kategori. Silakan tambahkan kategori baru.</p>
              </td>
            </tr>

            <!-- Data Loop -->
            <tr v-for="(kategori, index) in kategoris" :key="kategori.id" v-else>
              <td class="text-center id-col">#{{ index + 1 }}</td>
              <td class="name-col">
                <span class="tag-kategori-item">{{ kategori.nama_kategori }}</span>
              </td>
              <td class="action-col">
                <div class="action-buttons">
                  <button @click="openEditModal(kategori)" class="btn-icon edit" title="Edit Kategori">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <button @click="handleDelete(kategori.id)" class="btn-icon delete" title="Hapus Kategori">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tambah / Edit Kategori -->
    <div v-if="showModal" class="modal-overlay">
      <div class="form-card modal-content">
        <div class="form-header">
          <h2>{{ isEditMode ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
          <p>{{ isEditMode ? 'Perbarui nama kategori barang.' : 'Buat kategori baru untuk mengelompokkan barang.' }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="form-body">
          <div class="form-group">
            <label>Nama Kategori</label>
            <input v-model="form.nama_kategori" type="text" placeholder="Masukkan nama kategori" required class="input-control" />
            <p v-if="errors.nama_kategori" class="error-text">{{ errors.nama_kategori[0] }}</p>
          </div>

          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan Kategori' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import kategoriApi from '@/utils/kategori';

export default {
  name: 'ListKategori',
  data() {
    return {
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
  mounted() {
    this.fetchKategoris();
  },
  methods: {
    async fetchKategoris() {
      this.loading = true;
      try {
        const response = await kategoriApi.getAll();
        this.kategoris = response.data?.data || response.data || [];
      } catch (error) {
        console.error('Gagal mengambil kategori:', error);
        alert('Gagal memuat data kategori.');
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
          alert('Kategori berhasil diperbarui!');
        } else {
          await kategoriApi.addKategori({ nama_kategori: this.form.nama_kategori });
          alert('Kategori berhasil ditambahkan!');
        }
        this.closeModal();
        await this.fetchKategoris();
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          alert('Terjadi kesalahan saat menyimpan kategori.');
          console.error(error);
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
        alert('Kategori berhasil dihapus.');
      } catch (error) {
        if (error.response && error.response.status === 409) {
          alert(error.response.data.message || 'Kategori tidak dapat dihapus karena masih dipakai barang.');
        } else {
          alert('Gagal menghapus kategori.');
          console.error(error);
        }
      }
    }
  }
};
</script>

<style scoped>
/* Page Layout Disamakan Persis dengan Peminjaman & Barang */
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

/* Header Top */
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
  border: none;
  cursor: pointer;
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

.tag-kategori-item {
  color: #003049;
  font-weight: 700;
  font-size: 14px;
}

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

/* Styling Modal Edit / Tambah */
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
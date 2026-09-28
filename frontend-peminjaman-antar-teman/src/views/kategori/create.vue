<template>
  <div class="add-container">
    <div class="form-card">
      <div class="form-header">
        <h2>Tambah Kategori Baru</h2>
        <p>Tambahkan kategori baru untuk mengelompokkan barang Anda.</p>
      </div>

      <form @submit.prevent="saveKategori" class="form-body">
        <div class="form-group">
          <label for="kategori">Nama Kategori</label>
          <input
            id="kategori"
            v-model="form.nama_kategori"
            type="text"
            placeholder="Contoh: Elektronik, Buku, Alat Tulis"
            required
            class="input-control"
          />
          <p v-if="errors.nama_kategori" class="error-text">{{ errors.nama_kategori[0] }}</p>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="cancel">Batal</button>
          <button type="submit" class="btn-save" :disabled="loading">
            {{ loading ? 'Menyimpan...' : 'Simpan Kategori' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import kategoriApi from '@/utils/kategori';

export default {
  name: 'AddKategori',
  data() {
    return {
      form: {
        nama_kategori: ''
      },
      loading: false,
      errors: {}
    };
  },
  methods: {
    async saveKategori() {
      this.loading = true;
      this.errors = {};
      
      try {
        // Panggil metode addKategori dari utils
        await kategoriApi.addKategori(this.form);
        
        alert('Kategori berhasil ditambahkan!');
        this.$router.push('/kategori'); 
      } catch (error) {
        if (error.response && error.response.status === 422) {
          // Tangkap error validasi dari backend Laravel
          this.errors = error.response.data.errors || {};
        } else {
          console.error('Gagal menyimpan kategori:', error);
          alert('Terjadi kesalahan saat menyimpan kategori.');
        }
      } finally {
        this.loading = false;
      }
    },
    cancel() {
      this.$router.push('/kategori');
    }
  }
};
</script>

<style scoped>
.add-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #FDFBF7;
  padding: 24px;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.form-card {
  background: #ffffff;
  width: 100%;
  max-width: 500px;
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